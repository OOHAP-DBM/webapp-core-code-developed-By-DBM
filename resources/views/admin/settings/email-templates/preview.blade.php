@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 py-6">

    <!-- HEADER -->
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-semibold text-gray-800">
            📧 Preview — {{ $emailTemplate->name }}
        </h1>

        <div class="flex gap-3">
            <a href="{{ route('admin.mail.configuration.edit', $emailTemplate->id) }}"
               class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 text-sm">
               Edit
            </a>

            <a href="{{ route('admin.mail.configuration.index') }}"
               class="text-gray-600 hover:underline text-sm mt-2">
               ← Back
            </a>
        </div>
    </div>


    <!-- SUBJECT -->
    <div class="bg-white shadow rounded-lg p-4 mb-4 border-l-4 border-blue-500">
        <p class="text-sm text-gray-500 font-medium">Subject:</p>
        <p class="text-gray-800 mt-1 font-semibold">
            {{ $emailTemplate->subject }}
        </p>
    </div>

    <!-- EMAIL PREVIEW WRAPPER -->
    <div class="bg-gray-200 p-6 rounded-lg">
        <!-- Email Container -->
        <div class="max-w-2xl mx-auto bg-white shadow-lg rounded overflow-hidden">
            <!-- HEADER HTML & LOGO -->
            @if($emailTemplate->layout)
                <div class="p-6 border-b">
                    @if($emailTemplate->layout->logo_url)
                        <div class="mb-4 text-center">
                            <img src="{{ asset($emailTemplate->layout->logo_url) }}" alt="Logo" class="h-12 mx-auto">
                        </div>
                    @endif
                    {!! $emailTemplate->layout->header_html !!}
                </div>
            @endif

            <!-- EMAIL CONTENT -->
            <div class="p-6">
                {!! $rendered['body'] ?? $emailTemplate->body_html !!}
            </div>

            <!-- FOOTER HTML -->
            @if($emailTemplate->layout)
                <div class="p-6 border-t bg-gray-50">
                    {!! $emailTemplate->layout->footer_html !!}
                </div>
            @endif
        </div>
    </div>

</div>
@endsection