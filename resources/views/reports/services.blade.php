@extends('layouts.app')
@section('title', 'Servis Raporu')
@section('heading', 'Servis Raporu')

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
    <div class="self-end ml-auto">
        <span class="text-sm text-gray-500">{{ $from }} – {{ $to }} arasında <strong class="text-gray-800">{{ $totalCount }}</strong> servis</span>
    </div>
</form>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-5">

    {{-- Durum dağılımı --}}
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <h3 class="font-semibold text-gray-700 mb-4">Duruma Göre</h3>
        <canvas id="statusChart" height="220"></canvas>
        <div class="mt-4 space-y-1">
            @foreach($byStatus as $row)
            <div class="flex justify-between text-sm">
                <span class="text-gray-600">{{ ['pending'=>'Bekliyor','diagnosed'=>'Teşhis','in_progress'=>'Devam','waiting_part'=>'Parça Bekliyor','ready'=>'Hazır','delivered'=>'Teslim','cancelled'=>'İptal'][$row->status] ?? $row->status }}</span>
                <span class="font-semibold text-gray-800">{{ $row->count }}</span>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Tür dağılımı --}}
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <h3 class="font-semibold text-gray-700 mb-4">Türe Göre</h3>
        <canvas id="typeChart" height="220"></canvas>
        <div class="mt-4 space-y-1">
            @foreach($byType as $row)
            <div class="flex justify-between text-sm">
                <span class="text-gray-600">{{ ['repair'=>'Onarım','maintenance'=>'Bakım','installation'=>'Kurulum','inspection'=>'Muayene'][$row->type] ?? $row->type }}</span>
                <span class="font-semibold text-gray-800">{{ $row->count }}</span>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Öncelik dağılımı --}}
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <h3 class="font-semibold text-gray-700 mb-4">Önceliğe Göre</h3>
        <canvas id="priorityChart" height="220"></canvas>
        <div class="mt-4 space-y-1">
            @foreach($byPriority as $row)
            <div class="flex justify-between text-sm">
                <span class="text-gray-600">{{ ['low'=>'Düşük','normal'=>'Normal','high'=>'Yüksek','urgent'=>'Acil'][$row->priority] ?? $row->priority }}</span>
                <span class="font-semibold text-gray-800">{{ $row->count }}</span>
            </div>
            @endforeach
        </div>
    </div>
</div>

{{-- Günlük trend --}}
<div class="bg-white rounded-xl border border-gray-200 p-5 mb-5">
    <h3 class="font-semibold text-gray-700 mb-4">Günlük Servis Trendi</h3>
    <canvas id="trendChart" height="80"></canvas>
</div>

{{-- Teknisyen performansı --}}
<div class="bg-white rounded-xl border border-gray-200 p-5">
    <h3 class="font-semibold text-gray-700 mb-4">Teknisyen Performansı</h3>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="text-xs text-gray-400 border-b">
                <tr>
                    <th class="pb-2 text-left">Teknisyen</th>
                    <th class="pb-2 text-right">Toplam</th>
                    <th class="pb-2 text-right">Teslim Edilen</th>
                    <th class="pb-2 text-right">Tamamlanma %</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($topTechnicians as $t)
                <tr>
                    <td class="py-2">{{ $t->technician_name ?? 'Atanmadı' }}</td>
                    <td class="py-2 text-right text-gray-600">{{ $t->total }}</td>
                    <td class="py-2 text-right text-green-600">{{ $t->delivered }}</td>
                    <td class="py-2 text-right">
                        @php $pct = $t->total > 0 ? round($t->delivered / $t->total * 100) : 0; @endphp
                        <div class="flex items-center justify-end gap-2">
                            <div class="w-20 h-1.5 bg-gray-100 rounded-full overflow-hidden">
                                <div class="h-full bg-indigo-500 rounded-full" style="width:{{ $pct }}%"></div>
                            </div>
                            <span class="text-xs text-gray-500">{{ $pct }}%</span>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@push('scripts')
<script>
const COLORS = ['#6366f1','#22c55e','#f59e0b','#ef4444','#3b82f6','#a855f7','#14b8a6'];
const PIE_OPTS = { plugins: { legend: { display: false } } };

new Chart(document.getElementById('statusChart'), {
    type: 'doughnut',
    data: {
        labels: {!! json_encode($byStatus->pluck('status')) !!},
        datasets: [{ data: {!! json_encode($byStatus->pluck('count')) !!}, backgroundColor: COLORS }]
    },
    options: PIE_OPTS
});
new Chart(document.getElementById('typeChart'), {
    type: 'doughnut',
    data: {
        labels: {!! json_encode($byType->pluck('type')) !!},
        datasets: [{ data: {!! json_encode($byType->pluck('count')) !!}, backgroundColor: COLORS }]
    },
    options: PIE_OPTS
});
new Chart(document.getElementById('priorityChart'), {
    type: 'doughnut',
    data: {
        labels: {!! json_encode($byPriority->pluck('priority')) !!},
        datasets: [{ data: {!! json_encode($byPriority->pluck('count')) !!}, backgroundColor: ['#22c55e','#6366f1','#f59e0b','#ef4444'] }]
    },
    options: PIE_OPTS
});
new Chart(document.getElementById('trendChart'), {
    type: 'line',
    data: {
        labels: {!! json_encode($dailyTrend->pluck('day')) !!},
        datasets: [{
            label: 'Servis',
            data: {!! json_encode($dailyTrend->pluck('count')) !!},
            borderColor: '#6366f1', backgroundColor: 'rgba(99,102,241,0.1)',
            fill: true, tension: 0.4, pointRadius: 3
        }]
    },
    options: { scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }, plugins: { legend: { display: false } } }
});
</script>
@endpush

@endsection
