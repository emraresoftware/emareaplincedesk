@extends('layouts.guest')
@section('title', 'Kayıt Ol')

@section('content')
<h2 class="text-gray-800 text-xl font-bold mb-6 text-center">Yeni Hesap Oluştur</h2>

@if($errors->any())
<div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-red-600 text-sm">
    @foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach
</div>
@endif

<form method="POST" action="{{ route('register') }}">
    @csrf
    <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-1">Ad Soyad</label>
        <input type="text" name="name" value="{{ old('name') }}" required
               class="block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none">
    </div>
    <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-1">E-posta</label>
        <input type="email" name="email" value="{{ old('email') }}" required
               class="block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none">
    </div>
    <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-1">Şifre</label>
        <input type="password" name="password" required
               class="block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none">
    </div>
    <div class="mb-5">
        <label class="block text-sm font-medium text-gray-700 mb-1">Şifre Tekrar</label>
        <input type="password" name="password_confirmation" required
               class="block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none">
    </div>
    <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2.5 rounded-lg transition-colors text-sm">
        <i class="fas fa-user-plus mr-2"></i>Kayıt Ol
    </button>
</form>

<p class="text-center text-sm text-gray-500 mt-4">
    Hesabınız var mı?
    <a href="{{ route('login') }}" class="text-indigo-600 hover:underline font-medium">Giriş Yap</a>
</p>
@endsection
