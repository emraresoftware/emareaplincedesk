@extends('layouts.app')
@section('title', 'Yeni Fatura')
@section('heading', 'Yeni Fatura')

@section('content')

<div class="max-w-4xl" x-data="invoiceForm()">

    @if($errors->any())
    <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-red-600 text-sm">
        @foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach
    </div>
    @endif

    <form method="POST" action="{{ route('invoices.store') }}">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-5">
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <h3 class="font-semibold text-gray-700 mb-4">Müşteri & Servis</h3>
                <div class="space-y-3">
                    <div>
                        <label class="form-label">Müşteri *</label>
                        <select name="customer_id" required class="form-input">
                            <option value="">— Seçin —</option>
                            @if($customer)
                            <option value="{{ $customer->id }}" selected>{{ $customer->full_name }}</option>
                            @endif
                        </select>
                    </div>
                    @if($service)
                    <input type="hidden" name="service_request_id" value="{{ $service->id }}">
                    <div class="p-3 bg-indigo-50 rounded-lg text-sm">
                        <p class="text-indigo-700 font-medium">Servis: {{ $service->ticket_no }}</p>
                        <p class="text-indigo-500 text-xs">{{ $service->device?->full_name }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <h3 class="font-semibold text-gray-700 mb-4">Fatura Ayarları</h3>
                <div class="space-y-3">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="form-label">Düzenleme Tarihi *</label>
                            <input type="date" name="issue_date" value="{{ date('Y-m-d') }}" required class="form-input">
                        </div>
                        <div>
                            <label class="form-label">Vade Tarihi</label>
                            <input type="date" name="due_date" class="form-input">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="form-label">KDV (%)</label>
                            <input type="number" name="tax_rate" value="20" min="0" max="100" step="0.1" class="form-input">
                        </div>
                        <div>
                            <label class="form-label">Para Birimi</label>
                            <select name="currency" class="form-input">
                                <option value="TRY">TRY (₺)</option>
                                <option value="USD">USD ($)</option>
                                <option value="EUR">EUR (€)</option>
                                <option value="GBP">GBP (£)</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="form-label">Not</label>
                        <textarea name="notes" rows="2" class="form-input"></textarea>
                    </div>
                </div>
            </div>
        </div>

        {{-- Kalemler --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5 mb-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-gray-700">Fatura Kalemleri</h3>
                <button type="button" @click="addItem()" class="btn btn-secondary text-sm">
                    <i class="fas fa-plus mr-1"></i>Kalem Ekle
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm" id="items-table">
                    <thead class="text-xs text-gray-400 border-b">
                        <tr>
                            <th class="pb-2 text-left">Açıklama *</th>
                            <th class="pb-2 text-center w-20">Tür</th>
                            <th class="pb-2 text-right w-20">Adet</th>
                            <th class="pb-2 text-right w-28">Birim Fiyat</th>
                            <th class="pb-2 text-right w-24">İndirim</th>
                            <th class="pb-2 text-right w-28">Toplam</th>
                            <th class="pb-2 w-8"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(item, index) in items" :key="index">
                            <tr class="border-b border-gray-50">
                                <td class="py-2 pr-2">
                                    <input type="text" :name="'items['+index+'][description]'" x-model="item.description" required class="form-input text-sm w-full">
                                </td>
                                <td class="py-2 px-1">
                                    <select :name="'items['+index+'][type]'" x-model="item.type" class="form-input text-xs">
                                        <option value="service">İşçilik</option>
                                        <option value="part">Parça</option>
                                        <option value="other">Diğer</option>
                                    </select>
                                </td>
                                <td class="py-2 px-1">
                                    <input type="number" :name="'items['+index+'][quantity]'" x-model.number="item.quantity" @input="calcRow(index)" min="0.01" step="0.01" required class="form-input text-sm text-right w-20">
                                </td>
                                <td class="py-2 px-1">
                                    <input type="number" :name="'items['+index+'][unit_price]'" x-model.number="item.unit_price" @input="calcRow(index)" min="0" step="0.01" required class="form-input text-sm text-right w-28">
                                </td>
                                <td class="py-2 px-1">
                                    <input type="number" :name="'items['+index+'][discount]'" x-model.number="item.discount" @input="calcRow(index)" min="0" step="0.01" class="form-input text-sm text-right w-24">
                                </td>
                                <td class="py-2 pl-1 text-right font-medium" x-text="formatMoney(item.total)"></td>
                                <td class="py-2 pl-1">
                                    <button type="button" @click="removeItem(index)" class="text-red-400 hover:text-red-600">
                                        <i class="fas fa-times text-xs"></i>
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                    <tfoot class="text-sm font-semibold border-t">
                        <tr>
                            <td colspan="5" class="pt-3 text-right text-gray-600">Ara Toplam</td>
                            <td class="pt-3 text-right" x-text="formatMoney(subtotal)"></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td colspan="5" class="text-right text-gray-500 text-xs">KDV</td>
                            <td class="text-right text-xs" x-text="formatMoney(taxAmount)"></td>
                            <td></td>
                        </tr>
                        <tr class="text-base">
                            <td colspan="5" class="pt-1 text-right text-gray-800">TOPLAM</td>
                            <td class="pt-1 text-right text-indigo-700" x-text="formatMoney(total)"></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div class="flex gap-3">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save mr-2"></i>Fatura Oluştur
            </button>
            <a href="{{ route('invoices.index') }}" class="btn btn-secondary">İptal</a>
        </div>
    </form>
</div>

@push('scripts')
<script>
function invoiceForm() {
    return {
        taxRateEl: null,
        items: [
            { description: '', type: 'service', quantity: 1, unit_price: 0, discount: 0, total: 0 }
        ],
        get subtotal() {
            return this.items.reduce((s, i) => s + i.total, 0);
        },
        get taxRate() {
            const el = document.querySelector('[name="tax_rate"]');
            return el ? parseFloat(el.value) || 0 : 20;
        },
        get taxAmount() {
            return this.subtotal * (this.taxRate / 100);
        },
        get total() {
            return this.subtotal + this.taxAmount;
        },
        addItem() {
            this.items.push({ description: '', type: 'service', quantity: 1, unit_price: 0, discount: 0, total: 0 });
        },
        removeItem(idx) {
            if (this.items.length > 1) this.items.splice(idx, 1);
        },
        calcRow(idx) {
            const i = this.items[idx];
            i.total = Math.max(0, (i.quantity * i.unit_price) - i.discount);
        },
        formatMoney(v) {
            return new Intl.NumberFormat('tr-TR', { minimumFractionDigits: 2 }).format(v || 0) + ' ₺';
        }
    }
}
</script>
@endpush

@endsection
