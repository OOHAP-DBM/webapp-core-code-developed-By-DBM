# Import Module - Quick Start & Testing Guide

## Setup Checklist

### 1. Environment Configuration

Add to `.env`:
```env
# Import Service Configuration
IMPORT_PYTHON_URL=http://localhost:8000/api/import/validate
IMPORT_PYTHON_TOKEN=your_api_bearer_token_here
IMPORT_PYTHON_TIMEOUT=300

# Queue configuration (for async processing)
QUEUE_CONNECTION=database
```

### 2. Database Verification

Run migrations (if not already done):
```bash
php artisan migrate
```

Verify tables exist:
```bash
php artisan migrate:status
```

Expected tables:
- `imports` (generic)
- `inventory_import_batches` (vendor batches)
- `inventory_import_staging` (individual rows)

### 3. Authorization Setup

Ensure vendor user exists with `Vendor` role:
```bash
php artisan tinker

# Create test vendor if needed
$vendor = User::create([
    'name' => 'Test Vendor',
    'email' => 'vendor@test.com',
    'password' => bcrypt('password'),
    'role' => 'Vendor',
]);

# Create bearer token
$token = $vendor->createToken('import-test-token')->plainTextToken;
```

---

## Full Workflow Test

### Phase 1: Upload Inventory File

**Request:**
```bash
curl -X POST http://localhost:8000/api/import/upload \
  -H "Authorization: Bearer {vendor_token}" \
  -F "file=@sample_inventory.xlsx" \
  -F "media_type=ooh"
```

**Expected Response:**
```json
{
  "success": true,
  "message": "File uploaded successfully",
  "data": {
    "batch_id": 1,
    "status": "uploaded",
    "total_rows": 100
  }
}
```

**Verification:**
```bash
# Check batch was created
SELECT * FROM inventory_import_batches WHERE id = 1;

# Verify file stored
ls -la storage/app/imports/1/
```

---

### Phase 2: Monitor Processing

**Request:**
```bash
curl -X GET http://localhost:8000/api/import/1/status \
  -H "Authorization: Bearer {vendor_token}"
```

**Expected Response (Processing):**
```json
{
  "success": true,
  "data": {
    "batch_id": 1,
    "status": "processing",
    "total_rows": 100,
    "processed_rows": 45,
    "valid_rows": 42,
    "invalid_rows": 3
  }
}
```

**Expected Response (Processed):**
```json
{
  "success": true,
  "data": {
    "batch_id": 1,
    "status": "processed",
    "total_rows": 100,
    "processed_rows": 100,
    "valid_rows": 95,
    "invalid_rows": 5
  }
}
```

**Verification:**
```bash
# Check staging records created
SELECT COUNT(*) as count, status, media_type 
FROM inventory_import_staging 
WHERE batch_id = 1 
GROUP BY status, media_type;
```

Monitor job queue (if using async):
```bash
php artisan queue:work # Run in terminal 1
php artisan queue:monitor # Run in terminal 2
```

---

### Phase 3: Review Batch Details

**Request:**
```bash
curl -X GET http://localhost:8000/api/import/1/details \
  -H "Authorization: Bearer {vendor_token}"
```

**Expected Response:**
```json
{
  "success": true,
  "data": {
    "batch_id": 1,
    "vendor_id": 1,
    "media_type": "ooh",
    "status": "processed",
    "stats": {
      "total_rows": 100,
      "valid_rows": 95,
      "invalid_rows": 5
    },
    "invalid_records": [
      {
        "id": 10,
        "code": "HOARDING_001",
        "error_message": "Missing required field: city",
        "status": "invalid"
      }
    ],
    "valid_sample": [
      {
        "id": 1,
        "code": "HOARDING_002",
        "city": "Mumbai",
        "width": 100,
        "height": 50
      }
    ]
  }
}
```

---

### Phase 4: Approve & Create Hoardings

**Request:**
```bash
curl -X POST http://localhost:8000/api/import/1/approve \
  -H "Authorization: Bearer {vendor_token}" \
  -H "Content-Type: application/json"
```

**Expected Response:**
```json
{
  "success": true,
  "message": "Import approved and hoardings created successfully",
  "data": {
    "batch_id": 1,
    "created_count": 95,
    "failed_count": 0,
    "total_processed": 95,
    "status": "completed"
  }
}
```

**Verification:**
```bash
# Check hoardings created
SELECT COUNT(*) as count, hoarding_type, status 
FROM hoardings 
WHERE vendor_id = 1 
GROUP BY hoarding_type, status;

# Check OOH records
SELECT COUNT(*) FROM ooh_hoardings;

# Check media attached
SELECT COUNT(*) FROM hoarding_media;

# Check batch completed
SELECT status, valid_rows, processed_rows FROM inventory_import_batches WHERE id = 1;
```

---

## Error Scenario Testing

### Test 1: Approve Non-Processed Batch

**Scenario:** Try to approve batch still in 'uploaded' status

```bash
curl -X POST http://localhost:8000/api/import/1/approve \
  -H "Authorization: Bearer {vendor_token}"
```

**Expected Response (422):**
```json
{
  "success": false,
  "message": "Failed to approve import batch",
  "error": "Batch must be in 'processed' status to approve. Current status: uploaded"
}
```

---

### Test 2: Authorization Failure

**Scenario:** Non-owner vendor tries to approve batch

```bash
# Create second vendor
$vendor2 = User::create([...]);
$token2 = $vendor2->createToken('token2')->plainTextToken;

# Try to approve vendor1's batch
curl -X POST http://localhost:8000/api/import/1/approve \
  -H "Authorization: Bearer {token2}"
```

**Expected Response (403):**
```json
{
  "success": false,
  "message": "This action is unauthorized"
}
```

---

### Test 3: Handle Row-Level Errors

**Scenario:** Some rows fail, but batch still completes

Create staging rows with invalid hoarding_type:

```bash
php artisan tinker

$batch = \Modules\Import\Entities\InventoryImportBatch::find(1);

# Add invalid row
$batch->stagingRecords()->create([
    'vendor_id' => $batch->vendor_id,
    'code' => 'INVALID_001',
    'city' => 'Mumbai',
    'width' => 100,
    'height' => 50,
    'media_type' => 'invalid', // <-- Will cause creation to fail
    'status' => 'valid',
    'extra_attributes' => json_encode([...]),
]);

# Approve batch
$service = app(\Modules\Import\Services\ImportApprovalService::class);
$result = $service->approveBatch($batch);
```

**Expected Behavior:**
- created_count = 95 (successful rows)
- failed_count = 1 (invalid row)
- total_processed = 96
- Batch status = 'completed' (batch completion not blocked)
- Row marked as 'invalid' with error message

---

## Debugging Guide

### Check Logs

All operations logged to `storage/logs/laravel.log`:

```bash
# Tail logs
tail -f storage/logs/laravel.log

# Filter for import operations
grep "import\|Import" storage/logs/laravel.log

# Filter for errors
grep -i "error" storage/logs/laravel.log | grep -i "import"
```

### Database Inspection

```bash
php artisan tinker

# Get batch with stats
$batch = \Modules\Import\Entities\InventoryImportBatch::with('stagingRecords')->find(1);
echo "Status: {$batch->status}\n";
echo "Valid: {$batch->valid_rows}\n";
echo "Invalid: {$batch->invalid_rows}\n";

# Get staging records
$batch->stagingRecords()->get();

# Check invalid records
$batch->stagingRecords()->invalid()->get();

# Get created hoardings
\App\Models\Hoarding::where('vendor_id', $batch->vendor_id)->get();
```

### Connection Testing

Test Python API connection:

```bash
php artisan tinker

$service = app(\Modules\Import\Services\PythonImportService::class);

try {
    $result = $service->validateInventory(
        storage_path('app/imports/1/sample.xlsx'),
        storage_path('app/imports/1/sample.pptx'),
        1, // vendor_id
        'ooh'
    );
    dump($result);
} catch (\Exception $e) {
    echo "Error: {$e->getMessage()}\n";
}
```

---

## Performance Benchmarks

### Expected Performance

| Operation | Expected Duration | Notes |
|-----------|-------------------|-------|
| Upload 100-row batch | <5 seconds | File storage only |
| Process 100 rows | 30-60 seconds | Python API calls + DB inserts |
| Approve 100 rows | 15-30 seconds | Creates all relationships |
| Create 1000 rows | 5-10 minutes | Per-row hoarding creation |

### Optimization Queries

Monitor slow queries:

```bash
# Check query log
tail -f storage/logs/queries.log

# Database profiling
php artisan tinker
\DB::enableQueryLog();

$service = app(\Modules\Import\Services\ImportApprovalService::class);
$service->approveBatch($batch);

dd(\DB::getQueryLog());
```

---

## File Structure Reference

```
Modules/Import/
├── Routes/
│   └── api.php                          # API endpoints
├── Http/
│   ├── Controllers/
│   │   ├── ImportController.php         # Upload/status endpoints
│   │   └── ImportApprovalController.php # Approval endpoint
│   └── Requests/
│       └── UploadInventoryImportRequest.php # File validation
├── Services/
│   ├── PythonImportService.php          # Python API integration
│   ├── ImportService.php                # Generic service
│   └── ImportApprovalService.php        # Approval/hoarding creation
├── Entities/
│   ├── InventoryImportBatch.php         # Batch model
│   └── InventoryImportStaging.php       # Staging row model
├── Database/
│   └── Migrations/
│       ├── 2026_02_18_*.php             # All migrations
└── Policies/
    └── ImportPolicy.php                 # Authorization rules
```

---

## Common Issues & Solutions

### Issue: "Table inventory_import_batches doesn't exist"

**Solution:**
```bash
php artisan migrate --path=Modules/Import/Database/Migrations
```

### Issue: "Python API unreachable"

**Check:**
- `.env` has correct `IMPORT_PYTHON_URL`
- Python service is running
- Network connectivity
- Bearer token is valid

**Debug:**
```bash
curl -X POST http://your-python-url/api/import/validate \
  -H "Authorization: Bearer {token}" \
  -F "file=@test.xlsx"
```

### Issue: "SQLSTATE[HY000]: General error: 1366"

**Cause:** Invalid data in JSON columns

**Solution:**
```bash
# Check staging row
php artisan tinker
$row = \Modules\Import\Entities\InventoryImportStaging::find(1);
dd($row->extra_attributes);

# Fix invalid JSON
$row->extra_attributes = json_encode(['valid' => 'data']);
$row->save();
```

### Issue: Approval hangs or times out

**Check:**
- Database is responding
- No locks on related tables
- Check slow query log

**Solution:**
```bash
# Check processes
SHOW PROCESSLIST;

# Kill long-running query
KILL {process_id};

# Restart transaction manually
php artisan tinker
$batch->updateStatus('processed'); // Reset status
```

---

## Next Steps

1. ✅ Configure .env with Python API details
2. ✅ Test full workflow Phase 1-4
3. ✅ Execute Phase 3 error scenarios
4. ✅ Load test with 1000+ records
5. ☐ Configure webhook notifications
6. ☐ Set up approval audit trail
7. ☐ Deploy to production
