# Vendor Availability Calendar - Quick Reference Guide

## Overview
The Vendor Availability Calendar provides a visual, color-coded interface for vendors to track hoarding bookings, enquiries, and available dates using FullCalendar.js.

---

## Accessing the Calendar

### From Hoarding List
1. Navigate to **Vendor Dashboard** → **My Hoardings**
2. Find your hoarding card
3. Click the **"Calendar"** button (green, with calendar icon)

### From Hoarding Edit Page
1. Navigate to **Vendor Dashboard** → **My Hoardings**
2. Click **"Edit"** on any hoarding
3. Click **"View Calendar"** button in the header

### Direct URL
```
/vendor/hoarding/{hoarding_id}/calendar
```

---

## Understanding the Calendar

### Color Coding System

| Color | Meaning | Description |
|-------|---------|-------------|
| 🔴 **Red** (#dc2626) | Confirmed Booking | Customer has confirmed and paid |
| 🟠 **Orange** (#ea580c) | Payment Hold | Payment authorized, awaiting capture |
| 🟡 **Amber** (#f59e0b) | Pending Payment | Awaiting customer payment |
| 🟡 **Yellow** (#fbbf24) | Enquiry | Customer interested, pending response |
| 🟢 **Green** (#10b981) | Available | No bookings or enquiries |

---

## Statistics Dashboard

The top section displays 6 real-time metrics:

### 1. Total Bookings
- **Icon:** Calendar Check (blue)
- **Shows:** All-time confirmed bookings count
- **Excludes:** Cancelled bookings

### 2. Active Now
- **Icon:** Play Circle (green)
- **Shows:** Bookings currently running
- **Logic:** Today falls between start_date and end_date

### 3. Enquiries
- **Icon:** Inbox (yellow)
- **Shows:** Pending enquiries awaiting response
- **Action Required:** Review and respond promptly

### 4. This Month
- **Icon:** Calendar3 (indigo)
- **Shows:** Bookings starting in current month
- **Use Case:** Monthly performance tracking

### 5. Revenue
- **Icon:** Currency Rupee (green)
- **Shows:** Current month revenue in Lakh format
- **Example:** ₹4.50L = ₹450,000

### 6. Occupancy
- **Icon:** Pie Chart (blue)
- **Shows:** Percentage of days booked this month
- **Calculation:** (Booked days / Total days) × 100
- **Range:** 0% (empty) to 100% (fully booked)

---

## Calendar Views

### Month View (Default)
- **Best For:** Overview of availability
- **Shows:** Full month grid with all events
- **Click:** Any date to see events
- **Navigation:** Prev/Next buttons or month selector

### Week View
- **Best For:** Detailed weekly planning
- **Shows:** 7-day view with time slots
- **Toggle:** Click "Week" button in toolbar

### List View
- **Best For:** Chronological event listing
- **Shows:** Upcoming events in list format
- **Toggle:** Click "List" button in toolbar

---

## Interacting with Events

### Viewing Event Details

**Click any event** to open a detailed modal:

#### For Bookings:
- Booking ID (clickable link)
- Customer name and phone
- Date range (start to end)
- Duration (in days)
- Total amount
- Status badge

#### For Enquiries:
- Enquiry ID (clickable link)
- Customer name and phone
- Preferred date range
- Duration (in days)
- Customer message
- Status (Pending)

### Quick Actions
- **"View Details" Button:** Opens full booking/enquiry page
- **"Close" Button:** Dismisses modal

---

## Legend Bar

Located below statistics, shows all event types:

```
🟢 Available  |  🟡 Enquiry  |  🟠 Payment Hold  |  🟠 Payment Pending  |  🔴 Confirmed Booking
```

---

## Calendar Controls

### Toolbar Buttons

| Button | Function |
|--------|----------|
| **< Prev** | Go to previous month/week |
| **Today** | Jump to current date |
| **Next >** | Go to next month/week |
| **Month** | Switch to month view |
| **Week** | Switch to week view |
| **List** | Switch to list view |

### Page Controls

| Button | Function |
|--------|----------|
| **Back to Hoarding** | Return to hoarding detail page |
| **Refresh** | Reload calendar data |

---

## Use Cases

### 1. Check Availability for New Enquiry
1. Customer calls asking about specific dates
2. Open calendar for that hoarding
3. Visually check if dates are green (available)
4. Inform customer immediately

### 2. Track Monthly Performance
1. View "This Month" stat
2. Check "Revenue" stat
3. Compare "Occupancy" percentage
4. Identify slow periods (low occupancy)

### 3. Manage Overlapping Enquiries
1. Yellow events show pending enquiries
2. Click enquiry to see customer details
3. Respond via enquiry detail page
4. Convert to booking when confirmed

### 4. Monitor Active Campaigns
1. Check "Active Now" stat
2. Red events show running campaigns
3. Verify campaign progress
4. Prepare for campaign completion

### 5. Plan Maintenance Windows
1. Look for gaps between bookings (green dates)
2. Schedule hoarding maintenance
3. Avoid booking conflicts
4. Maintain hoarding quality

---

## Tips & Best Practices

### ✅ DO:
- Check calendar daily for new enquiries
- Respond to yellow enquiries within 24 hours
- Use occupancy rate to optimize pricing
- Monitor active bookings regularly
- Plan ahead using week/month views

### ❌ DON'T:
- Double-book dates (system prevents this)
- Ignore pending enquiries (they expire)
- Override confirmed bookings
- Forget to check availability before quoting

---

## Technical Details

### Data Refresh
- **Auto-refresh:** When changing months
- **Manual refresh:** Click refresh button
- **Real-time:** API fetches latest data

### Performance
- **Fast loading:** AJAX-based event loading
- **Efficient:** Only loads visible date range
- **Responsive:** Works on mobile devices

### Browser Support
- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+
- Mobile browsers

---

## Troubleshooting

### Calendar Not Loading
1. Check internet connection
2. Refresh page (Ctrl+R)
3. Clear browser cache
4. Contact support if issue persists

### Events Not Showing
1. Verify you own the hoarding
2. Check if bookings exist in system
3. Try different view (Week/List)
4. Click refresh button

### Stats Not Updating
1. Click manual refresh button
2. Change month view back and forth
3. Hard refresh page (Ctrl+Shift+R)

### Modal Not Opening
1. Ensure JavaScript is enabled
2. Try different browser
3. Check for browser extensions blocking popups

---

## API Endpoints (For Developers)

### Get Calendar Events
```
GET /vendor/hoarding/{id}/calendar/data?start=2025-12-01&end=2025-12-31
```

**Response:** Array of FullCalendar event objects

### Get Statistics
```
GET /vendor/hoarding/{id}/calendar/stats
```

**Response:**
```json
{
  "total_bookings": 45,
  "active_bookings": 3,
  "pending_enquiries": 8,
  "current_month_bookings": 12,
  "current_month_revenue": 450000.00,
  "occupancy_rate": 67.50
}
```

---

## Security & Privacy

- **Vendor-Only Access:** Only hoarding owner can view calendar
- **Data Privacy:** Customer details visible only to vendor
- **Secure API:** All endpoints protected by authentication
- **No External Sharing:** Calendar data not accessible externally

---

## Future Enhancements

Coming soon:
- 📅 Export to PDF/CSV
- 🔔 Email notifications for new enquiries
- 🎯 Click-to-book on available dates
- 📊 Advanced analytics dashboard
- 🔄 Drag-and-drop booking adjustment

---

## Support

Need help?
- **Email:** support@oohapp.com
- **Phone:** +91 XXX-XXX-XXXX
- **Documentation:** `/docs`
- **Video Tutorials:** Coming soon

---

## Keyboard Shortcuts

| Key | Action |
|-----|--------|
| **←** | Previous month/week |
| **→** | Next month/week |
| **T** | Jump to today |
| **Esc** | Close modal |
| **R** | Refresh calendar |

---

## Frequently Asked Questions

### Q: Why don't I see available (green) dates?
**A:** Available dates are shown as background events. If your hoarding is fully booked or has enquiries for all dates in view, no green will show.

### Q: Can I create bookings from the calendar?
**A:** Not yet. This feature is planned for a future update. Currently, bookings are created through the enquiry → quotation → booking flow.

### Q: What happens when an enquiry is accepted?
**A:** The yellow enquiry event disappears and is replaced by a red/orange booking event once payment is processed.

### Q: How is occupancy calculated?
**A:** Occupancy = (Number of days with confirmed bookings / Total days in month) × 100. Overlapping bookings are handled correctly.

### Q: Can I see historical data?
**A:** Yes! Navigate to previous months using the "< Prev" button. All past bookings and enquiries are retained.

### Q: Does the calendar work offline?
**A:** No, an internet connection is required to load events and statistics from the server.

---

## Version History

- **v1.0** (Dec 2025) - Initial release with FullCalendar.js integration
- Color-coded events (red, yellow, green)
- Real-time statistics dashboard
- Three view modes (Month/Week/List)
- Event details modal
- Responsive mobile design

---

**Last Updated:** December 10, 2025  
**Document Version:** 1.0  
**Feature:** PROMPT 49 - Vendor Availability Calendar
