<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Mi Empresa</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  @vite(['resources/css/app.css', 'resources/js/app.js'])
  <link rel="stylesheet" href="{{ asset('css/site.css') }}">
  <link rel="stylesheet" href="{{ asset('css/site-services.css') }}">
  <link rel="stylesheet" href="{{ asset('css/site-carousel.css') }}">
  <link rel="stylesheet" href="{{ asset('css/site-clients.css') }}">
  <link rel="stylesheet" href="{{ asset('css/site-contact.css') }}">
  <link rel="stylesheet" href="{{ asset('css/theme.css') }}">
  <link rel="stylesheet" href="{{ asset('css/toasts.css') }}">
  <link rel="stylesheet" href="{{ asset('css/site-header.css') }}">



</head>
<body>

  @include('profile.partials.site-header')

  <!-- Espacio para que el header fijo no tape el contenido -->
  <div class="page-offset"></div>

  @include('profile.partials.toasts')
  @yield('content')

  <script src="{{ asset('js/site.js') }}" defer></script>
  <script src="{{ asset('js/site-services.js') }}"></script>
  <script src="{{ asset('js/site-carousel.js') }}"></script>
  <script src="{{ asset('js/toasts.js') }}"></script>
  <script src="{{ asset('js/site-header.js') }}"></script>
</body>
</html>
