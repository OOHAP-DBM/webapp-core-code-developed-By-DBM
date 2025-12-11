<!-- Language Selector Component -->
<div class="language-selector dropdown">
    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="languageDropdown" data-bs-toggle="dropdown" aria-expanded="false">
        <span class="flag-icon">{{ $currentLanguage->flag_icon }}</span>
        <span class="language-name d-none d-md-inline">{{ $currentLanguage->native_name }}</span>
        <span class="language-code d-md-none">{{ strtoupper($currentLanguage->code) }}</span>
    </button>
    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="languageDropdown">
        @foreach($languages as $language)
            <li>
                <a class="dropdown-item {{ app()->getLocale() === $language->code ? 'active' : '' }}" 
                   href="{{ route('language.switch') }}?locale={{ $language->code }}"
                   onclick="event.preventDefault(); switchLanguage('{{ $language->code }}');">
                    <span class="flag-icon me-2">{{ $language->flag_icon }}</span>
                    <span class="language-name">{{ $language->native_name }}</span>
                    @if(app()->getLocale() === $language->code)
                        <i class="fas fa-check ms-2 text-success"></i>
                    @endif
                </a>
            </li>
        @endforeach
    </ul>
</div>

<style>
.language-selector .flag-icon {
    font-size: 1.2em;
    margin-right: 5px;
}

.language-selector .dropdown-menu {
    min-width: 200px;
}

.language-selector .dropdown-item {
    padding: 8px 15px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.language-selector .dropdown-item:hover {
    background-color: #f8f9fa;
}

.language-selector .dropdown-item.active {
    background-color: #e7f3ff;
    color: #0d6efd;
}
</style>

<script>
function switchLanguage(locale) {
    // Create form and submit
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route("language.switch") }}';
    
    // CSRF token
    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '_token';
    csrfInput.value = '{{ csrf_token() }}';
    
    // Locale input
    const localeInput = document.createElement('input');
    localeInput.type = 'hidden';
    localeInput.name = 'locale';
    localeInput.value = locale;
    
    form.appendChild(csrfInput);
    form.appendChild(localeInput);
    document.body.appendChild(form);
    form.submit();
}

// Alternative: AJAX-based language switch
function switchLanguageAjax(locale) {
    fetch('{{ route("language.switch") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ locale: locale })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Reload page to apply new language
            window.location.reload();
        }
    })
    .catch(error => {
        console.error('Language switch failed:', error);
    });
}
</script>
