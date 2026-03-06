@extends('layouts.guest')
@section('title', 'Giriş Yap')

@section('content')
<h2 class="text-gray-800 text-xl font-bold mb-6 text-center">Hesabınıza Giriş Yapın</h2>

@if($errors->any())
<div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-red-600 text-sm">
    @foreach($errors->all() as $error)
        <p><i class="fas fa-exclamation-circle mr-1"></i>{{ $error }}</p>
    @endforeach
</div>
@endif

<form method="POST" action="{{ route('login') }}">
    @csrf
    <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-1">E-posta</label>
        <input type="email" name="email" value="{{ old('email') }}" required autofocus
               class="block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none">
    </div>
    <div class="mb-5">
        <label class="block text-sm font-medium text-gray-700 mb-1">Şifre</label>
        <input type="password" name="password" required
               class="block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none">
    </div>
    <button type="submit"
            class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2.5 rounded-lg transition-colors text-sm">
        <i class="fas fa-sign-in-alt mr-2"></i>Giriş Yap
    </button>
</form>

<p class="text-center text-sm text-gray-500 mt-4">
    Hesabınız yok mu?
    <a href="{{ route('register') }}" class="text-indigo-600 hover:underline font-medium">Kayıt Ol</a>
</p>
@endsection
