<?php

declare(strict_types=1);

use App\Actions\Auth\SendOtpCode;
use App\Contracts\SmsSender;
use App\Sms\LogSmsSender;
use App\Sms\SmsIrSender;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

// The OTP is the only thing standing between a stranger and a customer's
// account, and every send costs real money, so both the delivery call and what
// happens when it fails are worth pinning down.

beforeEach(function (): void {
    Cache::forget(SendOtpCode::key('09120000000'));
    config()->set('services.sms_ir.api_key', 'test-key');
    config()->set('services.sms_ir.template_id', 654321);
    config()->set('services.sms_ir.base_url', 'https://api.sms.ir');
});

function smsIrOk(): void
{
    Http::fake([
        '*/v1/send/verify' => Http::response(['status' => 1, 'message' => 'موفق', 'data' => ['messageId' => 1, 'cost' => 1.0]]),
    ]);
}

it('sends the code to sms.ir send/verify with the api key and template', function (): void {
    smsIrOk();

    $expiresAt = Carbon::parse('2026-08-20 11:05:00', 'UTC');

    expect((new SmsIrSender)->sendVerificationCode('09120000000', '12345', $expiresAt))->toBeTrue();

    Http::assertSent(function (Request $request): bool {
        return $request->url() === 'https://api.sms.ir/v1/send/verify'
            && $request->method() === 'POST'
            && $request->header('x-api-key') === ['test-key']
            && $request['mobile'] === '09120000000'
            && $request['templateId'] === 654321
            // Both placeholders the panel template declares, named exactly as
            // it writes them: #code# and #expireDate#.
            && $request['parameters'] === [
                ['name' => 'code', 'value' => '12345'],
                ['name' => 'expireDate', 'value' => '14:35'],
            ];
    });
});

it('writes the expiry in Tehran time, not the app UTC timezone', function (): void {
    smsIrOk();

    // 11:05 UTC is 14:35 in Tehran. Sending the UTC clock would tell a customer
    // the code died three and a half hours ago.
    (new SmsIrSender)->sendVerificationCode('09120000000', '12345', Carbon::parse('2026-08-20 11:05:00', 'UTC'));

    Http::assertSent(fn (Request $request): bool => collect($request['parameters'])
        ->firstWhere('name', 'expireDate')['value'] === '14:35');
});

it('treats a non-success status in the body as a failure, even on http 200', function (): void {
    // 113 = template not found. The HTTP status is still 200, so only the body
    // reveals that nothing was delivered.
    Http::fake([
        '*/v1/send/verify' => Http::response(['status' => 113, 'message' => 'قالب یافت نشد'], 200),
    ]);

    expect((new SmsIrSender)->sendVerificationCode('09120000000', '12345', now()->addMinutes(2)))->toBeFalse();
});

it('reports failure instead of throwing when the request blows up', function (): void {
    Http::fake(fn () => throw new ConnectionException('timeout'));

    expect((new SmsIrSender)->sendVerificationCode('09120000000', '12345', now()->addMinutes(2)))->toBeFalse();
});

it('refuses to send when sms.ir is not configured', function (): void {
    config()->set('services.sms_ir.api_key', '');
    Http::fake();

    expect((new SmsIrSender)->sendVerificationCode('09120000000', '12345', now()->addMinutes(2)))->toBeFalse();
    Http::assertNothingSent();
});

it('stores the code only once the provider has accepted it', function (): void {
    smsIrOk();

    $code = app(SendOtpCode::class)('09120000000');

    expect($code)->not->toBeNull()
        ->and(Cache::get(SendOtpCode::key('09120000000')))->not->toBeNull();
});

it('stores nothing when the send fails, so the customer can retry at once', function (): void {
    Http::fake(['*/v1/send/verify' => Http::response(['status' => 113], 200)]);

    $code = app(SendOtpCode::class)('09120000000');

    // A cached code here would lock the customer out for the whole TTL waiting
    // for an SMS that was never sent, with no way to ask for another.
    expect($code)->toBeNull()
        ->and(Cache::get(SendOtpCode::key('09120000000')))->toBeNull()
        ->and(app(SendOtpCode::class)->secondsRemaining('09120000000'))->toBe(0);
});

it('does not send a second SMS while a code is still valid', function (): void {
    smsIrOk();
    $send = app(SendOtpCode::class);

    $first = $send('09120000000');
    $second = $send('09120000000');

    // Resending must reuse the live code: a new SMS per click would let anyone
    // burn the account's credit by pressing the button repeatedly.
    expect($second)->toBe($first);
    Http::assertSentCount(1);
});

it('logs instead of sending when no api key is configured', function (): void {
    config()->set('services.sms_ir.api_key', '');

    expect(app(SmsSender::class))->toBeInstanceOf(LogSmsSender::class);
});

it('sends over sms.ir once an api key is configured', function (): void {
    expect(app(SmsSender::class))->toBeInstanceOf(SmsIrSender::class);
});
