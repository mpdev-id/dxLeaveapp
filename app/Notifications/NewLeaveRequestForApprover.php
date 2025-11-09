<?php

namespace App\Notifications;

use App\Channels\WhatsAppChannel;
use App\Models\LeaveRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class NewLeaveRequestForApprover extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * The leave request instance.
     *
     * @var \App\Models\LeaveRequest
     */
    public $leaveRequest;

    /**
     * Create a new notification instance.
     *
     * @param \App\Models\LeaveRequest $leaveRequest
     */
    public function __construct(LeaveRequest $leaveRequest)
    {
        $this->leaveRequest = $leaveRequest;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return [WhatsAppChannel::class];
    }

    /**
     * Get the WhatsApp representation of the notification.
     *
     * @param  mixed  $notifiable (The approver)
     * @return string
     */
    public function toWhatsApp($notifiable)
    {
        $employee = $this->leaveRequest->user;
        $startDate = $this->leaveRequest->start_date->format('d M Y');
        $endDate = $this->leaveRequest->end_date->format('d M Y');
        $leaveType = $this->leaveRequest->leaveType->name;
        $approverLevel = $this->leaveRequest->currentStep->approverRole->name;    

        $message = "Hi {$notifiable->name},\n\n";
        $message .= "You have a new leave request to review as a {$approverLevel}:\n\n";
        $message .= "Employee: *{$employee->name}*\n";
        $message .= "Type: *{$leaveType}*\n";
        $message .= "Date: *{$startDate}* to *{$endDate}*\n";
        $message .= "Reason: *" . ucfirst($this->leaveRequest->reason) . "*\n\n";
        $message .= "Please log in to the DXLeave system to *Approve* or *Reject* this request.";
        $message .= "\n\nThank you,\nDXLeave System";

        return $message;
    }
}
