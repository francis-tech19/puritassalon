@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-extrabold text-[#7A1C49] tracking-tight">Salon Analytics & Performance</h1>
            <p class="text-gray-600 font-semibold mt-0.5">Visualize 7-day revenue velocity, high-demand treatments, and staff productivity.</p>
        </div>
    </div>

    <!-- Chart: 7-Day Revenue Trend -->
    <div class="card p-6">
        <h3 class="text-xl font-extrabold text-[#7A1C49] flex items-center gap-2 mb-4 pb-2 border-b">
            <i data-lucide="trending-up" class="w-5 h-5"></i> 7-Day Daily Revenue Trend
        </h3>
        <div class="h-64 sm:h-72 w-full">
            <canvas id="salesTrendChart"></canvas>
        </div>
    </div>

    <!-- Two Column: Top Services & Stylist Performance -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- Top Services -->
        <div class="card p-6">
            <h3 class="text-xl font-extrabold text-[#7A1C49] flex items-center gap-2 mb-4 pb-2 border-b">
                <i data-lucide="sparkles" class="w-5 h-5"></i> Most Requested Services
            </h3>

            @if($topServices->isEmpty())
                <p class="text-gray-400 font-bold text-center py-8">No service sales recorded yet.</p>
            @else
                <div class="space-y-3">
                    @foreach($topServices as $srv)
                        <div class="p-3 rounded-xl bg-gray-50 border border-gray-200 flex items-center justify-between">
                            <div>
                                <h4 class="font-extrabold text-gray-900 text-sm">{{ $srv->item_name }}</h4>
                                <span class="text-xs text-gray-500 font-bold">{{ $srv->total_qty }} clients served</span>
                            </div>
                            <span class="font-black text-base text-[#7A1C49]">₱{{ number_format($srv->total_revenue, 2) }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Stylist Productivity -->
        <div class="card p-6">
            <h3 class="text-xl font-extrabold text-[#7A1C49] flex items-center gap-2 mb-4 pb-2 border-b">
                <i data-lucide="user-check" class="w-5 h-5"></i> Stylist Activity & Output
            </h3>

            <div class="space-y-3">
                @foreach($stylistPerformance as $stylist)
                    <div class="p-3 rounded-xl bg-gray-50 border border-gray-200 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-pink-100 text-[#7A1C49] flex items-center justify-center font-black">
                                {{ substr($stylist->full_name, 0, 1) }}
                            </div>
                            <div>
                                <h4 class="font-extrabold text-gray-900 text-sm">{{ $stylist->full_name }}</h4>
                                <span class="text-xs text-gray-500 font-semibold">{{ $stylist->position }}</span>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="font-black text-sm text-gray-800 block">{{ $stylist->appointments_count }} Bookings</span>
                            <span class="text-xs text-emerald-700 font-extrabold">Active</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

    </div>

</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const trendData = @json($salesTrend);
        const labels = trendData.map(d => d.date);
        const values = trendData.map(d => d.total);

        const ctx = document.getElementById('salesTrendChart');
        if (ctx && window.Chart) {
            new window.Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Daily Revenue (₱)',
                        data: values,
                        borderColor: '#7A1C49',
                        backgroundColor: 'rgba(122, 28, 73, 0.1)',
                        borderWidth: 3,
                        pointBackgroundColor: '#7A1C49',
                        pointRadius: 6,
                        fill: true,
                        tension: 0.3
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) { return '₱' + value; },
                                font: { weight: 'bold' }
                            }
                        },
                        x: {
                            ticks: { font: { weight: 'bold' } }
                        }
                    }
                }
            });
        }
    });
</script>
@endpush
@endsection
