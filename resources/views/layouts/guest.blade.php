<!DOCTYPE html>
<html lang="tr" class="h-full bg-gray-900">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Giriş') — Emare PL İnce Desk</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="h-full flex items-center justify-center bg-gradient-to-br from-gray-900 via-gray-800 to-indigo-900">

<div class="w-full max-w-md px-4">
    {{-- Logo --}}
    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-indigo-600 rounded-2xl mb-4 shadow-lg">
            <i class="fas fa-tools text-white text-2xl"></i>
        </div>
        <h1 class="text-white text-2xl font-bold">Emare PL İnce Desk</h1>
        <p class="text-gray-400 text-sm mt-1">Global Teknik Servis Yönetimi</p>
    </div>

    {{-- Form Kutusu --}}
    <div class="bg-white rounded-2xl shadow-2xl p-8">
        @yield('content')
    </div>

    <p class="text-center text-gray-500 text-xs mt-6">
        &copy; {{ date('Y') }} Emare — Tüm Hakları Saklıdır
    </p>
</div>

</body>
</html>
