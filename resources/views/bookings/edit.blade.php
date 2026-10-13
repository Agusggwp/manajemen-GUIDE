@extends('layouts.app')

@section('title', 'Edit Booking ' . $booking->booking_code)
@section('header', 'Edit Booking: ' . $booking->booking_code)

@section('content')
<div class="max-w-3xl mx-auto clay-card p-6 md:p-8">
    <div class="border-b border-[#E8E6F0] pb-5 mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h2 class="text-xl font-extrabold text-[#29283D] flex items-center gap-2.5">
                <span class="w-8 h-8 rounded-xl bg-[#6C63FF]/10 text-[#6C63FF] flex items-center justify-center text-base">✏️</span>
                <span>Perbarui Data Booking</span>
            </h2>
            <p class="text-xs text-[#77768C] mt-1">Edit rincian pelanggan, waktu dan jumlah tamu</p>
        </div>
        <a href="{{ route('bookings.show', $booking) }}" class="clay-btn clay-btn-secondary text-xs px-3.5 py-2 inline-flex items-center gap-1.5 self-start sm:self-auto">
            <span>&larr;</span> Kembali ke Detail
        </a>
    </div>

    <form action="{{ route('bookings.update', $booking) }}" method="POST" class="space-y-5 text-xs">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
                <label class="block font-bold text-[#29283D] mb-1.5">Nama Pelanggan <span class="text-[#FF8A65]">*</span></label>
                <input type="text" name="customer_name" value="{{ old('customer_name', $booking->customer_name) }}" required
                       class="w-full clay-input">
            </div>

            <div>
                <label class="block font-bold text-[#29283D] mb-1.5">Nomor Telepon</label>
                <input type="text" name="customer_phone" value="{{ old('customer_phone', $booking->customer_phone) }}"
                       class="w-full clay-input" placeholder="08123456789">
            </div>

            <div>
                <label class="block font-bold text-[#29283D] mb-1.5">Kebangsaan</label>
                <input type="text" name="nationality" value="{{ old('nationality', $booking->nationality) }}"
                       class="w-full clay-input" placeholder="Contoh: Domestik, Australia, dsb">
            </div>

            <div>
                <label class="block font-bold text-[#29283D] mb-1.5">Jumlah Tamu (Minimal {{ $booking->assignedGuestsCount() }}) <span class="text-[#FF8A65]">*</span></label>
                <input type="number" name="guest_count" value="{{ old('guest_count', $booking->guest_count) }}" min="{{ max(1, $booking->assignedGuestsCount()) }}" required
                       class="w-full clay-input font-bold text-[#6C63FF]">
            </div>

            <div>
                <label class="block font-bold text-[#29283D] mb-1.5">Tanggal Booking <span class="text-[#FF8A65]">*</span></label>
                <input type="date" name="booking_date" value="{{ old('booking_date', $booking->booking_date->toDateString()) }}" required
                       class="w-full clay-input">
            </div>

            <div>
                <label class="block font-bold text-[#29283D] mb-1.5">Jam Mulai & Selesai <span class="text-[#FF8A65]">*</span></label>
                <div class="grid grid-cols-2 gap-2">
                    <input type="time" name="start_time" value="{{ old('start_time', substr($booking->start_time, 0, 5)) }}" required
                           class="w-full clay-input">
                    <input type="time" name="end_time" value="{{ old('end_time', substr($booking->end_time, 0, 5)) }}" required
                           class="w-full clay-input">
                </div>
            </div>

            <div>
                <label class="block font-bold text-[#29283D] mb-1.5">Lokasi Pickup</label>
                <input type="text" name="pickup_location" value="{{ old('pickup_location', $booking->pickup_location) }}"
                       class="w-full clay-input" placeholder="Hotel / Lokasi Penjemputan">
            </div>

            <div>
                <label class="block font-bold text-[#29283D] mb-1.5">Area Destinasi</label>
                <input type="text" name="location" value="{{ old('location', $booking->location) }}"
                       class="w-full clay-input" placeholder="Area Wisata">
            </div>
        </div>

        <div>
            <label class="block font-bold text-[#29283D] mb-1.5">Catatan Tambahan</label>
            <textarea name="notes" rows="3" class="w-full clay-input resize-y" placeholder="Permintaan khusus tamu, alergi, dsb...">{{ old('notes', $booking->notes) }}</textarea>
        </div>

        <div class="pt-5 border-t border-[#E8E6F0] flex items-center justify-end space-x-3">
            <a href="{{ route('bookings.show', $booking) }}" class="clay-btn clay-btn-secondary px-5 py-2.5 font-bold">
                Batal
            </a>
            <button type="submit" class="clay-btn clay-btn-primary px-6 py-2.5 font-bold">
                Simpan Perubahan
            </button>
        </div>
    </form>
</div>
@endsection
