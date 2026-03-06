@extends('layouts.app')
@section('title', $invoice->invoice_no)
@section('heading', 'Fatura: ' . $invoice->invoice_no)

@section('content')

@php
$invColors = ['gray'=>'bg-gray-100 text-gray-700','blue'=>'bg-blue-100 text-blue-700','green'=>'bg-green-100 text-green-700','yellow'=>'bg-yellow-100 text-yellow-700','red'=>'bg-red-100 text-red-700'];
$ic = $invColors[\App\Models\Invoice::STATUSES[$invoice->status]['color'] ?? 'gray'] ?? 'bg-gray-100 text-gray-700';
@endphp

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

    {{-- Sol: Fatura Detayı --}}
    <div class="lg:col-span-2 space-y-5">

        {{-- Başlık Bilgisi --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="flex flex-wrap gap-4 items-start justify-between">
                <div class="space-y-1">
                    <p class="text-2xl font-bold text-gray-800">{{ $invoice->invoice_no }}</p>
                    <span class="badge text-sm {{ $ic }}">
                        {{ \App\Models\Invoice::STATUSES[$invoice->status]['label'] ?? $invoice->status }}
                    </span>
                </div>
                <div class="text-right text-sm text-gray-500">
                    <p>Düzenleme: <span class="text-gray-700">{{ $invoice->issue_date->format('d.m.Y') }}</span></p>
                    @if($invoice->due_date)
                    <p>Vade: <span class="text-gray-700">{{ $invoice->due_date->format('d.m.Y') }}</span></p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Kalemler --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-700">Fatura Kalemleri</h3>
            </div>
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-400 text-xs">
                    <tr>
                        <th class="px-4 py-3 text-left">Açıklama</th>
                        <th class="px-4 py-3 text-center">Adet</th>
                        <th class="px-4 py-3 text-right">Birim Fiyat</th>
                        <th class="px-4 py-3 text-right">İndirim</th>
                        <th class="px-4 py-3 text-right">Toplam</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($invoice->items as $item)
                    <tr>
                        <td class="px-4 py-3">
                            {{ $item->description }}
                            <span class="text-xs text-gray-400 ml-1">{{ $item->type === 'service' ? '(İşçilik)' : ($item->type === 'part' ? '(Parça)' : '') }}</span>
                        </td>
                        <td class="px-4 py-3 text-center">{{ $item->quantity }} {{ $item->unit }}</td>
                        <td class="px-4 py-3 text-right">{{ number_format($item->unit_price, 2) }} ₺</td>
                        <td class="px-4 py-3 text-right text-red-400">{{ $item->discount > 0 ? '-' . number_format($item->discount, 2) . ' ₺' : '—' }}</td>
                        <td class="px-4 py-3 text-right font-medium">{{ number_format($item->total, 2) }} ₺</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-50 text-sm">
                    <tr>
                        <td colspan="4" class="px-4 py-2 text-right text-gray-600">Ara Toplam</td>
                        <td class="px-4 py-2 text-right">{{ number_format($invoice->subtotal, 2) }} ₺</td>
                    </tr>
                    <tr>
                        <td colspan="4" class="px-4 py-2 text-right text-gray-600">KDV (%{{ $invoice->tax_rate }})</td>
                        <td class="px-4 py-2 text-right">{{ number_format($invoice->tax_amount, 2) }} ₺</td>
                    </tr>
                    @if($invoice->discount > 0)
                    <tr>
                        <td colspan="4" class="px-4 py-2 text-right text-red-500">İndirim</td>
                        <td class="px-4 py-2 text-right text-red-500">-{{ number_format($invoice->discount, 2) }} ₺</td>
                    </tr>
                    @endif
                    <tr class="font-bold text-base">
                        <td colspan="4" class="px-4 py-3 text-right text-gray-800">TOPLAM</td>
                        <td class="px-4 py-3 text-right text-indigo-700">{{ number_format($invoice->total, 2) }} {{ $invoice->currency }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- Ödemeler --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden" x-data="{ addPayment: false }">
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-700">Ödemeler</h3>
                @if($invoice->remaining > 0)
                <button @click="addPayment = !addPayment" class="btn btn-success text-sm">
                    <i class="fas fa-plus mr-1"></i>Ödeme Ekle
                </button>
                @endif
            </div>

            {{-- Ödeme Formu --}}
            <div x-show="addPayment" x-cloak class="px-5 py-4 bg-green-50 border-b border-green-200">
                <form method="POST" action="{{ route('invoices.payment', $invoice) }}" class="flex flex-wrap gap-3 items-end">
                    @csrf
                    <div>
                        <label class="form-label">Tutar (₺) *</label>
                        <input type="number" name="amount" step="0.01" value="{{ $invoice->remaining }}" min="0.01" required class="form-input w-32">
                    </div>
                    <div>
                        <label class="form-label">Ödeme Yöntemi *</label>
                        <select name="payment_method" required class="form-input">
                            @foreach(\App\Models\Payment::METHODS as $k => $v)
                            <option value="{{ $k }}">{{ $v['label'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Ödeme Tarihi *</label>
                        <input type="datetime-local" name="paid_at" value="{{ now()->format('Y-m-d\TH:i') }}" required class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Ref No</label>
                        <input type="text" name="ref_no" class="form-input w-32">
                    </div>
                    <button type="submit" class="btn btn-success">Kaydet</button>
                    <button type="button" @click="addPayment=false" class="btn btn-secondary">İptal</button>
                </form>
            </div>

            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-400 text-xs">
                    <tr>
                        <th class="px-4 py-3 text-left">Tarih</th>
                        <th class="px-4 py-3 text-left">Yöntem</th>
                        <th class="px-4 py-3 text-left">Ref No</th>
                        <th class="px-4 py-3 text-right">Tutar</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($invoice->payments as $payment)
                    <tr>
                        <td class="px-4 py-3 text-gray-600">{{ $payment->paid_at->format('d.m.Y H:i') }}</td>
                        <td class="px-4 py-3 text-gray-600">
                            <i class="fas {{ \App\Models\Payment::METHODS[$payment->payment_method]['icon'] ?? 'fa-circle' }} mr-1 text-gray-400"></i>
                            {{ \App\Models\Payment::METHODS[$payment->payment_method]['label'] ?? $payment->payment_method }}
                        </td>
                        <td class="px-4 py-3 text-gray-400 text-xs">{{ $payment->ref_no ?? '—' }}</td>
                        <td class="px-4 py-3 text-right font-semibold text-green-600">{{ number_format($payment->amount, 2) }} ₺</td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="px-4 py-4 text-center text-gray-400">Henüz ödeme yok.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Sağ: Özet --}}
    <div class="space-y-5">
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h3 class="font-semibold text-gray-700 mb-4">Müşteri</h3>
            <p class="font-bold text-gray-800">{{ $invoice->customer?->full_name }}</p>
            <p class="text-gray-500 text-sm mt-1">{{ $invoice->customer?->phone }}</p>
            <p class="text-gray-500 text-sm">{{ $invoice->customer?->email }}</p>
        </div>

        @if($invoice->serviceRequest)
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h3 class="font-semibold text-gray-700 mb-3">Bağlı Servis</h3>
            <a href="{{ route('service.show', $invoice->serviceRequest) }}" class="text-indigo-600 hover:underline font-medium">
                {{ $invoice->serviceRequest->ticket_no }}
            </a>
        </div>
        @endif

        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h3 class="font-semibold text-gray-700 mb-4">Ödeme Özeti</h3>
            <div class="space-y-3">
                <div class="flex justify-between text-sm"><span class="text-gray-600">Toplam</span><span class="font-semibold">{{ number_format($invoice->total, 2) }} ₺</span></div>
                <div class="flex justify-between text-sm"><span class="text-gray-600">Ödenen</span><span class="text-green-600 font-semibold">{{ number_format($invoice->paid_amount, 2) }} ₺</span></div>
                <div class="flex justify-between text-sm border-t pt-3">
                    <span class="font-semibold">Kalan</span>
                    <span class="{{ $invoice->remaining > 0 ? 'text-red-600' : 'text-green-600' }} font-bold text-lg">{{ number_format($invoice->remaining, 2) }} ₺</span>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
