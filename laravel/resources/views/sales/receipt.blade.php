@extends('layouts.app')

@section('content')
<div class="max-w-xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <a href="{{ route('sales.index') }}" class="btn btn-secondary text-sm">
            <i data-lucide="arrow-left" class="w-4 h-4"></i> Back to POS
        </a>
        <button onclick="window.print()" class="btn btn-primary text-sm shadow-md">
            <i data-lucide="printer" class="w-4 h-4"></i> Print Customer Receipt
        </button>
    </div>

    <!-- Printable Receipt Paper Box -->
    <div class="card p-8 bg-white border-2 border-gray-300 shadow-xl print:shadow-none print:border-none">
        
        <!-- Header -->
        <div class="text-center pb-6 border-b-2 border-dashed border-gray-300">
            <h2 class="text-2xl font-black text-[#7A1C49] tracking-tight">{{ $settings?->salon_name ?? "Purita's Beauty Lounge" }}</h2>
            <p class="text-xs font-bold text-gray-500 mt-1">{{ $settings?->address ?? 'Poblacion Public Market, San Juan, Batangas' }}</p>
            <p class="text-xs font-bold text-gray-500">Phone: {{ $settings?->contact_phone ?? '09611556557' }} / {{ $settings?->contact_phone_secondary ?? '09192001649' }} · {{ $settings?->contact_email ?? 'dcsisters@yahoo.com' }}</p>
            <div class="mt-4 inline-block px-3 py-1 rounded-full bg-emerald-100 text-emerald-800 text-xs font-black uppercase tracking-wider">
                Official Sales Invoice
            </div>
        </div>

        <!-- Meta Details -->
        <div class="py-4 border-b border-gray-200 text-sm font-semibold space-y-1">
            <div class="flex justify-between">
                <span class="text-gray-500">Invoice Code:</span>
                <span class="font-extrabold text-gray-900">{{ $sale->invoice_code }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Date & Time:</span>
                <span class="font-bold text-gray-800">{{ $sale->created_at->format('M d, Y h:i A') }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Customer:</span>
                <span class="font-bold text-gray-800">{{ $sale->customer->full_name ?? 'Walk-in Customer' }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Attending Stylist:</span>
                <span class="font-bold text-gray-800">{{ $sale->employee->full_name ?? 'Salon Counter' }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Payment Method:</span>
                <span class="font-black text-gray-900 uppercase">{{ $sale->payment_method }}</span>
            </div>
        </div>

        <!-- Line Items -->
        <div class="py-4 border-b-2 border-dashed border-gray-300">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="text-xs font-black text-gray-400 uppercase">
                        <th class="pb-2">Item</th>
                        <th class="pb-2 text-center">Qty</th>
                        <th class="pb-2 text-right">Price</th>
                        <th class="pb-2 text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 font-semibold">
                    @foreach($sale->items as $item)
                        <tr>
                            <td class="py-2 pr-2">
                                <div class="font-bold text-gray-900">{{ $item->item_name }}</div>
                                <span class="text-xs text-gray-400 uppercase">{{ $item->item_type }}</span>
                            </td>
                            <td class="py-2 text-center">{{ $item->quantity }}</td>
                            <td class="py-2 text-right">₱{{ number_format($item->unit_price, 2) }}</td>
                            <td class="py-2 text-right font-bold text-gray-900">₱{{ number_format($item->subtotal, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Totals -->
        <div class="pt-4 space-y-1 text-sm font-semibold">
            <div class="flex justify-between text-gray-600">
                <span>Subtotal:</span>
                <span>₱{{ number_format($sale->total_amount, 2) }}</span>
            </div>
            @if($sale->discount_amount > 0)
                <div class="flex justify-between text-emerald-700">
                    <span>Discount Applied:</span>
                    <span>-₱{{ number_format($sale->discount_amount, 2) }}</span>
                </div>
            @endif
            <div class="flex justify-between text-xl font-black text-[#7A1C49] pt-2 border-t border-gray-200">
                <span>Total Paid:</span>
                <span>₱{{ number_format($sale->final_amount, 2) }}</span>
            </div>
        </div>

        <!-- Footer Thank you -->
        <div class="mt-8 text-center text-xs font-bold text-gray-400">
            <p>Thank you for choosing Purita's Beauty Lounge!</p>
            <p class="mt-0.5">Please come again and stay beautiful.</p>
        </div>

    </div>

</div>
@endsection
