<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use App\Models\Workflow;
use App\Services\LeaveRequestService;
use App\Services\EntitlementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class LeaveRequestController extends Controller
{
    protected $leaveRequestService;
    protected $entitlementService;

    // Injeksi dependensi (Dependency Injection) LeaveRequestService dan EntitlementService
    public function __construct(LeaveRequestService $leaveRequestService, EntitlementService $entitlementService)
    {
        $this->leaveRequestService = $leaveRequestService;
        $this->entitlementService = $entitlementService;
    }

    /**
     * Tampilkan daftar permintaan cuti.
     * Manajer melihat permintaan yang perlu disetujui.
     * Karyawan melihat permintaan miliknya.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = LeaveRequest::with(['user', 'leaveType', 'workflow']);

        // Jika pengguna adalah Admin/Manajer dan memiliki izin 'approve leave request'
        if ($user->hasPermissionTo('approve leave request')) {
            // Logika untuk Manajer: Tampilkan permintaan yang perlu dia setujui.
            // Di sini Anda perlu mengimplementasikan logika kompleks untuk filter:
            // 1. Dapatkan langkah workflow yang peran-nya adalah peran si manajer.
            // 2. Filter LeaveRequest yang statusnya 'Pending' atau 'In Progress' dan
            //    approver-nya adalah user saat ini.
            // Untuk kesederhanaan, kita hanya menampilkan semua yang Pending atau In Progress saat ini.
            
            // TODO: Implementasikan filter yang lebih ketat menggunakan WorkflowService.
            $requests = $query->whereIn('current_status', ['Pending'])->get();

        } else {
            // Logika untuk Karyawan: Tampilkan permintaan miliknya sendiri.
            $requests = $query->where('user_id', $user->id)->get();
        }

        return ResponseFormatter::success($requests, 'Leave requests retrieved successfully');
    }

    /**
     * Simpan permintaan cuti yang baru (Pengajuan oleh Karyawan).
     */
    public function store(Request $request)
    {
        // 1. Validasi Input
        $validatedData = $request->validate([
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|max:500',
        ]);

        // 2. Cari Alur Kerja yang Sesuai
        // Kita asumsikan ada logika untuk menentukan workflow, misal berdasarkan LeaveType
        $workflow = Workflow::where('applicable_model', LeaveRequest::class)->first();

        if (!$workflow) {
            return ResponseFormatter::error(null, 'Leave workflow not found.', 500);
        }

        // 3. Cek Jatah Cuti
        $startDate = Carbon::parse($validatedData['start_date']);
        $endDate = Carbon::parse($validatedData['end_date']);
        $daysNeeded = $startDate->diffInDays($endDate) + 1;

        if (!$this->entitlementService->hasSufficientBalance(Auth::user(), $validatedData['leave_type_id'], $daysNeeded)) {
            throw ValidationException::withMessages(['leave' => 'Insufficient leave balance.']);
        }

        // 4. Buat Permintaan Cuti
        try {
            $leaveRequest = DB::transaction(function() use ($validatedData, $workflow) {
                return LeaveRequest::create([
                    'user_id' => Auth::id(),
                    'leave_type_id' => $validatedData['leave_type_id'],
                    'workflow_id' => $workflow->id,
                    'start_date' => $validatedData['start_date'],
                    'end_date' => $validatedData['end_date'],
                    'reason' => $validatedData['reason'],
                    'duration_days' => Carbon::parse($validatedData['start_date'])->diffInDays(Carbon::parse($validatedData['end_date'])) + 1 ,
                    'supporting_attachment_path' => null,
                    'current_status' => 'Draft', // Selalu mulai dari Draft
                ]);
            });

            return ResponseFormatter::success($leaveRequest->load('leaveType'), 'Leave request submitted successfully');
        } catch (\Exception $e) {
            return ResponseFormatter::error(null, 'Failed to submit leave request: ' . $e->getMessage(), 500);
        }
    }


    public function update(Request $request, LeaveRequest $leaveRequest)
    {
        Log::info('Update method called', ['request_data' => $request->all()]);

        // Otorisasi: Pastikan pengguna hanya mengedit permintaan cuti miliknya sendiri.
        $this->authorize('update', $leaveRequest);
        Log::info('Authorization successful');

        // Validasi input dasar
        try {
            $validatedData = $request->validate([
                'leave_type_id' => 'sometimes|required|exists:leave_types,id',
                'start_date' => 'sometimes|required|date',
                'end_date' => 'sometimes|required|date|after_or_equal:start_date',
                'reason' => 'sometimes|required|string|max:500',
                'current_status' => 'sometimes|required|in:Draft,Pending', // Izinkan perubahan status
            ]);
            Log::info('Validation successful', ['validated_data' => $validatedData]);
        } catch (ValidationException $e) {
            Log::error('Validation failed', ['errors' => $e->errors()]);
            throw $e;
        }

        // Cek status: Hanya izinkan edit jika statusnya masih 'Draft'.
        if ($leaveRequest->current_status !== 'Draft') {
            Log::warning('Attempted to update a non-draft leave request', ['leave_request_id' => $leaveRequest->id, 'current_status' => $leaveRequest->current_status]);
            return ResponseFormatter::error(
                null,
                'This leave request cannot be edited because it is already being processed.',
                403
            );
        }

        // Jika status diubah ke 'Pending', pastikan semua field yang diperlukan sudah ada.
        if (isset($validatedData['current_status']) && $validatedData['current_status'] === 'Pending') {
            Log::info('Attempting to change status to Pending');
            try {
                $request->validate([
                    'leave_type_id' => 'required|exists:leave_types,id',
                    'start_date' => 'required|date',
                    'end_date' => 'required|date|after_or_equal:start_date',
                    'reason' => 'required|string|max:500',
                ]);
                Log::info('Validation for Pending status successful');
            } catch (ValidationException $e) {
                Log::error('Validation for Pending status failed', ['errors' => $e->errors()]);
                throw $e;
            }
        }


        // Hitung ulang durasi jika tanggal berubah
        if (isset($validatedData['start_date']) || isset($validatedData['end_date'])) {
            $startDate = Carbon::parse($validatedData['start_date'] ?? $leaveRequest->start_date);
            $endDate = Carbon::parse($validatedData['end_date'] ?? $leaveRequest->end_date);
            $validatedData['duration_days'] = $startDate->diffInDays($endDate) + 1;
        }

        // Lakukan pembaruan
        $leaveRequest->update($validatedData);
        Log::info('Leave request updated successfully', ['leave_request_id' => $leaveRequest->id]);

        return ResponseFormatter::success($leaveRequest->fresh(), 'Leave request updated successfully');
    }

    /**
     * Endpoint untuk manajer menyetujui atau menolak permintaan cuti (menggunakan Service Layer).
     */
    public function handleApproval(Request $request, LeaveRequest $leaveRequest)
    {
        $request->validate([
            'action' => 'required|in:Approved,Rejected',
            'comments' => 'nullable|string',
        ]);

        try {
            $approver = Auth::user();
            $action = $request->input('action');
            $comments = $request->input('comments');

            // Panggil Service Layer yang memegang semua logika sequential check.
            $this->leaveRequestService->processApproval($leaveRequest, $approver, $action, $comments);

            return ResponseFormatter::success($leaveRequest->fresh()->current_status, 'Approval action recorded successfully.');

        } catch (ValidationException $e) {
            // Menangkap kesalahan validasi, termasuk batasan urutan (sequential check)
            return ResponseFormatter::error($e->errors(), $e->getMessage(), 403);
        } catch (\Exception $e) {
            return ResponseFormatter::error(null, 'A system error occurred.', 500);
        }
    }
}
