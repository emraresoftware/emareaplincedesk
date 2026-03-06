<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $branchId = Auth::user()->branch_id;
        $query    = Customer::where('branch_id', $branchId)->withCount(['serviceRequests', 'devices']);

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%$s%")
                  ->orWhere('surname', 'like', "%$s%")
                  ->orWhere('company_name', 'like', "%$s%")
                  ->orWhere('phone', 'like', "%$s%")
                  ->orWhere('email', 'like', "%$s%")
                  ->orWhere('code', 'like', "%$s%");
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $customers = $query->latest()->paginate(20)->withQueryString();

        return view('customers.index', compact('customers'));
    }

    public function create()
    {
        return view('customers.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'         => 'required|string|max:100',
            'surname'      => 'nullable|string|max:100',
            'company_name' => 'nullable|string|max:200',
            'type'         => 'required|in:individual,corporate',
            'phone'        => 'nullable|string|max:20',
            'phone2'       => 'nullable|string|max:20',
            'email'        => 'nullable|email|max:150',
            'address'      => 'nullable|string',
            'city'         => 'nullable|string|max:80',
            'country'      => 'nullable|string|max:5',
            'tax_number'   => 'nullable|string|max:50',
            'tax_office'   => 'nullable|string|max:100',
            'id_number'    => 'nullable|string|max:20',
            'notes'        => 'nullable|string',
        ]);

        $data['branch_id'] = Auth::user()->branch_id;
        $data['code']      = 'C-' . str_pad(Customer::where('branch_id', $data['branch_id'])->count() + 1, 5, '0', STR_PAD_LEFT);

        $customer = Customer::create($data);

        return redirect()->route('customers.show', $customer)->with('success', 'Müşteri başarıyla eklendi.');
    }

    public function show(Customer $customer)
    {
        $this->authorizeBranch($customer);
        $customer->load(['devices.category', 'serviceRequests' => fn($q) => $q->latest()->take(10), 'invoices' => fn($q) => $q->latest()->take(5)]);
        return view('customers.show', compact('customer'));
    }

    public function edit(Customer $customer)
    {
        $this->authorizeBranch($customer);
        return view('customers.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
    {
        $this->authorizeBranch($customer);

        $data = $request->validate([
            'name'         => 'required|string|max:100',
            'surname'      => 'nullable|string|max:100',
            'company_name' => 'nullable|string|max:200',
            'type'         => 'required|in:individual,corporate',
            'phone'        => 'nullable|string|max:20',
            'phone2'       => 'nullable|string|max:20',
            'email'        => 'nullable|email|max:150',
            'address'      => 'nullable|string',
            'city'         => 'nullable|string|max:80',
            'country'      => 'nullable|string|max:5',
            'tax_number'   => 'nullable|string|max:50',
            'tax_office'   => 'nullable|string|max:100',
            'id_number'    => 'nullable|string|max:20',
            'notes'        => 'nullable|string',
        ]);

        $customer->update($data);

        return redirect()->route('customers.show', $customer)->with('success', 'Müşteri güncellendi.');
    }

    public function destroy(Customer $customer)
    {
        $this->authorizeBranch($customer);
        $customer->delete();
        return redirect()->route('customers.index')->with('success', 'Müşteri silindi.');
    }

    public function search(Request $request)
    {
        $branchId = Auth::user()->branch_id;
        $s        = $request->q;

        $customers = Customer::where('branch_id', $branchId)
            ->where(function ($q) use ($s) {
                $q->where('name', 'like', "%$s%")
                  ->orWhere('surname', 'like', "%$s%")
                  ->orWhere('phone', 'like', "%$s%")
                  ->orWhere('email', 'like', "%$s%");
            })
            ->take(10)
            ->get(['id', 'name', 'surname', 'company_name', 'phone', 'type']);

        return response()->json($customers->map(fn($c) => [
            'id'   => $c->id,
            'text' => $c->full_name . ($c->phone ? " ({$c->phone})" : ''),
        ]));
    }

    private function authorizeBranch(Customer $customer): void
    {
        abort_if($customer->branch_id !== Auth::user()->branch_id, 403);
    }
}
