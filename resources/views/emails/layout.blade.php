<!DOCTYPE html>
<html lang="{{ $locale }}" dir="{{ $locale === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $subject ?? __('emails.brand') }}</title>
    <link rel="stylesheet" href="{{ asset('css/al-shaheen-fonts.css') }}">
</head>
<body style="margin:0;padding:0;background:#f9f4ef;font-family:{{ $locale === 'ar' ? "'Alexandria', 'Century Gothic', ui-sans-serif, sans-serif" : "'Alexandria', ui-sans-serif, system-ui, sans-serif" }};color:#28414e;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background:#f9f4ef;padding:32px 16px;">
        <tr>
            <td align="center">
                <table width="100%" cellpadding="0" cellspacing="0" style="max-width:600px;background:#ffffff;border-radius:16px;overflow:hidden;border:1px solid #e8e0d8;">
                    <tr>
                        <td style="padding:24px 28px;background:#28414e;color:#ffffff;font-size:20px;font-weight:bold;">
                            {{ __('emails.brand') }}
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:28px;">
                            @isset($greeting)
                                <p style="margin:0 0 16px;font-size:16px;">{{ $greeting }}</p>
                            @endisset

                            @isset($heading)
                                <h1 style="margin:0 0 16px;font-size:22px;line-height:1.4;color:#28414e;">{{ $heading }}</h1>
                            @endisset

                            <div style="font-size:15px;line-height:1.7;color:#5a6a72;white-space:pre-line;">{{ $body }}</div>

                            @if (! empty($actionUrl) && ! empty($actionLabel))
                                <p style="margin:28px 0 0;">
                                    <a href="{{ $actionUrl }}" style="display:inline-block;background:#28414e;color:#ffffff;text-decoration:none;padding:12px 20px;border-radius:10px;font-weight:bold;">
                                        {{ $actionLabel }}
                                    </a>
                                </p>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:18px 28px;background:#faf5f0;font-size:12px;line-height:1.6;color:#8a9aa3;">
                            {{ __('emails.footer') }}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
