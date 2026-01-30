<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class POSController extends Controller
{
    public function index()
    {
        $vendor = Auth::user();
        
        // Get recent invoices
        $recentInvoices = $vendor->invoices()
            ->with('customer')
            ->latest()
            ->take(5)
            ->get();
        
        return view('vendor.pos.index', compact('recentInvoices'));
    }
    
    public function store(Request $request)
    {
        $vendor = Auth::user();
        
        $validated = $request->validate([
            'customer_id' => 'required|exists:users,id',
            'invoice_number' => 'required|string|unique:invoices',
            'invoice_date' => 'required|date',
            'due_date' => 'nullable|date',
            'items' => 'required|array',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.rate' => 'required|numeric|min:0',
            'discount_type' => 'nullable|in:percent,fixed',
            'discount_value' => 'nullable|numeric|min:0',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'notes' => 'nullable|string',
        ]);
        
        // Calculate totals
        $subtotal = 0;
        foreach ($validated['items'] as $item) {
            $subtotal += $item['quantity'] * $item['rate'];
        }
        
        $discount = 0;
        if ($request->filled('discount_value')) {
            if ($request->discount_type === 'percent') {
                $discount = $subtotal * ($request->discount_value / 100);
            } else {
                $discount = $request->discount_value;
            }
        }
        
        $taxableAmount = $subtotal - $discount;
        $tax = $request->filled('tax_rate') 
            ? $taxableAmount * ($request->tax_rate / 100) 
            : 0;
        
        $totalAmount = $taxableAmount + $tax;
        
        // Create invoice
        $invoice = $vendor->invoices()->create([
            'customer_id' => $validated['customer_id'],
            'invoice_number' => $validated['invoice_number'],
            'invoice_date' => $validated['invoice_date'],
            'due_date' => $validated['due_date'],
            'subtotal' => $subtotal,
            'discount_type' => $request->discount_type,
            'discount_value' => $request->discount_value,
            'discount_amount' => $discount,
            'tax_rate' => $request->tax_rate,
            'tax_amount' => $tax,
            'total_amount' => $totalAmount,
            'notes' => $validated['notes'],
            'status' => 'draft',
        ]);
        
        // Create invoice items
        foreach ($validated['items'] as $item) {
            $invoice->items()->create([
                'description' => $item['description'],
                'quantity' => $item['quantity'],
                'rate' => $item['rate'],
                'amount' => $item['quantity'] * $item['rate'],
            ]);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Invoice created successfully!',
            'invoice_id' => $invoice->id,
        ]);
    }
    
    public function show($id)
    {
        $vendor = Auth::user();
        $invoice = $vendor->invoices()
            ->with(['customer', 'items'])
            ->findOrFail($id);

        \Log::info("that  is" . $invoice);
        return view('vendor.pos.show', compact('invoice'));
    }
    
    public function preview($id)
    {
        $vendor = Auth::user();
        $invoice = $vendor->invoices()
            ->with(['customer', 'items'])
            ->findOrFail($id);
        
        return view('vendor.pos.preview', compact('invoice'));
    }
    
    public function download($id)
    {
        $vendor = Auth::user();
        $invoice = $vendor->invoices()
            ->with(['customer', 'items'])
            ->findOrFail($id);
        
        // Generate PDF using a library like DomPDF
        // $pdf = PDF::loadView('vendor.pos.pdf', compact('invoice'));
        // return $pdf->download($invoice->invoice_number . '.pdf');
        
        return response()->json(['message' => 'PDF generation not implemented yet']);
    }
    
    public function history(Request $request)
    {
        $vendor = Auth::user();
        
        $query = $vendor->invoices()->with('customer')->latest();
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('invoice_number', 'like', '%' . $request->search . '%')
                  ->orWhereHas('customer', function($q) use ($request) {
                      $q->where('name', 'like', '%' . $request->search . '%');
                  });
            });
        }
        
        $invoices = $query->paginate(20);
        
        return view('vendor.pos.history', compact('invoices'));
    }
    
    public function updateStatus(Request $request, $id)
    {
        $vendor = Auth::user();
        $invoice = $vendor->invoices()->findOrFail($id);
        
        $validated = $request->validate([
            'status' => 'required|in:draft,sent,paid,cancelled',
        ]);
        
        $invoice->update($validated);
        
        if ($validated['status'] === 'paid') {
            $invoice->update(['paid_at' => now()]);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Invoice status updated successfully!',
        ]);
    }

        /**
     * POS Customers page for OohApp POS system
     */

}
