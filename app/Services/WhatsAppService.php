<?php

namespace App\Services;

use App\Models\AdminSetting;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    private ?string $apiUrl;

    private ?string $token;

    private ?string $provider;

    public function __construct()
    {
        $this->provider = AdminSetting::get('wa_provider') ?: config('services.whatsapp.provider');
        $this->apiUrl = $this->resolveApiUrl();
        $this->token = AdminSetting::get('wa_api_key') ?: config('services.whatsapp.token');
    }

    public function sendNotification(string $phone, string $message): bool
    {
        if (empty($this->apiUrl) || empty($this->token)) {
            Log::warning('WhatsApp API not configured');

            return false;
        }

        try {
            $response = match ($this->provider) {
                'fonnte' => $this->sendFonnte($phone, $message),
                'wablas' => $this->sendWablas($phone, $message),
                default => $this->sendGeneric($phone, $message),
            };

            if ($response->successful()) {
                Log::info('WhatsApp message sent', ['phone' => $phone, 'provider' => $this->provider]);

                return true;
            }

            Log::error('WhatsApp send failed', ['status' => $response->status(), 'provider' => $this->provider]);

            return false;
        } catch (\Exception $e) {
            Log::error('WhatsApp send error', ['error' => $e->getMessage()]);

            return false;
        }
    }

    private function resolveApiUrl(): ?string
    {
        return match ($this->provider) {
            'fonnte' => AdminSetting::get('wa_api_url') ?: config('services.whatsapp.url'),
            'wablas' => AdminSetting::get('wa_api_url') ?: config('services.whatsapp.url'),
            default => config('services.whatsapp.url'),
        };
    }

    private function sendFonnte(string $phone, string $message): Response
    {
        return Http::withHeaders([
            'Authorization' => $this->token,
            'Content-Type' => 'application/json',
        ])->post($this->apiUrl, [
            'target' => $phone,
            'message' => $message,
        ]);
    }

    private function sendWablas(string $phone, string $message): Response
    {
        return Http::withHeaders([
            'Authorization' => $this->token,
            'Content-Type' => 'application/json',
        ])->post($this->apiUrl, [
            'phone' => $phone,
            'message' => $message,
        ]);
    }

    private function sendGeneric(string $phone, string $message): Response
    {
        return Http::withHeaders([
            'Authorization' => 'Bearer '.$this->token,
            'Content-Type' => 'application/json',
        ])->post($this->apiUrl, [
            'phone' => $phone,
            'message' => $message,
        ]);
    }

    public function isConnected(): bool
    {
        return ! empty($this->token) && ! empty($this->apiUrl);
    }
}
