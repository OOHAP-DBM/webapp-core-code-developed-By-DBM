<!-- CANCEL DEBIT NOTE MODAL -->
<div id="cancel-debit-note-modal"
     class="hidden fixed inset-0 bg-black/60 flex items-center justify-center z-50">
    <div class="bg-white rounded-2xl p-6 max-w-sm sm:max-w-md w-full mx-4 shadow-xl animate-fadeIn">
        <h3 class="text-lg sm:text-xl font-semibold text-amber-600 mb-2">⚠️ Cancel Debit Note</h3>
        <p class="text-sm text-gray-600 mb-3">
            This will cancel the debit note for this booking.
        </p>

        <textarea id="cancel-debit-note-reason" rows="3"
                  class="w-full rounded-lg border border-gray-300 p-2 mb-4"
                  placeholder="Reason (optional)"></textarea>

        <div class="flex flex-col-reverse sm:flex-row justify-end gap-2">
            <button onclick="closeCancelDebitNoteModal()"
                    class="w-full sm:w-auto px-4 py-2 rounded-lg border hover:bg-gray-100">
                Keep Debit Note
            </button>
            <button id="cancel-debit-note-confirm-btn" onclick="confirmCancelDebitNote()"
                    class="w-full sm:w-auto px-4 py-2 rounded-lg bg-amber-600 text-white hover:bg-amber-700 flex items-center justify-center gap-2">
                <span id="cancel-debit-note-btn-text">Cancel Debit Note</span>
                <span id="cancel-debit-note-spinner"
                      class="hidden animate-spin h-4 w-4 border-2 border-white border-t-transparent rounded-full"></span>
            </button>
        </div>
    </div>
</div>