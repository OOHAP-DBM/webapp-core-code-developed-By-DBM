@php
    // This view is handled by threads/index.blade.php with selected thread
    // Redirect to index with thread ID
    if (isset($thread)) {
        redirect()->route('customer.threads.index', ['selected' => $thread->id]);
    }
@endphp
