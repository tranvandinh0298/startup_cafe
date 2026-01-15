<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Startup_Cafe POS — Main Screen</title>

    <link rel="stylesheet" href="{{ asset('pos/assets/css/base.css') }} ">
    <link rel="stylesheet" href="{{ asset('pos/assets/css/styles.css') }} ">
    <link rel="stylesheet" href="{{ asset('pos/assets/css/responsive.css') }} ">
</head>

<body>
    <div class="pos">
        @yield('content')
    </div>

    <script src="{{ asset('pos/assets/js/styles.js') }}"></script>

    <!-- Page-specific scripts -->
    @stack('scripts')
</body>

</html>
