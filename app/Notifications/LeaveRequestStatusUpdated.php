<?php

namespace App\Notifications;

use App\Channels\WhatsAppChannel;
use App\Models\LeaveRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

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
    public function __construct(LeaveRequest $leaveRequest, public ?string $reason = null)
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
        $status = $this->leaveRequest->current_status;
        $startDate = $this->leaveRequest->start_date->format('d M Y');
        $endDate = $this->leaveRequest->end_date->format('d M Y');
        $leaveType = $this->leaveRequest->leaveType->name;

        $message = "Hi {$notifiable->name},\n\n";
        $message .= "There is an update on your leave request:\n\n";
        $message .= "Type: *{$leaveType}*\n";
        $message .= "Date: *{$startDate}* to *{$endDate}*\n";
        if ($this->leaveRequest->reason) {
            $message .= "Your Reason: *{$this->leaveRequest->reason}*\n";
        }
        $message .= "Status: *{$status}*\n\n";

        switch ($status) {
            case 'Approved':
                $message .= "Your leave has been approved. Enjoy your time off!";
                break;
            case 'Rejected':
                $message .= "Unfortunately, your leave request has been rejected.";
                if ($this->reason) {
                    $message .= "\nReason for Rejection: *{$this->reason}*";
                }
                break;
            case 'In Progress':
                if ($this->leaveRequest->currentStep && $this->leaveRequest->currentStep->approverRole) {
                    $approverLevel = $this->leaveRequest->currentStep->approverRole->name;
                    $message .= "Your request has been approved by the previous approver and is now pending approval from the *{$approverLevel}*.";
                } else {
                    $message .= "Your request is in progress and pending approval.";
                }
                break;
            case 'Pending':
                $message .= "Your leave request has been successfully submitted and is now pending approval.";
                break;
            default:
                // Fallback for other statuses
                if ($this->leaveRequest->currentStep && $this->leaveRequest->currentStep->approverRole) {
                    $approverLevel = $this->leaveRequest->currentStep->approverRole->name;
                    $message .= "The status of your leave request has been updated to '*{$status}*' by your '*{$approverLevel}*'.";
                } else {
                    $message .= "The status of your leave request has been updated to '*{$status}*'.";
                }
                break;
        }

        $message .= "\n\nThank you,\nCutikuy System";

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
        $status = $this->leaveRequest->current_status;
        $leaveType = $this->leaveRequest->leaveType->name;
        
        $title = "Leave Request Update";
        $body = "Your {$leaveType} request is now {$status}.";

        if ($status === 'Approved') {
            $body = "Your {$leaveType} request has been approved! 🎉";
        } elseif ($status === 'Rejected') {
            $body = "Your {$leaveType} request was rejected.";
        }

        return (new WebPushMessage)
            ->title($title)
            ->icon('/images/icons/icon-192x192.png')
            ->body($body)
            ->action('View Request', 'view_request')
            ->data(['url' => '/member/leaves']);
    }
}
