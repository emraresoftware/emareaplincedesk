@extends('layouts.app')
@section('title', 'Teknisyenler')
@section('heading', 'Teknisyen Yönetimi')

@section('content')

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Teknisyen Listesi --}}
    <div class="lg:col-span-2">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-700">{{ $technicians->count() }} Teknisyen</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-gray-400 text-xs">
                        <tr>
                            <th class="px-4 py-3 text-left">İsim</th>
                            <th class="px-4 py-3 text-left">Uzmanlık</th>
                            <th class="px-4 py-3 text-left">Telefon</th>
                            <th class="px-4 py-3 text-center">Aktif İş</th>
                            <th class="px-4 py-3 text-center">Toplam İş</th>
                            <th class="px-4 py-3 text-center">Durum</th>
                            <th class="px-4 py-3 text-right">İşlem</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($technicians as $tech)
                        <tr class="hover:bg-gray-50" x-data="{ edit: false }">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <div class="w-7 h-7 bg-indigo-100 text-indigo-600 rounded-full flex items-center justify-center text-xs font-bold">
                                        {{ substr($tech->name, 0, 1) }}
                                    </div>
                                    <span class="font-medium text-gray-800">{{ $tech->name }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-gray-500 text-xs">{{ $tech->speciality ?? '—' }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $tech->phone ?? '—' }}</td>
                            <td class="px-4 py-3 text-center font-semibold text-indigo-600">{{ $tech->active_jobs }}</td>
                            <td class="px-4 py-3 text-center text-gray-500">{{ $tech->total_jobs }}</td>
                            <td class="px-4 py-3 text-center">
                                <span class="badge {{ $tech->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                    {{ $tech->is_active ? 'Aktif' : 'Pasif' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right flex justify-end gap-2">
                                <button @click="edit = !edit" class="text-yellow-600 hover:text-yellow-800 text-xs">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form method="POST" action="{{ route('technicians.destroy', $tech) }}" onsubmit="return confirm('Silinsin mi?')">
                                    @csrf @method('DELETE')
                                    <button class="text-red-500 hover:text-red-700 text-xs"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        {{-- Düzenleme Satırı --}}
                        <tr x-show="edit" x-cloak class="bg-indigo-50">
                            <td colspan="7" class="px-4 py-3">
                                <form method="POST" action="{{ route('technicians.update', $tech) }}" class="flex flex-wrap gap-3 items-end">
                                    @csrf @method('PUT')
                                    <div>
                                        <label class="form-label text-xs">İsim</label>
                                        <input type="text" name="name" value="{{ $tech->name }}" required class="form-input text-sm">
                                    </div>
                                    <div>
                                        <label class="form-label text-xs">Telefon</label>
                                        <input type="text" name="phone" value="{{ $tech->phone }}" class="form-input text-sm">
                                    </div>
                                    <div>
                                        <label class="form-label text-xs">Uzmanlık</label>
                                        <input type="text" name="speciality" value="{{ $tech->speciality }}" class="form-input text-sm">
                                    </div>
                                    <div>
                                        <label class="form-label text-xs">Saatlik Ücret</label>
                                        <input type="number" name="hourly_rate" value="{{ $tech->hourly_rate }}" step="0.01" class="form-input text-sm w-28">
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <input type="checkbox" name="is_active" value="1" @checked($tech->is_active)>
                                        <span class="text-xs text-gray-600">Aktif</span>
                                    </div>
                                    <button type="submit" class="btn btn-primary text-sm">Kaydet</button>
                                    <button type="button" @click="edit=false" class="btn btn-secondary text-sm">İptal</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="px-4 py-8 text-center text-gray-400">Teknisyen bulunamadı.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Yeni Teknisyen Ekle --}}
    <div>
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h3 class="font-semibold text-gray-700 mb-4"><i class="fas fa-user-plus mr-2 text-indigo-500"></i>Yeni Teknisyen</h3>

            @if($errors->any())
            <div class="mb-3 p-2 bg-red-50 border border-red-200 rounded text-red-600 text-xs">
                @foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach
            </div>
            @endif

            <form method="POST" action="{{ route('technicians.store') }}" class="space-y-3">
                @csrf
                <div>
                    <label class="form-label">İsim *</label>
                    <input type="text" name="name" required class="form-input">
                </div>
                <div>
                    <label class="form-label">Telefon</label>
                    <input type="text" name="phone" class="form-input">
                </div>
                <div>
                    <label class="form-label">E-posta</label>
                    <input type="email" name="email" class="form-input">
                </div>
                <div>
                    <label class="form-label">Uzmanlık Alanı</label>
                    <input type="text" name="speciality" placeholder="Yazılım, Donanım, Ağ..." class="form-input">
                </div>
                <div>
                    <label class="form-label">Saatlik Ücret (₺)</label>
                    <input type="number" name="hourly_rate" step="0.01" min="0" value="0" class="form-input">
                </div>
                <button type="submit" class="btn btn-primary w-full justify-center">
                    <i class="fas fa-plus mr-2"></i>Teknisyen Ekle
                </button>
            </form>
        </div>
    </div>
</div>

@endsection
