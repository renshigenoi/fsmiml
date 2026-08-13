<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Attendance\Models\AttendanceRecord;
use App\Modules\Attendance\Models\LeaveRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class AttendanceController extends Controller
{
    private const TIMEZONE = 'Asia/Jakarta';

    public function today(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $date = now(self::TIMEZONE)->toDateString();

        return response()->json([
            'data' => [
                'date' => $date,
                'record' => $this->recordPayload(AttendanceRecord::query()->where('user_id', $user->id)->whereDate('attendance_date', $date)->first()),
                'leave' => $this->leavePayload(LeaveRequest::query()->where('user_id', $user->id)->where('leave_date', '<=', $date)->where(fn ($query) => $query->whereNull('leave_end_date')->orWhere('leave_end_date', '>=', $date))->latest()->first()),
                'policy' => $this->policyPayload($user),
            ],
        ]);
    }

    public function store(Request $request, string $type): JsonResponse
    {
        if (! in_array($type, ['check-in', 'check-out'], true)) {
            abort(404);
        }

        $data = $request->validate([
            'photo' => ['required', 'image', 'max:5120'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'accuracy_meters' => ['nullable', 'numeric', 'min:0', 'max:10000'],
        ]);
        /** @var User $user */
        $user = $request->user();
        $now = now(self::TIMEZONE);
        $date = $now->toDateString();

        $approvedLeave = LeaveRequest::query()->where('user_id', $user->id)->where('leave_date', '<=', $date)->where(fn ($query) => $query->whereNull('leave_end_date')->orWhere('leave_end_date', '>=', $date))->where('status', 'approved')->exists();
        if ($approvedLeave) {
            throw ValidationException::withMessages(['attendance' => 'Anda sedang cuti/izin yang telah disetujui hari ini.']);
        }

        $record = AttendanceRecord::query()->firstOrCreate(['user_id' => $user->id, 'attendance_date' => $date]);
        $field = $type === 'check-in' ? 'check_in_at' : 'check_out_at';
        if ($record->{$field}) {
            throw ValidationException::withMessages(['attendance' => $type === 'check-in' ? 'Absen datang sudah tercatat.' : 'Absen pulang sudah tercatat.']);
        }
        if ($type === 'check-out' && ! $record->check_in_at) {
            throw ValidationException::withMessages(['attendance' => 'Silakan absen datang terlebih dahulu.']);
        }

        $location = $this->locationCheck($user, (float) $data['latitude'], (float) $data['longitude']);
        if ($location['status'] === 'outside_rejected') {
            throw ValidationException::withMessages(['location' => 'Anda berada di luar radius lokasi kerja. Jarak saat ini '.$location['distance_meters'].' m.']);
        }

        $path = $request->file('photo')->store('attendance/'.Carbon::parse($date)->format('Y/m'), 'public');
        $prefix = $type === 'check-in' ? 'check_in' : 'check_out';
        $record->fill([
            $prefix.'_at' => $now,
            $prefix.'_photo_path' => $path,
            $prefix.'_latitude' => $data['latitude'],
            $prefix.'_longitude' => $data['longitude'],
            $prefix.'_accuracy_meters' => $data['accuracy_meters'] ?? null,
            $prefix.'_distance_meters' => $location['distance_meters'],
            $prefix.'_location_status' => $location['status'],
        ])->save();

        return response()->json(['message' => $type === 'check-in' ? 'Absen datang berhasil disimpan.' : 'Absen pulang berhasil disimpan.', 'data' => $this->recordPayload($record)]);
    }

    public function calendar(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $month = Carbon::createFromFormat('Y-m', $request->query('month', now(self::TIMEZONE)->format('Y-m')), self::TIMEZONE);
        $start = $month->copy()->startOfMonth(); $end = $month->copy()->endOfMonth();
        $records = AttendanceRecord::query()->where('user_id', $user->id)->whereBetween('attendance_date', [$start->toDateString(), $end->toDateString()])->get()->keyBy(fn (AttendanceRecord $r) => $r->attendance_date->toDateString());
        $leaves = LeaveRequest::query()->where('user_id', $user->id)->where('leave_date', '<=', $end->toDateString())->where(fn ($query) => $query->whereNull('leave_end_date')->orWhere('leave_end_date', '>=', $start->toDateString()))->get();
        $days = [];
        for ($day = $start->copy(); $day->lte($end); $day->addDay()) {
            $key = $day->toDateString();
            $leave = $leaves->first(fn (LeaveRequest $item) => $item->leave_date->toDateString() <= $key && (! $item->leave_end_date || $item->leave_end_date->toDateString() >= $key));
            $days[] = ['date' => $key, 'record' => $this->recordPayload($records->get($key)), 'leave' => $this->leavePayload($leave)];
        }
        return response()->json(['data' => $days]);
    }

    public function storeLeave(Request $request): JsonResponse
    {
        $data = $request->validate(['type' => ['required', 'in:leave,permission'], 'leave_date' => ['required', 'date'], 'leave_end_date' => ['nullable', 'date', 'after_or_equal:leave_date'], 'start_time' => ['nullable', 'date_format:H:i'], 'end_time' => ['nullable', 'date_format:H:i', 'after:start_time'], 'note' => ['nullable', 'string', 'max:1000']]);
        if ($data['type'] === 'leave') { $data['leave_end_date'] = $data['leave_end_date'] ?? $data['leave_date']; $data['start_time'] = null; $data['end_time'] = null; }
        if ($data['type'] === 'permission' && (! isset($data['start_time']) || ! isset($data['end_time']))) throw ValidationException::withMessages(['start_time' => 'Jam mulai dan jam selesai izin wajib diisi.']);
        /** @var User $user */
        $user = $request->user();
        $leave = LeaveRequest::query()->create([...$data, 'user_id' => $user->id, 'status' => 'pending']);
        return response()->json(['message' => 'Pengajuan berhasil dikirim dan menunggu persetujuan.', 'data' => $this->leavePayload($leave)], 201);
    }

    private function policyPayload(User $user): array
    {
        $tech = $user->technician?->loadMissing('workLocation'); $location = $tech?->workLocation;
        return ['mode' => $tech?->attendance_mode ?? 'anywhere', 'location_name' => $location?->name, 'radius_meters' => $tech?->attendance_radius_override ?? $location?->radius_meters, 'address' => $location?->address];
    }

    private function locationCheck(User $user, float $latitude, float $longitude): array
    {
        $tech = $user->technician?->loadMissing('workLocation'); $location = $tech?->workLocation;
        if (! $location || $tech?->attendance_mode === 'anywhere') return ['status' => 'outside_allowed', 'distance_meters' => null];
        $distance = (int) round($this->distance($latitude, $longitude, (float) $location->latitude, (float) $location->longitude));
        $radius = $tech->attendance_radius_override ?? $location->radius_meters;
        if ($distance <= $radius) return ['status' => 'valid', 'distance_meters' => $distance];
        return ['status' => $tech->attendance_mode === 'required_location' ? 'outside_rejected' : 'outside_allowed', 'distance_meters' => $distance];
    }

    private function distance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earth = 6371000; $dLat = deg2rad($lat2 - $lat1); $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;
        return $earth * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }

    private function recordPayload(?AttendanceRecord $record): ?array
    {
        if (! $record) return null;
        return ['id' => $record->id, 'date' => $record->attendance_date->toDateString(), 'check_in_at' => $record->check_in_at?->toIso8601String(), 'check_out_at' => $record->check_out_at?->toIso8601String(), 'check_in_status' => $record->check_in_location_status, 'check_out_status' => $record->check_out_location_status];
    }

    private function leavePayload(?LeaveRequest $leave): ?array
    {
        if (! $leave) return null;
        return ['id' => $leave->id, 'type' => $leave->type, 'date' => $leave->leave_date->toDateString(), 'end_date' => $leave->leave_end_date?->toDateString(), 'start_time' => $leave->start_time, 'end_time' => $leave->end_time, 'note' => $leave->note, 'status' => $leave->status];
    }
}
