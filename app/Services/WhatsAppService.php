<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    protected $appKey;
    protected $authKey;
    protected $url;

    public function __construct()
    {
        $this->appKey = config('whatsapp.app_key');
        $this->authKey = config('whatsapp.auth_key');
        $this->url = config('whatsapp.url') . '/create-message';
    }

    /**
     * Send WhatsApp message
     *
     * @param string $phoneNumber
     * @param string $message
     * @return bool
     */
    public function sendMessage($phoneNumber, $message)
    {
        if (!$this->appKey || !$this->authKey) {
            Log::error('WhatsApp service failed. API credentials are not set in config.');
            return false;
        }

        if (!$phoneNumber) {
            Log::warning('Could not send WhatsApp message. Phone number is empty.');
            return false;
        }

        // Ensure phone number format (remove any non-numeric characters except +)
        $phoneNumber = preg_replace('/[^0-9+]/', '', $phoneNumber);

        $requestData = [
            'appkey' => $this->appKey,
            'authkey' => $this->authKey,
            'to' => $phoneNumber,
            'message' => $message,
        ];

        try {
            $response = Http::timeout(30)
                ->connectTimeout(10)
                ->retry(2, 1000)
                ->get($this->url, $requestData);

            if ($response->failed()) {
                Log::error('WhatsApp message failed.', [
                    'phone' => $phoneNumber,
                    'status' => $response->status(),
                    'response' => $response->body(),
                ]);
                return false;
            }

            Log::info('WhatsApp message sent successfully.', [
                'phone' => $phoneNumber,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('WhatsApp message exception.', [
                'phone' => $phoneNumber,
                'error' => $e->getMessage(),
                'type' => get_class($e),
            ]);
            return false;
        }
    }

    /**
     * Send OTP via WhatsApp
     *
     * @param string $phoneNumber
     * @param string $otp
     * @param string $userName
     * @return bool
     */
    public function sendOTP($phoneNumber, $otp, $userName = 'User')
    {
        $message = "Halo {$userName},\n\n";
        $message .= "Kode OTP untuk reset password Anda adalah:\n\n";
        $message .= "🔐 *{$otp}*\n\n";
        $message .= "Kode ini berlaku selama 10 menit.\n";
        $message .= "Jangan bagikan kode ini kepada siapapun.\n\n";
        $message .= "Jika Anda tidak meminta reset password, abaikan pesan ini.\n\n";
        $message .= "Terima kasih,\nTim Cutikuy";

        return $this->sendMessage($phoneNumber, $message);
    }

    /**
     * Send phone number change notification
     *
     * @param string $phoneNumber
     * @param string $userName
     * @param string|null $oldPhone
     * @return bool
     */
    public function sendPhoneChangeNotification($phoneNumber, $userName, $oldPhone = null)
    {
        $message = "Halo {$userName},\n\n";
        $message .= "Nomor WhatsApp Anda telah berhasil diperbarui di sistem Cutikuy.\n\n";
        $message .= "📱 Nomor Baru: {$phoneNumber}\n";
        if ($oldPhone) {
            $message .= "📱 Nomor Lama: {$oldPhone}\n";
        }
        $message .= "\nJika Anda tidak melakukan perubahan ini, segera hubungi administrator.\n\n";
        $message .= "Terima kasih,\nTim Cutikuy";

        return $this->sendMessage($phoneNumber, $message);
    }
}
