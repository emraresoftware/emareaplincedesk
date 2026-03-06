<?php

namespace App\Http\Controllers;

use App\Models\Technician;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TechnicianController extends Controller
{
    public function index()
    {
        $branchId   = Auth::user()->branch_id;
        $technicians = Technician::where('branch_id', $branchId)
            ->withCount(['serviceRequests as active_jobs' => fn($q) => $q->whereNotIn('status', ['delivered', 'cancelled'])])
            ->withCount('serviceRequests as total_jobs')
            ->get();

        return view('technicians.index', compact('technicians'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:100',
            'phone'       => 'nullable|string|max:20',
            'email'       => 'nullable|email|max:150',
            'speciality'  => 'nullable|string|max:200',
            'hourly_rate' => 'nullable|numeric|min:0',
        ]);

        $data['branch_id'] = Auth::user()->branch_id;
        Technician::create($data);

        return back()->with('success', 'Teknisyen eklendi.');
    }

    public function update(Request $request, Technician $technician)
    {
        abort_if($technician->branch_id !== Auth::user()->branch_id, 403);

        $data = $request->validate([
            'name'        => 'required|string|max:100',
            'phone'       => 'nullable|string|max:20',
            'email'       => 'nullable|email|max:150',
            'speciality'  => 'nullable|string|max:200',
            'hourly_rate' => 'nullable|numeric|min:0',
            'is_active'   => 'boolean',
        ]);

        $technician->update($data);

        return back()->with('success', 'Teknisyen güncellendi.');
    }

    public function destroy(Technician $technician)
    {
        abort_if($technician->branch_id !== Auth::user()->branch_id, 403);
        $technician->delete();
        return back()->with('success', 'Teknisyen silindi.');
    }
}
