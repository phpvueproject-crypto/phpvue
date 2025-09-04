<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{!! csrf_token() !!}">
    <link rel="stylesheet" href="{{ mix('css/app.css') }}">
    <title>{!! config('app.name') !!}</title>
    <!-- Fonts -->
    <link href="/css/css2.css" rel="stylesheet">
</head>
<body id="container">
<div id="app">
    <app></app>
</div>
<script type="text/javascript">
    window.echoPort = '{{ env('ECHO_PORT') }}';
</script>
<script src="/js/socket.io.js"></script>
<script type="text/javascript" src="{{ mix('js/manifest.js') }}"></script>
<script type="text/javascript" src="{{ mix('js/vendor.js') }}"></script>
<script type="text/javascript" src="{{ mix('js/vendor-jquery.js') }}"></script>
<script type="text/javascript" src="{{ mix('js/vendor-vue.js') }}"></script>
<script type="text/javascript" src="{{ mix('js/vendor-boostrap.js') }}"></script>
<script type="text/javascript" src="{{ mix('js/app.js') }}"></script>
</body>
</html>
