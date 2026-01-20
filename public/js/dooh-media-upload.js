// JS for DOOH media upload (create/edit)
// Handles preview, removal, and deleted_media_ids[]

const mediaInput = document.getElementById('mediaUpload');
const filePreview = document.getElementById('filePreview');
const uploadCount = document.getElementById('uploadCount');
const existingMediaCount = document.getElementById('existingMediaCount');
const totalCount = document.getElementById('totalCount');
const deletedMediaIdsInput = document.getElementById('deletedMediaIds');

const MAX_FILES = 10;
let filesArray = [];
let deletedMediaIds = [];
let existingCount = existingMediaCount ? parseInt(existingMediaCount.textContent) || 0 : 0;

function updateUploadCount() {
    const newFilesCount = filesArray.length;
    const total = existingCount + newFilesCount;
    uploadCount.textContent = newFilesCount;
    totalCount.textContent = total;
    if (mediaInput) {
        mediaInput.disabled = total >= MAX_FILES;
    }
}

function removeExistingMedia(mediaId) {
    if (!confirm('Remove this media?')) return;
    const container = document.querySelector(`div[data-media-id="${mediaId}"]`);
    if (container) container.remove();
    deletedMediaIds.push(mediaId);
    deletedMediaIdsInput.value = deletedMediaIds.join(',');
    existingCount--;
    if (existingMediaCount) existingMediaCount.textContent = existingCount;
    updateUploadCount();
}

function removePreviewImage(index) {
    filesArray.splice(index, 1);
    syncFilesToInput();
    renderPreviews();
}

function syncFilesToInput() {
    const dt = new DataTransfer();
    filesArray.forEach(file => dt.items.add(file));
    mediaInput.files = dt.files;
}

function renderPreviews() {
    filePreview.innerHTML = '';
    filesArray.forEach((file, index) => {
        const isVideo = file.type.startsWith('video/');
        const div = document.createElement('div');
        div.className = 'relative group';
        div.setAttribute('data-file-index', index);
        if (isVideo) {
            const video = document.createElement('video');
            video.src = URL.createObjectURL(file);
            video.className = 'w-24 h-24 object-cover rounded-lg border-2 border-gray-200 shadow-sm';
            video.controls = true;
            div.appendChild(video);
        } else {
            const img = document.createElement('img');
            img.src = URL.createObjectURL(file);
            img.className = 'w-24 h-24 object-cover rounded-lg border-2 border-gray-200 shadow-sm';
            div.appendChild(img);
        }
        const removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.className = 'absolute -top-2 -right-2 w-6 h-6 bg-red-500 text-white rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity shadow-lg hover:bg-red-600';
        removeBtn.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>';
        removeBtn.setAttribute('data-index', index);
        removeBtn.addEventListener('click', function() {
            const idx = parseInt(this.getAttribute('data-index'));
            removePreviewImage(idx);
        });
        div.appendChild(removeBtn);
        filePreview.appendChild(div);
    });
    updateUploadCount();
}

mediaInput && mediaInput.addEventListener('change', function(e) {
    const newFiles = Array.from(e.target.files);
    e.target.value = '';
    const totalAfterAdd = existingCount + filesArray.length + newFiles.length;
    if (totalAfterAdd > MAX_FILES) {
        alert(`Cannot add ${newFiles.length} file(s). Maximum ${MAX_FILES} files allowed.`);
        return;
    }
    let errorMessages = [];
    for (const file of newFiles) {
        const isImage = /^image\/(jpeg|png|jpg|webp)$/i.test(file.type);
        const isVideo = /^video\/(mp4|webm)$/i.test(file.type);
        if (!isImage && !isVideo) {
            errorMessages.push(`"${file.name}" - unsupported format`);
            continue;
        }
        if (file.size > 5 * 1024 * 1024) {
            errorMessages.push(`"${file.name}" - exceeds 5MB limit`);
            continue;
        }
        const isDuplicate = filesArray.some(f => f.name === file.name && f.size === file.size);
        if (isDuplicate) {
            errorMessages.push(`"${file.name}" - already selected`);
            continue;
        }
        filesArray.push(file);
    }
    syncFilesToInput();
    if (errorMessages.length > 0) {
        alert('Some files were not added:\n\n' + errorMessages.join('\n'));
    }
    renderPreviews();
});

updateUploadCount();
