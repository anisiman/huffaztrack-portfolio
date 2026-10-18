<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    @stack('styles')
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background: radial-gradient(ellipse at 25% 50%, rgba(124,58,237,0.25) 0%, transparent 55%),
                        radial-gradient(ellipse at 75% 50%, rgba(109,40,217,0.18) 0%, transparent 55%),
                        #0f0b2e;
        }
    </style>
</head>
<body>
    @yield('content')
    @stack('scripts')
</body>
</html>
