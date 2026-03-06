@extends('layouts.app')
@section('title', 'Stok Raporu')
@section('heading', 'Stok Raporu')

@section('content')

{{-- KPI --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-5">
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <p class="text-xs text-gray-400 mb-1">Toplam Parça</p>
        <p class="text-2xl font-bold text-gray-800">{{ $totalParts }}</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <p class="text-xs text-gray-400 mb-1">Kritik Stok</p>
        <p class="text-2xl font-bold text-red-500">{{ $lowStockCount }}</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <p class="text-xs text-gray-400 mb-1">Stok Değeri (Maliyet)</p>
        <p class="text-2xl font-bold text-indigo-600">₺ {{ number_format($stockValue, 0, ',', '.') }}</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <p class="text-xs text-gray-400 mb-1">Stok Değeri (Satış)</p>
        <p class="text-2xl font-bold text-green-600">₺ {{ number_format($stockSaleValue, 0, ',', '.') }}</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-5">

    {{-- En çok kullanılan --}}
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <h3 class="font-semibold text-gray-700 mb-4">En Çok Kullanılan Parçalar</h3>
        <canvas id="topUsedChart" height="180"></canvas>
    </div>

    {{-- Kritik stok listesi --}}
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <h3 class="font-semibold text-gray-700 mb-4">
            Kritik Stok Uyarıları
            @if($lowStockParts->isNotEmpty())
            <span class="ml-2 badge bg-red-100 text-red-600">{{ $lowStockParts->count() }}</span>
            @endif
        </h3>
        @if($lowStockParts->isEmpty())
        <p class="text-sm text-gray-400 py-4 text-center"><i class="fas fa-check-circle text-green-400 mr-1"></i>Kritik stok yok</p>
        @else
        <div class="space-y-2 max-h-72 overflow-y-auto">
            @foreach($lowStockParts as $part)
            <div class="flex items-center justify-between p-2 rounded-lg bg-red-50 border border-red-100">
                <div>
                    <p class="text-sm font-medium text-gray-800">{{ $part->name }}</p>
                    <p class="text-xs text-gray-400">{{ $part->sku }}</p>
                </div>
                <div class="text-right">
                    <p class="text-sm font-bold text-red-600">{{ $part->stock_quantity }} adet</p>
                    <p class="text-xs text-gray-400">Min: {{ $part->min_stock }} adet</p>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</div>

{{-- Tüm parça listesi --}}
<div class="bg-white rounded-xl border border-gray-200 p-5">
    <h3 class="font-semibold text-gray-700 mb-4">Stok Listesi</h3>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="text-xs text-gray-400 border-b">
                <tr>
                    <th class="pb-2 text-left">Parça Adı</th>
                    <th class="pb-2 text-left">SKU</th>
                    <th class="pb-2 text-right">Stok</th>
                    <th class="pb-2 text-right">Min Stok</th>
                    <th class="pb-2 text-right">Maliyet</th>
                    <th class="pb-2 text-right">Satış Fiyatı</th>
                    <th class="pb-2 text-center">Durum</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($allParts as $part)
                <tr>
                    <td class="py-2 font-medium text-gray-800">{{ $part->name }}</td>
                    <td class="py-2 text-gray-400">{{ $part->sku }}</td>
                    <td class="py-2 text-right">{{ $part->stock_quantity }}</td>
                    <td class="py-2 text-right text-gray-400">{{ $part->min_stock }}</td>
                    <td class="py-2 text-right text-gray-600">₺{{ number_format($part->cost_price, 2) }}</td>
                    <td class="py-2 text-right text-gray-600">₺{{ number_format($part->sale_price, 2) }}</td>
                    <td class="py-2 text-center">
                        @if($part->is_low_stock)
                        <span class="badge bg-red-100 text-red-600">Kritik</span>
                        @else
                        <span class="badge bg-green-100 text-green-600">Normal</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@push('scripts')
<script>
new Chart(document.getElementById('topUsedChart'), {
    type: 'bar',
    data: {
        labels: {!! json_encode($topUsed->pluck('name')) !!},
        datasets: [{
            label: 'Kullanım Adedi',
            data: {!! json_encode($topUsed->pluck('used_qty')) !!},
            backgroundColor: 'rgba(99,102,241,0.7)',
            borderRadius: 5
        }]
    },
    options: {
        indexAxis: 'y',
        scales: { x: { beginAtZero: true } },
        plugins: { legend: { display: false } }
    }
});
</script>
@endpush

@endsection
