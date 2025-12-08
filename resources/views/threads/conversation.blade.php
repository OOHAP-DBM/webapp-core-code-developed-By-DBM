@extends('layouts.app')

@section('title', 'Thread Conversation')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <!-- Thread List Sidebar -->
        <div class="col-md-4 col-lg-3">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-comments me-2"></i>My Conversations</h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush" id="threadList">
                        <!-- Threads will be loaded here via AJAX -->
                        <div class="text-center py-4">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Thread Conversation Area -->
        <div class="col-md-8 col-lg-9">
            <div class="card" style="height: calc(100vh - 150px);">
                <!-- Thread Header -->
                <div class="card-header bg-white border-bottom" id="threadHeader">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-0" id="threadTitle">Select a conversation</h5>
                            <small class="text-muted" id="threadSubtitle"></small>
                        </div>
                        <div id="threadActions">
                            <!-- Actions will appear here -->
                        </div>
                    </div>
                </div>

                <!-- Messages Area -->
                <div class="card-body overflow-auto" id="messagesArea" style="height: calc(100% - 180px);">
                    <div class="text-center text-muted py-5">
                        <i class="fas fa-comments fa-3x mb-3"></i>
                        <p>Select a conversation to start messaging</p>
                    </div>
                </div>

                <!-- Message Input Area -->
                <div class="card-footer bg-light" id="messageInputArea" style="display: none;">
                    <form id="messageForm">
                        <div class="input-group">
                            <button type="button" class="btn btn-outline-secondary" id="attachFileBtn">
                                <i class="fas fa-paperclip"></i>
                            </button>
                            <input type="file" id="fileInput" multiple style="display: none;">
                            <input type="text" class="form-control" id="messageInput" 
                                   placeholder="Type your message..." required>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane me-1"></i>Send
                            </button>
                        </div>
                        <div id="attachedFiles" class="mt-2"></div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Offer Modal -->
<div class="modal fade" id="offerModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-file-invoice-dollar me-2"></i>Create Offer</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="offerForm">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Price *</label>
                            <div class="input-group">
                                <span class="input-group-text">₹</span>
                                <input type="number" class="form-control" name="price" required min="0" step="0.01">
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Price Type *</label>
                            <select class="form-select" name="price_type" required>
                                <option value="total">Total Amount</option>
                                <option value="monthly">Per Month</option>
                                <option value="weekly">Per Week</option>
                                <option value="daily">Per Day</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Valid For (Days)</label>
                        <input type="number" class="form-control" name="valid_days" min="1" max="90" value="30">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="3" 
                                  placeholder="Describe your offer..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Message to Customer (Optional)</label>
                        <textarea class="form-control" name="message" rows="2" 
                                  placeholder="Add a message with your offer..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane me-1"></i>Send Offer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Quotation Modal -->
<div class="modal fade" id="quotationModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="fas fa-file-invoice me-2"></i>Create Quotation</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="quotationForm">
                <div class="modal-body">
                    <input type="hidden" name="offer_id" id="quotationOfferId">
                    
                    <!-- Line Items -->
                    <h6 class="mb-3">Line Items</h6>
                    <div id="lineItemsContainer">
                        <div class="line-item row mb-2">
                            <div class="col-md-5">
                                <input type="text" class="form-control" name="items[0][description]" 
                                       placeholder="Item description" required>
                            </div>
                            <div class="col-md-2">
                                <input type="number" class="form-control item-qty" name="items[0][quantity]" 
                                       placeholder="Qty" min="1" value="1" required>
                            </div>
                            <div class="col-md-3">
                                <input type="number" class="form-control item-rate" name="items[0][rate]" 
                                       placeholder="Rate" min="0" step="0.01" required>
                            </div>
                            <div class="col-md-2 text-end">
                                <span class="item-total fw-bold">₹0.00</span>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-primary mb-3" id="addLineItem">
                        <i class="fas fa-plus me-1"></i>Add Item
                    </button>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tax Rate (%)</label>
                            <input type="number" class="form-control" name="tax_rate" id="taxRate" 
                                   min="0" max="100" step="0.01" value="18">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Discount (₹)</label>
                            <input type="number" class="form-control" name="discount" id="discount" 
                                   min="0" step="0.01" value="0">
                        </div>
                    </div>

                    <!-- Totals -->
                    <div class="card bg-light">
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Subtotal:</span>
                                <strong id="subtotal">₹0.00</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Tax (<span id="taxRateDisplay">18</span>%):</span>
                                <strong id="taxAmount">₹0.00</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Discount:</span>
                                <strong id="discountAmount">₹0.00</strong>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between">
                                <h5>Grand Total:</h5>
                                <h5 class="text-success" id="grandTotal">₹0.00</h5>
                            </div>
                        </div>
                    </div>

                    <div class="mt-3">
                        <label class="form-label">Notes</label>
                        <textarea class="form-control" name="notes" rows="2"></textarea>
                    </div>
                    <div class="mt-3">
                        <label class="form-label">Message to Customer (Optional)</label>
                        <textarea class="form-control" name="message" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-paper-plane me-1"></i>Send Quotation
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.message-bubble {
    max-width: 70%;
    margin-bottom: 15px;
    padding: 12px 16px;
    border-radius: 18px;
    word-wrap: break-word;
}
.message-sent {
    margin-left: auto;
    background-color: #0d6efd;
    color: white;
    border-bottom-right-radius: 4px;
}
.message-received {
    background-color: #f8f9fa;
    color: #212529;
    border-bottom-left-radius: 4px;
}
.message-system {
    text-align: center;
    font-size: 0.875rem;
    color: #6c757d;
    margin: 20px auto;
}
.offer-card, .quotation-card {
    border: 2px solid #dee2e6;
    border-radius: 10px;
    padding: 15px;
    margin-bottom: 15px;
    max-width: 80%;
}
.thread-item {
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
.unread-badge {
    background-color: #dc3545;
    color: white;
    border-radius: 50%;
    padding: 2px 6px;
    font-size: 0.75rem;
}
</style>

@push('scripts')
<script>
let currentThreadId = null;
let currentUserRole = '{{ auth()->user()->role ?? "customer" }}';
let pollingInterval = null;

// Initialize
$(document).ready(function() {
    loadThreads();
    
    // Message form submit
    $('#messageForm').on('submit', function(e) {
        e.preventDefault();
        sendMessage();
    });
    
    // Offer form submit
    $('#offerForm').on('submit', function(e) {
        e.preventDefault();
        createOffer();
    });
    
    // Quotation form submit
    $('#quotationForm').on('submit', function(e) {
        e.preventDefault();
        createQuotation();
    });
    
    // File attach
    $('#attachFileBtn').on('click', function() {
        $('#fileInput').click();
    });
    
    $('#fileInput').on('change', function() {
        displayAttachedFiles();
    });
    
    // Add line item
    $('#addLineItem').on('click', function() {
        addLineItem();
    });
    
    // Recalculate quotation
    $(document).on('input', '.item-qty, .item-rate, #taxRate, #discount', function() {
        calculateQuotation();
    });
});

// Load threads list
function loadThreads() {
    const endpoint = currentUserRole === 'vendor' ? '/api/v1/vendor/threads' : '/api/v1/customer/threads';
    
    $.ajax({
        url: endpoint,
        method: 'GET',
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('api_token')
        },
        success: function(response) {
            if (response.success) {
                renderThreadsList(response.data.data);
            }
        },
        error: function(xhr) {
            showError('Failed to load threads');
        }
    });
}

// Render threads list
function renderThreadsList(threads) {
    const container = $('#threadList');
    container.empty();
    
    if (threads.length === 0) {
        container.html('<div class="text-center text-muted py-4">No conversations yet</div>');
        return;
    }
    
    threads.forEach(thread => {
        const unreadBadge = thread.unread_count_customer > 0 || thread.unread_count_vendor > 0 
            ? `<span class="unread-badge">${currentUserRole === 'vendor' ? thread.unread_count_vendor : thread.unread_count_customer}</span>` 
            : '';
        
        const latestMessage = thread.latest_message ? thread.latest_message[0] : null;
        const preview = latestMessage ? latestMessage.message.substring(0, 50) + '...' : 'No messages yet';
        
        const item = `
            <a href="#" class="list-group-item list-group-item-action thread-item" data-thread-id="${thread.id}">
                <div class="d-flex w-100 justify-content-between">
                    <h6 class="mb-1">${thread.enquiry.hoarding.name}</h6>
                    ${unreadBadge}
                </div>
                <p class="mb-1 small text-muted">${preview}</p>
                <small class="text-muted">${formatTime(thread.last_message_at)}</small>
            </a>
        `;
        
        container.append(item);
    });
    
    // Click handler
    $('.thread-item').on('click', function(e) {
        e.preventDefault();
        const threadId = $(this).data('thread-id');
        loadThread(threadId);
        $('.thread-item').removeClass('active');
        $(this).addClass('active');
    });
}

// Load thread conversation
function loadThread(threadId) {
    currentThreadId = threadId;
    
    const endpoint = currentUserRole === 'vendor' ? '/api/v1/vendor/threads/' : '/api/v1/customer/threads/';
    
    $.ajax({
        url: endpoint + threadId,
        method: 'GET',
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('api_token')
        },
        success: function(response) {
            if (response.success) {
                renderThread(response.data.thread, response.data.messages);
                $('#messageInputArea').show();
                
                // Start polling for new messages
                if (pollingInterval) clearInterval(pollingInterval);
                pollingInterval = setInterval(() => loadThread(threadId), 5000);
            }
        },
        error: function(xhr) {
            showError('Failed to load thread');
        }
    });
}

// Render thread
function renderThread(thread, messages) {
    $('#threadTitle').text(thread.enquiry.hoarding.name);
    $('#threadSubtitle').text(thread.enquiry.hoarding.location);
    
    // Render action buttons
    const actions = $('#threadActions');
    actions.empty();
    
    if (currentUserRole === 'vendor') {
        actions.html(`
            <button class="btn btn-sm btn-primary me-2" onclick="showOfferModal()">
                <i class="fas fa-file-invoice-dollar me-1"></i>Create Offer
            </button>
            <button class="btn btn-sm btn-success" onclick="showQuotationModal()">
                <i class="fas fa-file-invoice me-1"></i>Create Quotation
            </button>
        `);
    }
    
    // Render messages
    renderMessages(messages);
}

// Render messages
function renderMessages(messages) {
    const container = $('#messagesArea');
    container.empty();
    
    messages.forEach(msg => {
        let html = '';
        
        if (msg.message_type === 'system') {
            html = `<div class="message-system">${msg.message}</div>`;
        } else if (msg.message_type === 'offer') {
            html = renderOfferMessage(msg);
        } else if (msg.message_type === 'quotation') {
            html = renderQuotationMessage(msg);
        } else {
            const isSent = msg.sender_type === currentUserRole;
            const bubbleClass = isSent ? 'message-sent' : 'message-received';
            html = `
                <div class="d-flex ${isSent ? 'justify-content-end' : 'justify-content-start'}">
                    <div class="message-bubble ${bubbleClass}">
                        <div>${escapeHtml(msg.message)}</div>
                        <small class="d-block mt-1 opacity-75">${formatTime(msg.created_at)}</small>
                    </div>
                </div>
            `;
        }
        
        container.append(html);
    });
    
    // Scroll to bottom
    container.scrollTop(container[0].scrollHeight);
}

// Render offer message
function renderOfferMessage(msg) {
    const offer = msg.offer;
    if (!offer) return '';
    
    const isCustomer = currentUserRole === 'customer';
    const canAct = isCustomer && offer.status === 'sent';
    
    const actions = canAct ? `
        <button class="btn btn-sm btn-success me-2" onclick="acceptOffer(${offer.id})">
            <i class="fas fa-check me-1"></i>Accept
        </button>
        <button class="btn btn-sm btn-danger" onclick="rejectOffer(${offer.id})">
            <i class="fas fa-times me-1"></i>Reject
        </button>
    ` : `<span class="badge bg-${offer.status === 'accepted' ? 'success' : 'secondary'}">${offer.status}</span>`;
    
    return `
        <div class="offer-card">
            <h6><i class="fas fa-file-invoice-dollar me-2"></i>Offer (Version ${offer.version})</h6>
            <p class="mb-2">${escapeHtml(offer.description || 'No description')}</p>
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <strong class="text-primary">₹${parseFloat(offer.price).toLocaleString()}</strong>
                    <span class="text-muted ms-2">(${offer.price_type})</span>
                </div>
                <div>${actions}</div>
            </div>
            <small class="text-muted d-block mt-2">${formatTime(msg.created_at)}</small>
        </div>
    `;
}

// Render quotation message
function renderQuotationMessage(msg) {
    const quotation = msg.quotation;
    if (!quotation) return '';
    
    const isCustomer = currentUserRole === 'customer';
    const canAct = isCustomer && quotation.status === 'sent';
    
    const actions = canAct ? `
        <button class="btn btn-sm btn-success me-2" onclick="approveQuotation(${quotation.id})">
            <i class="fas fa-check me-1"></i>Approve
        </button>
        <button class="btn btn-sm btn-danger" onclick="rejectQuotation(${quotation.id})">
            <i class="fas fa-times me-1"></i>Reject
        </button>
    ` : `<span class="badge bg-${quotation.status === 'approved' ? 'success' : 'secondary'}">${quotation.status}</span>`;
    
    return `
        <div class="quotation-card">
            <h6><i class="fas fa-file-invoice me-2"></i>Quotation (Version ${quotation.version})</h6>
            <div class="d-flex justify-content-between align-items-center mb-2">
                <div>
                    <strong class="text-success">Grand Total: ₹${parseFloat(quotation.grand_total).toLocaleString()}</strong>
                </div>
                <div>${actions}</div>
            </div>
            ${quotation.notes ? `<p class="small text-muted mb-0">${escapeHtml(quotation.notes)}</p>` : ''}
            <small class="text-muted d-block mt-2">${formatTime(msg.created_at)}</small>
        </div>
    `;
}

// Send message
function sendMessage() {
    const message = $('#messageInput').val().trim();
    if (!message) return;
    
    const endpoint = currentUserRole === 'vendor' 
        ? `/api/v1/vendor/threads/${currentThreadId}/messages`
        : `/api/v1/customer/threads/${currentThreadId}/messages`;
    
    const formData = new FormData();
    formData.append('message', message);
    
    // Add files if any
    const files = $('#fileInput')[0].files;
    for (let i = 0; i < files.length; i++) {
        formData.append('files[]', files[i]);
    }
    
    $.ajax({
        url: endpoint,
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('api_token')
        },
        success: function(response) {
            if (response.success) {
                $('#messageInput').val('');
                $('#fileInput').val('');
                $('#attachedFiles').empty();
                loadThread(currentThreadId);
            }
        },
        error: function(xhr) {
            showError('Failed to send message');
        }
    });
}

// Show offer modal
function showOfferModal() {
    $('#offerModal').modal('show');
}

// Create offer
function createOffer() {
    const formData = $('#offerForm').serialize();
    
    $.ajax({
        url: `/api/v1/vendor/threads/${currentThreadId}/offers/create`,
        method: 'POST',
        data: formData,
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('api_token')
        },
        success: function(response) {
            if (response.success) {
                $('#offerModal').modal('hide');
                $('#offerForm')[0].reset();
                showSuccess('Offer sent successfully');
                loadThread(currentThreadId);
            }
        },
        error: function(xhr) {
            showError('Failed to create offer');
        }
    });
}

// Accept/Reject offer
function acceptOffer(offerId) {
    $.ajax({
        url: `/api/v1/customer/threads/${currentThreadId}/offers/${offerId}/accept`,
        method: 'POST',
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('api_token')
        },
        success: function(response) {
            if (response.success) {
                showSuccess('Offer accepted!');
                loadThread(currentThreadId);
            }
        },
        error: function(xhr) {
            showError('Failed to accept offer');
        }
    });
}

function rejectOffer(offerId) {
    const reason = prompt('Reason for rejection (optional):');
    
    $.ajax({
        url: `/api/v1/customer/threads/${currentThreadId}/offers/${offerId}/reject`,
        method: 'POST',
        data: { reason: reason },
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('api_token')
        },
        success: function(response) {
            if (response.success) {
                showSuccess('Offer rejected');
                loadThread(currentThreadId);
            }
        },
        error: function(xhr) {
            showError('Failed to reject offer');
        }
    });
}

// Show quotation modal
function showQuotationModal() {
    // TODO: Get accepted offer ID
    $('#quotationModal').modal('show');
}

// Quotation functions
function addLineItem() {
    const index = $('.line-item').length;
    const item = `
        <div class="line-item row mb-2">
            <div class="col-md-5">
                <input type="text" class="form-control" name="items[${index}][description]" 
                       placeholder="Item description" required>
            </div>
            <div class="col-md-2">
                <input type="number" class="form-control item-qty" name="items[${index}][quantity]" 
                       placeholder="Qty" min="1" value="1" required>
            </div>
            <div class="col-md-3">
                <input type="number" class="form-control item-rate" name="items[${index}][rate]" 
                       placeholder="Rate" min="0" step="0.01" required>
            </div>
            <div class="col-md-2 text-end">
                <button type="button" class="btn btn-sm btn-danger" onclick="$(this).closest('.line-item').remove(); calculateQuotation();">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    `;
    $('#lineItemsContainer').append(item);
}

function calculateQuotation() {
    let subtotal = 0;
    
    $('.line-item').each(function() {
        const qty = parseFloat($(this).find('.item-qty').val()) || 0;
        const rate = parseFloat($(this).find('.item-rate').val()) || 0;
        const lineTotal = qty * rate;
        $(this).find('.item-total').text('₹' + lineTotal.toFixed(2));
        subtotal += lineTotal;
    });
    
    const taxRate = parseFloat($('#taxRate').val()) || 0;
    const discount = parseFloat($('#discount').val()) || 0;
    
    const tax = (subtotal * taxRate) / 100;
    const grandTotal = subtotal + tax - discount;
    
    $('#subtotal').text('₹' + subtotal.toFixed(2));
    $('#taxRateDisplay').text(taxRate.toFixed(2));
    $('#taxAmount').text('₹' + tax.toFixed(2));
    $('#discountAmount').text('₹' + discount.toFixed(2));
    $('#grandTotal').text('₹' + grandTotal.toFixed(2));
}

function createQuotation() {
    const formData = $('#quotationForm').serialize();
    
    $.ajax({
        url: `/api/v1/vendor/threads/${currentThreadId}/quotations/create`,
        method: 'POST',
        data: formData,
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('api_token')
        },
        success: function(response) {
            if (response.success) {
                $('#quotationModal').modal('hide');
                $('#quotationForm')[0].reset();
                showSuccess('Quotation sent successfully');
                loadThread(currentThreadId);
            }
        },
        error: function(xhr) {
            showError('Failed to create quotation');
        }
    });
}

// Approve/Reject quotation
function approveQuotation(quotationId) {
    $.ajax({
        url: `/api/v1/customer/threads/${currentThreadId}/quotations/${quotationId}/approve`,
        method: 'POST',
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('api_token')
        },
        success: function(response) {
            if (response.success) {
                showSuccess('Quotation approved! You can now proceed to booking.');
                loadThread(currentThreadId);
            }
        },
        error: function(xhr) {
            showError('Failed to approve quotation');
        }
    });
}

function rejectQuotation(quotationId) {
    const reason = prompt('Reason for rejection (optional):');
    
    $.ajax({
        url: `/api/v1/customer/threads/${currentThreadId}/quotations/${quotationId}/reject`,
        method: 'POST',
        data: { reason: reason },
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('api_token')
        },
        success: function(response) {
            if (response.success) {
                showSuccess('Quotation rejected');
                loadThread(currentThreadId);
            }
        },
        error: function(xhr) {
            showError('Failed to reject quotation');
        }
    });
}

// Utilities
function formatTime(timestamp) {
    const date = new Date(timestamp);
    const now = new Date();
    const diff = now - date;
    
    if (diff < 60000) return 'Just now';
    if (diff < 3600000) return Math.floor(diff / 60000) + 'm ago';
    if (diff < 86400000) return Math.floor(diff / 3600000) + 'h ago';
    return date.toLocaleDateString();
}

function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, m => map[m]);
}

function showSuccess(message) {
    alert(message); // Replace with toast notification
}

function showError(message) {
    alert(message); // Replace with toast notification
}

function displayAttachedFiles() {
    const files = $('#fileInput')[0].files;
    const container = $('#attachedFiles');
    container.empty();
    
    for (let i = 0; i < files.length; i++) {
        container.append(`<span class="badge bg-secondary me-2">${files[i].name}</span>`);
    }
}
</script>
@endpush
@endsection
