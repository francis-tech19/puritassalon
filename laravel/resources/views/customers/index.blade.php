@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{ addModal: false }">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-extrabold text-[#7A1C49] tracking-tight">Client Directory</h1>
            <p class="text-gray-600 font-semibold mt-0.5">Manage salon customer profiles, track total visits, spending, and preferences.</p>
        </div>
        <button @click="addModal = true" class="btn btn-primary shadow-md">
            <i data-lucide="user-plus" class="w-5 h-5"></i> Register New Client
        </button>
    </div>

    <!-- Search Bar -->
    <div class="card p-4">
        <form method="GET" action="{{ route('customers.index') }}" class="flex gap-3">
            <div class="relative flex-1">
                <input type="text" name="search" value="{{ $search }}" placeholder="Search client by name, phone, code, or email..." class="form-input text-base font-bold pl-10">
                <i data-lucide="search" class="w-5 h-5 absolute left-3.5 top-3.5 text-gray-400"></i>
            </div>
            <button type="submit" class="btn btn-primary">Search</button>
            @if($search)
                <a href="{{ route('customers.index') }}" class="btn btn-secondary">Clear</a>
            @endif
        </form>
    </div>

    <!-- Customers Table -->
    <div class="card p-0 overflow-hidden">
        @if($customers->isEmpty())
            <div class="text-center py-16 text-gray-500 font-bold">
                <i data-lucide="user-x" class="w-16 h-16 mx-auto mb-3 text-gray-400"></i>
                <h3 class="text-xl font-extrabold text-gray-700 mb-1">No Clients Found</h3>
                <p class="text-gray-500">No client profiles match your current search criteria.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-100 text-xs font-black text-gray-600 uppercase border-b-2 border-gray-200">
                            <th class="py-3 px-4">Client Code & Name</th>
                            <th class="py-3 px-4">Contact Info</th>
                            <th class="py-3 px-4 text-center">Visits</th>
                            <th class="py-3 px-4 text-right">Total Spent</th>
                            <th class="py-3 px-4">Special Notes</th>
                            <th class="py-3 px-4 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 text-sm font-semibold">
                        @foreach($customers as $c)
                            <tr class="hover:bg-pink-50/50 transition">
                                <td class="py-4 px-4">
                                    <div class="font-black text-[#7A1C49]">{{ $c->full_name }}</div>
                                    <span class="text-xs font-bold text-gray-400 uppercase">{{ $c->customer_code }}</span>
                                </td>
                                <td class="py-4 px-4">
                                    <div class="font-bold text-gray-900">{{ $c->phone }}</div>
                                    <div class="text-xs text-gray-500">{{ $c->email ?? 'No email provided' }}</div>
                                </td>
                                <td class="py-4 px-4 text-center font-black text-gray-800 text-base">
                                    {{ $c->visit_count }}
                                </td>
                                <td class="py-4 px-4 text-right font-black text-[#7A1C49] text-base">
                                    ₱{{ number_format($c->total_spent, 2) }}
                                </td>
                                <td class="py-4 px-4 text-xs text-gray-600 max-w-xs truncate">
                                    {{ $c->notes ?? 'None' }}
                                </td>
                                <td class="py-4 px-4 text-center">
                                    @if($c->status === 'ACTIVE')
                                        <span class="px-2.5 py-1 rounded-full text-xs font-black bg-emerald-100 text-emerald-800 border border-emerald-300">ACTIVE</span>
                                    @else
                                        <span class="px-2.5 py-1 rounded-full text-xs font-black bg-gray-100 text-gray-600 border border-gray-300">ARCHIVED</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-gray-200">
                {{ $customers->links() }}
            </div>
        @endif
    </div>

    <!-- Register Client Modal -->
    <div x-show="addModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 sm:p-6" style="display: none;" role="dialog" aria-modal="true">
        <!-- Background backdrop -->
        <div @click="addModal = false" class="fixed inset-0 modal-backdrop transition-opacity"></div>

        <div class="relative z-10 bg-white rounded-3xl text-left shadow-2xl w-full max-w-lg border-2 border-gray-200 p-6 sm:p-8 max-h-[90vh] overflow-y-auto my-auto">
            <div class="flex items-center justify-between pb-4 mb-4 border-b-2 border-gray-100">
                <h3 class="text-2xl font-extrabold text-[#7A1C49] flex items-center gap-2">
                    <i data-lucide="user-plus" class="w-6 h-6"></i> Register Client Profile
                </h3>
                <button @click="addModal = false" class="text-gray-400 hover:text-gray-700 p-1 rounded-lg">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>

            <form method="POST" action="{{ route('customers.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-extrabold text-gray-900 mb-1">Full Name</label>
                    <input type="text" name="full_name" required placeholder="e.g. Maria Clara Santos" class="form-input font-bold">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-extrabold text-gray-900 mb-1">Phone Number</label>
                        <input type="text" name="phone" required placeholder="0917-000-0000" class="form-input font-bold">
                    </div>
                    <div>
                        <label class="block text-sm font-extrabold text-gray-900 mb-1">Email Address</label>
                        <input type="email" name="email" placeholder="client@example.com" class="form-input font-bold">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-extrabold text-gray-900 mb-1">Home Address</label>
                    <input type="text" name="address" placeholder="City or Barangay" class="form-input font-bold">
                </div>

                <div>
                    <label class="block text-sm font-extrabold text-gray-900 mb-1">Styling Notes & Allergies</label>
                    <textarea name="notes" rows="2" class="form-textarea" placeholder="e.g. Sensitive scalp, preferred dye brand..."></textarea>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                    <button type="button" @click="addModal = false" class="btn btn-secondary">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Client Profile</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
