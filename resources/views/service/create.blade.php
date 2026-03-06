@extends('layouts.app')
@section('title', 'Yeni Servis Talebi')
@section('heading', 'Yeni Servis Talebi Oluştur')

@section('content')

<div class="max-w-4xl" x-data="serviceForm()">

    @if($errors->any())
    <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-red-600 text-sm">
        @foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach
    </div>
    @endif

    <form method="POST" action="{{ route('service.store') }}">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            {{-- Müşteri --}}
            <div class="bg-white rounded-xl border border-gray-200 p-5 lg:col-span-2">
                <h3 class="font-semibold text-gray-700 mb-4"><i class="fas fa-user mr-2 text-indigo-500"></i>Müşteri Bilgisi</h3>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-end">
                    <div class="sm:col-span-2">
                        <label class="form-label">Müşteri Ara veya Seç *</label>
                        <select name="customer_id" required class="form-input" @change="loadDevices($event.target.value)">
                            <option value="">— Müşteri Seçin —</option>
                            @if($customer)
                            <option value="{{ $customer->id }}" selected>{{ $customer->full_name }} ({{ $customer->phone }})</option>
                            @else
                            {{-- JS ile doldurulacak --}}
                            @endif
                        </select>
                    </div>
                    <div>
                        <a href="{{ route('customers.create') }}" class="btn btn-secondary w-full justify-center">
                            <i class="fas fa-user-plus mr-2"></i>Yeni Müşteri
                        </a>
                    </div>
                </div>
            </div>

            {{-- Cihaz --}}
            <div class="bg-white rounded-xl border border-gray-200 p-5 lg:col-span-2">
                <h3 class="font-semibold text-gray-700 mb-4"><i class="fas fa-mobile-screen mr-2 text-indigo-500"></i>Cihaz Bilgisi</h3>
                <div class="mb-4">
                    <label class="inline-flex items-center gap-2">
                        <input type="checkbox" x-model="isNewDevice" class="rounded border-gray-300">
                        <span class="text-sm text-gray-700">Yeni cihaz kaydet</span>
                    </label>
                </div>

                <div x-show="!isNewDevice">
                    <label class="form-label">Kayıtlı Cihaz (opsiyonel)</label>
                    <select name="device_id" class="form-input">
                        <option value="">— Cihaz Seçin —</option>
                    </select>
                </div>

                <div x-show="isNewDevice" x-cloak class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                    <div class="sm:col-span-1">
                        <label class="form-label">Kategori</label>
                        <select name="new_category_id" class="form-input">
                            <option value="">— Kategori —</option>
                            @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Marka *</label>
                        <input type="text" name="new_brand" placeholder="Apple, Samsung..." class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Model *</label>
                        <input type="text" name="new_model" placeholder="iPhone 15, S24..." class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Seri No</label>
                        <input type="text" name="new_serial_no" class="form-input">
                    </div>
                </div>
            </div>

            {{-- Servis Detayları --}}
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <h3 class="font-semibold text-gray-700 mb-4"><i class="fas fa-wrench mr-2 text-indigo-500"></i>Servis Detayları</h3>
                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="form-label">Tür *</label>
                            <select name="type" required class="form-input">
                                @foreach(\App\Models\ServiceRequest::TYPES as $k => $v)
                                <option value="{{ $k }}">{{ $v }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Öncelik *</label>
                            <select name="priority" required class="form-input">
                                @foreach(\App\Models\ServiceRequest::PRIORITIES as $k => $v)
                                <option value="{{ $k }}" @selected($k === 'normal')>{{ $v['label'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="form-label">Teknisyen</label>
                        <select name="assigned_technician_id" class="form-input">
                            <option value="">— Atanmadı —</option>
                            @foreach($technicians as $t)
                            <option value="{{ $t->id }}">{{ $t->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Teslim Alım Tarihi *</label>
                        <input type="datetime-local" name="received_at" value="{{ now()->format('Y-m-d\TH:i') }}" required class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Tahmini Teslim Tarihi</label>
                        <input type="datetime-local" name="estimated_completion_at" class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Tahmini Maliyet (₺)</label>
                        <input type="number" name="estimated_cost" step="0.01" min="0" value="0" class="form-input">
                    </div>
                </div>
            </div>

            {{-- Sorun Tanımı --}}
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <h3 class="font-semibold text-gray-700 mb-4"><i class="fas fa-comment-alt mr-2 text-indigo-500"></i>Sorun ve Cihaz Durumu</h3>
                <div class="space-y-4">
                    <div>
                        <label class="form-label">Sorun Açıklaması *</label>
                        <textarea name="problem_description" rows="4" required class="form-input" placeholder="Müşterinin bildirdiği arıza/bakım talebi...">{{ old('problem_description') }}</textarea>
                    </div>
                    <div>
                        <label class="form-label">Cihaz Teslim Alım Durumu</label>
                        <input type="text" name="device_condition_in" placeholder="Temiz, çizikli, kırık ekran..." class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Teslim Alınan Aksesuarlar</label>
                        <textarea name="accessories_received" rows="2" class="form-input" placeholder="Şarj aleti, kılıf, kulaklık..."></textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-5 flex gap-3">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save mr-2"></i>Servis Talebi Oluştur
            </button>
            <a href="{{ route('service.index') }}" class="btn btn-secondary">İptal</a>
        </div>
    </form>
</div>

@push('scripts')
<script>
function serviceForm() {
    return {
        isNewDevice: false,
        loadDevices(customerId) {
            // Seçilen müşterinin cihazlarını yükle
            if (!customerId) return;
            fetch('/customers/api/search?q=')
                .catch(() => {});
        }
    }
}
</script>
@endpush

@endsection
