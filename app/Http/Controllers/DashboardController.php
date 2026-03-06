<?php

namespace App\Http\Controllers;

use App\Models\ServiceRequest;
use App\Models\Customer;
use App\Models\Device;
use App\Models\Technician;
use App\Models\Invoice;
use App\Models\SparePart;
use App\Models\Payment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $branchId = Auth::user()->branch_id;

        // Servis istatistikleri
        $stats = [
            'total_requests'  => ServiceRequest::where('branch_id', $branchId)->count(),
            'pending'         => ServiceRequest::where('branch_id', $branchId)->where('status', 'pending')->count(),
            'in_progress'     => ServiceRequest::where('branch_id', $branchId)->whereIn('status', ['in_progress', 'diagnosed', 'waiting_part'])->count(),
            'ready'           => ServiceRequest::where('branch_id', $branchId)->where('status', 'ready')->count(),
            'delivered_today' => ServiceRequest::where('branch_id', $branchId)->where('status', 'delivered')->whereDate('delivered_at', today())->count(),
            'total_customers' => Customer::where('branch_id', $branchId)->count(),
            'total_devices'   => Device::where('branch_id', $branchId)->count(),
            'low_stock_parts' => SparePart::where('branch_id', $branchId)->whereColumn('quantity', '<=', 'min_quantity')->count(),
        ];

        // Bu ay gelir
        $thisMonthRevenue = Payment::where('branch_id', $branchId)
            ->whereMonth('paid_at', now()->month)
            ->whereYear('paid_at', now()->year)
            ->sum('amount');

        // Bekleyen fatura toplamı
        $pendingInvoice = Invoice::where('branch_id', $branchId)
            ->whereNotIn('status', ['paid', 'cancelled'])
            ->sum('remaining');

        // Son 7 günlük servis trendi
        $weeklyTrend = ServiceRequest::where('branch_id', $branchId)
            ->where('received_at', '>=', now()->subDays(6)->startOfDay())
            ->selectRaw("DATE(received_at) as date, COUNT(*) as count")
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Son 10 servis talebi
        $recentRequests = ServiceRequest::with(['customer', 'device', 'technician'])
            ->where('branch_id', $branchId)
            ->latest()
            ->take(10)
            ->get();

        // Teknisyen iş yükü
        $technicianLoad = Technician::where('branch_id', $branchId)
            ->where('is_active', true)
            ->withCount(['serviceRequests as active_jobs' => function ($q) {
                $q->whereNotIn('status', ['delivered', 'cancelled']);
            }])
            ->get();

        return view('dashboard', compact(
            'stats', 'thisMonthRevenue', 'pendingInvoice',
            'weeklyTrend', 'recentRequests', 'technicianLoad'
        ));
    }
}
