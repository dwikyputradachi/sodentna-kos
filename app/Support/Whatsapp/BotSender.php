<?php

namespace App\Support\Whatsapp;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * Sends one text message through the owner's WhatsApp bot (POST {pesan, nomor}, header x-api-key).
 * Unofficial bot: treat every failure as normal and let the owner fall back to the manual button.
 */
class BotSender
{
    public static function configured(): bool
    {
        return filled(config('whatsapp.bot.url')) && filled(config('whatsapp.bot.key'));
    }

    public function send(string $phone, string $message): SendResult
    {
        if (! self::configured()) return new SendResult(false, 'Bot belum dikonfigurasi di file .env.');

        try {
            $res = Http::withHeaders(['x-api-key' => config('whatsapp.bot.key')])->acceptJson()->asJson()
                ->timeout((int) config('whatsapp.bot.timeout', 10))
                ->post(config('whatsapp.bot.url'), ['pesan' => $message, 'nomor' => $phone]);
        } catch (\Throwable $e) {
            return new SendResult(false, 'Tidak dapat terhubung ke bot (' . class_basename($e) . ').'); // never echo the URL/key
        }

        if (! $res->successful()) {
            return new SendResult(false, 'Bot menjawab HTTP ' . $res->status() . ': ' . Str::limit(trim(strip_tags($res->body())), 120));
        }

        $json = $res->json();
        if (is_array($json)) {
            $flag = $json['success'] ?? $json['status'] ?? null;
            if ($flag === false || (is_string($flag) && in_array(strtolower($flag), ['error', 'failed', 'gagal', 'false'], true))) {
                return new SendResult(false, 'Bot menolak: ' . Str::limit((string) ($json['message'] ?? $json['pesan'] ?? $json['error'] ?? 'tanpa keterangan'), 120));
            }
        }

        return new SendResult(true);
    }
}
