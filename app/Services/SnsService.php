<?php

namespace App\Services;

use Aws\Sns\SnsClient;
use Aws\Exception\AwsException;

class SnsService
{
    private $snsClient;

    public function __construct()
    {
        $this->snsClient = new SnsClient([
            'version' => 'latest',
            'region'  => env('AWS_DEFAULT_REGION'),
            'credentials' => [
                'key'    => env('AWS_ACCESS_KEY_ID'),
                'secret' => env('AWS_SECRET_ACCESS_KEY'),
            ],
        ]);
    }

    public function sendSms($phoneNumber, $message)
    {
        try {
            $result = $this->snsClient->publish([
                'Message' => $message,
                'PhoneNumber' => $phoneNumber,
                'MessageAttributes' => [
                    'AWS.SNS.SMS.SenderID' => [
                        'DataType' => 'String',
                        'StringValue' => 'MoFlawers', // اسم الموقع الخاص بك
                    ],
                    'AWS.SNS.SMS.SMSType' => [
                        'DataType' => 'String',
                        'StringValue' => 'Transactional', // أو 'Promotional'
                    ],
                ],
            ]);
            return $result;
        } catch (AwsException $e) {
            // التعامل مع الأخطاء
            return $e->getMessage();
        }
    }
}


// in controller :

// use App\Services\SnsService;

// $snsService = new SnsService();
// $response = $snsService->publish('your-topic-arn', 'Hello, this is a test message!');
