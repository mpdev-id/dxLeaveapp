<?php

namespace App\Notifications;

use App\Channels\WhatsAppChannel;
use App\Models\LeaveRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

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
        return [WhatsAppChannel::class, WebPushChannel::class];
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
        
        // Safely get approver level with null checks
        $approverLevel = 'Approver';
        if ($this->leaveRequest->currentStep && $this->leaveRequest->currentStep->approverRole) {
            $approverLevel = $this->leaveRequest->currentStep->approverRole->name;
        }

        $message = "[Leave Request - *" . str_replace('_', ' ', ucwords(str_replace('_', ' ', $this->leaveRequest->leave_period))) . "*]\n\n";
        $message .= "Hi {$notifiable->name} as {$approverLevel},\n";


        $message .= "You have a new leave request to review:\n\n";
        $message .= "NIK: *{$employee->employee_code}*\n";
        $message .= "Employee: *{$employee->name}*\n";
        $message .= "Type: *{$leaveType}*\n";
        $message .= "Date: *{$startDate}* to *{$endDate}*\n";
        if ($this->leaveRequest->reason) {
            $message .= "Reason: *" . ucfirst($this->leaveRequest->reason) . "*\n";
        }
        $message .= "\n";
        $message .= "Please log in to the Cutikuy system to *Approve* or *Reject* this request.";
        $message .= "\n\nThank you,\nCutikuy System";

        $payload = ['message' => $message];

        // The accessor on LeaveRequest will provide the full URL.
        if ($this->leaveRequest->supporting_attachment_path) {
            $payload['file'] = $this->leaveRequest->supporting_attachment_path;
        }

        return $payload;
    }

    /**
     * Get the Web Push representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \NotificationChannels\WebPush\WebPushMessage
     */
    public function toWebPush($notifiable, $notification)
    {
        $employee = $this->leaveRequest->user;
        $leaveType = $this->leaveRequest->leaveType->name;
        
        return (new WebPushMessage)
            ->title('New Leave Request')
            ->icon('/images/icons/icon-192x192.png')
            ->body("{$employee->name} has requested {$leaveType} leave.")
            ->action('Review', 'review_request')
            ->data(['url' => '/member/approver-log']);
    }
}
