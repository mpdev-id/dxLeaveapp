<?php

namespace App\Notifications;

use App\Channels\WhatsAppChannel;
use App\Models\LeaveRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class LeaveRequestStatusUpdated extends Notification implements ShouldQueue
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
     * @param  mixed  $notifiable
     * @return string
     */
    public function toWhatsApp($notifiable)
    {
        $status = $this->leaveRequest->current_status;
        $startDate = $this->leaveRequest->start_date->format('d M Y');
        $endDate = $this->leaveRequest->end_date->format('d M Y');
        $leaveType = $this->leaveRequest->leaveType->name;

        $message = "Hi {$notifiable->name},\n\n";
        $message .= "There is an update on your leave request:\n\n";
        $message .= "Type: *{$leaveType}*\n";
        $message .= "Date: *{$startDate}* to *{$endDate}*\n";
        $message .= "Status: *{$status}*\n\n";

        switch ($status) {
            case 'Approved':
                $message .= "Your leave has been approved. Enjoy your time off!";
                break;
            case 'Rejected':
                $message .= "Unfortunately, your leave request has been rejected. Please contact your manager for details.";
                break;
            case 'Pending':
                $message .= "Your leave request has been successfully submitted and is now pending approval.";
                break;
            default:
                $message .= "The status of your leave request has been updated to '{$status}'.";
                break;
        }

        $message .= "\n\nThank you,\nDXLeave System";

        return $message;
    }
}
