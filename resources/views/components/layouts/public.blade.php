<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="ProjectFlow — manage projects and finances seamlessly. Tasks, orders, expenses, reports and team management in one calm workspace.">

    <title>{{ isset($title) ? $title.' · ProjectFlow' : 'ProjectFlow — Projects & Finances, in one flow' }}</title>

    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'><rect width='24' height='24' rx='6' fill='%23111827'/><path d='M7 18V7.5A1.5 1.5 0 0 1 8.5 6H13a4 4 0 0 1 0 8H7' stroke='%23A3E635' stroke-width='2.2' fill='none' stroke-linecap='round'/></svg>">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="min-h-screen bg-canvas antialiased">
    {{ $slot }}
    @stack('scripts')
</body>
</html>
