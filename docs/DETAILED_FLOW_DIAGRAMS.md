# 🎬 Visual Notification Flow - Step by Step

## The Complete Loop

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                        DIRECT ENQUIRY NOTIFICATION FLOW                    │
└─────────────────────────────────────────────────────────────────────────────┘

STEP 1: CUSTOMER SUBMITS ENQUIRY
═════════════════════════════════════════════════════════════════════════════
    
    Mobile App:                           Website:
    ┌─────────────────────┐              ┌──────────────────────┐
    │ Customer App Screen │              │ Website Homepage     │
    │                     │              │                      │
    │ 🗹 Name: John Doe   │              │ Direct Enquiry Form  │
    │ 🗹 Phone: 9876...   │              │ ┌────────────────┐   │
    │ 🗹 Hoarding:        │              │ │ Name            │   │
    │   - DOOH           │              │ │ Email           │   │
    │   - OOH            │              │ │ Phone           │   │
    │ 🗹 City: Mumbai     │              │ │ Hoarding Type   │   │
    │ 🗹 Areas: Andheri   │              │ │ City            │   │
    │                     │              │ │ [SUBMIT BTN]    │   │
    │ [SUBMIT ENQUIRY]    │              │ └────────────────┘   │
    └──────────┬──────────┘              └──────────┬───────────┘
               │                                    │
               └────────────────┬───────────────────┘
                                │
                    ┌───────────▼────────────┐
                    │ POST Request Sent      │
                    │ /api/v1/enquiries  or  │
                    │ /enquiry/direct        │
                    └───────────┬────────────┘
                                │

STEP 2: SERVER RECEIVES & VALIDATES
═════════════════════════════════════════════════════════════════════════════
    
    ┌──────────────────────────────────────────────────────┐
    │ DirectEnquiryApiController (API) or                  │
    │ DirectEnquiryController (Web) - store() method       │
    │                                                       │
    │ 1. Validate request data                             │
    │ 2. Create DirectEnquiry record                       │
    │ 3. Find matching vendors (by city, hoarding type)    │
    │ 4. Attach vendors to enquiry                         │
    │ 5. TRIGGER NOTIFICATIONS                            │
    └────────────┬─────────────────────────────────────────┘
                 │
        ┌────────▼─────────┐
        │ DirectEnquiry    │
        │ Record Created   │
        │ - ID: 123        │
        │ - Name: John...  │
        │ - Type: DOOH,OOH │
        │ - City: Mumbai   │
        └────────┬─────────┘
                 │

STEP 3: FIND VENDORS
═════════════════════════════════════════════════════════════════════════════
    
    ┌──────────────────────────────────────────────────────┐
    │ findRelevantVendors() Method                         │
    │                                                       │
    │ Queries vendors matching:                           │
    │ - Location: Mumbai ✓                                │
    │ - Hoarding Type: DOOH or OOH ✓                      │
    │ - Status: Active ✓                                  │
    │                                                       │
    │ Result: 3 vendors found                             │
    │ - Vendor 1 (ID: 101, FCM token: abc123)            │
    │ - Vendor 2 (ID: 102, FCM token: def456)            │
    │ - Vendor 3 (ID: 103, FCM token: ghi789)            │
    └────────┬─────────────────────────────────────────────┘
             │

STEP 4: SEND NOTIFICATIONS (FOR EACH VENDOR)
═════════════════════════════════════════════════════════════════════════════
    
    FOR EACH of the 3 vendors:
    
    ┌────────────────────────────────────────────────────────────┐
    │ foreach ($vendors as $vendor) {                            │
    │                                                             │
    │   ┌─ NOTIFICATION #1: EMAIL ──────────────────┐           │
    │   │                                            │           │
    │   │ Mail::to($vendor->email)->queue(...)      │           │
    │   │                                            │           │
    │   │ ✓ Queued for background delivery          │           │
    │   │ ✓ Recipient: vendor@example.com           │           │
    │   │ ✓ Template: VendorDirectEnquiryMail      │           │
    │   │ ✓ Contains: Full enquiry details         │           │
    │   │ ✓ Type: Background job (async)           │           │
    │   └────────────────────────────────────────────┘           │
    │                                                             │
    │   ┌─ NOTIFICATION #2: IN-APP ──────────────────┐          │
    │   │ (Web Dashboard Notification)              │          │
    │   │                                            │          │
    │   │ $vendor->notify(                          │          │
    │   │   new VendorDirectEnquiryNotification()   │          │
    │   │ );                                         │          │
    │   │                                            │          │
    │   │ ✓ Created in notifications table          │          │
    │   │ ✓ Type: vendor_direct_enquiry_received    │          │
    │   │ ✓ Contains: Customer details + hoarding   │          │
    │   │ ✓ Shows in web dashboard notification     │          │
    │   │ ✓ Type: Synchronous (immediate)           │          │
    │   └────────────────────────────────────────────┘          │
    │                                                             │
    │   ┌─ NOTIFICATION #3: PUSH ────────────────────┐          │
    │   │ (Mobile App Notification)                 │          │
    │   │                                            │          │
    │   │ send(                                      │          │
    │   │   $vendor,                                 │          │
    │   │   'New Hoarding Enquiry Received',        │          │
    │   │   'New DOOH, OOH enquiry from John...',   │          │
    │   │   [...]                                    │          │
    │   │ );                                         │          │
    │   │                                            │          │
    │   │ ✓ Sent via FCM (Firebase)                │          │
    │   │ ✓ Respects notification_push preference   │          │
    │   │ ✓ Appears in mobile notification tray    │          │
    │   │ ✓ Type: Background job (async)           │          │
    │   └────────────────────────────────────────────┘          │
    │                                                             │
    │ } // End foreach                                           │
    │                                                             │
    └────────┬──────────────────────────────────────────────────┘
             │
        ┌────▼──────────────────────────────┐
        │ VENDOR 1 (101) NOTIFICATIONS:      │
        │ ├─ 📧 Email queued for delivery   │
        │ ├─ 📱 In-app notification created │
        │ └─ 🔔 Push queued for FCM         │
        │                                   │
        │ VENDOR 2 (102) NOTIFICATIONS:     │
        │ ├─ 📧 Email queued                │
        │ ├─ 📱 In-app notification created │
        │ └─ 🔔 Push queued                 │
        │                                   │
        │ VENDOR 3 (103) NOTIFICATIONS:     │
        │ ├─ 📧 Email queued                │
        │ ├─ 📱 In-app notification created │
        │ └─ 🔔 Push queued                 │
        └────┬──────────────────────────────┘
             │

STEP 5: SEND CUSTOMER NOTIFICATIONS (IF REGISTERED)
═════════════════════════════════════════════════════════════════════════════
    
    IF customer exists as registered user:
    
    ┌─────────────────────────────────────────────┐
    │ ✓ Email confirmation sent                   │
    │ ✓ In-app notification created              │
    │ ✓ Push notification sent to mobile          │
    └─────────────────────────────────────────────┘

STEP 6: RESPOND TO CLIENT
═════════════════════════════════════════════════════════════════════════════
    
    ┌──────────────────────────────────────────────┐
    │ Return JSON Response:                        │
    │ {                                            │
    │   "success": true,                           │
    │   "message": "Enquiry submitted!",          │
    │   "data": {                                  │
    │     "enquiry_id": 123                       │
    │   }                                          │
    │ }                                            │
    │                                              │
    │ ✓ Fast response (~260ms)                    │
    │ ✓ Heavy operations in background            │
    │ ✓ Notifications being processed             │
    └──────────────────────────────────────────────┘
             │
        ┌────▼────────────┐
        │ Client receives │
        │ success response│
        │ Shows confirm.  │
        │ message to user │
        └─────────────────┘

STEP 7: BACKGROUND PROCESSING
═════════════════════════════════════════════════════════════════════════════
    
    Queue Worker processes:
    
    ┌─────────────────────────────────────────────────────────┐
    │ php artisan queue:work                                  │
    │                                                          │
    │ ✓ Email #1: Sends to vendor1@example.com (2 seconds)   │
    │   └─ Subject: "New Hoarding Enquiry in Mumbai"         │
    │                                                          │
    │ ✓ Email #2: Sends to vendor2@example.com (2 seconds)   │
    │                                                          │
    │ ✓ Email #3: Sends to vendor3@example.com (2 seconds)   │
    │                                                          │
    │ ✓ Push #1: Firebase sends to Vendor 1's mobile (1 sec) │
    │   └─ Shows in notification tray                        │
    │                                                          │
    │ ✓ Push #2: Firebase sends to Vendor 2's mobile (1 sec) │
    │                                                          │
    │ ✓ Push #3: Firebase sends to Vendor 3's mobile (1 sec) │
    │                                                          │
    │ Total background time: ~9 seconds                       │
    └─────────────────────────────────────────────────────────┘

STEP 8: VENDOR RECEIVES NOTIFICATIONS
═════════════════════════════════════════════════════════════════════════════
    
    VENDOR 1: Receives all 3 notifications
    
    Timeline:
    ├─ T+2s  📧 Email arrives in inbox
    │        Subject: "New Hoarding Enquiry in Mumbai"
    │        Body: Full details with customer info
    │
    ├─ T+1s  📱 In-app notification appears on dashboard
    │        (Available immediately after enquiry created)
    │        Shows: Title, Message, Customer Details
    │        Location: Notification Bell → Dropdown
    │
    └─ T+3s  🔔 Push notification on mobile device
    │        Title: "New Hoarding Enquiry Received"
    │        Body: "New DOOH, OOH enquiry from John Doe..."
    │        Action: Tap to view enquiry
    
    VENDOR 2 & 3: Same flow
```

---

## Notification Content Shown to Vendor

### 📧 EMAIL NOTIFICATION
```
TO: vendor@example.com
SUBJECT: New Hoarding Enquiry in Mumbai

═════════════════════════════════════════════════════════
             OOHAPP - New Enquiry Alert
═════════════════════════════════════════════════════════

Dear Vendor,

You have received a new hoarding enquiry! Please respond quickly.

CUSTOMER INFORMATION:
─────────────────────
Name:     John Doe
Email:    john@example.com
Phone:    9876543210

REQUIREMENTS:
─────────────────────
Hoarding Type:  DOOH, OOH
City:           Mumbai
Preferred Area: Andheri, Bandra
Duration:       Not specified
Budget:         Not mentioned

MESSAGE:
─────────────────────
"Need premium hoarding spaces in high traffic areas with 
good visibility for brand awareness campaign."

PREFERRED CONTACT MODE:
─────────────────────
• Call
• WhatsApp

[VIEW FULL ENQUIRY IN DASHBOARD]

═════════════════════════════════════════════════════════
Best regards,
OOHAPP Platform
```

---

### 📱 IN-APP NOTIFICATION (Web Dashboard)
```
LOCATION: 🔔 Notification Bell (Top Right Corner)

┌──────────────────────────────────────────────────────────────┐
│ 🔔 NOTIFICATIONS (Unread: 1)                                │
├──────────────────────────────────────────────────────────────┤
│                                                               │
│ ⚫ NEW HOARDING ENQUIRY RECEIVED                            │
│   ────────────────────────────────────────────────────────  │
│   New DOOH, OOH enquiry from John Doe in Mumbai            │
│                                                               │
│   DETAILS:                                                    │
│   ├─ Customer: John Doe                                     │
│   ├─ Phone: 9876543210                                      │
│   ├─ Email: john@example.com                                │
│   ├─ Hoarding Type: DOOH, OOH                              │
│   ├─ City: Mumbai                                           │
│   ├─ Preferred Areas: Andheri, Bandra                       │
│   └─ Status: New                                            │
│                                                               │
│   Submitted: March 9, 2026 at 10:30 AM                      │
│                                                               │
│   [MARK AS READ] [DELETE] [VIEW FULL ENQUIRY →]            │
│                                                               │
└──────────────────────────────────────────────────────────────┘
```

---

### 🔔 PUSH NOTIFICATION (Mobile Device)
```
Mobile Device Notification Tray:

┌──────────────────────────────────┐
│ OOHAPP                      9:31 │
├──────────────────────────────────┤
│  🔔 New Hoarding Enquiry       │
│     Received                     │
│                                  │
│  New DOOH, OOH enquiry from    │
│  John Doe in Mumbai             │
│                                  │
│  [OPEN]          [DISMISS ✕]    │
└──────────────────────────────────┘

Tap to open = Goes to enquiry details in mobile app
Dismiss = Notification disappears (stored in notification history)
```

---

## Speed & Timing

```
IMMEDIATE (Within 1 second):
  ✓ In-app notification created and stored
  ✓ Email & Push queued in background

FAST (Within 10 seconds):
  ✓ Push notification arrives on mobile device
  ✓ Email starts delivery

NORMAL (1-5 minutes):
  ✓ Email appears in vendor inbox
  ✓ Vendor refreshes dashboard → sees in-app notification
```

---

## Summary

When customer submits direct enquiry:

```
┌─────────────────────────────────────────────────────────┐
│ IMMEDIATE EFFECTS:                                      │
│ ✓ API responds with success (~260ms)                   │
│ ✓ Vendor notifications queued in background            │
│ ✓ In-app notification appears on web dashboard         │
│                                                         │
│ WITHIN 10 SECONDS:                                     │
│ ✓ Push notification on vendor's mobile                │
│                                                         │
│ WITHIN 5 MINUTES:                                      │
│ ✓ Email lands in vendor's inbox                       │
│                                                         │
│ VENDOR RECEIVES:                                       │
│ ✓ 3 notifications per enquiry                         │
│ ✓ All include hoarding type, customer details         │
│ ✓ Can act immediately across web & mobile             │
└─────────────────────────────────────────────────────────┘
```

