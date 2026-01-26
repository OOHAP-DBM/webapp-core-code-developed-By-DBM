@extends('layouts.vendor')

@section('page-title', 'Point of Sale')

@section('content')

{{-- Header --}}
<div class="flex justify-between items-center mb-6">
    <div>
        <h2 class="text-2xl font-semibold">Point of Sale</h2>
        <p class="text-gray-500 text-sm">Create invoices and manage billing</p>
    </div>

    <button onclick="startNewInvoice()"
        class="inline-flex items-center gap-2 bg-primary text-white px-4 py-2 rounded-lg hover:bg-primary/90">
        ➕ New Invoice
    </button>
</div>

<div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

    {{-- Invoice Creator --}}
    <div class="lg:col-span-8">
        <div class="bg-white rounded-xl shadow border">
            <div class="border-b px-6 py-4">
                <h6 class="font-semibold">Create Invoice</h6>
            </div>

            <div class="p-6">
                <form id="invoiceForm">
                    @csrf

                    {{-- Customer --}}
                    <div class="mb-6">
                        <label class="block font-medium mb-1">Customer *</label>
                        <div class="flex">
                            <input type="text" id="customerSearch"
                                placeholder="Search customer by name, email or phone"
                                class="flex-1 border rounded-l-lg px-3 py-2 focus:ring focus:ring-primary/30">

                            <button type="button"
                                data-bs-toggle="modal" data-bs-target="#addCustomerModal"
                                class="border border-l-0 rounded-r-lg px-3 hover:bg-gray-100">
                                ➕
                            </button>
                        </div>

                        <input type="hidden" name="customer_id" id="customerId">
                        <div id="customerInfo" class="mt-2 text-sm text-gray-600"></div>
                    </div>

                    {{-- Invoice Details --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                        <div>
                            <label class="block text-sm font-medium">Invoice Number</label>
                            <input readonly
                                value="INV-{{ date('Y') }}-{{ str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT) }}"
                                class="w-full border rounded-lg px-3 py-2 bg-gray-100">
                        </div>

                        <div>
                            <label class="block text-sm font-medium">Invoice Date *</label>
                            <input type="date" name="invoice_date"
                                value="{{ date('Y-m-d') }}"
                                class="w-full border rounded-lg px-3 py-2">
                        </div>

                        <div>
                            <label class="block text-sm font-medium">Due Date</label>
                            <input type="date" name="due_date"
                                value="{{ date('Y-m-d', strtotime('+7 days')) }}"
                                class="w-full border rounded-lg px-3 py-2">
                        </div>
                    </div>

                    {{-- Line Items --}}
                    <div class="mb-6">
                        <div class="flex justify-between items-center mb-3">
                            <label class="font-medium">Invoice Items</label>
                            <button type="button" onclick="addLineItem()"
                                class="text-sm border px-3 py-1 rounded hover:bg-gray-100">
                                ➕ Add Item
                            </button>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full border text-sm">
                                <thead class="bg-gray-100">
                                    <tr>
                                        <th class="border p-2 text-left w-2/5">Description</th>
                                        <th class="border p-2 w-1/6">Qty</th>
                                        <th class="border p-2 w-1/5">Rate (₹)</th>
                                        <th class="border p-2 w-1/5">Amount</th>
                                        <th class="border p-2 w-10"></th>
                                    </tr>
                                </thead>
                                <tbody id="lineItemsBody"></tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Notes --}}
                    <div>
                        <label class="block text-sm font-medium">Notes</label>
                        <textarea name="notes" rows="2"
                            class="w-full border rounded-lg px-3 py-2"
                            placeholder="Additional notes or terms"></textarea>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Summary --}}
    <div class="lg:col-span-4 space-y-4">

        <div class="bg-white rounded-xl shadow border sticky top-24">
            <div class="border-b px-6 py-4">
                <h6 class="font-semibold">Summary</h6>
            </div>

            <div class="p-6 space-y-3 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-500">Subtotal</span>
                    <strong id="subtotalAmount">₹0.00</strong>
                </div>

                <div class="flex justify-between items-center">
                    <label class="flex items-center gap-2 text-gray-500">
                        <input type="checkbox" id="enableDiscount"> Discount
                    </label>

                    <div id="discountInputs" class="hidden flex gap-1">
                        <input id="discountValue" type="number"
                            class="w-16 border rounded px-1">
                        <select id="discountType" class="border rounded px-1">
                            <option value="percent">%</option>
                            <option value="fixed">₹</option>
                        </select>
                    </div>

                    <strong id="discountAmount">₹0.00</strong>
                </div>

                <div class="flex justify-between items-center">
                    <label class="flex items-center gap-2 text-gray-500">
                        <input type="checkbox" id="enableTax"> GST
                    </label>

                    <input id="taxRate" type="number"
                        class="hidden w-16 border rounded px-1">

                    <strong id="taxAmount">₹0.00</strong>
                </div>

                <hr>

                <div class="flex justify-between items-center">
                    <span class="font-semibold">Total</span>
                    <h4 id="totalAmount" class="text-primary text-xl font-bold">₹0.00</h4>
                </div>

                <div class="space-y-2">
                    <button onclick="saveInvoice()"
                        class="w-full bg-primary text-white py-2 rounded-lg">
                        💾 Save Invoice
                    </button>

                    <button onclick="previewInvoice()"
                        class="w-full border py-2 rounded-lg">
                        👁 Preview
                    </button>

                    <button onclick="resetInvoice()"
                        class="w-full border py-2 rounded-lg text-gray-600">
                        ❌ Clear
                    </button>
                </div>

                <p class="text-xs text-gray-500 mt-3">
                    ℹ Invoice will be saved as draft
                </p>
            </div>
        </div>

    </div>
</div>

@endsection
