<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ trans('messages.auth.reset.email_subject') }}</title>
</head>
{{-- Inline styles only: email clients strip <style> blocks and never load web fonts. --}}
<body style="margin:0;padding:24px;background:#f9fafb;color:#111827;font-family:Tahoma,Arial,sans-serif;">
    <div style="max-width:520px;margin:0 auto;background:#ffffff;border-radius:16px;padding:32px;">
        <h1 style="margin:0 0 16px;font-size:18px;font-weight:700;">
            {{ trans('messages.auth.reset.email_subject') }}
        </h1>

        <p style="margin:0 0 16px;font-size:14px;line-height:2;color:#4b5563;">
            {{ trans('messages.auth.reset.email_intro', ['app' => config('app.name')]) }}
        </p>

        <p style="margin:0 0 24px;text-align:center;">
            <a href="{{ $url }}"
               style="display:inline-block;padding:12px 32px;border-radius:12px;background:#ff8615;color:#ffffff;font-size:14px;font-weight:700;text-decoration:none;">
                {{ trans('messages.auth.reset.email_action') }}
            </a>
        </p>

        <p style="margin:0 0 16px;font-size:13px;line-height:2;color:#6b7280;">
            {{ trans('messages.auth.reset.email_expires', ['minutes' => $minutes]) }}
        </p>

        <p style="margin:0 0 16px;font-size:13px;line-height:2;color:#6b7280;">
            {{ trans('messages.auth.reset.email_ignore') }}
        </p>

        <p style="margin:24px 0 0;padding-top:16px;border-top:1px solid #f3f4f6;font-size:12px;line-height:2;color:#9ca3af;word-break:break-all;">
            {{ trans('messages.auth.reset.email_fallback') }}
            <br>
            <span style="direction:ltr;display:inline-block;">{{ $url }}</span>
        </p>
    </div>
</body>
</html>
