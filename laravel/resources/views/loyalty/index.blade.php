@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-extrabold text-[#7A1C49] tracking-tight">Customer Loyalty & Rewards</h1>
            <p class="text-gray-600 font-semibold mt-0.5">Automate client rewards for repeat visits and configure salon discount rules.</p>
        </div>
    </div>

    <!-- 2 Column Layout: Program Rules & Top Loyal Clients -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Left: Loyalty Program Rules (1 col) -->
        <div class="card p-6 border-2 border-pink-200 bg-pink-50/40">
            <h3 class="text-xl font-extrabold text-[#7A1C49] flex items-center gap-2 mb-4 pb-2 border-b border-pink-200">
                <i data-lucide="settings-2" class="w-5 h-5"></i> Reward Program Rules
            </h3>

            <form method="POST" action="{{ route('loyalty.settings') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-black uppercase text-gray-700 mb-1">Visits Required for Reward</label>
                    <input type="number" name="visits_required_for_reward" value="{{ $setting->visits_required_for_reward }}" required min="1" max="50" class="form-input font-black text-lg text-center text-[#7A1C49]">
                    <span class="text-xs text-gray-500 font-semibold mt-0.5 block">Client completes this many visits to unlock a reward voucher.</span>
                </div>

                <div>
                    <label class="block text-xs font-black uppercase text-gray-700 mb-1">Reward Description</label>
                    <input type="text" name="reward_description" value="{{ $setting->reward_description }}" required class="form-input font-bold text-sm">
                </div>

                <div>
                    <label class="block text-xs font-black uppercase text-gray-700 mb-1">Discount Percentage (%)</label>
                    <input type="number" step="0.5" name="discount_percentage" value="{{ $setting->discount_percentage }}" required min="1" max="100" class="form-input font-black text-lg text-center text-[#7A1C49]">
                </div>

                <div>
                    <label class="flex items-center gap-2 cursor-pointer pt-2">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" {{ $setting->is_active ? 'checked' : '' }} class="w-5 h-5 rounded border-2 text-[#7A1C49] focus:ring-[#7A1C49]">
                        <span class="text-sm font-extrabold text-gray-800">Program Active</span>
                    </label>
                </div>

                <button type="submit" class="btn btn-primary w-full shadow-sm mt-4">
                    Save Loyalty Rules
                </button>
            </form>
        </div>

        <!-- Right: Top Loyal Clients Leaderboard (2 cols) -->
        <div class="lg:col-span-2 card p-6">
            <h3 class="text-xl font-extrabold text-[#7A1C49] flex items-center gap-2 mb-4 pb-2 border-b">
                <i data-lucide="crown" class="w-5 h-5 text-amber-500"></i> Top Loyal Clients Leaderboard
            </h3>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="bg-gray-100 text-xs font-black text-gray-600 uppercase border-b">
                            <th class="py-2.5 px-3">Client Name</th>
                            <th class="py-2.5 px-3 text-center">Completed Visits</th>
                            <th class="py-2.5 px-3 text-right">Total Spent</th>
                            <th class="py-2.5 px-3 text-center">Next Reward In</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 font-semibold">
                        @foreach($topLoyalCustomers as $cust)
                            @php
                                $threshold = $setting->visits_required_for_reward;
                                $visitsRemaining = $threshold - ($cust->visit_count % $threshold);
                                if ($visitsRemaining === $threshold && $cust->visit_count > 0) $visitsRemaining = 0;
                            @endphp
                            <tr class="hover:bg-pink-50/50">
                                <td class="py-3 px-3">
                                    <div class="font-extrabold text-gray-900">{{ $cust->full_name }}</div>
                                    <div class="text-xs text-gray-400 font-bold">{{ $cust->customer_code }}</div>
                                </td>
                                <td class="py-3 px-3 text-center font-black text-base text-[#7A1C49]">
                                    {{ $cust->visit_count }} visits
                                </td>
                                <td class="py-3 px-3 text-right font-black text-gray-900">
                                    ₱{{ number_format($cust->total_spent, 2) }}
                                </td>
                                <td class="py-3 px-3 text-center">
                                    @if($visitsRemaining === 0)
                                        <span class="px-2 py-0.5 rounded-full text-xs font-black bg-emerald-100 text-emerald-800">Eligible Now!</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-gray-100 text-gray-700">{{ $visitsRemaining }} visits away</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- Active & Issued Loyalty Rewards -->
    <div class="card p-0 overflow-hidden">
        <div class="p-5 border-b-2 border-gray-100 flex items-center justify-between">
            <h3 class="text-xl font-extrabold text-[#7A1C49] flex items-center gap-2">
                <i data-lucide="award" class="w-5 h-5"></i> Issued Customer Rewards & Vouchers
            </h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="bg-gray-100 text-xs font-black text-gray-600 uppercase border-b">
                        <th class="py-3 px-4">Client</th>
                        <th class="py-3 px-4">Reward Voucher</th>
                        <th class="py-3 px-4">Issued Date</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-right">Redemption</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 font-semibold">
                    @forelse($rewards as $r)
                        <tr class="hover:bg-pink-50/50">
                            <td class="py-4 px-4 font-extrabold text-gray-900">
                                {{ $r->customer->full_name ?? 'Client #' . $r->customer_id }}
                            </td>
                            <td class="py-4 px-4 font-bold text-[#7A1C49]">
                                {{ $r->reward_title }} ({{ $r->discount_percentage }}% OFF)
                            </td>
                            <td class="py-4 px-4 text-xs text-gray-500 font-bold">
                                {{ date('M d, Y', strtotime($r->issued_date)) }}
                            </td>
                            <td class="py-4 px-4 text-center">
                                @if($r->status === 'AVAILABLE')
                                    <span class="px-2.5 py-1 rounded-full text-xs font-black bg-emerald-100 text-emerald-800 border border-emerald-300">AVAILABLE</span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-xs font-black bg-gray-100 text-gray-600 border">REDEEMED</span>
                                @endif
                            </td>
                            <td class="py-4 px-4 text-right">
                                @if($r->status === 'AVAILABLE')
                                    <form method="POST" action="{{ route('loyalty.redeem', $r->id) }}">
                                        @csrf
                                        <button type="submit" onclick="return confirm('Apply and redeem this reward voucher?')" class="px-3 py-1.5 rounded-xl bg-[#7A1C49] text-white font-extrabold text-xs hover:bg-[#5C1236] transition shadow-sm">
                                            Redeem Voucher
                                        </button>
                                    </form>
                                @else
                                    <span class="text-xs text-gray-400 font-semibold">Redeemed {{ date('M d', strtotime($r->redeemed_date)) }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-10 text-gray-400 font-bold">
                                No reward vouchers issued yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
