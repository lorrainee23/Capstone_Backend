<?php

namespace App\Mail\Transports;

use Illuminate\Support\Facades\Http;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\MessageConverter;

class MailjetTransport extends AbstractTransport
{
    protected $apiKey;
    protected $secretKey;

    public function __construct($apiKey, $secretKey)
    {
        $this->apiKey = $apiKey;
        $this->secretKey = $secretKey;
    }

    public function __toString(): string
    {
        return 'mailjet';
    }

    protected function doSend(SentMessage $message): void
    {
        $email = MessageConverter::toEmail($message->getOriginalMessage());
        
        $from = $email->getFrom()[0];
        $to = $email->getTo();
        
        $data = [
            'Messages' => [
                [
                    'From' => [
                        'Email' => $from->getAddress(),
                        'Name' => $from->getName() ?: 'POSU Echague'
                    ],
                    'To' => array_map(function($recipient) {
                        return [
                            'Email' => $recipient->getAddress(),
                            'Name' => $recipient->getName() ?: ''
                        ];
                    }, $to),
                    'Subject' => $email->getSubject(),
                    'TextPart' => $email->getTextBody(),
                    'HTMLPart' => $email->getHtmlBody(),
                ]
            ]
        ];

        $response = Http::withBasicAuth($this->apiKey, $this->secretKey)
            ->post('https://api.mailjet.com/v3.1/send', $data);

        if (!$response->successful()) {
            throw new \Exception('Mailjet API error: ' . $response->body());
        }
    }
}
