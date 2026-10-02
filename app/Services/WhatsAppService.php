<?php

namespace App\Services;

use App\Models\AdminSetting;
use App\Support\NomorHp;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

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
            $phone = NomorHp::normalize($phone);

            $response = match ($this->provider) {
                'fonnte' => $this->sendFonnte($phone, $message),
                'wablas' => $this->sendWablas($this->keFormatInternasional($phone), $message),
                default => $this->sendGeneric($this->keFormatInternasional($phone), $message),
            };

            if ($response->successful()) {
                Log::info('WhatsApp message sent', ['phone' => Str::mask($phone, '*', 4, -3), 'provider' => $this->provider]);

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

    private function keFormatInternasional(string $phone): string
    {
        return str_starts_with($phone, '0') ? '62'.substr($phone, 1) : $phone;
    }

    public function isConnected(): bool
    {
        return ! empty($this->token) && ! empty($this->apiUrl);
    }
}
