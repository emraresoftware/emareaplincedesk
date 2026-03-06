@extends('layouts.app')
@section('title', 'Stok / Parçalar')
@section('heading', 'Yedek Parça & Stok')

@section('content')

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Parça Listesi --}}
    <div class="lg:col-span-2 space-y-4">

        {{-- Filtre --}}
        <form method="GET" class="bg-white rounded-xl border border-gray-200 p-4 flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-40">
                <label class="form-label">Ara</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Parça adı, kodu, marka..." class="form-input">
            </div>
            <div>
                <label class="flex items-center gap-2 mt-5 cursor-pointer">
                    <input type="checkbox" name="low_stock" value="1" @checked(request('low_stock'))>
                    <span class="text-sm text-gray-700">Sadece Az Stok</span>
                </label>
            </div>
            <button type="submit" class="btn btn-primary">Filtrele</button>
            <a href="{{ route('spare-parts.index') }}" class="btn btn-secondary">Temizle</a>
        </form>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-700">{{ $parts->total() }} Parça</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-gray-400 text-xs">
                        <tr>
                            <th class="px-4 py-3 text-left">Kod</th>
                            <th class="px-4 py-3 text-left">Parça Adı</th>
                            <th class="px-4 py-3 text-left">Marka</th>
                            <th class="px-4 py-3 text-center">Stok</th>
                            <th class="px-4 py-3 text-right">Alış</th>
                            <th class="px-4 py-3 text-right">Satış</th>
                            <th class="px-4 py-3 text-right">İşlem</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($parts as $part)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-gray-400 text-xs">{{ $part->code }}</td>
                            <td class="px-4 py-3">
                                <p class="font-medium text-gray-800">{{ $part->name }}</p>
                                @if($part->model_compatibility)
                                <p class="text-xs text-gray-400">{{ $part->model_compatibility }}</p>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-gray-500 text-xs">{{ $part->brand ?? '—' }}</td>
                            <td class="px-4 py-3 text-center">
                                <span class="{{ $part->is_low_stock ? 'text-red-600 font-bold' : 'text-gray-700' }}">
                                    {{ $part->quantity }}
                                </span>
                                @if($part->is_low_stock)
                                <span class="badge bg-red-100 text-red-600 ml-1 text-xs">Az!</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right text-gray-600">{{ number_format($part->purchase_price, 2) }} ₺</td>
                            <td class="px-4 py-3 text-right font-medium text-gray-800">{{ number_format($part->sale_price, 2) }} ₺</td>
                            <td class="px-4 py-3 text-right" x-data="{ addStock: false }">
                                <button @click="addStock = !addStock" class="text-green-600 hover:text-green-800 text-xs mr-2" title="Stok Ekle">
                                    <i class="fas fa-plus-circle"></i>
                                </button>
                                <form method="POST" action="{{ route('spare-parts.destroy', $part) }}" class="inline" onsubmit="return confirm('Silinsin mi?')">
                                    @csrf @method('DELETE')
                                    <button class="text-red-500 hover:text-red-700 text-xs"><i class="fas fa-trash"></i></button>
                                </form>
                                {{-- Stok Ekle Satırı --}}
                                <template x-if="addStock">
                                <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/30" @click.self="addStock=false">
                                    <div class="bg-white rounded-xl p-5 w-80 shadow-xl">
                                        <h4 class="font-semibold text-gray-700 mb-3">Stok Ekle: {{ $part->name }}</h4>
                                        <form method="POST" action="{{ route('spare-parts.stock', $part) }}">
                                            @csrf
                                            <div class="mb-3">
                                                <label class="form-label">Adet *</label>
                                                <input type="number" name="quantity" required min="1" class="form-input">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Alış Fiyatı (₺)</label>
                                                <input type="number" name="purchase_price" step="0.01" value="{{ $part->purchase_price }}" class="form-input">
                                            </div>
                                            <div class="mb-4">
                                                <label class="form-label">Not</label>
                                                <input type="text" name="notes" class="form-input">
                                            </div>
                                            <div class="flex gap-2">
                                                <button type="submit" class="btn btn-success flex-1 justify-center">Ekle</button>
                                                <button type="button" @click="addStock=false" class="btn btn-secondary">İptal</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                </template>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="px-4 py-8 text-center text-gray-400">Parça bulunamadı.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-5 py-4 border-t border-gray-100">
                {{ $parts->links() }}
            </div>
        </div>
    </div>

    {{-- Yeni Parça --}}
    <div>
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h3 class="font-semibold text-gray-700 mb-4"><i class="fas fa-plus mr-2 text-indigo-500"></i>Yeni Parça Ekle</h3>

            <form method="POST" action="{{ route('spare-parts.store') }}" class="space-y-3">
                @csrf
                <div>
                    <label class="form-label">Parça Adı *</label>
                    <input type="text" name="name" required class="form-input">
                </div>
                <div>
                    <label class="form-label">Marka</label>
                    <input type="text" name="brand" class="form-input">
                </div>
                <div>
                    <label class="form-label">Cihaz Uyumluluğu</label>
                    <input type="text" name="model_compatibility" placeholder="iPhone 13, Galaxy S..." class="form-input">
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="form-label">Başlangıç Stok</label>
                        <input type="number" name="quantity" value="0" min="0" class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Min Stok</label>
                        <input type="number" name="min_quantity" value="5" min="0" class="form-input">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="form-label">Alış (₺) *</label>
                        <input type="number" name="purchase_price" step="0.01" value="0" required class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Satış (₺) *</label>
                        <input type="number" name="sale_price" step="0.01" value="0" required class="form-input">
                    </div>
                </div>
                <div>
                    <label class="form-label">Raf / Konum</label>
                    <input type="text" name="location" placeholder="A-01, B-12..." class="form-input">
                </div>
                <div>
                    <label class="form-label">Tedarikçi</label>
                    <input type="text" name="supplier" class="form-input">
                </div>
                <button type="submit" class="btn btn-primary w-full justify-center">
                    <i class="fas fa-plus mr-2"></i>Parça Ekle
                </button>
            </form>
        </div>
    </div>
</div>

@endsection
