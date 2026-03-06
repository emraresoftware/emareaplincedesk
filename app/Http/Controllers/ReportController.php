<?php

namespace App\Http\Controllers;

use App\Models\ServiceRequest;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\SparePart;
use App\Models\Technician;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index()
    {
        return view('reports.index');
    }

    public function services(Request $request)
    {
        $branchId = Auth::user()->branch_id;

        $from = $request->from ?? now()->startOfMonth()->format('Y-m-d');
        $to   = $request->to   ?? now()->format('Y-m-d');

        $byStatus = ServiceRequest::where('branch_id', $branchId)
            ->whereBetween('received_at', [$from . ' 00:00:00', $to . ' 23:59:59'])
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        $byType = ServiceRequest::where('branch_id', $branchId)
            ->whereBetween('received_at', [$from . ' 00:00:00', $to . ' 23:59:59'])
            ->selectRaw('type, COUNT(*) as count')
            ->groupBy('type')
            ->pluck('count', 'type');

        $byPriority = ServiceRequest::where('branch_id', $branchId)
            ->whereBetween('received_at', [$from . ' 00:00:00', $to . ' 23:59:59'])
            ->selectRaw('priority, COUNT(*) as count')
            ->groupBy('priority')
            ->pluck('count', 'priority');

        $dailyTrend = ServiceRequest::where('branch_id', $branchId)
            ->whereBetween('received_at', [$from . ' 00:00:00', $to . ' 23:59:59'])
            ->selectRaw("DATE(received_at) as date, COUNT(*) as count")
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $topTechnicians = Technician::where('branch_id', $branchId)
            ->withCount(['serviceRequests as completed' => fn($q) => $q->where('status', 'delivered')])
            ->orderByDesc('completed')
            ->take(10)
            ->get();

        return view('reports.services', compact('byStatus', 'byType', 'byPriority', 'dailyTrend', 'topTechnicians', 'from', 'to'));
    }

    public function financial(Request $request)
    {
        $branchId = Auth::user()->branch_id;

        $from = $request->from ?? now()->startOfMonth()->format('Y-m-d');
        $to   = $request->to   ?? now()->format('Y-m-d');

        $totalRevenue  = Payment::where('branch_id', $branchId)->whereBetween('paid_at', [$from, $to])->sum('amount');
        $totalInvoiced = Invoice::where('branch_id', $branchId)->whereBetween('issue_date', [$from, $to])->sum('total');
        $outstanding   = Invoice::where('branch_id', $branchId)->whereNotIn('status', ['paid', 'cancelled'])->sum('remaining');

        $byMethod = Payment::where('branch_id', $branchId)
            ->whereBetween('paid_at', [$from, $to])
            ->selectRaw('payment_method, SUM(amount) as total')
            ->groupBy('payment_method')
            ->pluck('total', 'payment_method');

        $monthlyRevenue = Payment::where('branch_id', $branchId)
            ->whereBetween('paid_at', [now()->subMonths(11)->startOfMonth(), now()])
            ->selectRaw("DATE_FORMAT(paid_at, '%Y-%m') as month, SUM(amount) as total")
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month');

        return view('reports.financial', compact('totalRevenue', 'totalInvoiced', 'outstanding', 'byMethod', 'monthlyRevenue', 'from', 'to'));
    }

    public function inventory(Request $request)
    {
        $branchId = Auth::user()->branch_id;

        $lowStock  = SparePart::where('branch_id', $branchId)->whereColumn('quantity', '<=', 'min_quantity')->get();
        $topUsed   = SparePartUsage::where('branch_id', $branchId)
            ->where('type', 'used')
            ->selectRaw('spare_part_id, SUM(quantity) as total_used, SUM(quantity * unit_price) as total_value')
            ->groupBy('spare_part_id')
            ->with('sparePart')
            ->orderByDesc('total_used')
            ->take(15)
            ->get();

        $stockValue = SparePart::where('branch_id', $branchId)->sum(DB::raw('quantity * purchase_price'));

        return view('reports.inventory', compact('lowStock', 'topUsed', 'stockValue'));
    }
}
