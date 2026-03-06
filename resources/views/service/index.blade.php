@extends('layouts.app')
@section('title', 'Servis Talepleri')
@section('heading', 'Servis Talepleri')

@section('content')

{{-- Filtreler --}}
<form method="GET" class="bg-white rounded-xl border border-gray-200 p-4 mb-5 flex flex-wrap gap-3 items-end">
    <div class="flex-1 min-w-44">
        <label class="form-label">Ara</label>
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Talep no, müşteri, cihaz..."
               class="form-input">
    </div>
    <div class="w-40">
        <label class="form-label">Durum</label>
        <select name="status" class="form-input">
            <option value="">Tümü</option>
            @foreach(\App\Models\ServiceRequest::STATUSES as $k => $v)
            <option value="{{ $k }}" @selected(request('status') == $k)>{{ $v['label'] }}</option>
            @endforeach
        </select>
    </div>
    <div class="w-36">
        <label class="form-label">Öncelik</label>
        <select name="priority" class="form-input">
            <option value="">Tümü</option>
            @foreach(\App\Models\ServiceRequest::PRIORITIES as $k => $v)
            <option value="{{ $k }}" @selected(request('priority') == $k)>{{ $v['label'] }}</option>
            @endforeach
        </select>
    </div>
    <div class="w-44">
        <label class="form-label">Teknisyen</label>
        <select name="technician_id" class="form-input">
            <option value="">Tümü</option>
            @foreach($technicians as $t)
            <option value="{{ $t->id }}" @selected(request('technician_id') == $t->id)>{{ $t->name }}</option>
            @endforeach
        </select>
    </div>
    <button type="submit" class="btn btn-primary">
        <i class="fas fa-search mr-2"></i>Filtrele
    </button>
    <a href="{{ route('service.index') }}" class="btn btn-secondary">Temizle</a>
</form>

{{-- Tablo --}}
<div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
    <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
        <h3 class="font-semibold text-gray-700">{{ $requests->total() }} Kayıt</h3>
        <a href="{{ route('service.create') }}" class="btn btn-primary text-sm">
            <i class="fas fa-plus mr-2"></i>Yeni Servis Talebi
        </a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-500 text-xs">
                <tr>
                    <th class="px-4 py-3 text-left">Talep No</th>
                    <th class="px-4 py-3 text-left">Müşteri</th>
                    <th class="px-4 py-3 text-left">Cihaz</th>
                    <th class="px-4 py-3 text-left">Teknisyen</th>
                    <th class="px-4 py-3 text-left">Tür</th>
                    <th class="px-4 py-3 text-left">Durum</th>
                    <th class="px-4 py-3 text-left">Öncelik</th>
                    <th class="px-4 py-3 text-left">Tarih</th>
                    <th class="px-4 py-3 text-right">İşlem</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @php
                $statusColors = ['yellow'=>'bg-yellow-100 text-yellow-700','blue'=>'bg-blue-100 text-blue-700','indigo'=>'bg-indigo-100 text-indigo-700','orange'=>'bg-orange-100 text-orange-700','green'=>'bg-green-100 text-green-700','gray'=>'bg-gray-100 text-gray-700','red'=>'bg-red-100 text-red-700','teal'=>'bg-teal-100 text-teal-700'];
                @endphp
                @forelse($requests as $sr)
                @php
                $sc = $statusColors[$sr->status_color] ?? 'bg-gray-100 text-gray-700';
                $pc = $statusColors[\App\Models\ServiceRequest::PRIORITIES[$sr->priority]['color'] ?? 'gray'] ?? 'bg-gray-100 text-gray-700';
                @endphp
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3">
                        <a href="{{ route('service.show', $sr) }}" class="text-indigo-600 hover:underline font-medium">{{ $sr->ticket_no }}</a>
                    </td>
                    <td class="px-4 py-3 text-gray-700">{{ $sr->customer?->full_name }}</td>
                    <td class="px-4 py-3 text-gray-500 text-xs">{{ $sr->device?->full_name ?? '—' }}</td>
                    <td class="px-4 py-3 text-gray-500 text-xs">{{ $sr->technician?->name ?? '—' }}</td>
                    <td class="px-4 py-3 text-gray-500 text-xs">{{ \App\Models\ServiceRequest::TYPES[$sr->type] ?? $sr->type }}</td>
                    <td class="px-4 py-3"><span class="badge {{ $sc }}">{{ $sr->status_label }}</span></td>
                    <td class="px-4 py-3"><span class="badge {{ $pc }}">{{ $sr->priority_label }}</span></td>
                    <td class="px-4 py-3 text-gray-400 text-xs">{{ $sr->received_at->format('d.m.Y') }}</td>
                    <td class="px-4 py-3 text-right">
                        <a href="{{ route('service.show', $sr) }}" class="text-indigo-600 hover:text-indigo-800 text-xs">
                            <i class="fas fa-eye"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="9" class="px-4 py-8 text-center text-gray-400">Hiç servis talebi bulunamadı.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-5 py-4 border-t border-gray-100">
        {{ $requests->links() }}
    </div>
</div>

@endsection
