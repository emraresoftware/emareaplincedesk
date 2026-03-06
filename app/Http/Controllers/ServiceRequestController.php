<?php

namespace App\Http\Controllers;

use App\Models\ServiceRequest;
use App\Models\ServiceNote;
use App\Models\Customer;
use App\Models\Device;
use App\Models\Technician;
use App\Models\DeviceCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ServiceRequestController extends Controller
{
    public function index(Request $request)
    {
        $branchId = Auth::user()->branch_id;
        $query    = ServiceRequest::with(['customer', 'device', 'technician'])
            ->where('branch_id', $branchId);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }
        if ($request->filled('technician_id')) {
            $query->where('assigned_technician_id', $request->technician_id);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('ticket_no', 'like', "%$s%")
                  ->orWhereHas('customer', fn($cq) => $cq->where('name', 'like', "%$s%")->orWhere('phone', 'like', "%$s%"))
                  ->orWhereHas('device', fn($dq) => $dq->where('brand', 'like', "%$s%")->orWhere('model', 'like', "%$s%")->orWhere('serial_no', 'like', "%$s%"));
            });
        }

        $requests    = $query->latest()->paginate(20)->withQueryString();
        $technicians = Technician::where('branch_id', $branchId)->where('is_active', true)->get();

        return view('service.index', compact('requests', 'technicians'));
    }

    public function create(Request $request)
    {
        $branchId   = Auth::user()->branch_id;
        $customer   = $request->filled('customer_id') ? Customer::findOrFail($request->customer_id) : null;
        $technicians = Technician::where('branch_id', $branchId)->where('is_active', true)->get();
        $categories  = DeviceCategory::where('branch_id', $branchId)->where('is_active', true)->get();

        return view('service.create', compact('customer', 'technicians', 'categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_id'              => 'required|exists:customers,id',
            'device_id'                => 'nullable|exists:devices,id',
            'assigned_technician_id'   => 'nullable|exists:technicians,id',
            'type'                     => 'required|in:repair,maintenance,installation,inspection',
            'priority'                 => 'required|in:low,normal,high,urgent',
            'problem_description'      => 'required|string',
            'device_condition_in'      => 'nullable|string',
            'accessories_received'     => 'nullable|string',
            'estimated_cost'           => 'nullable|numeric|min:0',
            'estimated_completion_at'  => 'nullable|date',
            'received_at'              => 'required|date',
            // Yeni cihaz alanları
            'new_brand'                => 'nullable|string|max:100',
            'new_model'                => 'nullable|string|max:100',
            'new_serial_no'            => 'nullable|string|max:100',
            'new_category_id'          => 'nullable|exists:device_categories,id',
        ]);

        $branchId = Auth::user()->branch_id;
        $data['branch_id'] = $branchId;
        $data['ticket_no'] = ServiceRequest::generateTicketNo($branchId);
        $data['created_by'] = Auth::id();

        // Yeni cihaz oluştur
        if (empty($data['device_id']) && !empty($data['new_brand']) && !empty($data['new_model'])) {
            $device = Device::create([
                'branch_id'          => $branchId,
                'customer_id'        => $data['customer_id'],
                'device_category_id' => $data['new_category_id'] ?? null,
                'brand'              => $data['new_brand'],
                'model'              => $data['new_model'],
                'serial_no'          => $data['new_serial_no'] ?? null,
            ]);
            $data['device_id'] = $device->id;
        }

        unset($data['new_brand'], $data['new_model'], $data['new_serial_no'], $data['new_category_id']);

        $sr = ServiceRequest::create($data);

        return redirect()->route('service.show', $sr)->with('success', 'Servis talebi oluşturuldu. Talep No: ' . $sr->ticket_no);
    }

    public function show(ServiceRequest $service)
    {
        $this->authorizeBranch($service);
        $service->load(['customer', 'device.category', 'technician', 'notes.user', 'spareParts.sparePart', 'invoices']);

        $branchId    = Auth::user()->branch_id;
        $technicians = Technician::where('branch_id', $branchId)->where('is_active', true)->get();
        $spareParts  = \App\Models\SparePart::where('branch_id', $branchId)->where('is_active', true)->get();

        return view('service.show', compact('service', 'technicians', 'spareParts'));
    }

    public function edit(ServiceRequest $service)
    {
        $this->authorizeBranch($service);
        $branchId    = Auth::user()->branch_id;
        $technicians = Technician::where('branch_id', $branchId)->where('is_active', true)->get();
        $categories  = DeviceCategory::where('branch_id', $branchId)->where('is_active', true)->get();

        return view('service.edit', compact('service', 'technicians', 'categories'));
    }

    public function update(Request $request, ServiceRequest $service)
    {
        $this->authorizeBranch($service);

        $data = $request->validate([
            'assigned_technician_id'  => 'nullable|exists:technicians,id',
            'type'                    => 'required|in:repair,maintenance,installation,inspection',
            'priority'                => 'required|in:low,normal,high,urgent',
            'problem_description'     => 'required|string',
            'diagnosis'               => 'nullable|string',
            'solution'                => 'nullable|string',
            'internal_notes'          => 'nullable|string',
            'device_condition_in'     => 'nullable|string',
            'accessories_received'    => 'nullable|string',
            'labor_cost'              => 'nullable|numeric|min:0',
            'parts_cost'              => 'nullable|numeric|min:0',
            'discount'                => 'nullable|numeric|min:0',
            'estimated_completion_at' => 'nullable|date',
        ]);

        $service->update($data);

        return redirect()->route('service.show', $service)->with('success', 'Servis güncellendi.');
    }

    public function updateStatus(Request $request, ServiceRequest $service)
    {
        $this->authorizeBranch($service);

        $validated = $request->validate([
            'status' => 'required|in:' . implode(',', array_keys(ServiceRequest::STATUSES)),
            'note'   => 'nullable|string',
        ]);

        $oldStatus = $service->status;
        $service->update(['status' => $validated['status']]);

        if ($validated['status'] === 'completed' || $validated['status'] === 'ready') {
            $service->update(['completed_at' => now()]);
        }
        if ($validated['status'] === 'delivered') {
            $service->update(['delivered_at' => now()]);
        }

        ServiceNote::create([
            'service_request_id'      => $service->id,
            'user_id'                 => Auth::id(),
            'type'                    => 'status_change',
            'note'                    => $validated['note'] ?? 'Durum güncellendi.',
            'old_status'              => $oldStatus,
            'new_status'              => $validated['status'],
            'is_visible_to_customer'  => false,
        ]);

        return back()->with('success', 'Durum güncellendi: ' . ServiceRequest::STATUSES[$validated['status']]['label']);
    }

    public function addNote(Request $request, ServiceRequest $service)
    {
        $this->authorizeBranch($service);

        $data = $request->validate([
            'note'                   => 'required|string',
            'is_visible_to_customer' => 'boolean',
        ]);

        ServiceNote::create([
            'service_request_id'     => $service->id,
            'user_id'                => Auth::id(),
            'type'                   => 'note',
            'note'                   => $data['note'],
            'is_visible_to_customer' => $data['is_visible_to_customer'] ?? false,
        ]);

        return back()->with('success', 'Not eklendi.');
    }

    public function destroy(ServiceRequest $service)
    {
        $this->authorizeBranch($service);
        $service->delete();
        return redirect()->route('service.index')->with('success', 'Servis talebi silindi.');
    }

    private function authorizeBranch(ServiceRequest $service): void
    {
        abort_if($service->branch_id !== Auth::user()->branch_id, 403);
    }
}
