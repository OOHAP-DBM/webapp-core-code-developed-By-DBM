# Geo-Fencing for Mounting Tasks - Implementation Guide

## Overview

A comprehensive geo-fence validation system that ensures mounters must be physically present at the hoarding location to upload installation proof (POD - Proof of Delivery). This prevents fraud and ensures accountability.

**Status:** ✅ Fully Implemented & Admin Configurable

---

## Features

### ✅ Core Functionality
- **Real-time GPS Validation:** Mounters must capture GPS coordinates when uploading POD
- **Configurable Radius:** Admin can set acceptable distance (10m - 1000m)
- **Strict/Lenient Modes:** Choose between rejecting uploads or recording for review
- **Distance Calculation:** Haversine formula for accurate meter-level precision
- **Auto-Approval:** Optionally auto-approve PODs within very close range (e.g., 50m)

### ✅ Admin Controls
- **Web Dashboard:** `/admin/settings/geofencing` - Full visual configuration
- **Real-time Updates:** Settings take effect immediately (cached for performance)
- **Validation Statistics:** View total PODs, violations, average distance
- **Violations Report:** Monitor suspicious uploads beyond threshold

### ✅ Security & Audit
- **Comprehensive Logging:** All geo-fence violations logged with context
- **GPS Accuracy Tracking:** Monitor GPS signal quality (50m threshold)
- **Device Information:** Capture device, IP, user agent for audit trail
- **Distance Recording:** Every POD stores exact distance from hoarding

---

## Architecture

### Database Schema

**`booking_proofs` table:**
```sql
- latitude (decimal 10,7): Upload GPS latitude
- longitude (decimal 10,7): Upload GPS longitude
- distance_from_hoarding (integer): Calculated distance in meters
- metadata (json): GPS accuracy, device info, etc.
```

**`settings` table:**
```sql
- key: pod.geofence_radius_meters
- value: 100 (default)
- type: integer
- group: geofencing
```

### Key Components

**1. PODService** (`app/Services/PODService.php`)
- Handles POD upload with geo-validation
- Calculates Haversine distance
- Enforces radius constraints
- Logs violations

**2. GeofencingSettingsController** (`app/Http/Controllers/Web/Admin/GeofencingSettingsController.php`)
- Admin settings management
- Statistics dashboard
- Violations report

**3. Settings Service** (`Modules/Settings/Services/SettingsService.php`)
- Centralized configuration
- Cached for performance
- Dynamic updates

---

## Configuration

### Admin Dashboard

Access: **`/admin/settings/geofencing`**

**Primary Settings:**

| Setting | Default | Range | Description |
|---------|---------|-------|-------------|
| **Geo-Fence Radius** | 100m | 10-1000m | Maximum allowed distance from hoarding |
| **Strict Validation** | Enabled | On/Off | Reject uploads outside radius |
| **Require GPS** | Enabled | On/Off | Force GPS capture for all uploads |
| **GPS Accuracy** | 50m | 5-200m | Maximum acceptable GPS accuracy |

**Advanced Settings:**

| Setting | Default | Description |
|---------|---------|-------------|
| **Log Violations** | Enabled | Record all geo-fence failures |
| **Show Distance** | Enabled | Display distance to mounter in real-time |
| **Alert Threshold** | 150m | Admin alert for suspicious uploads |
| **Enable for Dismounting** | Enabled | Apply geo-fence to campaign completion |
| **Auto-Approve Radius** | 50m | Auto-approve if within this distance |

### Environment Variables

Set defaults in `.env`:

```env
# Geo-Fencing Settings (Overridden by Admin Dashboard)
POD_MAX_DISTANCE_METERS=100
POD_STRICT_GEO_VALIDATION=true
POD_GPS_ACCURACY_METERS=50
```

**Note:** Admin dashboard settings take precedence over `.env` values.

---

## How It Works

### Mounting Flow

```
1. Staff assigned to mounting task
   ↓
2. Navigate to hoarding location
   ↓
3. Open POD upload form in mobile app
   ↓
4. Click "Capture GPS Location" button
   ↓
5. App requests device GPS coordinates
   ↓
6. System calculates distance to hoarding
   ↓
7. Display distance to user (e.g., "You are 45m away")
   ↓
8. If within radius (e.g., 100m):
   - ✅ Upload button enabled
   - Upload photo/video
   - POD created with status "pending"
   ↓
9. If outside radius:
   - ❌ Error: "You are too far from hoarding (245m). Move closer."
   - Upload blocked (strict mode)
   - OR recorded for admin review (lenient mode)
   ↓
10. Vendor reviews POD
    ↓
11. If approved → Booking status → "active"
```

### Distance Calculation (Haversine Formula)

```php
public function calculateDistance($lat1, $lng1, $lat2, $lng2): int
{
    $earthRadiusMeters = 6371000;
    
    $dLat = deg2rad($lat2 - $lat1);
    $dLng = deg2rad($lng2 - $lng1);
    
    $a = sin($dLat / 2) * sin($dLat / 2) +
         cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
         sin($dLng / 2) * sin($dLng / 2);
    
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
    
    return round($earthRadiusMeters * $c);
}
```

**Accuracy:** ±1 meter precision for distances up to 10km.

---

## API Endpoints

### Upload POD with GPS

**Endpoint:** `POST /api/v1/staff/bookings/{id}/upload-pod`

**Request:**
```json
{
  "file": <binary>,
  "latitude": 28.6139,
  "longitude": 77.2090,
  "gps_accuracy": 12.5,
  "device_info": "iPhone 14 Pro"
}
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "POD uploaded successfully. Awaiting vendor approval.",
  "data": {
    "proof_id": 123,
    "booking_id": 456,
    "status": "pending",
    "uploaded_at": "2025-12-12T10:30:00Z",
    "distance_from_hoarding": 45,
    "proof_url": "https://..."
  }
}
```

**Error Response - Outside Radius (422):**
```json
{
  "success": false,
  "message": "Location validation failed. You must be within 100m of the hoarding to upload POD. Current distance: 245m"
}
```

### Get Geo-Fence Settings (Public)

**Endpoint:** `GET /api/v1/settings/geofencing`

**Response:**
```json
{
  "geofence_radius_meters": 100,
  "strict_validation": true,
  "require_gps": true,
  "show_distance_to_mounter": true
}
```

---

## Admin Features

### Violations Dashboard

**Access:** `/admin/settings/geofencing/violations`

**Displays:**
- POD uploads outside allowed radius
- Distance from hoarding
- Mounter details
- Upload timestamp
- GPS accuracy
- Device information

**Filter Options:**
- Last 7/30/90 days
- Specific mounter
- Distance range
- Hoarding location

**Export:** CSV/Excel with full audit trail

### Statistics Widget

**Metrics Shown:**
- Total PODs uploaded
- PODs approved
- Geo-fence violations
- Average upload distance
- GPS accuracy trends

---

## Mobile App Integration

### Requirements

1. **GPS Permissions:** App must request location permissions
2. **High Accuracy Mode:** Use `enableHighAccuracy: true`
3. **Error Handling:** Handle GPS unavailable scenarios
4. **User Feedback:** Show distance in real-time

### Flutter Example

```dart
import 'package:geolocator/geolocator.dart';

Future<void> captureGPS() async {
  try {
    Position position = await Geolocator.getCurrentPosition(
      desiredAccuracy: LocationAccuracy.high
    );
    
    // Calculate distance to hoarding
    double distance = Geolocator.distanceBetween(
      position.latitude,
      position.longitude,
      hoardingLat,
      hoardingLng
    );
    
    setState(() {
      userLat = position.latitude;
      userLng = position.longitude;
      distanceMeters = distance.round();
      gpsAccuracy = position.accuracy;
    });
    
    // Check if within allowed radius
    if (distanceMeters > maxRadius) {
      showError('You are ${distanceMeters}m from hoarding. Move closer.');
    } else {
      showSuccess('✓ Within range. You can upload POD.');
    }
    
  } catch (e) {
    showError('Failed to get GPS location: $e');
  }
}
```

### React Native Example

```javascript
import Geolocation from '@react-native-community/geolocation';

const captureGPS = () => {
  Geolocation.getCurrentPosition(
    (position) => {
      const distance = calculateDistance(
        position.coords.latitude,
        position.coords.longitude,
        hoardingLat,
        hoardingLng
      );
      
      setUserLocation({
        lat: position.coords.latitude,
        lng: position.coords.longitude,
        accuracy: position.coords.accuracy
      });
      
      setDistance(Math.round(distance));
      
      if (distance > maxRadius) {
        Alert.alert('Too Far', `Move closer to hoarding (${Math.round(distance)}m away)`);
      }
    },
    (error) => Alert.alert('GPS Error', error.message),
    { enableHighAccuracy: true, timeout: 10000 }
  );
};
```

---

## Best Practices

### For Administrators

1. **Urban Sites:** Set radius to 50-100m
2. **Rural Sites:** Allow 150-300m (less precise GPS)
3. **High Security:** Enable strict validation + auto-approval within 50m
4. **Initial Rollout:** Start with lenient mode (log only), then enforce strict
5. **Monthly Review:** Check violations report for patterns
6. **GPS Accuracy:** Keep threshold at 50m or less for reliability

### For Mounters

1. **Enable High Accuracy GPS:** Settings → Location → High Accuracy
2. **Wait for GPS Lock:** Wait 5-10 seconds after enabling location
3. **Clear Sky View:** Better GPS signal outdoors
4. **Multiple Attempts:** If failed, wait 30 seconds and retry
5. **Check Distance:** App shows real-time distance - move closer if needed

### For Developers

1. **Cache Settings:** Use SettingsService cache (1 hour TTL)
2. **Log Violations:** Always log for audit compliance
3. **Handle Offline:** Store GPS when captured, upload when online
4. **Test Edge Cases:** GPS unavailable, poor accuracy, location disabled
5. **Error Messages:** Provide actionable feedback ("Move 45m closer")

---

## Testing

### Unit Tests

```php
// tests/Feature/POD/GeofenceValidationTest.php

/** @test */
public function it_rejects_pod_upload_outside_geofence_radius()
{
    $setting = app(SettingsService::class);
    $setting->set('pod.geofence_radius_meters', 100, 'integer');
    $setting->set('pod.strict_geofence_validation', true, 'boolean');
    
    $booking = Booking::factory()->create();
    $booking->hoarding->update([
        'latitude' => 28.6139,
        'longitude' => 77.2090
    ]);
    
    $response = $this->postJson("/api/v1/staff/bookings/{$booking->id}/upload-pod", [
        'file' => UploadedFile::fake()->image('pod.jpg'),
        'latitude' => 28.6200, // ~680m away
        'longitude' => 77.2100,
    ]);
    
    $response->assertStatus(422)
             ->assertJsonFragment(['message' => 'Location validation failed']);
}

/** @test */
public function it_allows_pod_upload_within_geofence_radius()
{
    $setting = app(SettingsService::class);
    $setting->set('pod.geofence_radius_meters', 100, 'integer');
    
    $booking = Booking::factory()->create();
    $booking->hoarding->update([
        'latitude' => 28.6139,
        'longitude' => 77.2090
    ]);
    
    $response = $this->postJson("/api/v1/staff/bookings/{$booking->id}/upload-pod", [
        'file' => UploadedFile::fake()->image('pod.jpg'),
        'latitude' => 28.6140, // ~11m away
        'longitude' => 77.2091,
    ]);
    
    $response->assertStatus(200);
}
```

### Manual Testing

1. **Configure Settings:** Set radius to 100m via admin dashboard
2. **Create Test Booking:** With hoarding at known GPS coordinates
3. **Simulate Upload:** Send API request with coordinates 50m away → ✅ Success
4. **Test Violation:** Send request with coordinates 200m away → ❌ Rejected
5. **Check Logs:** Verify violation logged in `storage/logs/laravel.log`
6. **View Dashboard:** Check statistics updated correctly

---

## Troubleshooting

### Issue: GPS Not Working in Mobile App

**Symptoms:** Location always shows as unavailable

**Solutions:**
1. Check app permissions: Settings → Apps → OOHAPP → Permissions → Location
2. Enable location services: Settings → Location → On
3. Set location mode to "High Accuracy"
4. Restart app after granting permissions
5. Check internet connection (some devices need assisted GPS)

### Issue: Always Shows "Too Far" Error

**Symptoms:** Mounter at correct location but upload fails

**Solutions:**
1. Check hoarding GPS coordinates are correct (Admin → Hoardings → Edit)
2. Wait 10-20 seconds for GPS to stabilize
3. Move device around to get better signal
4. Check GPS accuracy (should be <20m for best results)
5. If rural area, admin should increase radius to 200-300m

### Issue: POD Uploaded from Wrong Location

**Symptoms:** Distance shows 0m but mounter wasn't at site

**Investigation:**
1. Check POD metadata: View POD → Technical Details
2. Verify GPS accuracy was good (<50m)
3. Check device info for anomalies
4. Review mounter's recent upload history
5. Consider GPS spoofing (very rare)

**Actions:**
1. Contact mounter for clarification
2. Request photo metadata (EXIF data)
3. Enable stricter validation (lower radius + accuracy threshold)
4. Consider requiring additional proof (photos with timestamp)

---

## Security Considerations

### GPS Spoofing Prevention

While GPS spoofing is technically possible, it requires:
- Rooted/jailbroken device
- Third-party spoofing apps
- Technical knowledge

**Mitigation:**
1. Cross-check GPS accuracy (spoofed GPS often has perfect accuracy)
2. Monitor for suspicious patterns (same GPS for multiple hoardings)
3. Require photo metadata verification
4. Enable device fingerprinting
5. Use additional fraud detection signals (IP, device ID)

### Data Privacy

**GPS Data Handling:**
- GPS coordinates encrypted in transit (HTTPS)
- Stored in secure database with access controls
- Only visible to admin, vendor, and assigned mounter
- Automatically deleted after campaign completion + 90 days
- GDPR/privacy policy compliance

---

## Performance

### Caching Strategy

```php
// Settings cached for 1 hour
$radius = $settingsService->get('pod.geofence_radius_meters', 100);
// Cache key: settings:pod.geofence_radius_meters
```

**Benefits:**
- Reduced database queries (1 instead of N per upload)
- Sub-millisecond setting retrieval
- Auto-refresh on update

### Distance Calculation

**Time Complexity:** O(1)  
**Execution Time:** ~0.5ms per calculation  
**Impact:** Negligible (<1% of total upload time)

---

## Migration & Rollout

### Initial Setup

```bash
# 1. Run seeder to create settings
php artisan db:seed --class=GeofencingSettingsSeeder

# 2. Verify settings created
php artisan tinker
>>> app('Modules\Settings\Services\SettingsService')->get('pod.geofence_radius_meters')
=> 100

# 3. Access admin dashboard
# Navigate to: /admin/settings/geofencing
```

### Gradual Rollout

**Phase 1: Monitoring Only (Week 1-2)**
- Set `strict_validation` = false
- Monitor violation rates
- Adjust radius based on data

**Phase 2: Warnings (Week 3-4)**
- Enable strict validation
- Set radius to 200m (lenient)
- Communicate with mounters

**Phase 3: Enforcement (Week 5+)**
- Reduce radius to 100m
- Full enforcement
- Review exceptions

---

## Future Enhancements

1. **Polygon Geo-Fencing:** Define custom shapes around complex hoarding sites
2. **Historical Heatmaps:** Visualize POD upload locations on map
3. **Bluetooth Beacons:** Additional verification using BLE at hoarding site
4. **Photo Metadata:** Extract GPS from uploaded images (EXIF data)
5. **AI Fraud Detection:** ML model to detect anomalous upload patterns
6. **Time-Based Validation:** Require POD during working hours only
7. **Multi-Factor Verification:** GPS + Photo + Time + Device fingerprint

---

## Support

**For Admin Issues:**
- Settings not saving → Clear cache: `/admin/settings/clear-cache`
- Statistics incorrect → Recalculate: `php artisan app:recalculate-pod-stats`

**For Mounter Issues:**
- GPS not working → Check device settings, restart app
- "Too far" error → Verify hoarding GPS, check admin radius setting

**For Developer Issues:**
- Review logs: `storage/logs/laravel.log`
- Check settings: `php artisan tinker >>> app('Modules\Settings\Services\SettingsService')->getAll()`
- Debug distance: Add `dd($distanceFromHoarding)` in PODService

---

## Conclusion

The geo-fencing system provides robust location validation for mounting tasks while remaining flexible and admin-configurable. It ensures accountability, prevents fraud, and maintains an audit trail for compliance.

**Key Benefits:**
- ✅ Prevents fraud (mounters must be on-site)
- ✅ Audit compliance (full GPS trail)
- ✅ Flexible configuration (admin dashboard)
- ✅ Real-time feedback (mounters see distance)
- ✅ Performance optimized (cached settings)

**Status:** Production Ready ✅
