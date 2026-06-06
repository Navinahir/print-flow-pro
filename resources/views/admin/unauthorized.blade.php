<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('admin.unauthorized.title') }} — {{ config('printflow.brand.name', 'XY Cubic Shopee') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: Figtree, ui-sans-serif, system-ui, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(160deg, #0f172a 0%, #1e293b 45%, #334155 100%);
            color: #f8fafc;
            padding: 1.5rem;
        }
        .card {
            max-width: 32rem;
            width: 100%;
            background: rgba(15, 23, 42, 0.85);
            border: 1px solid rgba(148, 163, 184, 0.25);
            border-radius: 1rem;
            padding: 2rem;
            text-align: center;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.45);
        }
        .status {
            display: inline-block;
            font-size: 0.75rem;
            font-weight: 600;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            color: #fbbf24;
            background: rgba(251, 191, 36, 0.12);
            border: 1px solid rgba(251, 191, 36, 0.35);
            border-radius: 9999px;
            padding: 0.25rem 0.75rem;
            margin-bottom: 1.25rem;
        }
        h1 {
            font-size: 1.375rem;
            font-weight: 600;
            line-height: 1.4;
            margin-bottom: 0.75rem;
        }
        p {
            font-size: 0.9375rem;
            line-height: 1.6;
            color: #cbd5e1;
        }
    </style>
</head>
<body>
    <main class="card" role="main">
        <p class="status">{{ __('admin.unauthorized.status') }}</p>
        <h1>{{ __('admin.unauthorized.heading') }}</h1>
        <p>{{ __('admin.unauthorized.message') }}</p>
    </main>
</body>
</html>
