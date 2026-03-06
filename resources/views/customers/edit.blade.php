@extends('layouts.app')
@section('title', 'Müşteri Düzenle')
@section('heading', 'Müşteri Düzenle')

@section('content')

<div class="max-w-3xl">
    @if($errors->any())
    <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-red-600 text-sm">
        @foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach
    </div>
    @endif

    <form method="POST" action="{{ route('customers.update', $customer) }}"
          x-data="{ type: '{{ old('type', $customer->type) }}' }">
        @csrf @method('PUT')

        <div class="bg-white rounded-xl border border-gray-200 p-5 mb-5">
            <h3 class="font-semibold text-gray-700 mb-4">Temel Bilgiler</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="form-label">Müşteri Türü *</label>
                    <div class="flex gap-4">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="type" value="individual" x-model="type">
                            <span class="text-sm">Bireysel</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="type" value="corporate" x-model="type">
                            <span class="text-sm">Kurumsal</span>
                        </label>
                    </div>
                </div>
                <div>
                    <label class="form-label">Ad *</label>
                    <input type="text" name="name" value="{{ old('name', $customer->name) }}" required class="form-input">
                </div>
                <div>
                    <label class="form-label">Soyad</label>
                    <input type="text" name="surname" value="{{ old('surname', $customer->surname) }}" class="form-input">
                </div>
                <div x-show="type === 'corporate'" x-cloak class="sm:col-span-2">
                    <label class="form-label">Firma Adı</label>
                    <input type="text" name="company_name" value="{{ old('company_name', $customer->company_name) }}" class="form-input">
                </div>
                <div>
                    <label class="form-label">Telefon</label>
                    <input type="tel" name="phone" value="{{ old('phone', $customer->phone) }}" class="form-input">
                </div>
                <div>
                    <label class="form-label">Telefon 2</label>
                    <input type="tel" name="phone2" value="{{ old('phone2', $customer->phone2) }}" class="form-input">
                </div>
                <div class="sm:col-span-2">
                    <label class="form-label">E-posta</label>
                    <input type="email" name="email" value="{{ old('email', $customer->email) }}" class="form-input">
                </div>
                <div class="sm:col-span-2">
                    <label class="form-label">Adres</label>
                    <textarea name="address" rows="2" class="form-input">{{ old('address', $customer->address) }}</textarea>
                </div>
                <div>
                    <label class="form-label">Şehir</label>
                    <input type="text" name="city" value="{{ old('city', $customer->city) }}" class="form-input">
                </div>
                <div>
                    <label class="form-label">Ülke</label>
                    <input type="text" name="country" value="{{ old('country', $customer->country) }}" maxlength="5" class="form-input">
                </div>
                <div>
                    <label class="form-label">Vergi No</label>
                    <input type="text" name="tax_number" value="{{ old('tax_number', $customer->tax_number) }}" class="form-input">
                </div>
                <div>
                    <label class="form-label">TC Kimlik No</label>
                    <input type="text" name="id_number" value="{{ old('id_number', $customer->id_number) }}" class="form-input">
                </div>
                <div class="sm:col-span-2">
                    <label class="form-label">Notlar</label>
                    <textarea name="notes" rows="2" class="form-input">{{ old('notes', $customer->notes) }}</textarea>
                </div>
            </div>
        </div>

        <div class="flex gap-3">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-2"></i>Kaydet</button>
            <a href="{{ route('customers.show', $customer) }}" class="btn btn-secondary">İptal</a>
        </div>
    </form>
</div>

@endsection
