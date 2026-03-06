@extends('layouts.app')
@section('title', 'Müşteriler')
@section('heading', 'Müşteriler')

@section('content')

<form method="GET" class="bg-white rounded-xl border border-gray-200 p-4 mb-5 flex flex-wrap gap-3 items-end">
    <div class="flex-1 min-w-44">
        <label class="form-label">Ara</label>
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Ad, telefon, e-posta..."
               class="form-input">
    </div>
    <div class="w-36">
        <label class="form-label">Tür</label>
        <select name="type" class="form-input">
            <option value="">Tümü</option>
            <option value="individual" @selected(request('type')==='individual')>Bireysel</option>
            <option value="corporate"  @selected(request('type')==='corporate')>Kurumsal</option>
        </select>
    </div>
    <button type="submit" class="btn btn-primary">
        <i class="fas fa-search mr-2"></i>Filtrele
    </button>
    <a href="{{ route('customers.index') }}" class="btn btn-secondary">Temizle</a>
</form>

<div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
    <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
        <h3 class="font-semibold text-gray-700">{{ $customers->total() }} Müşteri</h3>
        <a href="{{ route('customers.create') }}" class="btn btn-primary text-sm">
            <i class="fas fa-user-plus mr-2"></i>Yeni Müşteri
        </a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-500 text-xs">
                <tr>
                    <th class="px-4 py-3 text-left">Kodu</th>
                    <th class="px-4 py-3 text-left">Ad / Firma</th>
                    <th class="px-4 py-3 text-left">Telefon</th>
                    <th class="px-4 py-3 text-left">E-posta</th>
                    <th class="px-4 py-3 text-center">Cihazlar</th>
                    <th class="px-4 py-3 text-center">Servisler</th>
                    <th class="px-4 py-3 text-right">İşlem</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($customers as $c)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-gray-400 text-xs">{{ $c->code }}</td>
                    <td class="px-4 py-3">
                        <a href="{{ route('customers.show', $c) }}" class="text-indigo-600 hover:underline font-medium">{{ $c->full_name }}</a>
                        <span class="badge ml-2 {{ $c->type === 'corporate' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700' }} text-xs">
                            {{ $c->type === 'corporate' ? 'Kurumsal' : 'Bireysel' }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-gray-600">{{ $c->phone ?? '—' }}</td>
                    <td class="px-4 py-3 text-gray-400 text-xs">{{ $c->email ?? '—' }}</td>
                    <td class="px-4 py-3 text-center text-gray-600">{{ $c->devices_count }}</td>
                    <td class="px-4 py-3 text-center text-gray-600">{{ $c->service_requests_count }}</td>
                    <td class="px-4 py-3 text-right flex justify-end gap-2">
                        <a href="{{ route('customers.show', $c) }}" class="text-indigo-600 hover:text-indigo-800 text-xs">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="{{ route('customers.edit', $c) }}" class="text-yellow-600 hover:text-yellow-800 text-xs">
                            <i class="fas fa-edit"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-4 py-8 text-center text-gray-400">Müşteri bulunamadı.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-5 py-4 border-t border-gray-100">
        {{ $customers->links() }}
    </div>
</div>

@endsection
