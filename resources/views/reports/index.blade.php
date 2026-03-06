@extends('layouts.app')
@section('title', 'Raporlar')
@section('heading', 'Raporlar ve Analizler')

@section('content')

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">

    <a href="{{ route('reports.services') }}" class="block bg-white rounded-xl border border-gray-200 p-6 hover:shadow-md hover:border-indigo-200 transition group">
        <div class="flex items-center gap-4 mb-4">
            <div class="w-12 h-12 rounded-xl bg-indigo-50 flex items-center justify-center group-hover:bg-indigo-100 transition">
                <i class="fas fa-wrench text-indigo-600 text-xl"></i>
            </div>
            <div>
                <h3 class="font-semibold text-gray-800">Servis Raporu</h3>
                <p class="text-xs text-gray-400">Durum, tür, teknisyen</p>
            </div>
        </div>
        <p class="text-sm text-gray-500">Belirli tarih aralıklarında servis talebi istatistikleri, durum dağılımı, tür dağılımı ve teknisyen performansı.</p>
        <div class="mt-4 text-xs text-indigo-600 font-medium">Raporu Görüntüle →</div>
    </a>

    <a href="{{ route('reports.financial') }}" class="block bg-white rounded-xl border border-gray-200 p-6 hover:shadow-md hover:border-green-200 transition group">
        <div class="flex items-center gap-4 mb-4">
            <div class="w-12 h-12 rounded-xl bg-green-50 flex items-center justify-center group-hover:bg-green-100 transition">
                <i class="fas fa-chart-line text-green-600 text-xl"></i>
            </div>
            <div>
                <h3 class="font-semibold text-gray-800">Finansal Rapor</h3>
                <p class="text-xs text-gray-400">Gelir, ödeme yöntemleri</p>
            </div>
        </div>
        <p class="text-sm text-gray-500">Aylık gelir grafikleri, ödeme yöntemlerine göre dağılım, tahsilat oranları ve geciken ödemeler.</p>
        <div class="mt-4 text-xs text-green-600 font-medium">Raporu Görüntüle →</div>
    </a>

    <a href="{{ route('reports.inventory') }}" class="block bg-white rounded-xl border border-gray-200 p-6 hover:shadow-md hover:border-amber-200 transition group">
        <div class="flex items-center gap-4 mb-4">
            <div class="w-12 h-12 rounded-xl bg-amber-50 flex items-center justify-center group-hover:bg-amber-100 transition">
                <i class="fas fa-boxes text-amber-600 text-xl"></i>
            </div>
            <div>
                <h3 class="font-semibold text-gray-800">Stok Raporu</h3>
                <p class="text-xs text-gray-400">Parçalar, kritik stok</p>
            </div>
        </div>
        <p class="text-sm text-gray-500">Düşük stok uyarıları, en çok kullanılan parçalar, stok değeri ve hareket geçmişi.</p>
        <div class="mt-4 text-xs text-amber-600 font-medium">Raporu Görüntüle →</div>
    </a>

    <div class="bg-gradient-to-br from-indigo-50 to-purple-50 rounded-xl border border-indigo-100 p-6">
        <div class="flex items-center gap-4 mb-4">
            <div class="w-12 h-12 rounded-xl bg-white flex items-center justify-center shadow-sm">
                <i class="fas fa-calendar-alt text-purple-500 text-xl"></i>
            </div>
            <div>
                <h3 class="font-semibold text-gray-800">Hızlı Özet</h3>
                <p class="text-xs text-gray-400">Bu ay</p>
            </div>
        </div>
        <div class="space-y-2">
            <div class="flex justify-between text-sm">
                <span class="text-gray-500">Açık Servisler</span>
                <span class="font-bold text-indigo-700">{{ $openCount }}</span>
            </div>
            <div class="flex justify-between text-sm">
                <span class="text-gray-500">Bu Ay Teslim</span>
                <span class="font-bold text-green-700">{{ $deliveredCount }}</span>
            </div>
            <div class="flex justify-between text-sm">
                <span class="text-gray-500">Bu Ay Gelir</span>
                <span class="font-bold text-purple-700">₺ {{ number_format($monthlyRevenue, 2) }}</span>
            </div>
            <div class="flex justify-between text-sm">
                <span class="text-gray-500">Kritik Stok</span>
                <span class="font-bold text-red-600">{{ $lowStockCount }}</span>
            </div>
        </div>
    </div>

</div>

@endsection
