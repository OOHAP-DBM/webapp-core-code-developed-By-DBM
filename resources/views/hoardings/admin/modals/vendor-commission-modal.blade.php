{{-- Vendor Commission Modal --}}
<div x-data='{ open: false, vendorProfileId: null, vendorName: "", from: "", to: "", openModal(detail) { this.vendorProfileId = detail.vendor_profile_id; this.vendorName = detail.name || "Vendor"; this.from = ""; this.to = ""; this.open = true; }, close() { this.open = false; this.vendorProfileId = null; this.vendorName = ""; this.from = ""; this.to = ""; }, apply() { if (!this.from || !this.to) { Swal.fire({ icon: "warning", title: "Invalid Input", text: "Please enter vendor commission range" }); return; } if (Number(this.to) < Number(this.from)) { Swal.fire({ icon: "warning", title: "Invalid Range", text: "To commission cannot be less than From" }); return; } const url = "/admin/vendors/" + this.vendorProfileId + "/approve"; fetch(url, { method: "POST", headers: { "X-CSRF-TOKEN": document.querySelector("meta[name=csrf-token]").content, "Accept": "application/json", "Content-Type": "application/json" }, body: JSON.stringify({ commission_percentage: this.to }) }).then(res => res.json()).then(data => { this.close(); Swal.fire({ icon: "success", title: "Vendor Approved!", text: "Commission set to " + this.to + "%", confirmButtonColor: "#16a34a", timer: 2000, showConfirmButton: false }); setTimeout(() => location.reload(), 2000); }).catch(() => { Swal.fire({ icon: "error", title: "Error", text: "Failed to approve vendor" }); }); } }' x-show="open" x-cloak @open-vendor-commission.window="openModal($event.detail)" @keydown.escape="close()" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" @click.self="close()">
    <div class="bg-[#f5f5f5] w-full max-w-lg rounded-2xl shadow-xl relative px-10 py-8">
        <button type="button" @click="close()" class="absolute top-5 right-6 text-xl text-gray-700 hover:text-black">✕</button>
        <h2 class="text-2xl font-semibold text-center mb-6" x-text="vendorName"></h2>
        <p class="text-center text-gray-700 mb-6">Set a Vendor Commission</p>
        <div class="flex justify-center gap-6 mb-8">
            <div>
                <label class="block text-sm text-gray-600 mb-1">From</label>
                <input type="number" x-model.number="from" class="w-28 text-center border rounded-md px-3 py-2" placeholder="10">
            </div>
            <div>
                <label class="block text-sm text-gray-600 mb-1">To</label>
                <input type="number" x-model.number="to" class="w-28 text-center border rounded-md px-3 py-2" placeholder="20">
            </div>
        </div>
        <button type="button" @click="apply()" class="w-full bg-green-500 hover:bg-green-600 text-white py-3 rounded-xl text-lg font-medium">Apply</button>
    </div>
</div>
