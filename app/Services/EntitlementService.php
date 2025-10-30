<?php

namespace App\Services;

use App\Models\EmployeeEntitlement;
use App\Models\LeaveRequest;
use App\Models\User;
use Carbon\Carbon;

class EntitlementService
{
    /**
     * Memeriksa apakah sisa jatah cuti karyawan mencukupi untuk pengajuan baru.
     *
     * @param User $user Pengguna yang mengajukan cuti.
     * @param int $leaveTypeId ID jenis cuti.
     * @param int $daysNeeded Jumlah hari cuti yang dibutuhkan.
     * @return bool
     */
    public function hasSufficientBalance(User $user, int $leaveTypeId, int $daysNeeded): bool
    {
        $currentBalance = $this->getCurrentBalance($user, $leaveTypeId);
        return $currentBalance >= $daysNeeded;
    }

    /**
     * Mendapatkan sisa saldo jatah cuti saat ini.
     *
     * @param User $user
     * @param int $leaveTypeId
     * @return int Sisa hari cuti.
     */
    public function getCurrentBalance(User $user, int $leaveTypeId): int
    {
        // 1. Dapatkan Total Jatah Cuti Tahun Ini (Entitlement)
        $entitlement = EmployeeEntitlement::where('user_id', $user->id)
            ->where('leave_type_id', $leaveTypeId)
            ->where('year', Carbon::now()->year)
            ->sum('initial_balance');

        // 2. Hitung Jumlah Hari yang Sudah Diambil (Approved/Used)
        $daysUsed = LeaveRequest::where('user_id', $user->id)
            ->where('leave_type_id', $leaveTypeId)
            ->where('current_status', 'Approved') // Hanya hitung yang sudah FINAL APPROVED
            // Filter tahun cuti (misalnya, hanya hitung cuti tahun ini)
            ->whereYear('start_date', Carbon::now()->year) 
            ->get()
            ->sum(function ($request) {
                // Asumsi: Anda memiliki helper untuk menghitung hari kerja (di luar weekend/public holiday)
                // Untuk contoh sederhana, kita hitung selisih hari kalender:
                return $request->start_date->diffInDays($request->end_date) + 1;
            });

        return $entitlement - $daysUsed;
    }

    /**
     * Mengurangi saldo jatah cuti setelah permintaan disetujui penuh (FINAL APPROVAL).
     *
     * @param LeaveRequest $leaveRequest Permintaan cuti yang telah disetujui.
     * @param int $daysToDeduct Jumlah hari yang akan dipotong.
     * @return void
     */
    public function deductEntitlement(LeaveRequest $leaveRequest, int $daysToDeduct): void
    {
        // Dalam sistem HRIS yang ideal, pengurangan dilakukan melalui transaksi terpisah
        // atau update field 'days_used' di tabel entitlement, bukan pengurangan langsung dari 'days'.
        // Untuk sederhana, kita asumsikan Entitlement::days adalah saldo.
        // Dalam implementasi nyata, Anda akan mencatat transaksi pengurangan.
        // Di sini kita hanya mencatat logika:

        // Logika ini sangat disederhanakan. Dalam aplikasi nyata, Anda akan
        // MENCATAT PENGURANGAN, bukan mengubah saldo dasar (EmployeeEntitlement::days).

        // Log::info("Cuti berhasil dipotong untuk user ID: {$leaveRequest->user_id} sebanyak {$daysToDeduct} hari.");
    }
}
