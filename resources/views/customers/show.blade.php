@extends('layouts.app')
@section('title', $customer->full_name)
@section('heading', $customer->full_name)

@section('content')

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

    {{-- Müşteri Bilgileri --}}
    <div class="space-y-5">
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <h3 class="font-bold text-gray-800 text-lg">{{ $customer->full_name }}</h3>
                    <span class="badge {{ $customer->type === 'corporate' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700' }} text-xs">
                        {{ $customer->type === 'corporate' ? 'Kurumsal' : 'Bireysel' }}
                    </span>
                </div>
                <a href="{{ route('customers.edit', $customer) }}" class="btn btn-secondary text-xs">
                    <i class="fas fa-edit mr-1"></i>Düzenle
                </a>
            </div>
            <div class="space-y-2 text-sm">
                @if($customer->phone)
                <p class="flex gap-2 text-gray-600"><i class="fas fa-phone w-4 text-gray-400 mt-0.5"></i>{{ $customer->phone }}</p>
                @endif
                @if($customer->phone2)
                <p class="flex gap-2 text-gray-600"><i class="fas fa-phone w-4 text-gray-400 mt-0.5"></i>{{ $customer->phone2 }}</p>
                @endif
                @if($customer->email)
                <p class="flex gap-2 text-gray-600"><i class="fas fa-envelope w-4 text-gray-400 mt-0.5"></i>{{ $customer->email }}</p>
                @endif
                @if($customer->address)
                <p class="flex gap-2 text-gray-600"><i class="fas fa-location-dot w-4 text-gray-400 mt-0.5"></i>{{ $customer->address }}, {{ $customer->city }}</p>
                @endif
                @if($customer->tax_number)
                <p class="flex gap-2 text-gray-600"><i class="fas fa-building w-4 text-gray-400 mt-0.5"></i>VN: {{ $customer->tax_number }} / {{ $customer->tax_office }}</p>
                @endif
            </div>

            {{-- Hızlı Eylemler --}}
            <div class="mt-4 pt-4 border-t border-gray-100 flex gap-2 flex-wrap">
                <a href="{{ route('service.create', ['customer_id' => $customer->id]) }}"
                   class="btn btn-primary text-xs">
                    <i class="fas fa-plus mr-1"></i>Yeni Servis
                </a>
                <a href="{{ route('invoices.create', ['customer_id' => $customer->id]) }}"
                   class="btn btn-secondary text-xs">
                    <i class="fas fa-file-invoice mr-1"></i>Fatura
                </a>
            </div>
        </div>

        {{-- Cihazlar --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h3 class="font-semibold text-gray-700 mb-3"><i class="fas fa-mobile-screen mr-2 text-indigo-500"></i>Cihazlar ({{ $customer->devices->count() }})</h3>
            <div class="space-y-2">
                @forelse($customer->devices as $device)
                <div class="p-3 bg-gray-50 rounded-lg">
                    <p class="font-medium text-sm text-gray-800">{{ $device->full_name }}</p>
                    @if($device->serial_no)
                    <p class="text-xs text-gray-400">S/N: {{ $device->serial_no }}</p>
                    @endif
                </div>
                @empty
                <p class="text-gray-400 text-sm">Cihaz kaydı yok.</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Servisler & Faturalar --}}
    <div class="lg:col-span-2 space-y-5">

        {{-- Servis Talepleri --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="font-semibold text-gray-700">Son Servis Talepleri</h3>
                <a href="{{ route('service.index') }}?search={{ $customer->phone }}" class="text-indigo-600 text-xs hover:underline">Tümü →</a>
            </div>
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-400 text-xs">
                    <tr>
                        <th class="px-4 py-3 text-left">No</th>
                        <th class="px-4 py-3 text-left">Cihaz</th>
                        <th class="px-4 py-3 text-left">Durum</th>
                        <th class="px-4 py-3 text-left">Tarih</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @php $statusColors = ['yellow'=>'bg-yellow-100 text-yellow-700','blue'=>'bg-blue-100 text-blue-700','indigo'=>'bg-indigo-100 text-indigo-700','orange'=>'bg-orange-100 text-orange-700','green'=>'bg-green-100 text-green-700','gray'=>'bg-gray-100 text-gray-700','red'=>'bg-red-100 text-red-700']; @endphp
                    @forelse($customer->serviceRequests as $sr)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3"><a href="{{ route('service.show', $sr) }}" class="text-indigo-600 hover:underline">{{ $sr->ticket_no }}</a></td>
                        <td class="px-4 py-3 text-gray-500">{{ $sr->device?->full_name ?? '—' }}</td>
                        <td class="px-4 py-3"><span class="badge {{ $statusColors[$sr->status_color] ?? 'bg-gray-100 text-gray-700' }}">{{ $sr->status_label }}</span></td>
                        <td class="px-4 py-3 text-gray-400 text-xs">{{ $sr->received_at->format('d.m.Y') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="px-4 py-4 text-center text-gray-400">Servis talebi yok.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Faturalar --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-700">Son Faturalar</h3>
            </div>
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-400 text-xs">
                    <tr>
                        <th class="px-4 py-3 text-left">Fatura No</th>
                        <th class="px-4 py-3 text-left">Durum</th>
                        <th class="px-4 py-3 text-right">Toplam</th>
                        <th class="px-4 py-3 text-right">Kalan</th>
                        <th class="px-4 py-3 text-left">Tarih</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($customer->invoices as $inv)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3"><a href="{{ route('invoices.show', $inv) }}" class="text-indigo-600 hover:underline">{{ $inv->invoice_no }}</a></td>
                        <td class="px-4 py-3">
                            @php $ic = \App\Models\Invoice::STATUSES[$inv->status]['color'] ?? 'gray'; $invColors = ['gray'=>'bg-gray-100 text-gray-700','blue'=>'bg-blue-100 text-blue-700','green'=>'bg-green-100 text-green-700','yellow'=>'bg-yellow-100 text-yellow-700','red'=>'bg-red-100 text-red-700']; @endphp
                            <span class="badge {{ $invColors[$ic] ?? 'bg-gray-100 text-gray-700' }}">{{ \App\Models\Invoice::STATUSES[$inv->status]['label'] ?? $inv->status }}</span>
                        </td>
                        <td class="px-4 py-3 text-right font-medium">{{ number_format($inv->total, 2) }} ₺</td>
                        <td class="px-4 py-3 text-right {{ $inv->remaining > 0 ? 'text-red-600' : 'text-green-600' }}">{{ number_format($inv->remaining, 2) }} ₺</td>
                        <td class="px-4 py-3 text-gray-400 text-xs">{{ $inv->issue_date->format('d.m.Y') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="px-4 py-4 text-center text-gray-400">Fatura yok.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
