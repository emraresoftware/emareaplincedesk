<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Models\ServiceRequest;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $branchId = Auth::user()->branch_id;
        $query    = Invoice::with(['customer', 'serviceRequest'])->where('branch_id', $branchId);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) => $q->where('invoice_no', 'like', "%$s%")->orWhereHas('customer', fn($cq) => $cq->where('name', 'like', "%$s%")->orWhere('phone', 'like', "%$s%")));
        }

        $invoices = $query->latest()->paginate(20)->withQueryString();

        return view('invoices.index', compact('invoices'));
    }

    public function create(Request $request)
    {
        $branchId  = Auth::user()->branch_id;
        $customer  = $request->filled('customer_id') ? Customer::findOrFail($request->customer_id) : null;
        $service   = $request->filled('service_id') ? ServiceRequest::findOrFail($request->service_id) : null;

        return view('invoices.create', compact('customer', 'service'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_id'        => 'required|exists:customers,id',
            'service_request_id' => 'nullable|exists:service_requests,id',
            'tax_rate'           => 'required|numeric|min:0|max:100',
            'discount'           => 'nullable|numeric|min:0',
            'currency'           => 'nullable|string|max:5',
            'issue_date'         => 'required|date',
            'due_date'           => 'nullable|date',
            'notes'              => 'nullable|string',
            'items'              => 'required|array|min:1',
            'items.*.description'=> 'required|string',
            'items.*.quantity'   => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount'   => 'nullable|numeric|min:0',
            'items.*.tax_rate'   => 'nullable|numeric|min:0',
            'items.*.type'       => 'required|in:service,part,other',
        ]);

        $branchId = Auth::user()->branch_id;

        $invoice = DB::transaction(function () use ($data, $branchId) {
            $invoice = Invoice::create([
                'branch_id'          => $branchId,
                'customer_id'        => $data['customer_id'],
                'service_request_id' => $data['service_request_id'] ?? null,
                'created_by'         => Auth::id(),
                'invoice_no'         => Invoice::generateInvoiceNo($branchId),
                'type'               => 'service',
                'status'             => 'draft',
                'currency'           => $data['currency'] ?? 'TRY',
                'tax_rate'           => $data['tax_rate'],
                'discount'           => $data['discount'] ?? 0,
                'issue_date'         => $data['issue_date'],
                'due_date'           => $data['due_date'] ?? null,
                'notes'              => $data['notes'] ?? null,
                'subtotal'           => 0,
                'tax_amount'         => 0,
                'total'              => 0,
                'paid_amount'        => 0,
                'remaining'          => 0,
            ]);

            foreach ($data['items'] as $i => $item) {
                $lineTotal = round(($item['quantity'] * $item['unit_price']) - ($item['discount'] ?? 0), 2);
                InvoiceItem::create([
                    'invoice_id'  => $invoice->id,
                    'type'        => $item['type'],
                    'description' => $item['description'],
                    'quantity'    => $item['quantity'],
                    'unit_price'  => $item['unit_price'],
                    'discount'    => $item['discount'] ?? 0,
                    'tax_rate'    => $item['tax_rate'] ?? $data['tax_rate'],
                    'total'       => $lineTotal,
                    'sort_order'  => $i,
                ]);
            }

            $invoice->recalculate();

            return $invoice;
        });

        return redirect()->route('invoices.show', $invoice)->with('success', 'Fatura oluşturuldu: ' . $invoice->invoice_no);
    }

    public function show(Invoice $invoice)
    {
        abort_if($invoice->branch_id !== Auth::user()->branch_id, 403);
        $invoice->load(['customer', 'serviceRequest', 'items', 'payments.createdBy', 'createdBy']);
        return view('invoices.show', compact('invoice'));
    }

    public function addPayment(Request $request, Invoice $invoice)
    {
        abort_if($invoice->branch_id !== Auth::user()->branch_id, 403);

        $data = $request->validate([
            'amount'         => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:cash,card,bank,online',
            'paid_at'        => 'required|date',
            'ref_no'         => 'nullable|string|max:50',
            'notes'          => 'nullable|string',
        ]);

        Payment::create([
            'branch_id'      => $invoice->branch_id,
            'invoice_id'     => $invoice->id,
            'customer_id'    => $invoice->customer_id,
            'created_by'     => Auth::id(),
            'amount'         => $data['amount'],
            'payment_method' => $data['payment_method'],
            'currency'       => $invoice->currency,
            'paid_at'        => $data['paid_at'],
            'ref_no'         => $data['ref_no'] ?? null,
            'notes'          => $data['notes'] ?? null,
        ]);

        return back()->with('success', number_format($data['amount'], 2) . ' ' . $invoice->currency . ' ödeme kaydedildi.');
    }

    public function destroy(Invoice $invoice)
    {
        abort_if($invoice->branch_id !== Auth::user()->branch_id, 403);
        $invoice->delete();
        return redirect()->route('invoices.index')->with('success', 'Fatura silindi.');
    }
}
