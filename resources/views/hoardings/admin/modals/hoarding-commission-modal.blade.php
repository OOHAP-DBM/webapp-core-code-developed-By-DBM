{{-- Hoarding Commission Modal --}}
<div x-data='{ modalOpen: false, hoardingId: null, title: "", source: "", commission: "", openModal(detail) { this.hoardingId = detail.id; this.title = detail.title; this.source = detail.source; this.commission = ""; this.modalOpen = true; }, closeModal() { this.modalOpen = false; this.hoardingId = null; this.title = ""; this.source = ""; this.commission = ""; }, save() { if (!this.commission) { Swal.fire({ icon: "warning", title: "Invalid Input", text: "Please enter hoarding commission" }); return; } const url = "/admin/vendor-hoardings/" + this.hoardingId + "/set-commission"; fetch(url, { method: "POST", headers: { "X-CSRF-TOKEN": document.querySelector("meta[name=csrf-token]").content, "Accept": "application/json", "Content-Type": "application/json" }, body: JSON.stringify({ type: "hoarding", source: this.source, commission: this.commission }) }).then(res => res.json()).then(data => { this.closeModal(); Swal.fire({ icon: "success", title: "Commission Set!", text: "Hoarding commission has been saved successfully.", confirmButtonColor: "#16a34a", timer: 2000, showConfirmButton: false }); setTimeout(() => location.reload(), 2000); }).catch(() => { Swal.fire({ icon: "error", title: "Error", text: "Failed to save hoarding commission" }); }); } }' x-show="modalOpen" x-cloak @open-hoarding-commission.window="openModal($event.detail)" @keydown.escape="closeModal()" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" @click.self="closeModal()">
    <div class="bg-white w-full max-w-md rounded-2xl shadow-xl">
        <div class="flex justify-between px-6 py-4 border-b bg-gray-100 rounded-t-2xl">
            <h3 class="font-semibold text-lg">Hoarding Commission</h3>
            <button type="button" @click="closeModal()">✕</button>
        </div>
        <div class="px-6 py-6 space-y-4 text-center">
            <h2 class="font-semibold text-lg" x-text="title"></h2>
            <input type="number" x-model.number="commission" class="w-full border rounded-md px-3 py-2 text-center" placeholder="Enter hoarding commission %">
            <button type="button" class="w-full bg-green-500 hover:bg-green-600 text-white py-2 rounded-lg" @click="save()">Apply</button>
        </div>
    </div>
</div>
