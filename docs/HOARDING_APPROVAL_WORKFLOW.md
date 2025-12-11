# Hoarding Approval Workflow Documentation

## Overview
The Hoarding Approval Workflow system provides a comprehensive multi-stage approval process for vendor-submitted hoardings. It includes versioning, auto-approval for trusted vendors, checklist-based verification, SLA monitoring, and complete audit logging.

---

## Table of Contents
1. [Workflow Stages](#workflow-stages)
2. [Database Schema](#database-schema)
3. [Core Components](#core-components)
4. [Auto-Approval System](#auto-approval-system)
5. [Version Control](#version-control)
6. [Admin Dashboard](#admin-dashboard)
7. [API Endpoints](#api-endpoints)
8. [Configuration](#configuration)
9. [Usage Examples](#usage-examples)

---

## Workflow Stages

### 1. Draft
- **Initial State**: Hoarding created but not submitted
- **Vendor Actions**: Edit, delete, submit for approval
- **System State**: Not visible to customers

### 2. Pending
- **Trigger**: Vendor submits for approval
- **Auto-Check**: System evaluates auto-approval eligibility
- **Admin Actions**: Assign to verifier, start verification
- **SLA**: Should be picked up within 24 hours

### 3. Under Verification
- **Trigger**: Admin starts verification process
- **Process**: Admin reviews checklist items, compares with standards
- **Admin Actions**: Approve, reject, request changes
- **SLA**: Should be completed within 48 hours from submission

### 4. Approved
- **Final State**: Hoarding passes all checks
- **System State**: Available for customer bookings
- **Auto-Actions**: Updates status to 'available', creates sitemap entry
- **Re-Verification**: Triggered on critical field edits

### 5. Rejected
- **Final State**: Hoarding fails verification
- **Vendor Actions**: Review rejection reasons, edit and resubmit
- **System State**: Inactive, not bookable
- **Rejection Reasons**: Provided via templates (categories: location, images, pricing, etc.)

---

## Database Schema

### 1. Hoardings Table Enhancement
```sql
- approval_status: ENUM('draft', 'pending', 'under_verification', 'approved', 'rejected')
- current_version: INTEGER (default 1)
- submitted_at: TIMESTAMP
- verified_at: TIMESTAMP
- approved_at: TIMESTAMP
- rejected_at: TIMESTAMP
- verified_by: Foreign Key (admins)
- approved_by: Foreign Key (admins)
- rejected_by: Foreign Key (admins)
- rejection_reason: TEXT
- admin_notes: TEXT
- needs_reverification: BOOLEAN (default false)
```

### 2. Hoarding Versions
**Purpose**: Complete snapshot of hoarding data for each version

**Key Fields**:
- `version_number`: Sequential version tracking
- `change_type`: created/edited/resubmitted/auto_approved
- `change_summary`: Human-readable description
- `changed_fields`: JSON array of modified fields
- All hoarding data fields (26 total)
- Version-specific approval metadata

**Use Cases**:
- Compare versions side-by-side
- Audit trail for all changes
- Rollback capability
- Dispute resolution

### 3. Hoarding Approval Logs
**Purpose**: Comprehensive audit trail

**Action Types** (12 total):
- submitted, verification_started, approved, rejected
- resubmitted, edited, auto_approved, checklist_updated
- assigned, unassigned, notes_added, status_changed

**Tracked Data**:
- Status transitions (from → to)
- Performer (admin_id) and role
- Metadata: IP address, device, timestamp
- Version association

### 4. Hoarding Verification Assignments
**Purpose**: Admin workload management

**Features**:
- Priority levels: urgent (1), high (2), normal (3), low (4)
- Assignment status: assigned → in_progress → completed
- Due date tracking
- Assignment history per hoarding

**Use Cases**:
- Distribute workload among admins
- Track pending assignments
- Calculate admin performance metrics

### 5. Hoarding Approval Checklists
**Purpose**: Quality control verification

**Default Items** (6 total):
1. Location details verified
2. Images quality check
3. Pricing competitive
4. Specifications accurate
5. Legal compliance
6. Safety standards

**Item States**:
- pending: Not yet reviewed
- passed: Meets requirements
- failed: Does not meet requirements
- na: Not applicable

**Features**:
- Per-version checklist
- Admin notes per item
- Verified timestamp

### 6. Hoarding Rejection Templates
**Purpose**: Standardized rejection messaging

**Categories** (7 total):
1. Location Issues
2. Image Quality
3. Pricing Concerns
4. Specification Errors
5. Legal/Compliance
6. Safety Violations
7. Other

**Template Fields**:
- Title and message
- Category grouping
- Suggested vendor actions (JSON array)
- Usage count tracking

**Benefits**:
- Consistent communication
- Time-saving for admins
- Clear vendor guidance
- Analytics on rejection reasons

### 7. Hoarding Approval Settings
**Purpose**: Workflow configuration

**Configurable Parameters**:
```json
{
  "auto_approve_trusted_vendors": false,
  "trusted_vendor_rating_threshold": 4.5,
  "trusted_vendor_min_approved_hoardings": 10,
  "verification_sla_hours": 48,
  "approval_sla_hours": 24,
  "require_checklist_completion": true,
  "auto_reject_after_days": null
}
```

---

## Core Components

### HoardingApprovalService

**Location**: `app/Services/HoardingApprovalService.php`

#### Main Methods

##### 1. `submitForApproval($hoarding, $vendor)`
Handles vendor submission for approval.

**Process**:
1. Creates version snapshot (version 1 or increments)
2. Checks auto-approval eligibility
3. Updates status to 'pending' or 'approved'
4. Logs submission action
5. Returns result array

**Returns**:
```php
[
    'success' => true,
    'message' => 'Hoarding submitted for approval',
    'version' => 1,
    'status' => 'pending',
    'auto_approved' => false
]
```

##### 2. `startVerification($hoarding, $admin)`
Admin begins the verification process.

**Process**:
1. Updates status to 'under_verification'
2. Creates default checklist items
3. Logs verification start
4. Records admin assignment

##### 3. `approve($hoarding, $admin, $notes = null)`
Admin approves the hoarding.

**Validations**:
- All checklist items must be completed
- No failed checklist items allowed

**Process**:
1. Updates approval_status to 'approved'
2. Sets status to 'available'
3. Records approval timestamp and admin
4. Updates version record
5. Logs approval action
6. Triggers sitemap update (if SEO enabled)

##### 4. `reject($hoarding, $admin, $reason, $templateIds = [])`
Admin rejects the hoarding.

**Process**:
1. Builds rejection message from templates
2. Updates approval_status to 'rejected'
3. Sets status to 'inactive'
4. Records rejection details
5. Increments template usage counts
6. Logs rejection action

##### 5. `handleEdit($hoarding, $oldData, $newData, $vendor)`
Detects changes and triggers re-verification if needed.

**Critical Fields** (13 total):
- location_name, address, city, state, pincode
- latitude, longitude, width, height
- board_type, is_lit, price_per_month, images

**Logic**:
- If critical fields changed → requires re-verification
- Increments version number
- Updates approval_status to 'pending'
- Creates new version snapshot

**Returns**:
```php
[
    'requires_verification' => true,
    'changed_fields' => ['price_per_month', 'images'],
    'new_version' => 2
]
```

##### 6. `getStatistics($period = 'today')`
Returns approval workflow metrics.

**Periods**: today, week, month, all

**Returns**:
```php
[
    'pending_count' => 15,
    'under_verification_count' => 8,
    'approved_today' => 12,
    'rejected_today' => 3,
    'average_approval_time_hours' => 18.5,
    'sla_breaches' => 2
]
```

---

### HoardingApprovalController

**Location**: `app/Http/Controllers/Admin/HoardingApprovalController.php`

#### Routes & Methods

##### 1. `GET /admin/approvals` - Dashboard
**Method**: `index(Request $request)`

**Query Parameters**:
- `status`: Filter by approval status
- `period`: Statistics period (today/week/month)

**Returns**: Dashboard view with:
- Statistics cards (4 metrics)
- Pending approvals list
- SLA breach alerts
- Recent activity timeline

##### 2. `GET /admin/approvals/{id}` - Detail View
**Method**: `show($id)`

**Returns**: Hoarding detail page with:
- Complete hoarding information
- Version history table
- Approval logs timeline
- Checklist items (interactive)
- Rejection templates (grouped by category)
- Similar hoardings for comparison

##### 3. `POST /admin/approvals/{id}/start-verification`
**Method**: `startVerification($id)`

**Action**: Marks hoarding as under verification

##### 4. `POST /admin/approvals/{id}/checklist` - AJAX
**Method**: `updateChecklist(Request $request, $id)`

**Request Body**:
```json
{
  "item": "location_verified",
  "status": "passed",
  "notes": "Location confirmed via Google Maps"
}
```

##### 5. `POST /admin/approvals/{id}/approve`
**Method**: `approve(Request $request, $id)`

**Request Body**:
```json
{
  "notes": "All checks passed. Excellent hoarding."
}
```

**Validation**: All checklist items must be completed

##### 6. `POST /admin/approvals/{id}/reject`
**Method**: `reject(Request $request, $id)`

**Request Body**:
```json
{
  "template_ids": [1, 3, 5],
  "custom_reason": "Additional notes..."
}
```

**Process**: Combines template messages with custom notes

##### 7. `GET /admin/approvals/{id}/versions/{v1}/{v2}`
**Method**: `compareVersions($id, $version1, $version2)`

**Returns**: Side-by-side comparison of 14 fields

##### 8. `POST /admin/approvals/bulk-approve`
**Method**: `bulkApprove(Request $request)`

**Request Body**:
```json
{
  "hoarding_ids": [1, 2, 3, 4, 5]
}
```

**Use Case**: Approve multiple hoardings at once

---

### HoardingObserver

**Location**: `app/Observers/HoardingObserver.php`

**Purpose**: Automatic lifecycle event tracking

#### Events Handled

##### 1. `creating()`
Sets initial approval_status to 'draft'

##### 2. `updating()`
**Critical Field Detection**:
- Compares 13 critical fields
- Sets `needs_reverification = true` if changed
- Triggers version increment on save

##### 3. `created()`
Logs hoarding creation in approval_logs

##### 4. `deleting()`
**Safety Check**: Prevents deletion if active bookings exist

##### 5. `deleted()`
Logs deletion event

---

## Auto-Approval System

### Eligibility Criteria
A vendor qualifies for auto-approval if:
1. **Rating**: ≥ 4.5 stars (configurable)
2. **Approved Hoardings**: ≥ 10 previously approved (configurable)
3. **Auto-Approval Enabled**: Setting `auto_approve_trusted_vendors = true`

### Process Flow
```
Vendor submits → Check eligibility → If eligible:
  - Skip manual verification
  - Immediately approve
  - Log as 'auto_approved'
  - Set status to 'available'
  - Version marked as auto_approved
```

### Benefits
- Faster time-to-market for trusted vendors
- Reduced admin workload
- Incentive for vendor quality
- Maintains audit trail

### Configuration
Edit in Admin Settings:
```php
'trusted_vendor_rating_threshold' => 4.5,
'trusted_vendor_min_approved_hoardings' => 10,
'auto_approve_trusted_vendors' => true
```

---

## Version Control

### When Versions Are Created
1. **Initial Submission**: Version 1 created
2. **Critical Field Edits**: New version after approval
3. **Resubmission**: After rejection and edit

### Version Data Snapshot (26 fields)
- All hoarding details (location, size, price, etc.)
- All metadata (amenities, target audience, etc.)
- Images array
- Change tracking (type, summary, fields)

### Comparison Features
- Side-by-side view of any two versions
- Highlighted differences (14 comparable fields)
- Useful for disputes or audits

### Version States
Each version tracks:
- Approval status at that version
- Admin who approved/rejected
- Timestamps for all actions

---

## Admin Dashboard

### Access
**URL**: `/admin/approvals`

**Middleware**: `auth`, `admin`

### Dashboard Components

#### 1. Statistics Cards (4 metrics)
- **Pending Review**: Count with warning badge
- **Under Verification**: Count with info badge
- **Approved Today**: Count with success badge
- **SLA Breaches**: Count with danger badge

#### 2. SLA Breach Alert
- Displayed if any breaches detected
- Lists hoardings exceeding 48h verification SLA
- Red alert banner with count

#### 3. Filter Controls
- Status dropdown (all/pending/under_verification/approved/rejected)
- Period selector (today/week/month/all)
- Real-time filtering

#### 4. Pending Approvals Table
**Columns**:
- Bulk select checkbox
- Hoarding ID
- Location & City
- Vendor Name
- Type & Size
- Price
- Status Badge
- Waiting Time
- Review Button

**Row Colors**:
- Red background: Waiting > 48 hours (SLA breach)
- Yellow background: Waiting > 24 hours
- White: Normal

#### 5. Bulk Actions
- Select All checkbox
- Bulk Approve button (selected items)
- Confirmation dialog

#### 6. Recent Activity Timeline
- Last 20 approval actions
- Color-coded by action type
- Timestamps and performer names

---

## API Endpoints

### Admin Routes
All routes prefixed with `/admin/approvals`

| Method | Endpoint | Action | Middleware |
|--------|----------|--------|------------|
| GET | `/` | Dashboard | auth, admin |
| GET | `/{id}` | Hoarding Detail | auth, admin |
| POST | `/{id}/start-verification` | Start Verification | auth, admin |
| POST | `/{id}/checklist` | Update Checklist (AJAX) | auth, admin |
| POST | `/{id}/approve` | Approve Hoarding | auth, admin |
| POST | `/{id}/reject` | Reject Hoarding | auth, admin |
| GET | `/{id}/versions/{v1}/{v2}` | Compare Versions | auth, admin |
| POST | `/bulk-approve` | Bulk Approve | auth, admin |
| POST | `/{id}/assign` | Assign to Admin | auth, admin |
| GET | `/export` | Export CSV | auth, admin |
| GET | `/templates/manage` | Manage Templates | auth, admin |
| POST | `/templates` | Create Template | auth, admin |
| GET | `/settings/manage` | Approval Settings | auth, admin |
| POST | `/settings` | Save Settings | auth, admin |

---

## Configuration

### Settings Management
**Access**: `/admin/approvals/settings/manage`

### Available Settings

```php
[
    // Auto-Approval
    'auto_approve_trusted_vendors' => false,
    'trusted_vendor_rating_threshold' => 4.5,
    'trusted_vendor_min_approved_hoardings' => 10,
    
    // SLA Timings (hours)
    'verification_sla_hours' => 48,
    'approval_sla_hours' => 24,
    
    // Quality Control
    'require_checklist_completion' => true,
    
    // Auto-Rejection (optional)
    'auto_reject_after_days' => null // null = disabled
]
```

### Editing Settings
1. Navigate to Settings page
2. Update values in form
3. Click "Save Settings"
4. Changes apply immediately to new submissions

---

## Usage Examples

### Example 1: Vendor Submits Hoarding

```php
use App\Services\HoardingApprovalService;

$approvalService = app(HoardingApprovalService::class);

// Vendor submits
$result = $approvalService->submitForApproval($hoarding, $vendor);

if ($result['auto_approved']) {
    // Immediately available for booking
    echo "Congratulations! Your hoarding was auto-approved.";
} else {
    // Pending admin review
    echo "Submitted for approval. You'll be notified within 48 hours.";
}
```

### Example 2: Admin Approves Hoarding

```php
// Admin starts verification
$approvalService->startVerification($hoarding, $admin);

// Admin updates checklist
$approvalService->updateChecklistItem(
    $hoarding,
    'location_verified',
    'passed',
    $admin,
    'Location confirmed via Google Street View'
);

// Admin approves
$approvalService->approve($hoarding, $admin, 'All checks passed. Excellent hoarding.');

// Hoarding now available for booking
```

### Example 3: Admin Rejects Hoarding

```php
// Use rejection templates
$templateIds = [1, 3]; // Location Issues + Pricing Concerns

$approvalService->reject(
    $hoarding,
    $admin,
    'Please update location details and adjust pricing.',
    $templateIds
);

// Vendor receives combined rejection message
// Hoarding status set to inactive
```

### Example 4: Vendor Edits Approved Hoarding

```php
// Hoarding Observer detects change
$oldData = $hoarding->getOriginal();
$newData = $hoarding->getAttributes();

$result = $approvalService->handleEdit($hoarding, $oldData, $newData, $vendor);

if ($result['requires_verification']) {
    echo "Your changes require re-approval.";
    echo "Changed fields: " . implode(', ', $result['changed_fields']);
    echo "New version: " . $result['new_version'];
}
```

### Example 5: Get Dashboard Statistics

```php
$stats = $approvalService->getStatistics('week');

echo "Pending: " . $stats['pending_count'];
echo "Under Review: " . $stats['under_verification_count'];
echo "Approved This Week: " . $stats['approved_today'];
echo "Average Approval Time: " . $stats['average_approval_time_hours'] . " hours";
echo "SLA Breaches: " . $stats['sla_breaches'];
```

---

## Migration & Setup

### Run Migration
```bash
php artisan migrate
```

This creates:
- 7 new tables
- Adds fields to hoardings table
- Inserts default rejection templates
- Sets initial approval settings

### Register Observer
In `app/Providers/AppServiceProvider.php`:
```php
use App\Models\Hoarding;
use App\Observers\HoardingObserver;

public function boot()
{
    Hoarding::observe(HoardingObserver::class);
}
```

### Seed Default Data (Optional)
```bash
php artisan db:seed --class=ApprovalWorkflowSeeder
```

Seeds:
- Rejection templates (7 categories)
- Checklist items (6 defaults)
- Approval settings

---

## Best Practices

### For Admins
1. **Review SLA Dashboard Daily**: Check for breaches
2. **Use Checklist Diligently**: Ensure quality control
3. **Provide Clear Rejection Reasons**: Use templates + custom notes
4. **Compare Versions**: Review change history for re-submissions
5. **Assign Workload Evenly**: Use assignment system

### For Vendors
1. **Complete All Details**: Before submitting
2. **Use Quality Images**: Minimum 3 clear photos
3. **Set Competitive Pricing**: Check similar hoardings
4. **Read Rejection Reasons**: Carefully before resubmitting
5. **Build Trust**: Maintain high rating for auto-approval

### For Developers
1. **Always Use Service Methods**: Don't update approval_status directly
2. **Log All Actions**: Use `logAction()` method
3. **Handle Versions**: Create snapshots for critical changes
4. **Check SLA**: Monitor breach counts
5. **Test Auto-Approval**: Ensure criteria work correctly

---

## Troubleshooting

### Issue: Hoarding Stuck in "Pending"
**Cause**: No admin assigned or verification not started

**Solution**:
1. Check assignment in `hoarding_verification_assignments`
2. Manually assign to admin
3. Admin should start verification

### Issue: Cannot Approve - "Checklist Not Complete"
**Cause**: Not all checklist items have status

**Solution**:
1. Review all checklist items
2. Set status (passed/failed/na) for each
3. Add notes if failed
4. Try approval again

### Issue: Auto-Approval Not Working
**Cause**: Setting disabled or vendor doesn't meet criteria

**Solution**:
1. Check `hoarding_approval_settings.auto_approve_trusted_vendors = true`
2. Verify vendor rating ≥ threshold
3. Verify vendor approved count ≥ minimum
4. Check vendor's previous approvals

### Issue: Re-verification Not Triggering
**Cause**: Observer not registered or non-critical field edited

**Solution**:
1. Ensure `HoardingObserver` is registered in `AppServiceProvider`
2. Check if edited field is in critical fields list (13 fields)
3. Review `needs_reverification` flag

---

## Future Enhancements

### Planned Features
1. **Email Notifications**: Auto-notify vendors on status changes
2. **SMS Alerts**: For SLA breaches
3. **Automated Quality Checks**: AI-based image quality detection
4. **Advanced Analytics**: Approval rates by category, admin performance
5. **Vendor Appeals**: Dispute rejection decisions
6. **Checklist Templates**: Custom checklists per hoarding type
7. **Multi-Level Approval**: Senior admin final approval for high-value hoardings

---

## Support

For issues or questions:
- **Email**: support@oohapp.com
- **Documentation**: https://docs.oohapp.com/approval-workflow
- **Admin Training**: Contact admin team lead

---

**Last Updated**: December 11, 2025  
**Version**: 1.0  
**Author**: OohApp Development Team
