<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" class="h-full bg-gray-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — Emare PL İnce Desk</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3/dist/cdn.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }
        .sidebar-link { @apply flex items-center px-3 py-2 rounded-lg text-sm font-medium transition-colors duration-150; }
        .sidebar-link.active { @apply bg-gray-800 text-white; }
        .sidebar-link:not(.active) { @apply text-gray-300 hover:bg-gray-800 hover:text-white; }
        .badge { @apply inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium; }
        .btn { @apply inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium transition-colors; }
        .btn-primary { @apply bg-indigo-600 text-white hover:bg-indigo-700; }
        .btn-success { @apply bg-green-600 text-white hover:bg-green-700; }
        .btn-danger  { @apply bg-red-600 text-white hover:bg-red-700; }
        .btn-secondary { @apply bg-gray-200 text-gray-700 hover:bg-gray-300; }
        .card { @apply bg-white rounded-xl shadow-sm border border-gray-200 p-5; }
        .form-input { @apply block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border; }
        .form-label { @apply block text-sm font-medium text-gray-700 mb-1; }
    </style>

    @stack('styles')
</head>
<body class="h-full font-sans antialiased">

<div class="min-h-full flex" x-data="{ sidebarOpen: true, mobileOpen: false }">

    {{-- Mobile overlay --}}
    <div x-show="mobileOpen" x-cloak @click="mobileOpen=false" class="fixed inset-0 z-40 bg-black/50 lg:hidden"></div>

    {{-- Sidebar --}}
    <aside :class="sidebarOpen ? 'w-64' : 'w-16'"
           class="hidden lg:flex fixed inset-y-0 left-0 z-50 bg-gray-900 flex-col transition-all duration-300 overflow-hidden">

        {{-- Logo --}}
        <div class="flex items-center justify-between h-16 px-4 bg-gray-800 flex-shrink-0">
            <a href="{{ route('dashboard') }}" class="flex items-center space-x-2 min-w-0">
                <div class="w-8 h-8 bg-indigo-600 rounded-lg flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-tools text-white text-sm"></i>
                </div>
                <span x-show="sidebarOpen" x-cloak class="text-white font-bold text-sm truncate">PL İnce Desk</span>
            </a>
            <button @click="sidebarOpen = !sidebarOpen" class="text-gray-400 hover:text-white ml-2 flex-shrink-0">
                <i class="fas fa-bars text-sm"></i>
            </button>
        </div>

        {{-- Menü --}}
        <nav class="flex-1 px-2 py-4 space-y-1 overflow-y-auto overflow-x-hidden">

            @php
            $menu = [
                ['route' => 'dashboard',           'icon' => 'fa-chart-line',    'label' => 'Dashboard',       'match' => 'dashboard'],
                ['route' => 'service.index',        'icon' => 'fa-screwdriver-wrench', 'label' => 'Servis Talepleri', 'match' => 'service.*'],
                ['route' => 'customers.index',      'icon' => 'fa-users',         'label' => 'Müşteriler',      'match' => 'customers.*'],
                ['route' => 'technicians.index',    'icon' => 'fa-user-gear',     'label' => 'Teknisyenler',    'match' => 'technicians.*'],
                ['route' => 'spare-parts.index',    'icon' => 'fa-box-open',      'label' => 'Stok / Parçalar', 'match' => 'spare-parts.*'],
                ['route' => 'invoices.index',       'icon' => 'fa-file-invoice',  'label' => 'Faturalar',       'match' => 'invoices.*'],
                ['route' => 'reports.index',        'icon' => 'fa-chart-bar',     'label' => 'Raporlar',        'match' => 'reports.*'],
            ];
            @endphp

            @foreach($menu as $item)
            <a href="{{ route($item['route']) }}"
               class="sidebar-link {{ request()->routeIs($item['match']) ? 'active' : '' }}">
                <i class="fas {{ $item['icon'] }} w-5 text-center flex-shrink-0 text-sm"></i>
                <span x-show="sidebarOpen" x-cloak class="ml-3 truncate">{{ $item['label'] }}</span>
            </a>
            @endforeach
        </nav>

        {{-- Kullanıcı --}}
        <div x-show="sidebarOpen" x-cloak class="px-3 py-3 border-t border-gray-700">
            <div class="flex items-center space-x-2">
                <div class="w-7 h-7 bg-indigo-500 rounded-full flex items-center justify-center flex-shrink-0">
                    <span class="text-white text-xs font-bold">{{ substr(Auth::user()->name,0,1) }}</span>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-white text-xs font-medium truncate">{{ Auth::user()->name }}</p>
                    <p class="text-gray-400 text-xs truncate">{{ Auth::user()->role }}</p>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-gray-400 hover:text-red-400" title="Çıkış">
                        <i class="fas fa-sign-out-alt text-sm"></i>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    {{-- Ana içerik --}}
    <div :class="sidebarOpen ? 'lg:pl-64' : 'lg:pl-16'" class="flex-1 flex flex-col min-h-screen transition-all duration-300">

        {{-- Üst bar --}}
        <header class="sticky top-0 z-30 bg-white border-b border-gray-200 h-14 flex items-center px-4 sm:px-6 gap-4">
            <button class="lg:hidden text-gray-500 hover:text-gray-700" @click="mobileOpen=true">
                <i class="fas fa-bars"></i>
            </button>
            <div class="flex-1">
                <h1 class="text-gray-800 font-semibold text-sm sm:text-base">@yield('heading', 'Dashboard')</h1>
            </div>
            <div class="flex items-center gap-3">
                @yield('header_actions')
                <a href="{{ route('service.create') }}" class="btn btn-primary text-xs sm:text-sm">
                    <i class="fas fa-plus mr-1.5"></i>
                    <span class="hidden sm:inline">Yeni Servis</span>
                </a>
            </div>
        </header>

        {{-- Flash Mesajları --}}
        @if(session('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show=false, 4000)"
             class="mx-4 mt-4 p-3 bg-green-50 border border-green-200 rounded-lg flex items-center justify-between text-green-700 text-sm">
            <span><i class="fas fa-check-circle mr-2"></i>{{ session('success') }}</span>
            <button @click="show=false"><i class="fas fa-times"></i></button>
        </div>
        @endif
        @if(session('error'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show=false, 5000)"
             class="mx-4 mt-4 p-3 bg-red-50 border border-red-200 rounded-lg flex items-center justify-between text-red-700 text-sm">
            <span><i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}</span>
            <button @click="show=false"><i class="fas fa-times"></i></button>
        </div>
        @endif

        {{-- Sayfa İçeriği --}}
        <main class="flex-1 p-4 sm:p-6 max-w-screen-2xl mx-auto w-full">
            @yield('content')
        </main>

        <footer class="text-center text-gray-400 text-xs py-3 border-t border-gray-100">
            Emare PL İnce Desk &copy; {{ date('Y') }} — Global Teknik Servis Yönetimi
        </footer>
    </div>
</div>

@stack('scripts')
</body>
</html>
