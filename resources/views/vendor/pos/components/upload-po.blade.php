{{-- PO Upload (Optional) --}}
<div id="po-upload-block" class="mt-3">

    <label class="block text-[11px] font-semibold uppercase mb-1">
        Purchase Order (PO)
        <span class="normal-case font-normal text-gray-400 ml-1">(optional)</span>
    </label>

    {{-- Drop zone --}}
    <div id="po-drop-zone"
         onclick="document.getElementById('po-file-input').click()"
         class="relative flex flex-col items-center justify-center gap-1 border border-dashed border-gray-200 rounded-lg px-3 py-3 cursor-pointer hover:border-[#2D5A43] hover:bg-green-50/40 transition-colors group">

        {{-- Icon --}}
        <div class="w-7 h-7 rounded-full bg-gray-100 group-hover:bg-green-100 flex items-center justify-center">
            <svg class="w-4 h-4 text-gray-400 group-hover:text-[#2D5A43]" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1M16 10l-4-4m0 0L8 10m4-4v12"/>
            </svg>
        </div>

        <p class="text-[11px] font-medium text-gray-500 group-hover:text-[#2D5A43]">
            Upload PO
        </p>

        <p class="text-[9px] text-gray-400">PDF, JPG, PNG · Max 10 MB</p>

        <input type="file"
               id="po-file-input"
               accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
               class="hidden" />
    </div>

    {{-- Selected file card --}}
    <div id="po-file-card" class="hidden mt-2 flex items-center gap-2 bg-green-50 border border-green-200 rounded-lg px-2 py-1.5">

        <div id="po-file-icon" class="w-6 h-6 rounded flex items-center justify-center flex-shrink-0 bg-green-100">
        </div>

        <div class="flex-1 min-w-0">
            <p id="po-file-name" class="text-[11px] font-semibold text-gray-800 truncate"></p>
            <p id="po-file-size" class="text-[9px] text-gray-500"></p>
        </div>

        <button type="button" id="po-clear-btn"
                class="w-5 h-5 flex items-center justify-center rounded-full bg-red-100 hover:bg-red-200">
            <svg class="w-3 h-3 text-red-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>

</div>

<script>
(function () {
    let selectedPOFile = null;

    function formatBytes(bytes) {
        if (bytes < 1024)       return bytes + ' B';
        if (bytes < 1048576)    return (bytes / 1024).toFixed(1) + ' KB';
        return (bytes / 1048576).toFixed(1) + ' MB';
    }

    function getFileIcon(name) {
        const ext = (name.split('.').pop() || '').toLowerCase();
        const isPdf = ext === 'pdf';
        const isImg = ['jpg','jpeg','png','gif','webp'].includes(ext);

        if (isPdf) return `<svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>`;
        if (isImg) return `<svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>`;
        return `<svg class="w-4 h-4 text-[#2D5A43]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>`;
    }

    function getIconBg(name) {
        const ext = (name.split('.').pop() || '').toLowerCase();
        if (ext === 'pdf')                              return 'bg-red-100';
        if (['jpg','jpeg','png','gif','webp'].includes(ext)) return 'bg-blue-100';
        return 'bg-green-100';
    }

    function showFile(file) {
        selectedPOFile = file;
        const card     = document.getElementById('po-file-card');
        const zone     = document.getElementById('po-drop-zone');
        const nameEl   = document.getElementById('po-file-name');
        const sizeEl   = document.getElementById('po-file-size');
        const iconEl   = document.getElementById('po-file-icon');

        nameEl.textContent = file.name;
        sizeEl.textContent = formatBytes(file.size);
        iconEl.className   = `w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0 ${getIconBg(file.name)}`;
        iconEl.innerHTML   = getFileIcon(file.name);

        card.classList.remove('hidden');
        zone.classList.add('hidden');
        // Expose globally for booking submit
        window.selectedPOFile = file;
    }

    function clearFile() {
        selectedPOFile = null;
        window.selectedPOFile = null;
        const input = document.getElementById('po-file-input');
        if (input) input.value = '';
        document.getElementById('po-file-card').classList.add('hidden');
        document.getElementById('po-drop-zone').classList.remove('hidden');
    }

    document.addEventListener('DOMContentLoaded', function () {
        const input   = document.getElementById('po-file-input');
        const clearBtn= document.getElementById('po-clear-btn');
        const dropZone= document.getElementById('po-drop-zone');

        input?.addEventListener('change', function () {
            if (input.files?.[0]) showFile(input.files[0]);
        });

        clearBtn?.addEventListener('click', clearFile);

        // Drag-and-drop support
        dropZone?.addEventListener('dragover', function (e) {
            e.preventDefault();
            dropZone.classList.add('border-[#2D5A43]', 'bg-green-50/60');
        });
        dropZone?.addEventListener('dragleave', function () {
            dropZone.classList.remove('border-[#2D5A43]', 'bg-green-50/60');
        });
        dropZone?.addEventListener('drop', function (e) {
            e.preventDefault();
            dropZone.classList.remove('border-[#2D5A43]', 'bg-green-50/60');
            const file = e.dataTransfer?.files?.[0];
            if (file) showFile(file);
        });
    });
}());
</script>