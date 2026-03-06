@extends('layouts.app')
@section('title', 'Dashboard')
@section('heading', 'Dashboard')

@section('content')

{{-- İstatistik Kartları --}}
<div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">

    @php
    $statCards = [
        ['label' => 'Toplam Servis',  'value' => $stats['total_requests'],  'icon' => 'fa-wrench',         'color' => 'indigo'],
        ['label' => 'Bekleyen',       'value' => $stats['pending'],          'icon' => 'fa-clock',          'color' => 'yellow'],
        ['label' => 'İşlemdeki',      'value' => $stats['in_progress'],      'icon' => 'fa-spinner',        'color' => 'blue'],
        ['label' => 'Teslime Hazır',  'value' => $stats['ready'],            'icon' => 'fa-check-circle',   'color' => 'green'],
        ['label' => 'Bugün Teslim',   'value' => $stats['delivered_today'],  'icon' => 'fa-handshake',      'color' => 'teal'],
        ['label' => 'Müşteriler',     'value' => $stats['total_customers'],  'icon' => 'fa-users',          'color' => 'purple'],
        ['label' => 'Cihazlar',       'value' => $stats['total_devices'],    'icon' => 'fa-mobile-screen',  'color' => 'pink'],
        ['label' => 'Az Stok',        'value' => $stats['low_stock_parts'],  'icon' => 'fa-box-open',       'color' => 'orange'],
    ];
    $colorMap = [
        'indigo' => 'bg-indigo-50 text-indigo-600',
        'yellow' => 'bg-yellow-50 text-yellow-600',
        'blue'   => 'bg-blue-50   text-blue-600',
        'green'  => 'bg-green-50  text-green-600',
        'teal'   => 'bg-teal-50   text-teal-600',
        'purple' => 'bg-purple-50 text-purple-600',
        'pink'   => 'bg-pink-50   text-pink-600',
        'orange' => 'bg-orange-50 text-orange-600',
    ];
    @endphp

    @foreach($statCards as $card)
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 flex items-center gap-4">
        <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0 {{ $colorMap[$card['color']] }}">
            <i class="fas {{ $card['icon'] }}"></i>
        </div>
        <div>
            <p class="text-2xl font-bold text-gray-800">{{ $card['value'] }}</p>
            <p class="text-xs text-gray-500">{{ $card['label'] }}</p>
        </div>
    </div>
    @endforeach
</div>

{{-- Gelir Özeti --}}
<div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
    <div class="bg-gradient-to-r from-green-500 to-emerald-600 rounded-xl p-5 text-white">
        <p class="text-sm opacity-80 mb-1"><i class="fas fa-money-bill-wave mr-2"></i>Bu Ay Gelir</p>
        <p class="text-3xl font-bold">{{ number_format($thisMonthRevenue, 2, ',', '.') }} ₺</p>
    </div>
    <div class="bg-gradient-to-r from-orange-500 to-red-500 rounded-xl p-5 text-white">
        <p class="text-sm opacity-80 mb-1"><i class="fas fa-file-invoice mr-2"></i>Bekleyen Fatura</p>
        <p class="text-3xl font-bold">{{ number_format($pendingInvoice, 2, ',', '.') }} ₺</p>
    </div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

    {{-- Son Servisler --}}
    <div class="xl:col-span-2 bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
            <h3 class="font-semibold text-gray-700">Son Servis Talepleri</h3>
            <a href="{{ route('service.index') }}" class="text-indigo-600 text-sm hover:underline">Tümünü Gör →</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-500 text-xs">
                    <tr>
                        <th class="px-4 py-3 text-left">Talep No</th>
                        <th class="px-4 py-3 text-left">Müşteri</th>
                        <th class="px-4 py-3 text-left">Cihaz</th>
                        <th class="px-4 py-3 text-left">Durum</th>
                        <th class="px-4 py-3 text-left">Öncelik</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($recentRequests as $sr)
                    @php
                    $statusColors = ['yellow'=>'bg-yellow-100 text-yellow-700','blue'=>'bg-blue-100 text-blue-700','indigo'=>'bg-indigo-100 text-indigo-700','orange'=>'bg-orange-100 text-orange-700','green'=>'bg-green-100 text-green-700','gray'=>'bg-gray-100 text-gray-700','red'=>'bg-red-100 text-red-700'];
                    $sc = $statusColors[$sr->status_color] ?? 'bg-gray-100 text-gray-700';
                    $pc = $statusColors[\App\Models\ServiceRequest::PRIORITIES[$sr->priority]['color'] ?? 'gray'] ?? 'bg-gray-100 text-gray-700';
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <a href="{{ route('service.show', $sr) }}" class="text-indigo-600 hover:underline font-medium">{{ $sr->ticket_no }}</a>
                        </td>
                        <td class="px-4 py-3 text-gray-700">{{ $sr->customer?->full_name }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $sr->device?->full_name ?? '—' }}</td>
                        <td class="px-4 py-3">
                            <span class="badge {{ $sc }}">{{ $sr->status_label }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="badge {{ $pc }}">{{ $sr->priority_label }}</span>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="px-4 py-6 text-center text-gray-400">Henüz servis talebi yok.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Teknisyen İş Yükü --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
            <h3 class="font-semibold text-gray-700">Teknisyen İş Yükü</h3>
            <a href="{{ route('technicians.index') }}" class="text-indigo-600 text-sm hover:underline">Yönet →</a>
        </div>
        <div class="p-4 space-y-3">
            @forelse($technicianLoad as $tech)
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 bg-indigo-100 text-indigo-600 rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0">
                    {{ substr($tech->name, 0, 1) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-700 truncate">{{ $tech->name }}</p>
                    <div class="flex items-center gap-2 mt-0.5">
                        <div class="flex-1 bg-gray-100 rounded-full h-1.5">
                            <div class="bg-indigo-500 h-1.5 rounded-full" style="width: {{ min(100, ($tech->active_jobs / max(1,10)) * 100) }}%"></div>
                        </div>
                        <span class="text-xs text-gray-500">{{ $tech->active_jobs }} aktif</span>
                    </div>
                </div>
            </div>
            @empty
            <p class="text-gray-400 text-sm text-center py-4">Teknisyen bulunamadı.</p>
            @endforelse
        </div>
    </div>
</div>

@endsection
