@extends('layouts.app')
@section('title', 'Finansal Rapor')
@section('heading', 'Finansal Rapor')

@section('content')

{{-- Filtreler --}}
<form method="GET" class="bg-white rounded-xl border border-gray-200 p-4 mb-5 flex flex-wrap gap-3">
    <div>
        <label class="form-label text-xs">Başlangıç</label>
        <input type="date" name="from" value="{{ $from }}" class="form-input text-sm">
    </div>
    <div>
        <label class="form-label text-xs">Bitiş</label>
        <input type="date" name="to" value="{{ $to }}" class="form-input text-sm">
    </div>
    <div class="self-end">
        <button type="submit" class="btn btn-primary text-sm">
            <i class="fas fa-filter mr-1"></i>Uygula
        </button>
    </div>
</form>

{{-- KPI kartları --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-5">
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <p class="text-xs text-gray-400 mb-1">Toplam Gelir</p>
        <p class="text-2xl font-bold text-green-600">₺ {{ number_format($totalRevenue, 0, ',', '.') }}</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <p class="text-xs text-gray-400 mb-1">Ödenen</p>
        <p class="text-2xl font-bold text-indigo-600">₺ {{ number_format($totalPaid, 0, ',', '.') }}</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <p class="text-xs text-gray-400 mb-1">Bekleyen Tahsilat</p>
        <p class="text-2xl font-bold text-amber-500">₺ {{ number_format($totalRevenue - $totalPaid, 0, ',', '.') }}</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <p class="text-xs text-gray-400 mb-1">Fatura Adedi</p>
        <p class="text-2xl font-bold text-gray-700">{{ $invoiceCount }}</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-5">

    {{-- Aylık gelir trendi --}}
    <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200 p-5">
        <h3 class="font-semibold text-gray-700 mb-4">Aylık Gelir</h3>
        <canvas id="monthlyChart" height="100"></canvas>
    </div>

    {{-- Ödeme yöntemleri --}}
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <h3 class="font-semibold text-gray-700 mb-4">Ödeme Yöntemi</h3>
        <canvas id="methodChart" height="180"></canvas>
        <div class="mt-4 space-y-1">
            @foreach($byMethod as $row)
            <div class="flex justify-between text-sm">
                <span class="text-gray-600">{{ ['cash'=>'Nakit','credit_card'=>'Kredi Kartı','bank_transfer'=>'Havale','check'=>'Çek','other'=>'Diğer'][$row->method] ?? $row->method }}</span>
                <span class="font-semibold text-gray-800">₺ {{ number_format($row->total, 2, ',', '.') }}</span>
            </div>
            @endforeach
        </div>
    </div>
</div>

@push('scripts')
<script>
new Chart(document.getElementById('monthlyChart'), {
    type: 'bar',
    data: {
        labels: {!! json_encode($monthlyRevenue->pluck('month')) !!},
        datasets: [
            { label: 'Gelir', data: {!! json_encode($monthlyRevenue->pluck('revenue')) !!}, backgroundColor: 'rgba(99,102,241,0.7)', borderRadius: 5 },
            { label: 'Tahsilat', data: {!! json_encode($monthlyRevenue->pluck('paid')) !!}, backgroundColor: 'rgba(34,197,94,0.5)', borderRadius: 5 }
        ]
    },
    options: { scales: { y: { beginAtZero: true } }, plugins: { legend: { position: 'top' } } }
});
new Chart(document.getElementById('methodChart'), {
    type: 'pie',
    data: {
        labels: {!! json_encode($byMethod->pluck('method')) !!},
        datasets: [{ data: {!! json_encode($byMethod->pluck('total')) !!}, backgroundColor: ['#6366f1','#22c55e','#f59e0b','#ef4444','#3b82f6'] }]
    },
    options: { plugins: { legend: { display: false } } }
});
</script>
@endpush

@endsection
