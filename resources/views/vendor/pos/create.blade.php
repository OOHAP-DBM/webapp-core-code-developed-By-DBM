@extends('layouts.vendor')

@section('title', 'Create POS Booking')

@section('content')
<div class="px-6 py-6">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- Main Form -->
        <div class="lg:col-span-8">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <!-- Header -->
                <div class="px-6 py-4 rounded-t-xl bg-blue-600 text-white">
                    <h4 class="text-lg font-semibold flex items-center gap-2">
                        ➕ Create New POS Booking
                    </h4>
                </div>

                <!-- Body -->
                <div class="p-6">
                    <form id="pos-booking-form">
                        @csrf

                        <!-- Customer Details -->
                        <h5 class="text-md font-semibold mb-4">Customer Details</h5>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-medium mb-1">Customer Name *</label>
                                <input type="text" name="customer_name" required
                                    class="w-full rounded-lg border-gray-300 focus:ring focus:ring-blue-200">
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-1">Phone *</label>
                                <input type="tel" name="customer_phone" required
                                    class="w-full rounded-lg border-gray-300 focus:ring focus:ring-blue-200">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-medium mb-1">Email</label>
                                <input type="email" name="customer_email"
                                    class="w-full rounded-lg border-gray-300 focus:ring focus:ring-blue-200">
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-1">GSTIN</label>
                                <input type="text" name="customer_gstin" maxlength="15"
                                    class="w-full rounded-lg border-gray-300 focus:ring focus:ring-blue-200">
                            </div>
                        </div>

                        <div class="mb-6">
                            <label class="block text-sm font-medium mb-1">Address</label>
                            <textarea name="customer_address" rows="2"
                                class="w-full rounded-lg border-gray-300 focus:ring focus:ring-blue-200"></textarea>
                        </div>

                        <hr class="my-6">

                        <!-- Booking Details -->
                        <h5 class="text-md font-semibold mb-4">Booking Details</h5>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-medium mb-1">Booking Type *</label>
                                <select name="booking_type" required
                                    class="w-full rounded-lg border-gray-300 focus:ring focus:ring-blue-200">
                                    <option value="ooh">OOH (Hoarding)</option>
                                    <option value="dooh">DOOH (Digital)</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-1">Select Hoarding *</label>
                                <select name="hoarding_id" id="hoarding-select" required
                                    class="w-full rounded-lg border-gray-300 focus:ring focus:ring-blue-200">
                                    <option value="">-- Search & Select --</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                            <div>
                                <label class="block text-sm font-medium mb-1">Start Date *</label>
                                <input type="date" name="start_date" required
                                    class="w-full rounded-lg border-gray-300 focus:ring focus:ring-blue-200">
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-1">End Date *</label>
                                <input type="date" name="end_date" required
                                    class="w-full rounded-lg border-gray-300 focus:ring focus:ring-blue-200">
                            </div>
                        </div>

                        <hr class="my-6">

                        <!-- Pricing -->
                        <h5 class="text-md font-semibold mb-4">Pricing</h5>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-medium mb-1">Base Amount *</label>
                                <input type="number" step="0.01" id="base-amount" name="base_amount" required
                                    class="w-full rounded-lg border-gray-300 focus:ring focus:ring-blue-200">
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-1">Discount Amount</label>
                                <input type="number" step="0.01" id="discount-amount" name="discount_amount" value="0"
                                    class="w-full rounded-lg border-gray-300 focus:ring focus:ring-blue-200">
                            </div>
                        </div>

                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 text-sm mb-6">
                            <strong>Price Breakdown:</strong><br>
                            Base Amount: ₹<span id="display-base">0.00</span><br>
                            Discount: ₹<span id="display-discount">0.00</span><br>
                            After Discount: ₹<span id="display-after-discount">0.00</span><br>
                            GST (@<span id="gst-rate">18</span>%): ₹<span id="display-gst">0.00</span><br>
                            <strong>Total Amount: ₹<span id="display-total">0.00</span></strong>
                        </div>

                        <hr class="my-6">

                        <!-- Payment Details -->
                        <h5 class="text-md font-semibold mb-4">Payment Details</h5>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-medium mb-1">Payment Mode *</label>
                                <select name="payment_mode" required
                                    class="w-full rounded-lg border-gray-300 focus:ring focus:ring-blue-200">
                                    <option value="cash">Cash</option>
                                    <option value="credit_note">Credit Note</option>
                                    <option value="bank_transfer">Bank Transfer</option>
                                    <option value="cheque">Cheque</option>
                                    <option value="online">Online</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-1">Payment Reference</label>
                                <input type="text" name="payment_reference"
                                    class="w-full rounded-lg border-gray-300 focus:ring focus:ring-blue-200">
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium mb-1">Payment Notes</label>
                            <textarea name="payment_notes" rows="2"
                                class="w-full rounded-lg border-gray-300 focus:ring focus:ring-blue-200"></textarea>
                        </div>

                        <div class="mb-6">
                            <label class="block text-sm font-medium mb-1">Additional Notes</label>
                            <textarea name="notes" rows="2"
                                class="w-full rounded-lg border-gray-300 focus:ring focus:ring-blue-200"></textarea>
                        </div>

                        <div class="flex justify-between">
                            <a href="{{ route('vendor.pos.dashboard') }}"
                               class="px-5 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-100">
                                Cancel
                            </a>

                            <button type="submit"
                                class="px-6 py-3 rounded-lg bg-blue-600 text-white font-medium hover:bg-blue-700">
                                💾 Create Booking
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="lg:col-span-4">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="px-6 py-4 rounded-t-xl bg-cyan-600 text-white">
                    <h5 class="font-semibold">POS Settings</h5>
                </div>

                <div class="p-6 space-y-3 text-sm">
                    <div class="flex justify-between">
                        <span>Auto-Approval</span>
                        <strong id="auto-approval-status">Loading...</strong>
                    </div>
                    <div class="flex justify-between">
                        <span>Auto-Invoice</span>
                        <strong id="auto-invoice-status">Loading...</strong>
                    </div>
                    <div class="flex justify-between">
                        <span>GST Rate</span>
                        <strong id="gst-rate-display">18%</strong>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
