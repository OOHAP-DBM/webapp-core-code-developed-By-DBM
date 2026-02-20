# Frontend Quick Start Guide

**Dashboard:** `/import`  
**API:** `/api/import/*`  
**Built with:** Tailwind CSS + Axios

---

## ✅ Setup Checklist

- [ ] Verify routes registered (`Routes/web.php` and `Routes/api.php`)
- [ ] Check layout template exists (`layouts.app`)
- [ ] Confirm auth middleware is working
- [ ] Set Sanctum token in browser
- [ ] Test API connectivity

---

## 🚀 Access Dashboard

Navigate to:
```
http://localhost:8000/import
```

You should see:
- Upload Card (left)
- Stats Cards (right)
- Batch List Table (below)

---

## 🎯 Features Overview

### Upload Section (Left Card)

**Fields:**
- Excel File input (.xlsx)
- PPT File input (.pptx)
- Media Type radio (OOH/DOOH)
- Submit button

**Behavior:**
- Drag & drop support
- File preview after selection
- Loading spinner while uploading
- Error messages display
- Auto-refresh batch list on success

**Max Sizes:**
- Excel: 20MB
- PPT: 50MB

### Stats Cards (4 cards)

- **Total Batches** - All batches
- **Processing** - Currently processing
- **Completed** - All completed
- **Failed** - All failed

Updates every 5 seconds automatically.

### Batch List Table

**Columns:**
- Batch ID
- Type (OOH/DOOH)
- Total Rows
- Valid Rows (green)
- Invalid Rows (red)
- Status (colored badges)
- Date Created
- Actions

**Actions:**
- **Approve** - If status = `processed`
- **Errors** - If invalid rows > 0
- **View** - Always available

---

## 📋 Step-by-Step Workflow

### Step 1: Upload Files

```
1. Open /import dashboard
2. Click "Click to upload" in Excel section
3. Select .xlsx file
4. (Optional) Select PPT file
5. Select "OOH" or "DOOH"
6. Click "Upload Files" button
7. Wait for success toast
```

Result: Batch appears in table with "uploaded" status

### Step 2: Monitor Processing

```
1. Check batch status in table
2. Wait for Python API to validate rows
3. Status progresses: uploaded → processing → processed
4. Check "Processing" stat card for count
5. Page auto-refreshes every 5 seconds
```

Result: Status becomes "processed" with blue badge

### Step 3: Review Invalid Rows (if any)

```
1. If invalid_rows > 0, "Errors" button appears
2. Click "Errors" button
3. Modal opens with error table:
   - Row #
   - Record code
   - Error message
4. Review errors
5. Click "Close" to dismiss
```

Result: Understand why rows failed validation

### Step 4: Approve Batch

```
1. Click "Approve" button (only for processed status)
2. Confirmation modal appears showing:
   - Batch ID
   - Valid records count
3. Click "Approve" in modal
4. Wait for spinner to finish
5. Success toast confirms
6. Status changes to "completed" (green)
```

Result: All hoardings created from valid rows

---

## 🎨 UI Highlights

### Upload Box (Drag & Drop)
- Dashed border with upload icon
- Hover changes to blue
- Shows file name after selection
- Green checkmark when file selected

### Status Badges
```
📤 uploaded    - Gray
⏳ processing  - Yellow
✓ processed    - Blue
✓✓ completed   - Green
✕ cancelled    - Gray
✕ failed       - Red
```

### Loading States
- Submit button: Spinner + disabled
- Approve button: Spinner + disabled
- Table: Shows loading placeholders

### Notifications
- **Success** (Green) - Upload success, approval success
- **Error** (Red) - Validation errors, API failures
- **Info** (Blue) - Refresh, filter actions
- Auto-dismiss after 4 seconds

---

## 🔧 API Endpoints

All endpoints require: `Authorization: Bearer {token}`

| Method | Endpoint | Purpose |
|--------|----------|---------|
| GET | /api/import | List all batches |
| POST | /api/import/upload | Upload files |
| GET | /api/import/{id}/status | Get batch status |
| GET | /api/import/{id}/details | Get batch details + errors |
| POST | /api/import/{id}/approve | Approve batch |
| DELETE | /api/import/{id} | Cancel batch |

---

## 📱 Responsive Behavior

**Mobile (<1024px):**
- Single column layout
- Stats stack vertically
- Upload form full width
- Table scrolls horizontally

**Desktop (≥1024px):**
- Side-by-side layout
- Stats in 2x2 grid
- Upload form narrower
- Table has more space

---

## 🐛 Troubleshooting

### "Unauthorized" Error
**Problem:** API returns 401  
**Solution:** Check token in browser storage or refresh page to re-authenticate

### "This action is unauthorized"
**Problem:** API returns 403  
**Solution:** Only batch owner can approve. Ensure viewing own batches only.

### File Not Uploading
**Problem:** Upload button loading forever  
**Solution:**
- Check file size (max 20MB excel, 50MB ppt)
- Check file format (.xlsx or .pptx only)
- Check network errors in DevTools
- Verify Python API is running

### Table Not Refreshing
**Problem:** New batches don't appear  
**Solution:**
- Click "Refresh" button in Quick Actions
- Check browser console for errors
- Verify API is responding

### Modal Won't Close
**Problem:** Error modal stays open  
**Solution:**
- Click "Close" button
- Click outside modal (backdrop)
- Press Esc key in browser

---

## 💡 Tips & Tricks

### Search Batches
Type in search box at top of table to filter by:
- Batch ID
- Media type
- Status
- Date

Real-time search as you type.

### Quick Refresh
Click "Refresh" button in Quick Actions to reload immediately (vs. 5-second auto-refresh).

### Filter by Status
Click "Pending Approval" in Quick Actions to see only processed batches (placeholder - can be enhanced).

### Copy Batch ID
Click on batch ID in table (font-mono styling) to easily identify batches.

---

## 🚀 Production Checklist

Before deploying to production:

- [ ] Test in Chrome, Firefox, Safari, Edge
- [ ] Test on mobile devices
- [ ] Verify upload with large files (20MB+)
- [ ] Test approval with 100+ row batches
- [ ] Check error handling for API failures
- [ ] Verify CSRF token is included
- [ ] Test with disabled JavaScript (or handle gracefully)
- [ ] Ensure rate limiting is configured
- [ ] Set up backup/recovery procedures

---

## 📊 Example Data Flow

```
User @ /import Dashboard
       ↓
Select Files & Click Upload
       ↓
POST /api/import/upload
(FormData with files & media_type)
       ↓
Backend creates InventoryImportBatch
Dispatches ProcessInventoryImportJob
       ↓
Dashboard auto-refreshes (5s interval)
GET /api/import
       ↓
Table shows new batch with "uploaded" status
       ↓
Python API validates rows in background
       ↓
Status changes to "processing" → "processed"
       ↓
User clicks "Approve"
       ↓
POST /api/import/{batch}/approve
(Confirmation modal)
       ↓
Service creates hoardings atomically
       ↓
Status changes to "completed"
Hoarding records created in DB
       ↓
Success toast + stats updated
```

---

## 📞 Getting Help

### Development
- Check Laravel logs: `storage/logs/laravel.log`
- Browser DevTools: F12 → Network/Console tabs
- Test API with Postman
- Review `FRONTEND_DOCUMENTATION.md` for detailed info

### Support
- Check module README: `Modules/Import/README.md`
- Review TESTING_GUIDE: `Modules/Import/TESTING_GUIDE.md`
- Contact development team with:
  - Batch ID
  - Error message
  - Browser console screenshot
  - Network requests screenshot

---

**Dashboard Ready!** 🎉

Start at: `/import`  
Questions? Check `FRONTEND_DOCUMENTATION.md`
