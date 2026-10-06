@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{ addModal: false }">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-extrabold text-[#7A1C49] tracking-tight">Staff & Stylists Directory</h1>
            <p class="text-gray-600 font-semibold mt-0.5">Manage salon master stylists, technicians, schedules, and active statuses.</p>
        </div>
        <button @click="addModal = true" class="btn btn-primary shadow-md">
            <i data-lucide="user-plus" class="w-5 h-5"></i> Add Stylist
        </button>
    </div>

    <!-- Stylist Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @foreach($employees as $emp)
            <div class="card p-6 flex flex-col justify-between border-2 border-gray-200 hover:border-[#7A1C49] transition">
                <div>
                    <div class="flex items-start justify-between">
                        <div class="w-12 h-12 rounded-2xl bg-pink-100 text-[#7A1C49] flex items-center justify-center font-black text-lg">
                            {{ substr($emp->full_name, 0, 1) }}
                        </div>
                        @if($emp->status === 'ACTIVE')
                            <span class="px-2.5 py-1 rounded-full text-xs font-black bg-emerald-100 text-emerald-800 border border-emerald-300">ACTIVE</span>
                        @else
                            <span class="px-2.5 py-1 rounded-full text-xs font-black bg-gray-100 text-gray-600 border border-gray-300">INACTIVE</span>
                        @endif
                    </div>

                    <h3 class="text-lg font-black text-gray-900 mt-3">{{ $emp->full_name }}</h3>
                    <div class="text-xs font-bold text-[#7A1C49] uppercase tracking-wide">{{ $emp->position }}</div>
                    <div class="text-xs font-bold text-gray-400 mt-0.5">{{ $emp->employee_code }}</div>

                    <div class="mt-4 pt-3 border-t border-gray-100 space-y-1.5 text-xs font-semibold text-gray-600">
                        <div class="flex items-center gap-2">
                            <i data-lucide="phone" class="w-4 h-4 text-gray-400"></i>
                            <span>{{ $emp->phone ?? 'No phone' }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <i data-lucide="clock" class="w-4 h-4 text-gray-400"></i>
                            <span>{{ $emp->working_days ? implode(', ', array_map('ucfirst', $emp->working_days)) : ($emp->schedule_notes ?? 'Business operating days') }}
                                @if($emp->shift_start_time && $emp->shift_end_time)
                                    · {{ date('g:i A', strtotime($emp->shift_start_time)) }}–{{ date('g:i A', strtotime($emp->shift_end_time)) }}
                                @endif
                            </span>
                        </div>
                    </div>
                </div>

                <details class="mt-4 border-t border-gray-100 pt-3">
                    <summary class="cursor-pointer text-xs font-bold text-[#7A1C49]">Edit working hours</summary>
                    <form method="POST" action="{{ route('employees.schedule', $emp) }}" class="mt-3 space-y-3">
                        @csrf
                        @method('PATCH')
                        <fieldset>
                            <legend class="mb-2 text-xs font-bold text-gray-700">Working days</legend>
                            <div class="grid grid-cols-2 gap-2 text-xs">
                                @foreach(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $day)
                                    <label class="flex items-center gap-2">
                                        <input type="checkbox" name="working_days[]" value="{{ $day }}" @checked(in_array($day, $emp->working_days ?? [], true))>
                                        {{ ucfirst($day) }}
                                    </label>
                                @endforeach
                            </div>
                        </fieldset>
                        <div class="grid grid-cols-2 gap-2">
                            <label class="text-xs font-bold text-gray-700">Shift starts
                                <input type="time" name="shift_start_time" value="{{ $emp->shift_start_time ?? '09:00' }}" required class="form-input mt-1 text-sm">
                            </label>
                            <label class="text-xs font-bold text-gray-700">Shift ends
                                <input type="time" name="shift_end_time" value="{{ $emp->shift_end_time ?? '19:00' }}" required class="form-input mt-1 text-sm">
                            </label>
                        </div>
                        <button type="submit" class="btn btn-secondary w-full text-xs">Save Schedule</button>
                    </form>
                </details>

                <div class="mt-5 pt-3 border-t border-gray-100 flex items-center justify-between text-xs font-bold text-gray-500">
                    <span>{{ $emp->appointments_count }} Bookings</span>
                    <span>{{ $emp->sales_count }} Sales Handled</span>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Add Employee Modal -->
    <div x-show="addModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 sm:p-6" style="display: none;" role="dialog" aria-modal="true">
        <!-- Background backdrop -->
        <div @click="addModal = false" class="fixed inset-0 modal-backdrop transition-opacity"></div>

        <div class="relative z-10 bg-white rounded-3xl text-left shadow-2xl w-full max-w-lg border-2 border-gray-200 p-6 sm:p-8 max-h-[90vh] overflow-y-auto my-auto">
            <div class="flex items-center justify-between pb-3 mb-4 border-b">
                <h3 class="text-2xl font-extrabold text-[#7A1C49]">Register Stylist</h3>
                <button @click="addModal = false" class="text-gray-400 hover:text-gray-700">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>

            <form method="POST" action="{{ route('employees.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-extrabold text-gray-900 mb-1">Full Name</label>
                    <input type="text" name="full_name" required placeholder="e.g. Maria Santos" class="form-input font-bold">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-extrabold text-gray-900 mb-1">Position / Title</label>
                        <input type="text" name="position" required value="Senior Stylist" class="form-input font-bold">
                    </div>
                    <div>
                        <label class="block text-sm font-extrabold text-gray-900 mb-1">Status</label>
                        <select name="status" class="form-select font-bold">
                            <option value="ACTIVE">Active</option>
                            <option value="INACTIVE">Inactive</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-extrabold text-gray-900 mb-1">Phone Number</label>
                        <input type="text" name="phone" placeholder="0918-000-0000" class="form-input font-bold">
                    </div>
                    <div>
                        <label class="block text-sm font-extrabold text-gray-900 mb-1">Email</label>
                        <input type="email" name="email" placeholder="stylist@beauty.com" class="form-input font-bold">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-extrabold text-gray-900 mb-1">Weekly Work Schedule</label>
                    <input type="text" name="schedule_notes" placeholder="e.g. Tue - Sun (9:00 AM - 6:00 PM)" class="form-input font-bold">
                </div>

                <fieldset class="space-y-2">
                    <legend class="block text-sm font-extrabold text-gray-900">Structured Availability</legend>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-xs">
                        @foreach(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $day)
                            <label class="flex items-center gap-2">
                                <input type="checkbox" name="working_days[]" value="{{ $day }}">
                                {{ ucfirst($day) }}
                            </label>
                        @endforeach
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="text-xs font-bold text-gray-700">Shift starts
                            <input type="time" name="shift_start_time" class="form-input mt-1">
                        </label>
                        <label class="text-xs font-bold text-gray-700">Shift ends
                            <input type="time" name="shift_end_time" class="form-input mt-1">
                        </label>
                    </div>
                </fieldset>

                <div class="flex justify-end gap-3 pt-4 border-t">
                    <button type="button" @click="addModal = false" class="btn btn-secondary">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Stylist</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
