<?php

namespace App\Http\Controllers;

use App\Models\SparePart;
use App\Models\SparePartUsage;
use App\Models\ServiceRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SparePartController extends Controller
{
    public function index(Request $request)
    {
        $branchId = Auth::user()->branch_id;
        $query    = SparePart::where('branch_id', $branchId);

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) => $q->where('name', 'like', "%$s%")->orWhere('code', 'like', "%$s%")->orWhere('brand', 'like', "%$s%")->orWhere('barcode', 'like', "%$s%"));
        }

        if ($request->filled('low_stock')) {
            $query->whereColumn('quantity', '<=', 'min_quantity');
        }

        $parts = $query->latest()->paginate(25)->withQueryString();

        return view('spare-parts.index', compact('parts'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'                => 'required|string|max:200',
            'brand'               => 'nullable|string|max:100',
            'model_compatibility' => 'nullable|string|max:300',
            'description'         => 'nullable|string',
            'barcode'             => 'nullable|string|max:80',
            'quantity'            => 'required|integer|min:0',
            'min_quantity'        => 'required|integer|min:0',
            'purchase_price'      => 'required|numeric|min:0',
            'sale_price'          => 'required|numeric|min:0',
            'unit'                => 'nullable|string|max:20',
            'location'            => 'nullable|string|max:100',
            'supplier'            => 'nullable|string|max:200',
            'warranty_months'     => 'nullable|string|max:20',
        ]);

        $data['branch_id'] = Auth::user()->branch_id;
        $data['code']      = 'P-' . str_pad(SparePart::where('branch_id', $data['branch_id'])->count() + 1, 5, '0', STR_PAD_LEFT);

        SparePart::create($data);

        return back()->with('success', 'Parça stoka eklendi.');
    }

    public function update(Request $request, SparePart $sparePart)
    {
        abort_if($sparePart->branch_id !== Auth::user()->branch_id, 403);

        $data = $request->validate([
            'name'                => 'required|string|max:200',
            'brand'               => 'nullable|string|max:100',
            'model_compatibility' => 'nullable|string|max:300',
            'purchase_price'      => 'required|numeric|min:0',
            'sale_price'          => 'required|numeric|min:0',
            'min_quantity'        => 'required|integer|min:0',
            'location'            => 'nullable|string|max:100',
            'supplier'            => 'nullable|string|max:200',
        ]);

        $sparePart->update($data);

        return back()->with('success', 'Parça güncellendi.');
    }

    public function addStock(Request $request, SparePart $sparePart)
    {
        abort_if($sparePart->branch_id !== Auth::user()->branch_id, 403);

        $data = $request->validate([
            'quantity'       => 'required|integer|min:1',
            'purchase_price' => 'nullable|numeric|min:0',
            'notes'          => 'nullable|string',
        ]);

        DB::transaction(function () use ($sparePart, $data) {
            SparePartUsage::create([
                'branch_id'      => $sparePart->branch_id,
                'spare_part_id'  => $sparePart->id,
                'user_id'        => Auth::id(),
                'type'           => 'added',
                'quantity'       => $data['quantity'],
                'unit_price'     => $data['purchase_price'] ?? $sparePart->purchase_price,
                'notes'          => $data['notes'] ?? null,
            ]);

            $sparePart->increment('quantity', $data['quantity']);
            if (!empty($data['purchase_price'])) {
                $sparePart->update(['purchase_price' => $data['purchase_price']]);
            }
        });

        return back()->with('success', $data['quantity'] . ' adet stok eklendi.');
    }

    public function usePart(Request $request)
    {
        $data = $request->validate([
            'spare_part_id'      => 'required|exists:spare_parts,id',
            'service_request_id' => 'required|exists:service_requests,id',
            'quantity'           => 'required|integer|min:1',
        ]);

        $part = SparePart::findOrFail($data['spare_part_id']);
        abort_if($part->branch_id !== Auth::user()->branch_id, 403);
        abort_if($part->quantity < $data['quantity'], 422, 'Yeterli stok yok.');

        $service = ServiceRequest::findOrFail($data['service_request_id']);
        abort_if($service->branch_id !== Auth::user()->branch_id, 403);

        DB::transaction(function () use ($part, $service, $data) {
            SparePartUsage::create([
                'branch_id'          => $part->branch_id,
                'spare_part_id'      => $part->id,
                'service_request_id' => $service->id,
                'user_id'            => Auth::id(),
                'type'               => 'used',
                'quantity'           => $data['quantity'],
                'unit_price'         => $part->sale_price,
            ]);

            $part->decrement('quantity', $data['quantity']);

            $service->increment('parts_cost', $data['quantity'] * $part->sale_price);
            $service->update(['total_cost' => $service->labor_cost + $service->parts_cost - $service->discount]);
        });

        return back()->with('success', 'Parça servise eklendi.');
    }

    public function destroy(SparePart $sparePart)
    {
        abort_if($sparePart->branch_id !== Auth::user()->branch_id, 403);
        $sparePart->delete();
        return back()->with('success', 'Parça silindi.');
    }
}
