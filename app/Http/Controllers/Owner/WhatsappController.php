<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\MessageLog;
use App\Support\CurrentProperty;
use App\Support\Owner\Billing;
use App\Support\Settings;
use App\Support\Whatsapp\AutoMessenger;
use App\Support\Whatsapp\BotSender;
use Illuminate\Http\Request;

class WhatsappController extends Controller
{
    private function pid(): int
    {
        return CurrentProperty::id() ?? abort(403, 'Akun Anda belum terhubung ke properti.');
    }

    public function show()
    {
        $pid = $this->pid();

        return view('owner.whatsapp', [
            'configured' => BotSender::configured(),
            'enabled' => Settings::bool($pid, 'wa_auto'),
            'stages' => AutoMessenger::stages($pid),
            'logs' => MessageLog::orderByDesc('id')->limit(30)->get(),
        ]);
    }

    public function update(Request $r)
    {
        $pid = $this->pid();
        $d = $r->validate(['wa_auto' => ['nullable', 'boolean'], 'stages' => ['nullable', 'array'], 'stages.*' => ['in:reminder,due,overdue,receipt']]);
        $on = $r->boolean('wa_auto');

        if ($on && ! BotSender::configured()) {
            return back()->withErrors(['wa_auto' => 'Bot belum dikonfigurasi. Isi WA_BOT_URL dan WA_BOT_KEY di file .env terlebih dahulu.']);
        }

        // Only events after switching on are sent automatically (no flood of old receipts).
        if ($on && ! Settings::bool($pid, 'wa_auto')) Settings::set($pid, 'wa_auto_since', now()->toDateTimeString());
        Settings::set($pid, 'wa_auto', $on);
        Settings::set($pid, 'wa_stages', array_values($d['stages'] ?? []));

        return back()->with('ok', $on ? 'Pengiriman otomatis diaktifkan.' : 'Pengiriman otomatis dimatikan. Tombol manual tetap bisa dipakai.');
    }

    public function test(Request $r, BotSender $bot)
    {
        $pid = $this->pid();
        $r->merge(['phone' => preg_replace('/[\s\-\.]/', '', (string) $r->input('phone'))]);
        $d = $r->validate(['phone' => ['required', 'regex:/^(\+?62|0)8[0-9]{8,12}$/']]);

        $phone = Billing::phone($d['phone']);
        $body = 'Tes dari Sodentna: pengiriman WhatsApp otomatis berfungsi.';
        $res = $bot->send($phone, $body);

        MessageLog::create(['property_id' => $pid, 'kind' => 'test', 'channel' => 'whatsapp_bot', 'status' => $res->ok ? 'sent' : 'failed',
            'to_phone' => $phone, 'body' => $body, 'error' => $res->error, 'user_id' => $r->user()->id]);

        return back()->with($res->ok ? 'ok' : 'err', $res->ok ? 'Pesan uji terkirim ke ' . $phone . '. Cek WhatsApp di nomor itu.' : 'Pesan uji gagal: ' . $res->error);
    }
}
