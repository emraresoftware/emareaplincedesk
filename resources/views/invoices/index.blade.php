@extends('layouts.app')
@section('title', 'Faturalar')
@section('heading', 'Fatura Yönetimi')

@section('content')

<form method="GET" class="bg-white rounded-xl border border-gray-200 p-4 mb-5 flex flex-wrap gap-3 items-end">
    <div class="flex-1 min-w-44">
        <label class="form-label">Ara</label>
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Fatura no, müşteri..." class="form-input">
    </div>
    <div class="w-40">
        <label class="form-label">Durum</label>
        <select name="status" class="form-input">
            <option value="">Tümü</option>
            @foreach(\App\Models\Invoice::STATUSES as $k => $v)
            <option value="{{ $k }}" @selected(request('status') == $k)>{{ $v['label'] }}</option>
            @endforeach
        </select>
    </div>
    <button type="submit" class="btn btn-primary">Filtrele</button>
    <a href="{{ route('invoices.index') }}" class="btn btn-secondary">Temizle</a>
</form>

<div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
    <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
        <h3 class="font-semibold text-gray-700">{{ $invoices->total() }} Fatura</h3>
        <a href="{{ route('invoices.create') }}" class="btn btn-primary text-sm">
            <i class="fas fa-plus mr-2"></i>Yeni Fatura
        </a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-400 text-xs">
                <tr>
                    <th class="px-4 py-3 text-left">Fatura No</th>
                    <th class="px-4 py-3 text-left">Müşteri</th>
                    <th class="px-4 py-3 text-left">Servis</th>
                    <th class="px-4 py-3 text-left">Durum</th>
                    <th class="px-4 py-3 text-right">Toplam</th>
                    <th class="px-4 py-3 text-right">Ödenen</th>
                    <th class="px-4 py-3 text-right">Kalan</th>
                    <th class="px-4 py-3 text-left">Tarih</th>
                    <th class="px-4 py-3 text-right">İşlem</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @php
                $invColors = ['gray'=>'bg-gray-100 text-gray-700','blue'=>'bg-blue-100 text-blue-700','green'=>'bg-green-100 text-green-700','yellow'=>'bg-yellow-100 text-yellow-700','red'=>'bg-red-100 text-red-700'];
                @endphp
                @forelse($invoices as $inv)
                @php $ic = $invColors[\App\Models\Invoice::STATUSES[$inv->status]['color'] ?? 'gray'] ?? 'bg-gray-100 text-gray-700'; @endphp
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3"><a href="{{ route('invoices.show', $inv) }}" class="text-indigo-600 hover:underline font-medium">{{ $inv->invoice_no }}</a></td>
                    <td class="px-4 py-3 text-gray-700">{{ $inv->customer?->full_name }}</td>
                    <td class="px-4 py-3 text-gray-400 text-xs">{{ $inv->serviceRequest?->ticket_no ?? '—' }}</td>
                    <td class="px-4 py-3"><span class="badge {{ $ic }}">{{ \App\Models\Invoice::STATUSES[$inv->status]['label'] ?? $inv->status }}</span></td>
                    <td class="px-4 py-3 text-right font-medium">{{ number_format($inv->total, 2) }} ₺</td>
                    <td class="px-4 py-3 text-right text-green-600">{{ number_format($inv->paid_amount, 2) }} ₺</td>
                    <td class="px-4 py-3 text-right {{ $inv->remaining > 0 ? 'text-red-600 font-semibold' : 'text-gray-400' }}">{{ number_format($inv->remaining, 2) }} ₺</td>
                    <td class="px-4 py-3 text-gray-400 text-xs">{{ $inv->issue_date->format('d.m.Y') }}</td>
                    <td class="px-4 py-3 text-right"><a href="{{ route('invoices.show', $inv) }}" class="text-indigo-600 text-xs"><i class="fas fa-eye"></i></a></td>
                </tr>
                @empty
                <tr><td colspan="9" class="px-4 py-8 text-center text-gray-400">Fatura bulunamadı.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-5 py-4 border-t border-gray-100">{{ $invoices->links() }}</div>
</div>

@endsection
