<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Modules\Attendance\Models\AttendanceRecord;
use App\Modules\Attendance\Models\LeaveRequest;
use App\Modules\Attendance\Models\WorkLocation;
use App\Modules\Identity\Models\Technician;
use App\Modules\Identity\Enums\UserRole;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Validation\Rule;

class AttendanceAdminController extends Controller
{
	public function index(Request $request): View
	{
		$this->authorizeAttendanceAdmin($request);
		return view('dashboard.attendance', [
			'locations' => WorkLocation::query()->orderBy('name')->get(),
			'technicians' => Technician::query()->with(['user', 'workLocation'])->orderBy('employee_code')->get(),
			'leaveRequests' => LeaveRequest::query()->with('user')->where('status', 'pending')->latest()->get(),
			'records' => AttendanceRecord::query()->with('user')->whereDate('attendance_date', now('Asia/Jakarta')->toDateString())->latest('check_in_at')->get(),
		]);
	}

    public function storeLocation(Request $request): RedirectResponse
    {
		$this->authorizeAttendanceAdmin($request);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'address' => ['nullable', 'string', 'max:255'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'radius_meters' => ['required', 'integer', 'min:10', 'max:10000']
        ]);

        WorkLocation::query()->create($data);

        return back()->with('success', 'Lokasi kerja berhasil ditambahkan.');
    }

    public function updateLocation(Request $request, WorkLocation $location): RedirectResponse
    {
		$this->authorizeAttendanceAdmin($request);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'address' => ['nullable', 'string', 'max:255'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'radius_meters' => ['required', 'integer', 'min:10', 'max:10000'],
            'is_active' => ['required', 'boolean']
        ]);

        if (! $data['is_active'] && $location->technicians()->exists()) {
            return back()->withErrors(['is_active' => 'Pindahkan seluruh teknisi ke lokasi aktif lain sebelum menonaktifkan lokasi ini.'])->withInput();
        }

        $location->update($data);

        return back()->with('success', 'Lokasi kerja diperbarui.');
    }

    public function updateTechnician(Request $request, Technician $technician): RedirectResponse
    {
		$this->authorizeAttendanceAdmin($request);
        $data = $request->validate([
            'work_location_id' => ['nullable', Rule::exists('work_locations', 'id')->where('is_active', true)],
            'attendance_mode' => ['required', 'in:anywhere,required_location,allowed_outside'],
            'attendance_radius_override' => ['nullable', 'integer', 'min:10', 'max:10000']
        ]);

        $technician->update($data);

        return back()->with('success', 'Aturan absensi karyawan diperbarui.');
    }

    public function reviewLeave(Request $request, LeaveRequest $leaveRequest): RedirectResponse
    {
		$this->authorizeAttendanceAdmin($request);
        $data = $request->validate([
            'status' => ['required', 'in:approved,rejected'],
            'review_note' => ['nullable', 'string', 'max:1000']
        ]);

        $leaveRequest->update([
            ...$data,
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now()
        ]);

        return back()->with('success', $data['status'] === 'approved' ? 'Pengajuan disetujui.' : 'Pengajuan ditolak.');
    }

    private function authorizeAttendanceAdmin(Request $request): void
    {
        abort_unless(in_array($request->user()?->role, [UserRole::Administrator, UserRole::Coordinator], true), 403);
    }
}
