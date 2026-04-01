<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>

<div style="max-width:900px;margin:0 auto;background:#f3f4f6;padding:40px 20px;font-family:Arial,Helvetica,sans-serif;color:#333;" id="invoiceWrapper">

    <div style="max-width:660px;margin:0 auto;background:#fff;padding:28px 24px;border-radius:8px;box-sizing:border-box;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.04);">

        <!-- HEADER -->
        <table width="100%" style="margin-bottom:24px;table-layout:fixed;">
            <tr>
                <td width="50%" valign="middle">
                    <div style="display:inline-block;color:#111827;font-size:18px;font-weight:800;padding:8px 16px;border-radius:4px;letter-spacing:0.3px;line-height:1;">
                        INVOICE
                    </div>

                    <div style="color:#6B7280;font-size:13px;margin-top:6px;">
                        #{{ $invoice->invoice_number }}
                    </div>
                </td>

                <td width="50%" align="right" valign="top">
                    <img 
                        src="{{ public_path('assets/images/logo/logo_image.png') }}"
                        alt="OOHApp Logo"
                        width="110"
                        style="display:block;max-width:110px;height:auto;margin-left:auto;"
                    >
                </td>
            </tr>
        </table>


        <!-- INFO SECTION -->
        <table width="100%" cellpadding="12" cellspacing="0"
               style="border-top:1px solid #E5E7EB;border-bottom:1px solid #E5E7EB;margin-bottom:28px;table-layout:fixed;">
            <tr>
                <td width="33%" valign="top" style="border-right:1px solid #E5E7EB;">
                    <div style="font-weight:700;font-size:13px;color:#111827;margin-bottom:6px;">
                        Issued Date
                    </div>
                    <div style="font-size:12px;color:#6B7280;line-height:1.6;margin-bottom:12px;">
                        {{ $invoice->invoice_date->format('d/m/Y') }}
                    </div>

                    <div style="font-weight:700;font-size:13px;color:#111827;margin-bottom:6px;">
                        Due Date
                    </div>
                    <div style="font-size:12px;color:#6B7280;line-height:1.6;">
                        {{ $invoice->due_date?->format('d/m/Y') ?? '-' }}
                    </div>
                </td>

                <td width="34%" valign="top" style="border-right:1px solid #E5E7EB;padding-left:14px;padding-right:14px;">
                    <div style="font-weight:700;font-size:13px;color:#111827;margin-bottom:6px;">
                        Billed to
                    </div>

                    <div style="font-size:13px;font-weight:600;color:#111827;line-height:1.5;">
                        {{ $invoice->buyer_name }}
                    </div>

                    <div style="font-size:12px;color:#6B7280;line-height:1.6;margin-top:4px;">
                        {{ $invoice->buyer_address ?: '-' }}<br>
                        {{ $invoice->buyer_city && $invoice->buyer_state ? $invoice->buyer_city . ', ' . $invoice->buyer_state : '' }}
                        {{ $invoice->buyer_pincode ? ' - ' . $invoice->buyer_pincode : '' }}<br>
                        Email: {{ $invoice->buyer_email ?: '-' }}
                    </div>
                </td>

                <td width="33%" valign="top" style="padding-left:14px;">
                    <div style="font-weight:700;font-size:13px;color:#111827;margin-bottom:6px;">
                        From
                    </div>

                    <div style="font-size:13px;font-weight:600;color:#111827;line-height:1.5;">
                        {{ $invoice->seller_name }}
                    </div>

                    <div style="font-size:12px;color:#6B7280;line-height:1.6;margin-top:4px;">
                        {{ $invoice->seller_address }}<br>
                        GSTIN Number: {{ $invoice->seller_gstin }}
                    </div>
                </td>
            </tr>
        </table>


        <!-- ITEMS TABLE -->
        <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:30px;font-size:13px;border-collapse:collapse;table-layout:fixed;">
            <thead>
                <tr style="border-bottom:1px solid #D1D5DB;">
                    <th align="left" style="padding:10px 0;font-size:13px;color:#374151;font-weight:700;width:55%;">
                        Item Details
                    </th>
                    <th align="center" style="padding:10px 0;font-size:13px;color:#374151;font-weight:700;width:15%;">
                        Type
                    </th>
                    <th align="center" style="padding:10px 0;font-size:13px;color:#374151;font-weight:700;width:10%;">
                        Qty
                    </th>
                    <th align="right" style="padding:10px 0;font-size:13px;color:#374151;font-weight:700;width:20%;">
                        Prices
                    </th>
                </tr>
            </thead>

            <tbody>
                @foreach($invoice->items as $item)
                    <tr style="border-bottom:1px solid #F3F4F6;">
                        <td style="padding:14px 8px 14px 0;vertical-align:top;">
                            <div style="font-weight:600;font-size:13px;line-height:1.5;color:#111827;">
                                {{ $item->description }}
                            </div>

                            @if($item->service_start_date && $item->service_end_date)
                                <div style="font-size:11px;color:#9CA3AF;margin-top:4px;">
                                    {{ $item->service_start_date->format('d M Y') }} - {{ $item->service_end_date->format('d M Y') }}
                                </div>
                            @endif
                        </td>

                        <td align="center" style="color:#4F46E5;font-size:12px;font-weight:600;vertical-align:top;padding-top:14px;">
                            {{ $invoice->supply_type === 'services' ? 'OOH' : 'DOOH' }}
                        </td>

                        <td align="center" style="vertical-align:top;padding-top:14px;font-size:12px;color:#374151;">
                            1
                        </td>

                        <td align="right" style="vertical-align:top;padding-top:14px;font-size:12px;color:#111827;white-space:nowrap;">
                            Rs. {{ number_format($item->total_amount, 2) }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>


        <!-- TOTALS -->
        <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:32px;">
            <tr>
                <td></td>

                <td width="240" valign="top">
                    <table width="100%" cellpadding="5" cellspacing="0">
                        <tr>
                            <td style="font-size:13px;color:#6B7280;">
                                Subtotal
                            </td>
                            <td align="right" style="font-size:13px;color:#111827;white-space:nowrap;">
                                Rs. {{ number_format($invoice->subtotal, 2) }}
                            </td>
                        </tr>

                        <tr>
                            <td style="font-size:13px;color:#6B7280;">
                                GST {{ $invoice->gst_rate ?? 18 }}%
                            </td>
                            <td align="right" style="font-size:13px;color:#111827;white-space:nowrap;">
                                Rs. {{ number_format($invoice->total_tax, 2) }}
                            </td>
                        </tr>

                        @if($invoice->discount_amount > 0)
                            <tr>
                                <td style="font-size:13px;color:#6B7280;">
                                    Discount
                                </td>
                                <td align="right" style="font-size:13px;color:#DC2626;white-space:nowrap;">
                                    - Rs. {{ number_format($invoice->discount_amount, 2) }}
                                </td>
                            </tr>
                        @endif

                        <tr>
                            <td colspan="2">
                                <div style="border-top:2px solid #6366F1;margin:8px 0;"></div>
                            </td>
                        </tr>

                        @if($invoice->isPaid())
                            <tr style="font-weight:700;color:#4F46E5;font-size:15px;">
                                <td>
                                    Amount Paid
                                </td>
                                <td align="right" style="white-space:nowrap;">
                                    Rs. {{ number_format($invoice->grand_total, 2) }}
                                </td>
                            </tr>
                        @elseif($invoice->isPartiallyPaid())
                            <tr style="font-weight:700;color:#4F46E5;font-size:15px;">
                                <td>
                                    Balance Due
                                </td>
                                <td align="right" style="white-space:nowrap;">
                                    Rs. {{ number_format($invoice->getBalanceDue(), 2) }}
                                </td>
                            </tr>
                        @elseif($invoice->isOverdue())
                            <tr style="font-weight:700;color:#DC2626;font-size:15px;">
                                <td>
                                    Overdue Amount
                                </td>
                                <td align="right" style="white-space:nowrap;">
                                    Rs. {{ number_format($invoice->grand_total, 2) }}
                                </td>
                            </tr>
                        @else
                            <tr style="font-weight:700;color:#4F46E5;font-size:15px;">
                                <td>
                                    Payable Amount
                                </td>
                                <td align="right" style="white-space:nowrap;">
                                    Rs. {{ number_format($invoice->grand_total, 2) }}
                                </td>
                            </tr>
                        @endif
                    </table>
                </td>
            </tr>
        </table>


        <!-- FOOTER -->
        <table width="100%" style="table-layout:fixed;border-top:1px solid #F3F4F6;padding-top:16px;">
            <tr>
                <td style="font-style:italic;color:#6B7280;font-size:12px;">
                    Thankyou for the business!
                </td>

                <td align="right" style="font-size:12px;color:#9CA3AF;">
                    {{ $invoice->seller_name }}
                </td>
            </tr>
        </table>

    </div>
</div>