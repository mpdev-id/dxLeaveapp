<?php

namespace App\Notifications;

use App\Channels\WhatsAppChannel;
use App\Models\LeaveRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class LeaveRequestCreated extends Notification implements ShouldQueue
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
     * @param  mixed  $notifiable
     * @return string
     */
    public function toWhatsApp($notifiable)
    {
        $startDate = $this->leaveRequest->start_date->format('d M Y');
        $endDate = $this->leaveRequest->end_date->format('d M Y');
        $leaveType = $this->leaveRequest->leaveType->name;
        $duration = $this->leaveRequest->duration;
        $leavePeriod = str_replace('_', ' ', ucwords(str_replace('_', ' ', $this->leaveRequest->leave_period)));

        $message = "Hi {$notifiable->name},\n\n";
        $message .= "Your leave request has been successfully submitted! ✅\n\n";
        $message .= "📋 *Leave Request Details:*\n";
        $message .= "Type: *{$leaveType}*\n";
        $message .= "Period: *{$leavePeriod}*\n";
        $message .= "Date: *{$startDate}* to *{$endDate}*\n";
        $message .= "Duration: *{$duration} day(s)*\n";
        
        if ($this->leaveRequest->reason) {
            $message .= "Reason: *{$this->leaveRequest->reason}*\n";
        }
        
        $message .= "Status: *Pending Approval*\n\n";

        // Get approver info if available
        if ($this->leaveRequest->currentStep && $this->leaveRequest->currentStep->approverRole) {
            $approverLevel = $this->leaveRequest->currentStep->approverRole->name;
            $message .= "Your request is now pending approval from the *{$approverLevel}*.\n\n";
        } else {
            $message .= "Your request is now pending approval.\n\n";
        }

        $message .= "You will be notified once your request has been reviewed.\n\n";
        $message .= "Thank you,\nCutikuy System";

        return $message;
    }

    /**
     * Get the Web Push representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \NotificationChannels\WebPush\WebPushMessage
     */
    public function toWebPush($notifiable, $notification)
    {
        $leaveType = $this->leaveRequest->leaveType->name;
        $duration = $this->leaveRequest->duration;
        
        $title = "Leave Request Submitted ✅";
        $body = "Your {$leaveType} request ({$duration} day(s)) has been submitted and is pending approval.";

        return (new WebPushMessage)
            ->title($title)
            ->icon('/images/icons/icon-192x192.png')
            ->body($body)
            ->badge('/images/icons/icon-72x72.png')
            ->action('View Request', 'view_request')
            ->data([
                'url' => '/member/leaves',
                'leave_request_id' => $this->leaveRequest->id,
                'status' => 'Pending',
            ]);
    }
}
