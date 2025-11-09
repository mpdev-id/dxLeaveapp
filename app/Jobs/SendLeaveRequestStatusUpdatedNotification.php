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
        $this->leaveRequest->user->notify(new LeaveRequestStatusUpdated($this->leaveRequest));
    }
}
