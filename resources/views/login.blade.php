@extends('layout')

@section('content')
<form method="post" action="{{ route('login') }}" class="card" style="max-width:380px;margin:0 auto">
    @csrf
    <h2>Log in</h2>
    <p><input type="email" name="email" value="{{ old('email') }}" placeholder="Email" required style="width:100%"></p>
    @error('email')<div class="error">{{ $message }}</div>@enderror
    <p><input type="password" name="password" placeholder="Password" required style="width:100%"></p>
    <button class="btn">Log in</button>
    <p class="muted">Demo users: asha@, ravi@, meera@example.com / password</p>
</form>
@endsection
