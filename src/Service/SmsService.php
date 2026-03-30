<?php

namespace App\Service;

use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class SmsService
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly LoggerInterface $logger,
        private readonly string $smsApiKey,
        private readonly string $smsApiUrl,
        private readonly string $smsSender,
    ) {}

    public function send(string $phoneNumber, string $message): bool
    {
        if (empty($this->smsApiKey)) {
            $this->logger->warning('SMS API key not configured. SMS not sent.', [
                'phone' => $phoneNumber,
                'message' => $message,
            ]);
            return false;
        }

        try {
            $response = $this->httpClient->request('POST', $this->smsApiUrl, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->smsApiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'to' => $phoneNumber,
                    'from' => $this->smsSender,
                    'message' => $message,
                ],
            ]);

            $statusCode = $response->getStatusCode();
            if ($statusCode >= 200 && $statusCode < 300) {
                $this->logger->info('SMS sent successfully', ['phone' => $phoneNumber]);
                return true;
            }

            $this->logger->error('SMS API returned error', [
                'status' => $statusCode,
                'response' => $response->getContent(false),
            ]);
            return false;
        } catch (\Throwable $e) {
            $this->logger->error('SMS sending failed', [
                'phone' => $phoneNumber,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
}
