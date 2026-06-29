<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title inertia>{{ config('app.name', 'GymNanba') }}</title>

        <script>
            try {
                const lsTheme = localStorage.getItem('gymos-theme') || 'dark';
                document.documentElement.dataset.theme = lsTheme;
            } catch (e) {}
        </script>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @inertiaHead
    </head>
    <body class="antialiased">
        @inertia
    </body>
</html>
