@extends('layouts.vendor')

@section('title', 'Messages')

@section('content')
<div class="container-fluid py-4">
    <div class="row g-0" style="height: calc(100vh - 120px);">
        <!-- Threads List Sidebar -->
        <div class="col-md-4 col-lg-3 border-end">
            <div class="d-flex flex-column h-100">
                <!-- Header -->
                <div class="p-3 border-bottom bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="bi bi-chat-dots me-2"></i>Messages</h5>
                        <span class="badge bg-primary" id="unreadBadge">0</span>
                    </div>
                    <div class="mt-3">
                        <input type="text" class="form-control form-control-sm" id="searchThreads" 
                               placeholder="Search conversations...">
                    </div>
                </div>

                <!-- Threads List -->
                <div class="overflow-auto flex-grow-1" id="threadsList">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Messages Area -->
        <div class="col-md-8 col-lg-9">
            <div class="d-flex flex-column h-100">
                <!-- Thread Header -->
                <div class="p-3 border-bottom bg-white" id="threadHeader" style="display: none;">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0" id="threadTitle"></h6>
                            <small class="text-muted" id="threadSubtitle"></small>
                        </div>
                        <div>
                            <button class="btn btn-sm btn-outline-secondary" id="archiveThreadBtn" title="Archive">
                                <i class="bi bi-archive"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Messages Area -->
                <div class="overflow-auto flex-grow-1 p-3 bg-light" id="messagesArea">
                    <div class="text-center text-muted py-5" id="noThreadSelected">
                        <i class="bi bi-chat-dots display-1 mb-3"></i>
                        <p class="lead">Select a conversation to start messaging</p>
                    </div>
                    <div id="messagesList"></div>
                </div>

                <!-- Message Input -->
                <div class="p-3 border-top bg-white" id="messageInput" style="display: none;">
                    <form id="messageForm" enctype="multipart/form-data">
                        <div class="mb-2" id="attachmentsPreview"></div>
                        <div class="input-group">
                            <button type="button" class="btn btn-outline-secondary" id="attachBtn" title="Attach files">
                                <i class="bi bi-paperclip"></i>
                            </button>
                            <input type="file" id="fileInput" name="attachments[]" multiple 
                                   accept="image/*,application/pdf,.doc,.docx" style="display: none;">
                            <textarea class="form-control" id="messageText" name="message" 
                                      placeholder="Type your message..." rows="1" style="resize: none;"></textarea>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-send me-1"></i>Send
                            </button>
                        </div>
                        <small class="text-muted">Press Enter to send, Shift+Enter for new line</small>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.thread-item {
    padding: 1rem;
    border-bottom: 1px solid #e9ecef;
    cursor: pointer;
    transition: background-color 0.2s;
}

.thread-item:hover {
    background-color: #f8f9fa;
}

.thread-item.active {
    background-color: #e7f1ff;
    border-left: 3px solid #0d6efd;
}

.thread-item.unread {
    background-color: #fff;
    font-weight: 600;
}

.message-bubble {
    max-width: 70%;
    margin-bottom: 1rem;
}

.message-bubble.sent {
    margin-left: auto;
}

.message-bubble.received {
    margin-right: auto;
}

.message-content {
    padding: 0.75rem 1rem;
    border-radius: 1rem;
    word-wrap: break-word;
}

.message-bubble.sent .message-content {
    background-color: #0d6efd;
    color: white;
    border-bottom-right-radius: 0.25rem;
}

.message-bubble.received .message-content {
    background-color: white;
    border: 1px solid #dee2e6;
    border-bottom-left-radius: 0.25rem;
}

.message-time {
    font-size: 0.75rem;
    color: #6c757d;
    margin-top: 0.25rem;
}

.message-bubble.sent .message-time {
    text-align: right;
}

.system-message {
    text-align: center;
    color: #6c757d;
    font-size: 0.875rem;
    margin: 1rem 0;
    padding: 0.5rem;
    background-color: #f8f9fa;
    border-radius: 0.5rem;
}

.attachment-item {
    display: inline-flex;
    align-items: center;
    padding: 0.5rem;
    background-color: rgba(255,255,255,0.1);
    border-radius: 0.5rem;
    margin-top: 0.5rem;
    margin-right: 0.5rem;
}

.message-bubble.received .attachment-item {
    background-color: #f8f9fa;
}

.attachment-preview {
    display: inline-block;
    padding: 0.5rem;
    margin-right: 0.5rem;
    background-color: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 0.5rem;
    position: relative;
}

.attachment-preview .remove-attachment {
    position: absolute;
    top: -8px;
    right: -8px;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background-color: #dc3545;
    color: white;
    border: none;
    cursor: pointer;
    font-size: 0.75rem;
    line-height: 1;
}
</style>

<script>
let currentThreadId = null;
let threads = [];

document.addEventListener('DOMContentLoaded', function() {
    loadThreads();
    loadUnreadCount();
    
    // Auto-refresh every 10 seconds
    setInterval(loadUnreadCount, 10000);
    
    // Search threads
    document.getElementById('searchThreads').addEventListener('input', function(e) {
        const query = e.target.value.toLowerCase();
        document.querySelectorAll('.thread-item').forEach(item => {
            const text = item.textContent.toLowerCase();
            item.style.display = text.includes(query) ? 'block' : 'none';
        });
    });
    
    // Attach files
    document.getElementById('attachBtn').addEventListener('click', function() {
        document.getElementById('fileInput').click();
    });
    
    document.getElementById('fileInput').addEventListener('change', function(e) {
        handleFileSelect(e.target.files);
    });
    
    // Message form submit
    document.getElementById('messageForm').addEventListener('submit', function(e) {
        e.preventDefault();
        sendMessage();
    });
    
    // Auto-resize textarea
    document.getElementById('messageText').addEventListener('input', function() {
        this.style.height = 'auto';
        this.style.height = (this.scrollHeight) + 'px';
    });
    
    // Enter to send, Shift+Enter for new line
    document.getElementById('messageText').addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            document.getElementById('messageForm').dispatchEvent(new Event('submit'));
        }
    });
    
    // Archive thread
    document.getElementById('archiveThreadBtn').addEventListener('click', function() {
        if (confirm('Archive this conversation?')) {
            archiveThread(currentThreadId);
        }
    });
});

function loadThreads() {
    fetch('/vendor/threads', {
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            threads = data.threads;
            renderThreads(threads);
        }
    })
    .catch(error => {
        console.error('Error loading threads:', error);
    });
}

function renderThreads(threadsList) {
    const container = document.getElementById('threadsList');
    
    if (threadsList.length === 0) {
        container.innerHTML = `
            <div class="text-center text-muted py-5">
                <i class="bi bi-inbox display-4"></i>
                <p class="mt-3">No conversations yet</p>
            </div>
        `;
        return;
    }
    
    container.innerHTML = threadsList.map(thread => `
        <div class="thread-item ${thread.unread_count > 0 ? 'unread' : ''}" 
             data-thread-id="${thread.id}" onclick="loadThread(${thread.id})">
            <div class="d-flex justify-content-between align-items-start mb-1">
                <strong>${thread.customer ? thread.customer.name : 'Customer'}</strong>
                <small class="text-muted">${thread.last_message_at || ''}</small>
            </div>
            <div class="text-muted small text-truncate">${thread.title}</div>
            ${thread.last_message ? `
                <div class="text-muted small text-truncate mt-1">
                    ${thread.last_message.message || 'Attachment'}
                </div>
            ` : ''}
            ${thread.unread_count > 0 ? `
                <span class="badge bg-primary badge-sm mt-1">${thread.unread_count} new</span>
            ` : ''}
        </div>
    `).join('');
}

function loadThread(threadId) {
    currentThreadId = threadId;
    
    // Update active state
    document.querySelectorAll('.thread-item').forEach(item => {
        item.classList.remove('active');
    });
    document.querySelector(`[data-thread-id="${threadId}"]`)?.classList.add('active');
    
    // Show loading
    document.getElementById('noThreadSelected').style.display = 'none';
    document.getElementById('messagesList').innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status"></div>
        </div>
    `;
    
    fetch(`/vendor/threads/${threadId}`, {
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            renderThread(data.thread, data.messages);
            markThreadAsRead(threadId);
        }
    })
    .catch(error => {
        console.error('Error loading thread:', error);
    });
}

function renderThread(thread, messages) {
    // Update header
    document.getElementById('threadHeader').style.display = 'block';
    document.getElementById('threadTitle').textContent = thread.customer ? thread.customer.name : 'Customer';
    document.getElementById('threadSubtitle').textContent = thread.title;
    
    // Show message input
    document.getElementById('messageInput').style.display = 'block';
    
    // Render messages
    const messagesContainer = document.getElementById('messagesList');
    messagesContainer.innerHTML = messages.map(msg => renderMessage(msg)).join('');
    
    // Scroll to bottom
    document.getElementById('messagesArea').scrollTop = document.getElementById('messagesArea').scrollHeight;
}

function renderMessage(msg) {
    if (msg.message_type === 'system') {
        return `<div class="system-message">${msg.message}</div>`;
    }
    
    const isSent = msg.sender.is_me;
    
    return `
        <div class="message-bubble ${isSent ? 'sent' : 'received'}">
            ${!isSent ? `<div class="small text-muted mb-1">${msg.sender.name}</div>` : ''}
            <div class="message-content">
                ${msg.message ? `<div>${msg.message}</div>` : ''}
                ${msg.attachments && msg.attachments.length > 0 ? `
                    <div class="mt-2">
                        ${msg.attachments.map(att => `
                            <a href="${att.url}" target="_blank" class="attachment-item text-decoration-none ${isSent ? 'text-white' : ''}">
                                <i class="bi bi-paperclip me-1"></i>${att.name}
                            </a>
                        `).join('')}
                    </div>
                ` : ''}
            </div>
            <div class="message-time">${msg.formatted_time}</div>
        </div>
    `;
}

function sendMessage() {
    const messageText = document.getElementById('messageText').value.trim();
    const fileInput = document.getElementById('fileInput');
    
    if (!messageText && fileInput.files.length === 0) {
        return;
    }
    
    const formData = new FormData();
    if (messageText) {
        formData.append('message', messageText);
    }
    
    // Add files
    for (let i = 0; i < fileInput.files.length; i++) {
        formData.append('attachments[]', fileInput.files[i]);
    }
    
    // Disable form
    const submitBtn = document.querySelector('#messageForm button[type="submit"]');
    submitBtn.disabled = true;
    
    fetch(`/vendor/threads/${currentThreadId}/send-message`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Clear form
            document.getElementById('messageText').value = '';
            document.getElementById('messageText').style.height = 'auto';
            fileInput.value = '';
            document.getElementById('attachmentsPreview').innerHTML = '';
            
            // Add message to UI
            const messagesContainer = document.getElementById('messagesList');
            messagesContainer.insertAdjacentHTML('beforeend', renderMessage(data.data));
            
            // Scroll to bottom
            document.getElementById('messagesArea').scrollTop = document.getElementById('messagesArea').scrollHeight;
            
            // Reload threads to update last message
            loadThreads();
        } else {
            alert('Failed to send message');
        }
    })
    .catch(error => {
        console.error('Error sending message:', error);
        alert('Failed to send message');
    })
    .finally(() => {
        submitBtn.disabled = false;
    });
}

function handleFileSelect(files) {
    const preview = document.getElementById('attachmentsPreview');
    preview.innerHTML = '';
    
    Array.from(files).forEach((file, index) => {
        const div = document.createElement('div');
        div.className = 'attachment-preview';
        div.innerHTML = `
            <i class="bi bi-file-earmark me-2"></i>${file.name}
            <button type="button" class="remove-attachment" onclick="removeAttachment(${index})">×</button>
        `;
        preview.appendChild(div);
    });
}

function removeAttachment(index) {
    const fileInput = document.getElementById('fileInput');
    const dt = new DataTransfer();
    const files = Array.from(fileInput.files);
    
    files.forEach((file, i) => {
        if (i !== index) dt.items.add(file);
    });
    
    fileInput.files = dt.files;
    handleFileSelect(fileInput.files);
}

function markThreadAsRead(threadId) {
    fetch(`/vendor/threads/${threadId}/mark-read`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        }
    })
    .then(() => {
        loadUnreadCount();
        loadThreads();
    });
}

function archiveThread(threadId) {
    fetch(`/vendor/threads/${threadId}/archive`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Thread archived');
            loadThreads();
            // Clear thread view
            currentThreadId = null;
            document.getElementById('threadHeader').style.display = 'none';
            document.getElementById('messageInput').style.display = 'none';
            document.getElementById('messagesList').innerHTML = '';
            document.getElementById('noThreadSelected').style.display = 'block';
        }
    });
}

function loadUnreadCount() {
    fetch('/vendor/threads/unread-count', {
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const badge = document.getElementById('unreadBadge');
            badge.textContent = data.unread_count;
            badge.style.display = data.unread_count > 0 ? 'inline-block' : 'none';
        }
    });
}
</script>
@endsection
