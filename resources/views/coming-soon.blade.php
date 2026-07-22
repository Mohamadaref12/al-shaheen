<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>قريباً — {{ config('app.name', 'Al Shaheen 360') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Alexandria:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --ink: #1a2b33;
            --brand: #28414e;
            --brand-soft: #3a5a6b;
            --sand: #e8e2d6;
            --sand-deep: #d4cdc0;
            --paper: #f7f4ef;
            --accent: #c4a35a;
            --danger: #9b2c2c;
            --muted: #6b7a82;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            min-height: 100vh;
            font-family: "Alexandria", sans-serif;
            color: var(--ink);
            background:
                radial-gradient(ellipse 80% 60% at 10% 20%, rgba(196, 163, 90, 0.18), transparent 55%),
                radial-gradient(ellipse 70% 50% at 90% 80%, rgba(40, 65, 78, 0.12), transparent 50%),
                linear-gradient(160deg, var(--paper) 0%, var(--sand) 55%, var(--sand-deep) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }

        .shell {
            width: min(440px, 100%);
            text-align: center;
            animation: rise 0.7s ease-out both;
        }

        @keyframes rise {
            from { opacity: 0; transform: translateY(18px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .logo {
            width: 88px;
            height: auto;
            margin: 0 auto 1.5rem;
            display: block;
            filter: drop-shadow(0 6px 16px rgba(40, 65, 78, 0.18));
            animation: float 4s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-6px); }
        }

        .brand {
            font-size: clamp(1.75rem, 5vw, 2.25rem);
            font-weight: 700;
            color: var(--brand);
            letter-spacing: -0.02em;
            margin-bottom: 0.35rem;
        }

        .eyebrow {
            display: inline-block;
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--accent);
            letter-spacing: 0.12em;
            margin-bottom: 1rem;
            animation: fadeIn 1s ease 0.2s both;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .lead {
            color: var(--muted);
            font-size: 1rem;
            line-height: 1.7;
            margin-bottom: 2rem;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            text-align: right;
        }

        label {
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--brand);
        }

        input[type="password"],
        input[type="text"] {
            width: 100%;
            padding: 0.9rem 1rem;
            border: 1px solid rgba(40, 65, 78, 0.22);
            background: rgba(255, 255, 255, 0.72);
            color: var(--ink);
            font: inherit;
            font-size: 1rem;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        input:focus {
            border-color: var(--brand);
            box-shadow: 0 0 0 3px rgba(40, 65, 78, 0.12);
        }

        button {
            margin-top: 0.35rem;
            padding: 0.95rem 1.25rem;
            border: none;
            background: var(--brand);
            color: #fff;
            font: inherit;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: background 0.2s, transform 0.15s;
        }

        button:hover { background: var(--brand-soft); }
        button:active { transform: scale(0.98); }

        .error {
            color: var(--danger);
            font-size: 0.85rem;
            margin-top: 0.15rem;
        }

        .hint {
            margin-top: 1.75rem;
            font-size: 0.8rem;
            color: var(--muted);
        }
    </style>
</head>
<body>
    <main class="shell">
        <img class="logo" src="{{ asset('al-shaheen.png') }}" alt="{{ config('app.name') }}">
        <p class="eyebrow">قريباً</p>
        <h1 class="brand">{{ config('app.name', 'Al Shaheen 360') }}</h1>
        <p class="lead">نجهّز شيئاً يليق بكم. إن كان لديك مفتاح الوصول، أدخله أدناه للمعاينة.</p>

        <form method="POST" action="{{ route('coming-soon.unlock') }}">
            @csrf
            <label for="key">مفتاح الوصول</label>
            <input
                id="key"
                type="password"
                name="key"
                value="{{ old('key') }}"
                placeholder="أدخل المفتاح"
                autocomplete="off"
                autofocus
                required
            >
            @error('key')
                <p class="error">{{ $message }}</p>
            @enderror
            <button type="submit">فتح الموقع</button>
        </form>

        <p class="hint">بدون المفتاح لن تتمكن من تصفح المحتوى حالياً.</p>
    </main>
</body>
</html>
