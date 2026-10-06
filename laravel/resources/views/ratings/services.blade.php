@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-3xl font-extrabold text-[#7A1C49] tracking-tight">Service Ratings</h1>
        <p class="text-gray-600 font-semibold mt-0.5">Review customer feedback for completed salon services.</p>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @foreach($serviceSummaries as $service)
            <div class="card p-5 border-l-4 border-l-[#7A1C49]"><p class="text-sm font-black text-gray-900">{{ $service->service_name }}</p><p class="mt-2 text-2xl font-black text-[#7A1C49]">{{ $service->ratings_avg_rating ? number_format($service->ratings_avg_rating, 1) : '—' }} <span class="text-sm text-gray-500">/ 5</span></p><p class="text-xs font-bold text-gray-500">{{ $service->ratings_count }} rating(s)</p></div>
        @endforeach
    </div>

    <div class="card p-0 overflow-hidden">
        <div class="p-5 border-b-2 border-gray-100"><h2 class="text-xl font-extrabold text-[#7A1C49]">Customer Comments</h2></div>
        <div class="divide-y divide-gray-100">
            @forelse($ratings as $rating)
                <article class="p-5"><div class="flex flex-wrap items-center justify-between gap-2"><div><h3 class="font-black text-gray-900">{{ $rating->service->service_name }}</h3><p class="text-xs font-semibold text-gray-500">{{ $rating->customer->full_name }} · Appointment {{ $rating->appointment->appointment_code }}</p></div><span class="font-black text-amber-600">{{ $rating->rating }} / 5 stars</span></div><p class="mt-3 text-sm font-semibold text-gray-700">{{ $rating->comment ?: 'No written comment.' }}</p></article>
            @empty
                <p class="p-6 text-sm font-semibold text-gray-500">No service ratings have been submitted yet.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection