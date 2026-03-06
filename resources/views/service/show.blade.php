@extends('layouts.app')
@section('title', $service->ticket_no)
@section('heading', 'Servis: ' . $service->ticket_no)

@section('content')

@php
$statusColors = ['yellow'=>'bg-yellow-100 text-yellow-700','blue'=>'bg-blue-100 text-blue-700','indigo'=>'bg-indigo-100 text-indigo-700','orange'=>'bg-orange-100 text-orange-700','green'=>'bg-green-100 text-green-700','gray'=>'bg-gray-100 text-gray-700','red'=>'bg-red-100 text-red-700'];
$sc = $statusColors[$service->status_color] ?? 'bg-gray-100 text-gray-700';
$pc = $statusColors[\App\Models\ServiceRequest::PRIORITIES[$service->priority]['color'] ?? 'gray'] ?? 'bg-gray-100 text-gray-700';
@endphp

{{-- Üst Bilgi Şeridi --}}
<div class="bg-white rounded-xl border border-gray-200 p-5 mb-5 flex flex-wrap gap-4 items-start justify-between">
    <div class="flex flex-wrap gap-4 items-start">
        <div>
            <p class="text-xs text-gray-400">Talep No</p>
            <p class="text-xl font-bold text-indigo-600">{{ $service->ticket_no }}</p>
        </div>
        <div>
            <p class="text-xs text-gray-400">Durum</p>
            <span class="badge text-sm {{ $sc }}">{{ $service->status_label }}</span>
        </div>
        <div>
            <p class="text-xs text-gray-400">Öncelik</p>
            <span class="badge text-sm {{ $pc }}">{{ $service->priority_label }}</span>
        </div>
        <div>
            <p class="text-xs text-gray-400">Tür</p>
            <p class="text-sm text-gray-700">{{ \App\Models\ServiceRequest::TYPES[$service->type] ?? $service->type }}</p>
        </div>
        <div>
            <p class="text-xs text-gray-400">Teslim Alındı</p>
            <p class="text-sm text-gray-700">{{ $service->received_at->format('d.m.Y H:i') }}</p>
        </div>
        @if($service->estimated_completion_at)
        <div>
            <p class="text-xs text-gray-400">Tahmini Teslim</p>
            <p class="text-sm text-gray-700">{{ $service->estimated_completion_at->format('d.m.Y H:i') }}</p>
        </div>
        @endif
    </div>
    <div class="flex gap-2">
        <a href="{{ route('service.edit', $service) }}" class="btn btn-secondary text-sm">
            <i class="fas fa-edit mr-1"></i>Düzenle
        </a>
        <form method="POST" action="{{ route('service.destroy', $service) }}" onsubmit="return confirm('Silinsin mi?')">
            @csrf @method('DELETE')
            <button class="btn btn-danger text-sm"><i class="fas fa-trash mr-1"></i>Sil</button>
        </form>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

    {{-- Sol Kolon --}}
    <div class="lg:col-span-2 space-y-5">

        {{-- Sorun & Teşhis --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h3 class="font-semibold text-gray-700 mb-4"><i class="fas fa-stethoscope mr-2 text-indigo-500"></i>Sorun & Teşhis</h3>
            <div class="space-y-3 text-sm">
                <div>
                    <p class="font-medium text-gray-600 mb-1">Müşteri Bildirimi</p>
                    <p class="text-gray-700 bg-gray-50 rounded-lg p-3">{{ $service->problem_description }}</p>
                </div>
                @if($service->diagnosis)
                <div>
                    <p class="font-medium text-gray-600 mb-1">Teknisyen Teşhisi</p>
                    <p class="text-gray-700 bg-blue-50 rounded-lg p-3">{{ $service->diagnosis }}</p>
                </div>
                @endif
                @if($service->solution)
                <div>
                    <p class="font-medium text-gray-600 mb-1">Uygulanan Çözüm</p>
                    <p class="text-gray-700 bg-green-50 rounded-lg p-3">{{ $service->solution }}</p>
                </div>
                @endif
            </div>
        </div>

        {{-- Durum Güncelle --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5" x-data="{ open: false }">
            <div class="flex items-center justify-between mb-3">
                <h3 class="font-semibold text-gray-700"><i class="fas fa-sync-alt mr-2 text-indigo-500"></i>Durum Güncelle</h3>
                <button @click="open = !open" class="btn btn-secondary text-sm">
                    <i class="fas fa-caret-down mr-1"></i>Güncelle
                </button>
            </div>
            <div x-show="open" x-cloak>
                <form method="POST" action="{{ route('service.status', $service) }}" class="flex flex-wrap gap-3 items-end">
                    @csrf
                    <div class="w-48">
                        <label class="form-label">Yeni Durum</label>
                        <select name="status" class="form-input">
                            @foreach(\App\Models\ServiceRequest::STATUSES as $k => $v)
                            <option value="{{ $k }}" @selected($k === $service->status)>{{ $v['label'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex-1 min-w-48">
                        <label class="form-label">Not (opsiyonel)</label>
                        <input type="text" name="note" class="form-input" placeholder="Durum notu...">
                    </div>
                    <button type="submit" class="btn btn-primary">Güncelle</button>
                </form>
            </div>
        </div>

        {{-- Kullanılan Parçalar --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5" x-data="{ addPart: false }">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-gray-700"><i class="fas fa-box-open mr-2 text-indigo-500"></i>Kullanılan Parçalar</h3>
                <button @click="addPart = !addPart" class="btn btn-secondary text-sm">
                    <i class="fas fa-plus mr-1"></i>Parça Ekle
                </button>
            </div>
            <div x-show="addPart" x-cloak class="mb-4 p-4 bg-gray-50 rounded-lg">
                <form method="POST" action="{{ route('spare-parts.use') }}" class="flex flex-wrap gap-3 items-end">
                    @csrf
                    <input type="hidden" name="service_request_id" value="{{ $service->id }}">
                    <div class="flex-1 min-w-40">
                        <label class="form-label">Parça</label>
                        <select name="spare_part_id" class="form-input">
                            <option value="">— Seçin —</option>
                            @foreach($spareParts as $p)
                            <option value="{{ $p->id }}">{{ $p->name }} (Stok: {{ $p->quantity }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="w-24">
                        <label class="form-label">Adet</label>
                        <input type="number" name="quantity" value="1" min="1" class="form-input">
                    </div>
                    <button type="submit" class="btn btn-success text-sm">Ekle</button>
                </form>
            </div>
            @if($service->spareParts->count())
            <table class="w-full text-sm">
                <thead class="text-xs text-gray-500 border-b">
                    <tr>
                        <th class="text-left pb-2">Parça</th>
                        <th class="text-right pb-2">Adet</th>
                        <th class="text-right pb-2">Birim Fiyat</th>
                        <th class="text-right pb-2">Toplam</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($service->spareParts as $usage)
                    <tr>
                        <td class="py-2">{{ $usage->sparePart?->name }}</td>
                        <td class="py-2 text-right">{{ $usage->quantity }}</td>
                        <td class="py-2 text-right">{{ number_format($usage->unit_price, 2) }} ₺</td>
                        <td class="py-2 text-right font-medium">{{ number_format($usage->total, 2) }} ₺</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <p class="text-gray-400 text-sm">Henüz parça eklenmedi.</p>
            @endif
        </div>

        {{-- Notlar --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-gray-700"><i class="fas fa-comments mr-2 text-indigo-500"></i>Notlar & Geçmiş</h3>
            </div>
            <form method="POST" action="{{ route('service.note', $service) }}" class="flex gap-3 mb-4">
                @csrf
                <input type="text" name="note" class="form-input flex-1" placeholder="Not ekle..." required>
                <label class="flex items-center gap-1 text-xs text-gray-600">
                    <input type="checkbox" name="is_visible_to_customer" value="1" class="rounded">
                    Müşteriye göster
                </label>
                <button class="btn btn-primary text-sm">Ekle</button>
            </form>
            <div class="space-y-3">
                @foreach($service->notes as $note)
                <div class="flex gap-3">
                    <div class="w-7 h-7 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center text-xs font-bold flex-shrink-0">
                        {{ substr($note->user?->name ?? 'S', 0, 1) }}
                    </div>
                    <div class="flex-1 bg-gray-50 rounded-lg p-3">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="font-medium text-xs text-gray-700">{{ $note->user?->name ?? 'Sistem' }}</span>
                            @if($note->type === 'status_change')
                            <span class="badge bg-blue-100 text-blue-600 text-xs">Durum Değişikliği</span>
                            @endif
                            @if($note->is_visible_to_customer)
                            <span class="badge bg-green-100 text-green-600 text-xs">Müşteri görür</span>
                            @endif
                            <span class="text-xs text-gray-400 ml-auto">{{ $note->created_at->diffForHumans() }}</span>
                        </div>
                        <p class="text-sm text-gray-700">{{ $note->note }}</p>
                        @if($note->type === 'status_change' && $note->old_status && $note->new_status)
                        <p class="text-xs text-gray-400 mt-1">
                            {{ \App\Models\ServiceRequest::STATUSES[$note->old_status]['label'] ?? $note->old_status }}
                            <i class="fas fa-arrow-right mx-1"></i>
                            {{ \App\Models\ServiceRequest::STATUSES[$note->new_status]['label'] ?? $note->new_status }}
                        </p>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Sağ Kolon --}}
    <div class="space-y-5">

        {{-- Müşteri & Cihaz --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h3 class="font-semibold text-gray-700 mb-4"><i class="fas fa-user mr-2 text-indigo-500"></i>Müşteri</h3>
            <div class="space-y-2 text-sm">
                <p class="font-semibold text-gray-800">{{ $service->customer?->full_name }}</p>
                @if($service->customer?->phone)
                <p class="text-gray-500"><i class="fas fa-phone w-4 mr-2 text-gray-400"></i>{{ $service->customer->phone }}</p>
                @endif
                @if($service->customer?->email)
                <p class="text-gray-500"><i class="fas fa-envelope w-4 mr-2 text-gray-400"></i>{{ $service->customer->email }}</p>
                @endif
            </div>
            <a href="{{ route('customers.show', $service->customer_id) }}" class="mt-3 inline-block text-indigo-600 text-xs hover:underline">Müşteri Profiline Git →</a>
        </div>

        @if($service->device)
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h3 class="font-semibold text-gray-700 mb-4"><i class="fas fa-mobile-screen mr-2 text-indigo-500"></i>Cihaz</h3>
            <div class="space-y-2 text-sm">
                <p class="font-semibold text-gray-800">{{ $service->device->full_name }}</p>
                @if($service->device->serial_no)
                <p class="text-gray-500">S/N: {{ $service->device->serial_no }}</p>
                @endif
                @if($service->device->imei)
                <p class="text-gray-500">IMEI: {{ $service->device->imei }}</p>
                @endif
                <p class="text-gray-500">Durum: {{ $service->device_condition_in ?? '—' }}</p>
            </div>
        </div>
        @endif

        {{-- Teknisyen --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h3 class="font-semibold text-gray-700 mb-3"><i class="fas fa-user-gear mr-2 text-indigo-500"></i>Atanan Teknisyen</h3>
            @if($service->technician)
            <p class="font-medium text-gray-800 text-sm">{{ $service->technician->name }}</p>
            @if($service->technician->phone)
            <p class="text-gray-400 text-xs mt-1">{{ $service->technician->phone }}</p>
            @endif
            @else
            <p class="text-gray-400 text-sm">Henüz atanmadı.</p>
            @endif
        </div>

        {{-- Maliyet --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h3 class="font-semibold text-gray-700 mb-3"><i class="fas fa-lira-sign mr-2 text-indigo-500"></i>Maliyet</h3>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between text-gray-600"><span>İşçilik</span><span>{{ number_format($service->labor_cost, 2) }} ₺</span></div>
                <div class="flex justify-between text-gray-600"><span>Parçalar</span><span>{{ number_format($service->parts_cost, 2) }} ₺</span></div>
                @if($service->discount > 0)
                <div class="flex justify-between text-red-500"><span>İndirim</span><span>-{{ number_format($service->discount, 2) }} ₺</span></div>
                @endif
                <div class="flex justify-between font-bold text-gray-800 border-t pt-2"><span>Toplam</span><span>{{ number_format($service->total_cost, 2) }} ₺</span></div>
            </div>
            @if(!$service->invoices->count() && $service->total_cost > 0)
            <a href="{{ route('invoices.create', ['service_id' => $service->id, 'customer_id' => $service->customer_id]) }}"
               class="btn btn-success text-sm w-full justify-center mt-3">
                <i class="fas fa-file-invoice mr-2"></i>Fatura Oluştur
            </a>
            @elseif($service->invoices->count())
            @foreach($service->invoices as $inv)
            <a href="{{ route('invoices.show', $inv) }}" class="btn btn-secondary text-sm w-full justify-center mt-2">
                <i class="fas fa-file mr-1"></i>{{ $inv->invoice_no }}
            </a>
            @endforeach
            @endif
        </div>
    </div>
</div>

@endsection
