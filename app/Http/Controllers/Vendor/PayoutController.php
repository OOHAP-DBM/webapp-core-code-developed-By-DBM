<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class PayoutController extends Controller
{
    public function index(Request $request)
    {
        $vendor = Auth::user();
        
        // Calculate balance
        $totalEarned = $vendor->bookings()
            ->where('payment_status', 'paid')
            ->sum('total_amount');
        
        $totalWithdrawn = $vendor->payouts()
            ->where('status', 'completed')
            ->sum('amount');
        
        $pendingWithdrawals = $vendor->payouts()
            ->whereIn('status', ['pending', 'processing'])
            ->sum('amount');
        
        $availableBalance = $totalEarned - $totalWithdrawn - $pendingWithdrawals;
        
        $thisMonthEarnings = $vendor->bookings()
            ->where('payment_status', 'paid')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('total_amount');
        
        $balance = [
            'available' => max(0, $availableBalance),
            'pending' => $pendingWithdrawals,
            'this_month' => $thisMonthEarnings,
            'total_earned' => $totalEarned,
        ];
        
        // Get bank details
        $bankDetails = $vendor->bankDetails;
        
        // Get payout history
        $query = $vendor->payouts()->latest();
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        $payouts = $query->paginate(20);
        
        return view('vendor.payouts.index', compact('balance', 'bankDetails', 'payouts'));
    }
    
    public function request(Request $request)
    {
        $vendor = Auth::user();
        
        // Check if bank details exist
        if (!$vendor->bankDetails) {
            return back()->with('error', 'Please add your bank account details first');
        }
        
        $validated = $request->validate([
            'amount' => 'required|numeric|min:100',
            'method' => 'required|in:bank_transfer,upi',
            'notes' => 'nullable|string',
        ]);
        
        // Calculate available balance
        $totalEarned = $vendor->bookings()
            ->where('payment_status', 'paid')
            ->sum('total_amount');
        
        $totalWithdrawn = $vendor->payouts()
            ->where('status', 'completed')
            ->sum('amount');
        
        $pendingWithdrawals = $vendor->payouts()
            ->whereIn('status', ['pending', 'processing'])
            ->sum('amount');
        
        $availableBalance = $totalEarned - $totalWithdrawn - $pendingWithdrawals;
        
        if ($validated['amount'] > $availableBalance) {
            return back()->with('error', 'Insufficient balance');
        }
        
        // Create payout request
        $payout = $vendor->payouts()->create([
            'transaction_id' => 'TXN-' . strtoupper(Str::random(12)),
            'amount' => $validated['amount'],
            'method' => $validated['method'],
            'status' => 'pending',
            'notes' => $validated['notes'],
        ]);
        
        // Send notification to admin for approval
        // Notification::send($admins, new PayoutRequested($payout));
        
        return redirect()
            ->route('vendor.payouts.index')
            ->with('success', 'Payout request submitted successfully! It will be processed within 2-3 business days.');
    }
    
    public function show($id)
    {
        $vendor = Auth::user();
        $payout = $vendor->payouts()->findOrFail($id);
        
        return view('vendor.payouts.show', compact('payout'));
    }
    
    public function updateBank(Request $request)
    {
        $vendor = Auth::user();
        
        $validated = $request->validate([
            'account_holder_name' => 'required|string|max:255',
            'account_number' => 'required|string|max:20',
            'account_number_confirmation' => 'required|same:account_number',
            'ifsc_code' => 'required|string|size:11|regex:/^[A-Z]{4}0[A-Z0-9]{6}$/',
            'bank_name' => 'required|string|max:255',
            'branch_name' => 'nullable|string|max:255',
        ]);
        
        unset($validated['account_number_confirmation']);
        
        // Update or create bank details
        if ($vendor->bankDetails) {
            $vendor->bankDetails()->update($validated);
        } else {
            $vendor->bankDetails()->create($validated);
        }
        
        return redirect()
            ->route('vendor.payouts.index')
            ->with('success', 'Bank details updated successfully!');
    }
}
