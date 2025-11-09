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
        $this->approver->notify(new NewLeaveRequestForApprover($this->leaveRequest));
    }
}
