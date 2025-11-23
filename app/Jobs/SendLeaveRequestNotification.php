<?php

namespace App\Jobs;

use App\Models\LeaveRequest;
use App\Models\User;
use App\Notifications\NewLeaveRequestForApprover;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendLeaveRequestNotification implements ShouldQueue
{
    use Queueable;

    /**
     * The number of times the job may be attempted.
     */
    public $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     */
    public $backoff = [60, 300]; // 1 min, 5 min

    /**
     * The maximum number of seconds the job can run.
     */
    public $timeout = 120;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public User $approver,
        public LeaveRequest $leaveRequest
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Eager load relationships to prevent null errors
        $this->leaveRequest->load(['user', 'leaveType', 'currentStep.approverRole']);
        
        $this->approver->notify(new NewLeaveRequestForApprover($this->leaveRequest));
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        \Log::error('Failed to send leave request notification after all retries', [
            'approver_id' => $this->approver->id,
            'leave_request_id' => $this->leaveRequest->id,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}
