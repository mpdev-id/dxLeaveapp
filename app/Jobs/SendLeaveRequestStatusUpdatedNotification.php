<?php

namespace App\Jobs;

use App\Models\LeaveRequest;
use App\Notifications\LeaveRequestStatusUpdated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendLeaveRequestStatusUpdatedNotification implements ShouldQueue
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
    public function __construct(public LeaveRequest $leaveRequest)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Eager load relationships to prevent null errors
        $this->leaveRequest->load(['user', 'leaveType', 'currentStep.approverRole']);
        
        $this->leaveRequest->user->notify(new LeaveRequestStatusUpdated($this->leaveRequest));
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        \Log::error('Failed to send leave status update notification after all retries', [
            'leave_request_id' => $this->leaveRequest->id,
            'user_id' => $this->leaveRequest->user_id,
            'status' => $this->leaveRequest->current_status,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}
