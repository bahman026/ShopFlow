<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title') - {{ config('app.name') }}</title>
    <style>
        @font-face {
            font-family: 'A Iranian Sans';
            src: url('/fonts/AIranianSans.ttf') format('truetype');
            font-display: swap;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f9fafb;
            color: #111827;
            font-family: 'A Iranian Sans', Tahoma, sans-serif;
        }

        .card {
            text-align: center;
            padding: 48px 24px;
        }

        .code {
            font-size: 96px;
            font-weight: 800;
            line-height: 1;
            color: #ff8615;
        }

        .title {
            margin-top: 16px;
            font-size: 24px;
            font-weight: 700;
        }

        .desc {
            margin-top: 12px;
            color: #6b7280;
        }

        .btn {
            display: inline-block;
            margin-top: 24px;
            padding: 10px 24px;
            border-radius: 10px;
            background: #ff8615;
            color: #fff;
            font-weight: 600;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="code">@yield('code')</div>
        <h1 class="title">@yield('title')</h1>
        <p class="desc">@yield('message')</p>
        <a class="btn" href="/">بازگشت به خانه</a>
    </div>
</body>
</html>
