# Universal Messaging Thread System - Developer Documentation

## Table of Contents
1. [Overview](#overview)
2. [Architecture](#architecture)
3. [Database Schema](#database-schema)
4. [Models](#models)
5. [Controllers](#controllers)
6. [Views](#views)
7. [Routes](#routes)
8. [Common Tasks](#common-tasks)
9. [Troubleshooting](#troubleshooting)

---

## Overview

The Universal Messaging Thread System is a comprehensive messaging solution that combines features of email and chat applications. It allows customers and vendors to communicate about enquiries, offers, and quotations in real-time.

### Key Features
- **Text Messaging**: Send and receive text messages
- **File Attachments**: Support for images, PDFs, and documents (max 5 files, 10MB each)
- **Read Receipts**: Track when messages are read (separate for customer/vendor)
- **System Messages**: Automated messages for workflow events (enquiry created, offer sent, etc.)
- **Real-time Updates**: AJAX polling every 10 seconds
- **Unread Counts**: Badge showing number of unread threads
- **Thread Archiving**: Archive conversations to clean up inbox
- **Mobile Support**: Full API support for mobile apps

### How It Works
1. When a customer creates an **Enquiry**, a **Thread** is automatically created
2. The thread links the **Customer** and **Vendor** together
3. Both parties can send messages in the thread
4. System automatically posts messages when offers/quotations are sent
5. Unread counts are tracked separately for customer and vendor

---

## Architecture

### Flow Diagram
```
Customer Creates Enquiry
         ↓
    Thread Created (Auto)
         ↓
    Initial System Message Posted
         ↓
    Vendor Sends Offer
         ↓
    System Message: "Offer Sent" (Auto)
         ↓
    Customer/Vendor Exchange Messages
         ↓
    Read Receipts Updated
         ↓
    Unread Counts Updated
```

### Component Structure
```
app/
├── Models/
│   ├── Thread.php                    # Thread management
│   ├── ThreadMessage.php             # Message management
│   ├── Enquiry.php                   # Auto-creates threads
│   └── Offer.php                     # Posts system messages
├── Http/Controllers/
│   ├── Customer/ThreadController.php # Customer inbox
│   └── Vendor/ThreadController.php   # Vendor inbox
resources/views/
├── customer/threads/
│   └── index.blade.php               # Customer inbox UI (existing)
├── vendor/threads/
│   └── index.blade.php               # Vendor inbox UI
└── components/
    └── thread-notification-badge.blade.php  # Unread badge
routes/
├── web.php                           # Web routes (Blade views)
└── api_v1/threads.php                # API routes (JSON responses)
```

---

## Database Schema

### `threads` Table
Stores conversation threads between customers and vendors.

| Column | Type | Description |
|--------|------|-------------|
| `id` | bigint | Primary key |
| `enquiry_id` | bigint | Foreign key to enquiries table |
| `customer_id` | bigint | Foreign key to users table (customer) |
| `vendor_id` | bigint | Foreign key to users table (vendor) |
| `is_multi_vendor` | boolean | If admin mediates (not used yet) |
| `status` | enum | `active`, `closed`, `archived` |
| `last_message_at` | timestamp | Last message time (for sorting) |
| `unread_count_customer` | integer | Number of unread messages for customer |
| `unread_count_vendor` | integer | Number of unread messages for vendor |
| `created_at` | timestamp | Thread creation time |
| `updated_at` | timestamp | Last update time |

**Indexes:**
- `enquiry_id`, `customer_id`, `vendor_id`, `status`, `last_message_at`

### `thread_messages` Table
Stores individual messages within threads.

| Column | Type | Description |
|--------|------|-------------|
| `id` | bigint | Primary key |
| `thread_id` | bigint | Foreign key to threads table |
| `sender_id` | bigint | Foreign key to users table |
| `sender_type` | enum | `customer`, `vendor`, `admin`, `system` |
| `message_type` | enum | `text`, `offer`, `quotation`, `system` |
| `message` | text | Message content (nullable for attachments-only) |
| `attachments` | json | Array of file attachments (see format below) |
| `offer_id` | bigint | Foreign key to offers table (nullable) |
| `quotation_id` | bigint | Foreign key to quotations table (nullable) |
| `is_read_customer` | boolean | Has customer read this message? |
| `is_read_vendor` | boolean | Has vendor read this message? |
| `read_at` | timestamp | When message was first read |
| `created_at` | timestamp | Message sent time |
| `updated_at` | timestamp | Last update time |

**Indexes:**
- `thread_id`, `sender_id`, `message_type`, `created_at`
- Compound: `(thread_id, created_at)` for efficient message ordering

**Attachments JSON Format:**
```json
[
  {
    "name": "invoice.pdf",
    "path": "threads/123/xyz.pdf",
    "size": 204800,
    "type": "application/pdf"
  }
]
```

---

## Models

### Thread Model (`app/Models/Thread.php`)

**Purpose:** Manages conversation threads between customers and vendors.

#### Key Relationships
```php
$thread->enquiry        // Get associated enquiry
$thread->customer       // Get customer user
$thread->vendor         // Get vendor user
$thread->messages       // Get all messages (ordered by created_at ASC)
$thread->latestMessage  // Get most recent message
```

#### Important Methods

##### `markAsReadFor($userId)`
Marks all unread messages as read for a specific user.

**Usage:**
```php
$thread = Thread::find(1);
$thread->markAsReadFor(auth()->id());
```

**What it does:**
- Finds all unread messages for the user
- Updates `is_read_customer` or `is_read_vendor` to true
- Sets `read_at` timestamp
- Resets unread count to 0

##### `getTitleAttribute()`
Generates a human-readable title for the thread.

**Example Output:**
- "Enquiry #123 - Times Square Billboard"
- "Thread #456" (if no enquiry)

##### `getOtherParticipant($currentUserId)`
Gets the other person in the conversation.

**Usage:**
```php
$otherUser = $thread->getOtherParticipant(auth()->id());
echo $otherUser->name; // "John Doe"
```

#### Scopes

##### `active()`
Get only active threads (not archived).

**Usage:**
```php
$threads = Thread::active()->get();
```

##### `forCustomer($customerId)`
Get threads for a specific customer.

**Usage:**
```php
$myThreads = Thread::forCustomer(auth()->id())->get();
```

##### `forVendor($vendorId)`
Get threads for a specific vendor.

**Usage:**
```php
$myThreads = Thread::forVendor(auth()->id())->get();
```

##### `withUnread($userId)`
Get threads that have unread messages for a user.

**Usage:**
```php
$unreadThreads = Thread::withUnread(auth()->id())->get();
```

---

### ThreadMessage Model (`app/Models/ThreadMessage.php`)

**Purpose:** Manages individual messages within threads.

#### Key Relationships
```php
$message->thread      // Get parent thread
$message->sender      // Get user who sent message
$message->offer       // Get linked offer (if any)
$message->quotation   // Get linked quotation (if any)
```

#### Important Methods

##### `isReadBy($userId)`
Check if a user has read this message.

**Usage:**
```php
if ($message->isReadBy(auth()->id())) {
    echo "You've read this message";
}
```

##### `markAsReadBy($userId)`
Mark message as read by a specific user.

**Usage:**
```php
$message->markAsReadBy(auth()->id());
```

##### `hasAttachments()`
Check if message has file attachments.

**Usage:**
```php
if ($message->hasAttachments()) {
    // Show attachment icons
}
```

##### `getAttachmentUrls()`
Get formatted attachment URLs for display.

**Returns:**
```php
[
    [
        'name' => 'invoice.pdf',
        'url' => 'https://example.com/storage/threads/123/invoice.pdf',
        'size' => 204800,
        'type' => 'application/pdf'
    ]
]
```

**Usage:**
```php
foreach ($message->getAttachmentUrls() as $attachment) {
    echo "<a href='{$attachment['url']}'>{$attachment['name']}</a>";
}
```

#### Automatic Behaviors

The model automatically updates the parent thread when a message is created:

```php
// When you create a message:
ThreadMessage::create([
    'thread_id' => 1,
    'sender_id' => 2,
    'sender_type' => 'customer',
    'message' => 'Hello!'
]);

// The following happens automatically:
// 1. Thread's last_message_at is set to now()
// 2. Thread's unread_count_vendor is incremented by 1
```

---

## Controllers

### Customer ThreadController (`app/Http/Controllers/Customer/ThreadController.php`)

Handles all messaging functionality for customers.

#### `index(Request $request)`
**Purpose:** Display inbox with list of threads.

**Route:** `GET /customer/threads`

**Returns:**
- **Web:** Blade view with threads list
- **API:** JSON with paginated threads

**Example API Response:**
```json
{
  "success": true,
  "threads": [
    {
      "id": 1,
      "title": "Enquiry #123 - Times Square Billboard",
      "enquiry_id": 123,
      "vendor": {
        "id": 5,
        "name": "ABC Advertising",
        "company_name": "ABC Corp"
      },
      "status": "active",
      "last_message": {
        "message": "Thank you for your enquiry",
        "sender": "ABC Advertising",
        "created_at": "2 minutes ago"
      },
      "unread_count": 3,
      "last_message_at": "2 minutes ago"
    }
  ],
  "pagination": {
    "current_page": 1,
    "last_page": 5,
    "total": 87
  }
}
```

#### `show(Request $request, $id)`
**Purpose:** Show specific thread with all messages.

**Route:** `GET /customer/threads/{id}`

**What it does:**
1. Loads thread with messages
2. Automatically marks messages as read
3. Returns thread details and messages

**Example API Response:**
```json
{
  "success": true,
  "thread": {
    "id": 1,
    "title": "Enquiry #123 - Times Square Billboard",
    "status": "active",
    "vendor": {
      "id": 5,
      "name": "ABC Advertising",
      "phone": "+1234567890"
    }
  },
  "messages": [
    {
      "id": 1,
      "message": "Hi, I'm interested in this hoarding",
      "message_type": "text",
      "sender": {
        "id": 2,
        "name": "John Doe",
        "is_me": true
      },
      "attachments": [],
      "is_read": true,
      "created_at": "Dec 9, 2025 10:30 AM",
      "formatted_time": "5 minutes ago"
    }
  ]
}
```

#### `sendMessage(Request $request, $id)`
**Purpose:** Send a new message in the thread.

**Route:** `POST /customer/threads/{id}/send-message`

**Request Parameters:**
- `message` (string, optional): Message text (required if no attachments)
- `attachments[]` (files, optional): Array of files (max 5, 10MB each)

**Validation Rules:**
```php
'message' => 'required_without:attachments|string|max:5000',
'attachments' => 'nullable|array|max:5',
'attachments.*' => 'file|max:10240' // 10MB
```

**Example Request (JavaScript):**
```javascript
const formData = new FormData();
formData.append('message', 'Hello, vendor!');
formData.append('attachments[]', fileInput.files[0]);

fetch('/customer/threads/1/send-message', {
    method: 'POST',
    headers: {
        'X-CSRF-TOKEN': csrfToken,
        'Accept': 'application/json'
    },
    body: formData
})
.then(response => response.json())
.then(data => {
    console.log('Message sent!', data);
});
```

**Example Response:**
```json
{
  "success": true,
  "message": "Message sent successfully",
  "data": {
    "id": 45,
    "message": "Hello, vendor!",
    "sender": {
      "id": 2,
      "name": "John Doe"
    },
    "attachments": [
      {
        "name": "invoice.pdf",
        "url": "https://example.com/storage/threads/1/invoice.pdf"
      }
    ],
    "created_at": "Dec 9, 2025 10:35 AM",
    "formatted_time": "Just now"
  }
}
```

#### `markAsRead(Request $request, $id)`
**Purpose:** Mark all messages in thread as read.

**Route:** `POST /customer/threads/{id}/mark-read`

**Usage:**
```javascript
fetch('/customer/threads/1/mark-read', {
    method: 'POST',
    headers: {
        'X-CSRF-TOKEN': csrfToken,
        'Accept': 'application/json'
    }
});
```

#### `archive(Request $request, $id)`
**Purpose:** Archive a thread (hide from main inbox).

**Route:** `POST /customer/threads/{id}/archive`

**What it does:**
- Changes thread status to "archived"
- Thread won't appear in inbox list anymore

#### `unreadCount(Request $request)`
**Purpose:** Get total number of unread threads.

**Route:** `GET /customer/threads/unread-count`

**Returns:**
```json
{
  "success": true,
  "unread_count": 5
}
```

**Usage in JavaScript:**
```javascript
// Load unread count
function loadUnreadCount() {
    fetch('/customer/threads/unread-count')
        .then(response => response.json())
        .then(data => {
            document.getElementById('badge').textContent = data.unread_count;
        });
}

// Call every 30 seconds
setInterval(loadUnreadCount, 30000);
```

---

### Vendor ThreadController (`app/Http/Controllers/Vendor/ThreadController.php`)

**Note:** Identical to Customer ThreadController but for vendor routes (`/vendor/threads`).

All methods work the same way, just replace `/customer/` with `/vendor/` in routes.

---

## Views

### Vendor Inbox View (`resources/views/vendor/threads/index.blade.php`)

**Purpose:** Full-featured inbox interface with split-view layout.

#### Layout Structure
```
+------------------+------------------------+
| Thread List      | Messages Area          |
| (Left Sidebar)   | (Right Main Content)   |
|                  |                        |
| - Search box     | - Thread Header        |
| - Thread 1 ●●    | - Messages (bubbles)   |
| - Thread 2       | - Message Input        |
| - Thread 3 ●     |                        |
+------------------+------------------------+
```

#### Key JavaScript Functions

##### `loadThreads()`
Loads all threads from server and displays in sidebar.

**Called:**
- On page load
- After sending a message (to update last message)

**Example:**
```javascript
function loadThreads() {
    fetch('/vendor/threads', {
        headers: { 'Accept': 'application/json' }
    })
    .then(response => response.json())
    .then(data => {
        threads = data.threads;
        renderThreads(threads);
    });
}
```

##### `loadThread(threadId)`
Loads a specific thread's messages.

**Called:**
- When user clicks a thread in sidebar

**What it does:**
1. Shows loading spinner
2. Fetches thread messages
3. Renders messages in chat bubbles
4. Marks thread as read
5. Scrolls to bottom

**Example:**
```javascript
function loadThread(threadId) {
    currentThreadId = threadId;
    
    // Update active state in sidebar
    document.querySelectorAll('.thread-item').forEach(item => {
        item.classList.remove('active');
    });
    document.querySelector(`[data-thread-id="${threadId}"]`).classList.add('active');
    
    // Load messages
    fetch(`/vendor/threads/${threadId}`, {
        headers: { 'Accept': 'application/json' }
    })
    .then(response => response.json())
    .then(data => {
        renderThread(data.thread, data.messages);
    });
}
```

##### `sendMessage()`
Sends a new message with optional attachments.

**Called:**
- When user submits message form
- When user presses Enter (without Shift)

**Example:**
```javascript
function sendMessage() {
    const messageText = document.getElementById('messageText').value.trim();
    const fileInput = document.getElementById('fileInput');
    
    if (!messageText && fileInput.files.length === 0) {
        return; // Nothing to send
    }
    
    const formData = new FormData();
    formData.append('message', messageText);
    
    // Add files
    for (let i = 0; i < fileInput.files.length; i++) {
        formData.append('attachments[]', fileInput.files[i]);
    }
    
    fetch(`/vendor/threads/${currentThreadId}/send-message`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrfToken },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Clear form
            document.getElementById('messageText').value = '';
            fileInput.value = '';
            
            // Add message to UI
            addMessageToUI(data.data);
            
            // Reload threads (update last message)
            loadThreads();
        }
    });
}
```

##### `renderMessage(msg)`
Renders a single message as HTML.

**Returns:** HTML string for message bubble

**Message Types:**
1. **System Message:** Centered, gray background
2. **Sent Message:** Right-aligned, blue background
3. **Received Message:** Left-aligned, white background with border

**Example Output:**
```html
<div class="message-bubble sent">
    <div class="message-content">
        <div>Hello, how are you?</div>
        <div class="mt-2">
            <a href="..." class="attachment-item">
                <i class="bi bi-paperclip"></i>invoice.pdf
            </a>
        </div>
    </div>
    <div class="message-time">5 minutes ago</div>
</div>
```

#### CSS Classes

##### `.thread-item`
Individual thread in sidebar.

**States:**
- `.active` - Currently selected thread (blue background)
- `.unread` - Has unread messages (bold text)

##### `.message-bubble`
Individual message container.

**Variants:**
- `.sent` - Message sent by current user (right-aligned, blue)
- `.received` - Message from other user (left-aligned, white)

##### `.system-message`
System-generated message (centered, gray).

---

### Thread Notification Badge (`resources/views/components/thread-notification-badge.blade.php`)

**Purpose:** Shows unread message count in navigation.

#### Usage in Layout
```blade
<!-- In layouts/customer.blade.php or layouts/vendor.blade.php -->
<nav>
    <!-- Other nav items -->
    
    @include('components.thread-notification-badge', ['role' => 'customer'])
</nav>
```

#### How It Works
1. Displays a chat icon with badge
2. Badge shows unread count (hidden if 0)
3. Auto-refreshes every 30 seconds
4. Clicking goes to threads inbox

#### JavaScript
```javascript
// Automatically loads unread count
function loadUnreadThreadCount() {
    fetch('/customer/threads/unread-count')
        .then(response => response.json())
        .then(data => {
            const badge = document.getElementById('unreadThreadCount');
            badge.textContent = data.unread_count;
            badge.style.display = data.unread_count > 0 ? 'inline-block' : 'none';
        });
}

// Called on page load and every 30 seconds
```

---

## Routes

### Web Routes (Blade Views)

**Customer Routes:**
```php
// In routes/web.php
Route::prefix('customer')->middleware(['auth', 'role:customer'])->group(function () {
    Route::get('/threads', [CustomerThreadController::class, 'index'])->name('customer.threads.index');
    Route::get('/threads/{id}', [CustomerThreadController::class, 'show'])->name('customer.threads.show');
    Route::post('/threads/{id}/send-message', [CustomerThreadController::class, 'sendMessage'])->name('customer.threads.send-message');
    Route::post('/threads/{id}/mark-read', [CustomerThreadController::class, 'markAsRead'])->name('customer.threads.mark-read');
    Route::post('/threads/{id}/archive', [CustomerThreadController::class, 'archive'])->name('customer.threads.archive');
    Route::get('/threads/unread-count', [CustomerThreadController::class, 'unreadCount'])->name('customer.threads.unread-count');
});
```

**Vendor Routes:**
```php
// Same as customer but with /vendor prefix
Route::prefix('vendor')->middleware(['auth', 'role:vendor'])->group(function () {
    // ... same routes as customer
});
```

### API Routes (JSON Responses)

**File:** `routes/api_v1/threads.php`

**Customer API Routes:**
```php
Route::middleware(['auth:sanctum', 'role:customer'])->prefix('customer')->group(function () {
    Route::get('/threads', [CustomerThreadController::class, 'index']);
    Route::get('/threads/{id}', [CustomerThreadController::class, 'show']);
    Route::post('/threads/{id}/send-message', [CustomerThreadController::class, 'sendMessage']);
    Route::post('/threads/{id}/mark-read', [CustomerThreadController::class, 'markAsRead']);
    Route::post('/threads/{id}/archive', [CustomerThreadController::class, 'archive']);
    Route::get('/threads/unread-count', [CustomerThreadController::class, 'unreadCount']);
});
```

**Usage from Mobile App:**
```javascript
// Example: Load threads from mobile app
fetch('https://api.example.com/api/v1/customer/threads', {
    headers: {
        'Authorization': 'Bearer ' + apiToken,
        'Accept': 'application/json'
    }
})
.then(response => response.json())
.then(data => {
    console.log('Threads:', data.threads);
});
```

---

## Common Tasks

### Task 1: Add a New Message Type

**Scenario:** You want to add support for "booking_confirmation" messages.

**Steps:**

1. **Update Database Migration:**
```php
// In create_thread_messages_table migration
Schema::create('thread_messages', function (Blueprint $table) {
    // ...
    $table->enum('message_type', [
        'text', 
        'offer', 
        'quotation', 
        'system',
        'booking_confirmation' // ADD THIS
    ])->default('text');
    // ...
});
```

2. **Update ThreadMessage Model:**
```php
// app/Models/ThreadMessage.php
class ThreadMessage extends Model
{
    // Add constant
    const TYPE_BOOKING_CONFIRMATION = 'booking_confirmation';
    
    // Rest of the model...
}
```

3. **Create System Message When Booking is Confirmed:**
```php
// In Booking model or controller
$thread = Thread::where('enquiry_id', $booking->enquiry_id)->first();
if ($thread) {
    ThreadMessage::create([
        'thread_id' => $thread->id,
        'sender_id' => $booking->vendor_id,
        'sender_type' => 'system',
        'message_type' => 'booking_confirmation',
        'message' => "Booking #{$booking->id} has been confirmed!",
    ]);
}
```

4. **Update Frontend to Display Special Icon:**
```javascript
// In renderMessage() function
function renderMessage(msg) {
    if (msg.message_type === 'booking_confirmation') {
        return `
            <div class="system-message">
                <i class="bi bi-check-circle-fill text-success"></i>
                ${msg.message}
            </div>
        `;
    }
    // ... rest of the code
}
```

### Task 2: Add Support for Voice Messages

**Scenario:** Allow users to send voice recordings.

**Steps:**

1. **Update Attachments Structure:**
```php
// Voice message attachment format
[
    'name' => 'voice_message.mp3',
    'path' => 'threads/123/voice_xyz.mp3',
    'size' => 52480,
    'type' => 'audio/mp3',
    'duration' => 15 // seconds
]
```

2. **Add Voice Recording to Frontend:**
```html
<!-- In vendor/threads/index.blade.php -->
<button type="button" id="recordVoiceBtn" title="Record voice message">
    <i class="bi bi-mic"></i>
</button>
```

```javascript
// JavaScript for voice recording
let mediaRecorder;
let audioChunks = [];

document.getElementById('recordVoiceBtn').addEventListener('click', async function() {
    if (!mediaRecorder || mediaRecorder.state === 'inactive') {
        // Start recording
        const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
        mediaRecorder = new MediaRecorder(stream);
        
        mediaRecorder.ondataavailable = (event) => {
            audioChunks.push(event.data);
        };
        
        mediaRecorder.onstop = () => {
            const audioBlob = new Blob(audioChunks, { type: 'audio/mp3' });
            const audioFile = new File([audioBlob], 'voice_message.mp3', { type: 'audio/mp3' });
            
            // Add to file input
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(audioFile);
            document.getElementById('fileInput').files = dataTransfer.files;
            
            // Reset
            audioChunks = [];
        };
        
        mediaRecorder.start();
        this.innerHTML = '<i class="bi bi-stop-circle"></i>';
        this.classList.add('recording');
    } else {
        // Stop recording
        mediaRecorder.stop();
        this.innerHTML = '<i class="bi bi-mic"></i>';
        this.classList.remove('recording');
    }
});
```

3. **Update Display Logic:**
```javascript
// In renderMessage() function
if (attachment.type.startsWith('audio/')) {
    return `
        <audio controls>
            <source src="${attachment.url}" type="${attachment.type}">
        </audio>
        <div class="small text-muted">
            Voice message • ${formatDuration(attachment.duration)}
        </div>
    `;
}
```

### Task 3: Add Typing Indicator

**Scenario:** Show "Vendor is typing..." when other user is typing.

**Steps:**

1. **Add WebSocket or AJAX Polling:**
```javascript
// When user starts typing
let typingTimeout;
document.getElementById('messageText').addEventListener('input', function() {
    clearTimeout(typingTimeout);
    
    // Send "typing" event
    fetch(`/vendor/threads/${currentThreadId}/typing`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        }
    });
    
    // Stop typing after 2 seconds of inactivity
    typingTimeout = setTimeout(() => {
        fetch(`/vendor/threads/${currentThreadId}/stop-typing`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        });
    }, 2000);
});
```

2. **Add Controller Methods:**
```php
// In ThreadController
public function typing(Request $request, $id)
{
    $thread = Thread::where('vendor_id', $request->user()->id)->findOrFail($id);
    
    // Store in cache (expires in 5 seconds)
    Cache::put("thread_{$id}_vendor_typing", true, 5);
    
    return response()->json(['success' => true]);
}

public function stopTyping(Request $request, $id)
{
    Cache::forget("thread_{$id}_vendor_typing");
    return response()->json(['success' => true]);
}
```

3. **Check Typing Status:**
```php
// In show() method
public function show(Request $request, $id)
{
    // ... existing code ...
    
    if ($request->expectsJson()) {
        return response()->json([
            'success' => true,
            'thread' => $threadData,
            'messages' => $messagesData,
            'other_user_typing' => Cache::get("thread_{$id}_customer_typing", false)
        ]);
    }
}
```

4. **Display Typing Indicator:**
```javascript
// Poll for typing status
setInterval(function() {
    if (!currentThreadId) return;
    
    fetch(`/vendor/threads/${currentThreadId}`, {
        headers: { 'Accept': 'application/json' }
    })
    .then(response => response.json())
    .then(data => {
        const typingIndicator = document.getElementById('typingIndicator');
        if (data.other_user_typing) {
            typingIndicator.style.display = 'block';
            typingIndicator.textContent = 'Customer is typing...';
        } else {
            typingIndicator.style.display = 'none';
        }
    });
}, 2000);
```

### Task 4: Add Message Search

**Scenario:** Allow users to search messages within a thread.

**Steps:**

1. **Add Search Input:**
```html
<!-- In vendor/threads/index.blade.php -->
<div class="p-3 border-bottom">
    <input type="text" class="form-control form-control-sm" id="searchMessages"
           placeholder="Search messages..." style="display: none;">
</div>
```

2. **Add JavaScript Search:**
```javascript
document.getElementById('searchMessages').addEventListener('input', function(e) {
    const query = e.target.value.toLowerCase();
    
    document.querySelectorAll('.message-bubble').forEach(bubble => {
        const text = bubble.textContent.toLowerCase();
        if (text.includes(query)) {
            bubble.style.display = 'block';
            // Highlight matching text
            bubble.classList.add('search-highlight');
        } else {
            bubble.style.display = 'none';
        }
    });
});
```

3. **Add Backend Search (Optional):**
```php
// In ThreadController
public function searchMessages(Request $request, $id)
{
    $thread = Thread::where('customer_id', $request->user()->id)->findOrFail($id);
    $query = $request->query('q');
    
    $messages = $thread->messages()
        ->where('message', 'LIKE', "%{$query}%")
        ->get();
    
    return response()->json([
        'success' => true,
        'messages' => $messages
    ]);
}
```

### Task 5: Add Email Notifications

**Scenario:** Send email when new message is received.

**Steps:**

1. **Create Notification Class:**
```bash
php artisan make:notification NewThreadMessage
```

2. **Define Notification:**
```php
// app/Notifications/NewThreadMessage.php
namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class NewThreadMessage extends Notification
{
    protected $thread;
    protected $message;
    
    public function __construct($thread, $message)
    {
        $this->thread = $thread;
        $this->message = $message;
    }
    
    public function via($notifiable)
    {
        return ['mail', 'database'];
    }
    
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('New Message from ' . $this->message->sender->name)
            ->line('You have a new message in thread: ' . $this->thread->title)
            ->line($this->message->message)
            ->action('View Message', route('customer.threads.show', $this->thread->id));
    }
    
    public function toArray($notifiable)
    {
        return [
            'thread_id' => $this->thread->id,
            'message_id' => $this->message->id,
            'sender_name' => $this->message->sender->name,
            'message' => $this->message->message,
        ];
    }
}
```

3. **Send Notification in Controller:**
```php
// In sendMessage() method
public function sendMessage(Request $request, $id)
{
    // ... create message ...
    
    // Send notification to other participant
    $thread = Thread::findOrFail($id);
    $recipient = $thread->getOtherParticipant($request->user()->id);
    
    if ($recipient) {
        $recipient->notify(new NewThreadMessage($thread, $message));
    }
    
    return response()->json([...]);
}
```

---

## Troubleshooting

### Problem: Messages Not Showing

**Symptoms:**
- Inbox loads but clicking thread shows no messages
- Console shows 404 or 500 error

**Solution:**
1. Check if thread belongs to current user:
```php
// Make sure you're using correct query
$thread = Thread::where('customer_id', auth()->id())->findOrFail($id);
// NOT just: Thread::findOrFail($id)
```

2. Check relationships are loaded:
```php
$thread = Thread::with(['messages.sender'])->findOrFail($id);
```

3. Check JavaScript console for errors:
```javascript
fetch('/customer/threads/1')
    .then(response => {
        console.log('Response status:', response.status);
        return response.json();
    })
    .catch(error => console.error('Error:', error));
```

### Problem: File Upload Not Working

**Symptoms:**
- Files selected but not uploading
- "Failed to send message" error

**Solution:**
1. Check file size limits in `php.ini`:
```ini
upload_max_filesize = 10M
post_max_size = 12M
```

2. Check form encoding:
```html
<form enctype="multipart/form-data">
```

3. Check JavaScript FormData:
```javascript
const formData = new FormData();
// Don't do this: formData.append('attachments', files);
// Do this instead:
for (let i = 0; i < files.length; i++) {
    formData.append('attachments[]', files[i]);
}
```

4. Check storage permissions:
```bash
chmod -R 775 storage/app/public
php artisan storage:link
```

### Problem: Unread Count Not Updating

**Symptoms:**
- Badge shows wrong number
- Badge doesn't update after reading messages

**Solution:**
1. Make sure markAsRead is being called:
```javascript
function loadThread(threadId) {
    // ... load thread ...
    
    // Don't forget this:
    markThreadAsRead(threadId);
}
```

2. Check if boot method is running:
```php
// In ThreadMessage model
protected static function boot()
{
    parent::boot();
    
    static::created(function ($message) {
        \Log::info('Message created, updating thread counts');
        // ... update counts ...
    });
}
```

3. Manually reset counts:
```php
// In markAsReadFor() method
$thread->update(['unread_count_customer' => 0]);
```

### Problem: CSRF Token Mismatch

**Symptoms:**
- "CSRF token mismatch" error when sending messages
- 419 HTTP status code

**Solution:**
1. Include CSRF token in meta tag:
```html
<!-- In layout -->
<meta name="csrf-token" content="{{ csrf_token() }}">
```

2. Get token in JavaScript:
```javascript
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
```

3. Include in fetch requests:
```javascript
fetch('/customer/threads/1/send-message', {
    method: 'POST',
    headers: {
        'X-CSRF-TOKEN': csrfToken,
        'Accept': 'application/json'
    },
    body: formData
});
```

### Problem: Thread Not Created on Enquiry

**Symptoms:**
- Customer creates enquiry but no thread appears
- Can't message vendor

**Solution:**
1. Check if boot method is running in Enquiry model:
```php
// In app/Models/Enquiry.php
protected static function boot()
{
    parent::boot();
    
    static::created(function ($enquiry) {
        \Log::info('Enquiry created, creating thread');
        
        if ($enquiry->hoarding && $enquiry->hoarding->vendor_id) {
            Thread::create([...]);
        } else {
            \Log::error('Cannot create thread: No vendor found');
        }
    });
}
```

2. Check if hoarding has vendor_id:
```php
$hoarding = Hoarding::find(1);
echo $hoarding->vendor_id; // Should not be null
```

3. Manually create thread:
```php
// If thread doesn't exist, create it
$thread = Thread::firstOrCreate([
    'enquiry_id' => $enquiry->id,
], [
    'customer_id' => $enquiry->customer_id,
    'vendor_id' => $enquiry->hoarding->vendor_id,
    'status' => 'active',
    'last_message_at' => now(),
]);
```

---

## Best Practices

### 1. Always Check Permissions
```php
// Don't do this:
$thread = Thread::findOrFail($id);

// Do this:
$thread = Thread::where('customer_id', auth()->id())->findOrFail($id);
```

### 2. Use Eager Loading
```php
// Don't do this (N+1 problem):
$threads = Thread::all();
foreach ($threads as $thread) {
    echo $thread->vendor->name; // New query each time
}

// Do this:
$threads = Thread::with('vendor')->get();
foreach ($threads as $thread) {
    echo $thread->vendor->name; // Already loaded
}
```

### 3. Validate File Uploads
```php
$validator = Validator::make($request->all(), [
    'attachments' => 'nullable|array|max:5',
    'attachments.*' => 'file|mimes:jpg,png,pdf,doc,docx|max:10240'
]);
```

### 4. Handle Errors Gracefully
```javascript
fetch('/customer/threads/1/send-message', {...})
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Success
        } else {
            alert(data.message || 'Failed to send message');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
    });
```

### 5. Sanitize User Input
```php
// In controller
$message = strip_tags($request->message); // Remove HTML tags
$message = substr($message, 0, 5000); // Limit length
```

### 6. Use Transactions for Critical Operations
```php
DB::transaction(function () use ($request, $thread) {
    $message = ThreadMessage::create([...]);
    $thread->update(['last_message_at' => now()]);
    // If any operation fails, both are rolled back
});
```

---

## Performance Optimization

### 1. Index Database Columns
```php
// In migration
$table->index('last_message_at'); // For sorting
$table->index(['thread_id', 'created_at']); // For message ordering
```

### 2. Paginate Large Lists
```php
// Don't load all at once:
$threads = Thread::all();

// Use pagination:
$threads = Thread::paginate(20);
```

### 3. Cache Unread Counts
```php
// Cache for 5 minutes
$unreadCount = Cache::remember("user_{$userId}_unread_count", 300, function () use ($userId) {
    return Thread::where('customer_id', $userId)
        ->where('unread_count_customer', '>', 0)
        ->count();
});
```

### 4. Use WebSockets Instead of Polling
For production, consider using Laravel Echo + Pusher or Socket.io for real-time updates instead of AJAX polling.

```javascript
// With Laravel Echo
Echo.private(`thread.${threadId}`)
    .listen('NewMessagePosted', (e) => {
        addMessageToUI(e.message);
    });
```

---

## Security Checklist

- ✅ CSRF protection on all POST requests
- ✅ Authorization checks (user can only access their own threads)
- ✅ File upload validation (size, type, count)
- ✅ Input sanitization (strip HTML tags)
- ✅ XSS prevention (escape output in Blade templates)
- ✅ SQL injection prevention (use Eloquent ORM)
- ✅ Rate limiting on message sending
- ✅ File storage outside public directory

---

## Conclusion

The Universal Messaging Thread System provides a complete solution for customer-vendor communication. By following this documentation, you should be able to:

- Understand how threads and messages work
- Modify existing functionality
- Add new features
- Troubleshoot common issues
- Optimize performance

For additional help, refer to the Laravel documentation or contact the development team.

**Happy Coding! 🚀**
