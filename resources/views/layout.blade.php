<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Livefeed</title>
    <link rel="stylesheet" href="/css/app.css">
</head>
<body>
<div class="nav">
    <a href="/"><strong>Livefeed</strong></a>
    @auth
        <form method="post" action="{{ route('logout') }}" class="row">
            @csrf
            <span class="muted">{{ auth()->user()->name }}</span>
            <button class="link-btn">Log out</button>
        </form>
    @endauth
</div>
<div class="container">
    @yield('content')
</div>
@yield('scripts')
</body>
</html>
