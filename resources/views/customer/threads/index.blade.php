@extends('layouts.customer')

@section('title', 'Messages')

@section('content')
<div class="container py-5">
    <h2 class="mb-4">Messages & Threads</h2>

    <div class="row g-4">
        <!-- Threads List -->
        <div class="col-lg-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-bottom">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Conversations</h5>
                        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#newThreadModal">
                            <i class="bi bi-plus-circle"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body p-0" style="max-height: 600px; overflow-y: auto;">
                    @forelse($threads as $thread)
                        <a href="{{ route('customer.threads.show', $thread->id) }}" 
                           class="thread-item {{ request()->route('id') == $thread->id ? 'active' : '' }} {{ $thread->unread_count > 0 ? 'unread' : '' }}">
                            <div class="d-flex align-items-start">
                                <div class="flex-shrink-0 me-3">
                                    <div class="avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" 
                                         style="width: 45px; height: 45px;">
                                        <i class="bi bi-person-fill"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 min-width-0">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <h6 class="mb-0 text-truncate">{{ $thread->vendor->name ?? 'Vendor' }}</h6>
                                        <small class="text-muted">{{ $thread->last_message_at ? \Carbon\Carbon::parse($thread->last_message_at)->diffForHumans() : 'No messages' }}</small>
                                    </div>
                                    <p class="text-muted small mb-0 text-truncate">
                                        {{ $thread->last_message ?? 'No messages yet' }}
                                    </p>
                                    @if($thread->unread_count > 0)
                                        <span class="badge bg-primary rounded-pill mt-1">{{ $thread->unread_count }}</span>
                                    @endif
                                </div>
                            </div>
                        </a>
                    @empty
                        <div class="text-center py-5">
                            <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                            <p class="text-muted mt-3">No conversations yet</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Thread Messages -->
        <div class="col-lg-8">
            @if(isset($selectedThread))
                <div class="card shadow-sm border-0">
                    <!-- Thread Header -->
                    <div class="card-header bg-gradient-primary text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <div class="avatar bg-white text-primary rounded-circle d-flex align-items-center justify-content-center me-3" 
                                     style="width: 40px; height: 40px;">
                                    <i class="bi bi-person-fill"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0">{{ $selectedThread->vendor->name ?? 'Vendor' }}</h6>
                                    <small class="opacity-75">
                                        @if(isset($selectedThread->hoarding))
                                            Re: {{ $selectedThread->hoarding->title }}
                                        @endif
                                    </small>
                                </div>
                            </div>
                            <button class="btn btn-sm btn-outline-light">
                                <i class="bi bi-three-dots-vertical"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Messages Container -->
                    <div class="card-body" style="height: 450px; overflow-y: auto;" id="messagesContainer">
                        @forelse($selectedThread->messages as $message)
                            <div class="message-item {{ $message->sender_id === auth()->id() ? 'sent' : 'received' }} mb-3">
                                <div class="message-bubble">
                                    <p class="mb-1">{{ $message->message }}</p>
                                    @if($message->attachment)
                                        <a href="{{ asset('storage/' . $message->attachment) }}" target="_blank" class="d-block mt-2">
                                            <i class="bi bi-paperclip me-1"></i>Attachment
                                        </a>
                                    @endif
                                    <small class="text-muted">{{ \Carbon\Carbon::parse($message->created_at)->format('h:i A') }}</small>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-5">
                                <i class="bi bi-chat-left-text text-muted" style="font-size: 3rem;"></i>
                                <p class="text-muted mt-3">No messages yet. Start the conversation!</p>
                            </div>
                        @endforelse
                    </div>

                    <!-- Message Input -->
                    <div class="card-footer bg-white border-top">
                        <form id="messageForm" action="{{ route('customer.threads.send-message', $selectedThread->id) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="input-group">
                                <input type="text" 
                                       class="form-control" 
                                       name="message" 
                                       id="messageInput" 
                                       placeholder="Type your message..." 
                                       required>
                                <button type="button" class="btn btn-outline-secondary" onclick="document.getElementById('attachment').click()">
                                    <i class="bi bi-paperclip"></i>
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-send"></i>
                                </button>
                            </div>
                            <input type="file" name="attachment" id="attachment" class="d-none" onchange="showAttachment()">
                            <div id="attachmentPreview" class="mt-2 d-none">
                                <small class="text-muted">
                                    <i class="bi bi-paperclip me-1"></i>
                                    <span id="attachmentName"></span>
                                    <button type="button" class="btn btn-sm btn-link text-danger" onclick="removeAttachment()">Remove</button>
                                </small>
                            </div>
                        </form>
                    </div>
                </div>
            @else
                <!-- No Thread Selected -->
                <div class="card shadow-sm border-0">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-chat-left-dots text-muted" style="font-size: 5rem;"></i>
                        <h5 class="text-muted mt-4">Select a conversation</h5>
                        <p class="text-muted">Choose a conversation from the list to view messages</p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- New Thread Modal -->
<div class="modal fade" id="newThreadModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">New Conversation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('customer.threads.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="vendor_id" class="form-label">Select Vendor</label>
                        <select class="form-select" name="vendor_id" id="vendor_id" required>
                            <option value="">Choose vendor...</option>
                            @foreach($vendors ?? [] as $vendor)
                                <option value="{{ $vendor->id }}">{{ $vendor->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="hoarding_id" class="form-label">Related Hoarding (Optional)</label>
                        <select class="form-select" name="hoarding_id" id="hoarding_id">
                            <option value="">None</option>
                            @foreach($hoardings ?? [] as $hoarding)
                                <option value="{{ $hoarding->id }}">{{ $hoarding->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="initial_message" class="form-label">Message</label>
                        <textarea class="form-control" name="message" id="initial_message" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Start Conversation</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Auto-scroll to bottom of messages
const messagesContainer = document.getElementById('messagesContainer');
if (messagesContainer) {
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
}

// Handle message form submission
document.getElementById('messageForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch(this.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Clear input
            document.getElementById('messageInput').value = '';
            removeAttachment();
            
            // Reload page to show new message
            window.location.reload();
        } else {
            alert('Failed to send message');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred');
    });
});

function showAttachment() {
    const input = document.getElementById('attachment');
    if (input.files.length > 0) {
        document.getElementById('attachmentPreview').classList.remove('d-none');
        document.getElementById('attachmentName').textContent = input.files[0].name;
    }
}

function removeAttachment() {
    document.getElementById('attachment').value = '';
    document.getElementById('attachmentPreview').classList.add('d-none');
}
</script>

<style>
.thread-item {
    display: block;
    padding: 1rem;
    text-decoration: none;
    color: inherit;
    border-bottom: 1px solid #f0f0f0;
    transition: background 0.2s;
}

.thread-item:hover {
    background: #f8f9fa;
}

.thread-item.active {
    background: #e7f1ff;
    border-left: 3px solid #667eea;
}

.thread-item.unread {
    background: #fff8e1;
}

.message-item {
    display: flex;
}

.message-item.sent {
    justify-content: flex-end;
}

.message-item.received {
    justify-content: flex-start;
}

.message-bubble {
    max-width: 70%;
    padding: 0.75rem 1rem;
    border-radius: 1rem;
    word-wrap: break-word;
}

.message-item.sent .message-bubble {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-bottom-right-radius: 0.25rem;
}

.message-item.received .message-bubble {
    background: #f1f3f5;
    border-bottom-left-radius: 0.25rem;
}

.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.min-width-0 {
    min-width: 0;
}
</style>
@endsection
