@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-3xl font-extrabold text-indigo-900 tracking-tight">Website Feedback</h1>
        <p class="text-gray-600 font-semibold mt-0.5">Review customer ratings and comments about the F Salon website.</p>
    </div>

    <div class="card p-6 border-l-4 border-l-indigo-700"><span class="text-xs font-black uppercase text-gray-500">Average website rating</span><div class="mt-1 text-3xl font-black text-indigo-900">{{ number_format($averageRating, 1) }} <span class="text-base text-gray-500">/ 5 stars</span></div><p class="mt-1 text-sm font-semibold text-gray-500">{{ $ratings->count() }} submitted rating(s)</p></div>

    <div class="card p-0 overflow-hidden">
        <div class="p-5 border-b-2 border-gray-100"><h2 class="text-xl font-extrabold text-indigo-900">Customer Website Ratings</h2></div>
        <div class="divide-y divide-gray-100">
            @forelse($ratings as $rating)
                <article class="p-5"><div class="flex flex-wrap items-center justify-between gap-2"><div><h3 class="font-black text-gray-900">{{ $rating->user->customer->full_name ?? $rating->user->name }}</h3><p class="text-xs font-semibold text-gray-500">{{ $rating->user->email }} · {{ $rating->created_at->format('M d, Y h:i A') }}</p></div><span class="font-black text-indigo-700">{{ $rating->rating }} / 5 stars</span></div><p class="mt-3 text-sm font-semibold text-gray-700">{{ $rating->comment ?: 'No written comment.' }}</p></article>
            @empty
                <p class="p-6 text-sm font-semibold text-gray-500">No website ratings have been submitted yet.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection