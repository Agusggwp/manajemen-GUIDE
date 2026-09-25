@extends('layouts.app')

@section('title', 'Edit Booking ' . $booking->booking_code)
@section('header', 'Edit Booking: ' . $booking->booking_code)

@section('content')
<div class="max-w-3xl mx-auto bg-white rounded-xl border border-slate-200/80 p-6 shadow-2xs">
    <div class="border-b border-slate-100 pb-4 mb-6 flex items-center justify-between">
        <div>
            <h2 class="text-base font-bold text-slate-900">Perbarui Data Booking</h2>
            <p class="text-xs text-slate-500">Edit rincian pelanggan, waktu dan jumlah tamu</p>
        </div>
        <a href="{{ route('bookings.show', $booking) }}" class="text-xs font-semibold text-slate-500 hover:text-slate-700">
            &larr; Kembali ke Detail
        </a>
    </div>

    <form action="{{ route('bookings.update', $booking) }}" method="POST" class="space-y-4 text-xs">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block font-semibold text-slate-700 mb-1">Nama Pelanggan</label>
                <input type="text" name="customer_name" value="{{ old('customer_name', $booking->customer_name) }}" required
                       class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-md">
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Nomor Telepon</label>
                <input type="text" name="customer_phone" value="{{ old('customer_phone', $booking->customer_phone) }}"
                       class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-md">
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Kebangsaan</label>
                <input type="text" name="nationality" value="{{ old('nationality', $booking->nationality) }}"
                       class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-md">
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Jumlah Tamu (Minimal {{ $booking->assignedGuestsCount() }})</label>
                <input type="number" name="guest_count" value="{{ old('guest_count', $booking->guest_count) }}" min="{{ max(1, $booking->assignedGuestsCount()) }}" required
                       class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-md font-bold">
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Tanggal Booking</label>
                <input type="date" name="booking_date" value="{{ old('booking_date', $booking->booking_date->toDateString()) }}" required
                       class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-md">
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Jam Mulai & Selesai</label>
                <div class="grid grid-cols-2 gap-2">
                    <input type="time" name="start_time" value="{{ old('start_time', substr($booking->start_time, 0, 5)) }}" required
                           class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-md">
                    <input type="time" name="end_time" value="{{ old('end_time', substr($booking->end_time, 0, 5)) }}" required
                           class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-md">
                </div>
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Lokasi Pickup</label>
                <input type="text" name="pickup_location" value="{{ old('pickup_location', $booking->pickup_location) }}"
                       class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-md">
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Area Destinasi</label>
                <input type="text" name="location" value="{{ old('location', $booking->location) }}"
                       class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-md">
            </div>
        </div>

        <div>
            <label class="block font-semibold text-slate-700 mb-1">Catatan</label>
            <textarea name="notes" rows="2" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-md">{{ old('notes', $booking->notes) }}</textarea>
        </div>

        <div class="pt-4 border-t border-slate-100 flex items-center justify-end space-x-2">
            <a href="{{ route('bookings.show', $booking) }}" class="px-4 py-2 border border-slate-300 rounded-lg text-slate-600 font-semibold">
                Batal
            </a>
            <button type="submit" class="px-5 py-2 rounded-lg bg-[#14433b] hover:bg-[#1b584d] text-white font-bold transition-colors">
                Simpan Perubahan
            </button>
        </div>
    </form>
</div>
@endsection
