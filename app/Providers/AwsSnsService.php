<?php

namespace App\Providers;

use Aws\Sns\SnsClient;
use Illuminate\Support\Facades\Log;

class AwsSnsService
{
    private $snsClient;

    public function __construct()
    {
        $this->snsClient = new SnsClient([
            'version' => 'latest',
            'region' => config('services.sns.region'),
            'credentials' => [
                'key' => config('services.sns.key'),
                'secret' => config('services.sns.secret'),
            ],
        ]);
    }


    public function sendSms($phoneNumber, $message)
    {
        try {
            $result = $this->snsClient->publish([
                'Message' => $message,
                'PhoneNumber' => $phoneNumber,
            ]);

            Log::info('SMS sent successfully', [
                'message_id' => $result->get('MessageId'),
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Error sending SMS', [
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
