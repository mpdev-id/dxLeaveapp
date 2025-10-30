<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use App\Models\Workflow;
use App\Services\LeaveRequestService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;

class LeaveRequestController extends Controller
{
    protected $leaveRequestService;

    // Injeksi dependensi (Dependency Injection) LeaveRequestService
    public function __construct(LeaveRequestService $leaveRequestService)
    {
        $this->leaveRequestService = $leaveRequestService;
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
            $requests = $query->whereIn('status', ['Pending', 'In Progress', 'Rejected'])->get();

        } else {
            // Logika untuk Karyawan: Tampilkan permintaan miliknya sendiri.
            $requests = $query->where('user_id', $user->id)->get();
        }

        return response()->json($requests);
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
        $workflow = Workflow::where('applies_to_model', LeaveRequest::class)->first();

        if (!$workflow) {
            return response()->json(['message' => 'Alur kerja cuti tidak ditemukan.'], 500);
        }

        // 3. Cek Jatah Cuti (Placeholder)
        // TODO: Panggil EntitlementService untuk cek apakah jatah cuti mencukupi.
        if (!$this->entitlementService->hasSufficientBalance(Auth::user(), $validatedData['leave_type_id'], $daysNeeded)) {
            throw ValidationException::withMessages(['leave' => 'Jatah cuti tidak mencukupi.']);
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
                    'status' => 'Pending', // Selalu mulai dari Pending
                ]);
            });

            return response()->json([
                'message' => 'Permintaan cuti berhasil diajukan.',
                'request' => $leaveRequest->load('leaveType')
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal mengajukan cuti.', 'error' => $e->getMessage()], 500);
        }
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

            return response()->json([
                'message' => 'Tindakan persetujuan berhasil dicatat.',
                'status' => $leaveRequest->fresh()->status
            ], 200);

        } catch (ValidationException $e) {
            // Menangkap kesalahan validasi, termasuk batasan urutan (sequential check)
            return response()->json([
                'message' => $e->getMessage(),
                'errors' => $e->errors()
            ], 403); // 403 Forbidden
        } catch (\Exception $e) {
            return response()->json(['message' => 'Terjadi kesalahan sistem.'], 500);
        }
    }
}
