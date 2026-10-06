@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{ addModal: false }">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-extrabold text-[#7A1C49] tracking-tight">Salon Services & Menu</h1>
            <p class="text-gray-600 font-semibold mt-0.5">Manage salon treatments, styling packages, service durations, and pricing.</p>
        </div>
        <button @click="addModal = true" class="btn btn-primary shadow-md">
            <i data-lucide="plus" class="w-5 h-5"></i> Add New Treatment
        </button>
    </div>

    <!-- Category Grouped Display -->
    @foreach($categories as $cat)
        <div class="card p-5">
            <div class="flex items-center justify-between pb-3 mb-4 border-b-2 border-gray-100">
                <h3 class="text-xl font-extrabold text-[#7A1C49] flex items-center gap-2">
                    <i data-lucide="sparkles" class="w-5 h-5"></i> {{ $cat }}
                </h3>
                <span class="text-xs font-black uppercase text-gray-400">
                    {{ $services->where('category', $cat)->count() }} Services
                </span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($services->where('category', $cat) as $srv)
                    <div class="p-4 rounded-2xl border-2 border-gray-200 bg-white flex flex-col justify-between hover:border-[#7A1C49] transition shadow-sm">
                        <div>
                            @if($srv->photo_path)
                                <img src="{{ asset('storage/' . $srv->photo_path) }}" alt="{{ $srv->service_name }}" class="w-full h-32 object-cover rounded-xl mb-3">
                            @endif
                            <div class="flex items-center justify-between">
                                <h4 class="font-extrabold text-gray-900 text-base">{{ $srv->service_name }}</h4>
                                @if($srv->status === 'ACTIVE')
                                    <span class="px-2 py-0.5 rounded text-xs font-bold bg-emerald-100 text-emerald-800">Active</span>
                                @else
                                    <span class="px-2 py-0.5 rounded text-xs font-bold bg-gray-100 text-gray-600">Inactive</span>
                                @endif
                            </div>
                            <p class="text-xs text-gray-500 mt-1 font-semibold">{{ $srv->description ?? 'No description' }}</p>
                        </div>

                        <div class="mt-4 pt-3 border-t border-gray-100 flex items-center justify-between">
                            <span class="text-xs text-gray-500 font-bold flex items-center gap-1">
                                <i data-lucide="clock" class="w-3.5 h-3.5"></i> {{ $srv->duration_minutes }} mins
                            </span>
                            <span class="text-lg font-black text-[#7A1C49]">₱{{ number_format($srv->price, 2) }}</span>
                        </div>

                        <details class="mt-3 border-t border-gray-100 pt-3">
                            <summary class="cursor-pointer text-xs font-bold text-[#7A1C49]">Edit service details</summary>
                            <form method="POST" action="{{ route('services.update', $srv) }}" class="mt-3 space-y-3">
                                @csrf
                                @method('PUT')
                                <label class="block text-xs font-bold text-gray-700">Service name
                                    <input type="text" name="service_name" value="{{ $srv->service_name }}" required maxlength="100" class="form-input mt-1 text-sm">
                                </label>
                                <label class="block text-xs font-bold text-gray-700">Category
                                    <input type="text" name="category" value="{{ $srv->category }}" required maxlength="50" class="form-input mt-1 text-sm">
                                </label>
                                <label class="block text-xs font-bold text-gray-700">Description
                                    <textarea name="description" rows="2" class="form-textarea mt-1 text-sm">{{ $srv->description }}</textarea>
                                </label>
                                <div class="grid grid-cols-2 gap-2">
                                    <label class="text-xs font-bold text-gray-700">Price (₱)
                                        <input type="number" name="price" value="{{ $srv->price }}" step="0.01" min="0" required class="form-input mt-1 text-sm">
                                    </label>
                                    <label class="text-xs font-bold text-gray-700">Duration (minutes)
                                        <input type="number" name="duration_minutes" value="{{ $srv->duration_minutes }}" min="5" max="720" required class="form-input mt-1 text-sm">
                                    </label>
                                </div>
                                <label class="block text-xs font-bold text-gray-700">Availability
                                    <select name="status" class="form-select mt-1 text-sm">
                                        <option value="ACTIVE" @selected($srv->status === 'ACTIVE')>Available</option>
                                        <option value="INACTIVE" @selected($srv->status === 'INACTIVE')>Unavailable</option>
                                    </select>
                                </label>
                                <button type="submit" class="btn btn-secondary w-full text-xs">Save Service</button>
                            </form>
                        </details>
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach

    <!-- Add Service Modal -->
    <div x-show="addModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 sm:p-6" style="display: none;" role="dialog" aria-modal="true">
        <!-- Background backdrop -->
        <div @click="addModal = false" class="fixed inset-0 modal-backdrop transition-opacity"></div>

        <div class="relative z-10 bg-white rounded-3xl text-left shadow-2xl w-full max-w-lg border-2 border-gray-200 p-6 sm:p-8 max-h-[90vh] overflow-y-auto my-auto">
            <div class="flex items-center justify-between pb-4 mb-4 border-b-2 border-gray-100">
                <h3 class="text-2xl font-extrabold text-[#7A1C49] flex items-center gap-2">
                    <i data-lucide="sparkles" class="w-6 h-6"></i> Add Treatment Service
                </h3>
                <button @click="addModal = false" class="text-gray-400 hover:text-gray-700 p-1 rounded-lg">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>

            <form method="POST" action="{{ route('services.store') }}" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-extrabold text-gray-900 mb-1">Service Name</label>
                    <input type="text" name="service_name" required placeholder="e.g. Brazilian Blowout" class="form-input font-bold">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-extrabold text-gray-900 mb-1">Category</label>
                        <select name="category" required class="form-select font-bold">
                            <option value="Hair Care">Hair Care</option>
                            <option value="Nail Care">Nail Care</option>
                            <option value="Facial & Skin">Facial & Skin</option>
                            <option value="Massage & Spa">Massage & Spa</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-extrabold text-gray-900 mb-1">Price (₱)</label>
                        <input type="number" step="1" min="0" name="price" required placeholder="500.00" class="form-input font-bold">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-extrabold text-gray-900 mb-1">Duration (Minutes)</label>
                        <input type="number" step="5" min="15" name="duration_minutes" value="60" required class="form-input font-bold">
                    </div>
                    <div>
                        <label class="block text-sm font-extrabold text-gray-900 mb-1">Status</label>
                        <select name="status" class="form-select font-bold">
                            <option value="ACTIVE">Active</option>
                            <option value="INACTIVE">Inactive</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-extrabold text-gray-900 mb-1">Description</label>
                    <textarea name="description" rows="2" class="form-textarea" placeholder="Treatment procedure notes..."></textarea>
                </div>

                <div>
                    <label class="block text-sm font-extrabold text-gray-900 mb-1">Service Photo</label>
                    <input type="file" name="photo" accept="image/jpeg,image/png,image/webp" class="form-input font-bold">
                    <p class="text-xs text-gray-500 mt-1">JPG, PNG, or WebP up to 5 MB.</p>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                    <button type="button" @click="addModal = false" class="btn btn-secondary">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Service</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
