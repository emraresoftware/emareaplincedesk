@extends('layouts.app')
@section('title', 'Servis Düzenle #' . $serviceRequest->ticket_no)
@section('heading', 'Servis Düzenle')

@section('content')

<div class="max-w-4xl">

    @if($errors->any())
    <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-red-600 text-sm">
        @foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach
    </div>
    @endif

    <form method="POST" action="{{ route('service.update', $serviceRequest) }}" x-data="editService()">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-5">

            {{-- Müşteri & Cihaz --}}
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <h3 class="font-semibold text-gray-700 mb-4">
                    <i class="fas fa-user mr-2 text-indigo-400"></i>Müşteri & Cihaz
                </h3>
                <div class="space-y-3">
                    <div>
                        <label class="form-label">Müşteri</label>
                        <div class="p-2 bg-gray-50 rounded-lg text-sm text-gray-700">
                            {{ $serviceRequest->customer->full_name }}
                        </div>
                        <input type="hidden" name="customer_id" value="{{ $serviceRequest->customer_id }}">
                    </div>
                    <div>
                        <label class="form-label">Cihaz</label>
                        <div class="p-2 bg-gray-50 rounded-lg text-sm text-gray-700">
                            {{ $serviceRequest->device?->full_name ?? '—' }}
                        </div>
                        <input type="hidden" name="device_id" value="{{ $serviceRequest->device_id }}">
                    </div>
                    <div>
                        <label class="form-label">Kategori</label>
                        <select name="device_category_id" class="form-input">
                            <option value="">— Seçin —</option>
                            @foreach($deviceCategories as $cat)
                            <option value="{{ $cat->id }}" @selected($serviceRequest->device_category_id == $cat->id)>{{ $cat->icon }} {{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            {{-- Servis Bilgileri --}}
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <h3 class="font-semibold text-gray-700 mb-4">
                    <i class="fas fa-info-circle mr-2 text-indigo-400"></i>Servis Bilgileri
                </h3>
                <div class="space-y-3">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="form-label">Tür</label>
                            <select name="type" class="form-input">
                                @foreach(\App\Models\ServiceRequest::TYPES as $t)
                                <option value="{{ $t }}" @selected($serviceRequest->type == $t)>
                                    {{ ['repair'=>'Onarım','maintenance'=>'Bakım','installation'=>'Kurulum','inspection'=>'Muayene'][$t] ?? $t }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Öncelik</label>
                            <select name="priority" class="form-input">
                                @foreach(\App\Models\ServiceRequest::PRIORITIES as $p)
                                <option value="{{ $p }}" @selected($serviceRequest->priority == $p)>
                                    {{ ['low'=>'Düşük','normal'=>'Normal','high'=>'Yüksek','urgent'=>'Acil'][$p] ?? $p }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="form-label">Teknisyen</label>
                        <select name="technician_id" class="form-input">
                            <option value="">— Atanmadı —</option>
                            @foreach($technicians as $tech)
                            <option value="{{ $tech->id }}" @selected($serviceRequest->technician_id == $tech->id)>{{ $tech->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Tahmini Teslim Tarihi</label>
                        <input type="date" name="estimated_delivery_date" value="{{ $serviceRequest->estimated_delivery_date?->format('Y-m-d') }}" class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Tahmini Maliyet (₺)</label>
                        <input type="number" name="estimated_cost" value="{{ $serviceRequest->estimated_cost }}" min="0" step="0.01" class="form-input">
                    </div>
                </div>
            </div>
        </div>

        {{-- Sorun bilgisi --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5 mb-5">
            <h3 class="font-semibold text-gray-700 mb-4">
                <i class="fas fa-exclamation-circle mr-2 text-indigo-400"></i>Sorun & Detaylar
            </h3>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Müşteri Şikayeti *</label>
                    <textarea name="problem_description" rows="4" required class="form-input">{{ old('problem_description', $serviceRequest->problem_description) }}</textarea>
                </div>
                <div>
                    <label class="form-label">İç Not / Tanı</label>
                    <textarea name="diagnosis_notes" rows="4" class="form-input">{{ old('diagnosis_notes', $serviceRequest->diagnosis_notes) }}</textarea>
                </div>
                <div class="lg:col-span-2">
                    <label class="form-label">Müşteri Teslimat Notu</label>
                    <textarea name="customer_notes" rows="2" class="form-input">{{ old('customer_notes', $serviceRequest->customer_notes) }}</textarea>
                </div>
            </div>
        </div>

        {{-- Cihaz detayları --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5 mb-5">
            <h3 class="font-semibold text-gray-700 mb-4">
                <i class="fas fa-mobile-alt mr-2 text-indigo-400"></i>Cihaz Teslimat Durumu
            </h3>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="has_charger" class="rounded" @if($serviceRequest->has_charger) checked @endif>
                    <span class="text-sm">Şarj Aleti</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="has_bag" class="rounded" @if($serviceRequest->has_bag) checked @endif>
                    <span class="text-sm">Çanta/Kılıf</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="has_battery" class="rounded" @if($serviceRequest->has_battery) checked @endif>
                    <span class="text-sm">Batarya</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="has_password" class="rounded" @if($serviceRequest->has_password) checked @endif>
                    <span class="text-sm">Şifresi Var</span>
                </label>
            </div>
        </div>

        <div class="flex gap-3">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save mr-2"></i>Güncelle
            </button>
            <a href="{{ route('service.show', $serviceRequest) }}" class="btn btn-secondary">İptal</a>
        </div>
    </form>
</div>

@push('scripts')
<script>
function editService() {
    return {}
}
</script>
@endpush

@endsection
