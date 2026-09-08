<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    private ?string $apiUrl;

    private ?string $token;

    public function __construct()
    {
        $this->apiUrl = config('services.whatsapp.url');
        $this->token = config('services.whatsapp.token');
    }

    public function sendNotification(string $phone, string $message): bool
    {
        if (empty($this->apiUrl) || empty($this->token)) {
            Log::warning('WhatsApp API not configured');

            return false;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$this->token,
                'Content-Type' => 'application/json',
            ])->post($this->apiUrl, [
                'phone' => $phone,
                'message' => $message,
            ]);

            if ($response->successful()) {
                Log::info('WhatsApp message sent', ['phone' => $phone]);

                return true;
            }

            Log::error('WhatsApp send failed', ['status' => $response->status()]);

            return false;
        } catch (\Exception $e) {
            Log::error('WhatsApp send error', ['error' => $e->getMessage()]);

            return false;
        }
    }
}
