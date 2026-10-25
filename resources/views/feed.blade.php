@extends('layout')

@section('content')
<div class="catalogue">
    <div class="card filters">
        <h3>Online now</h3>
        <ul id="online" class="muted" style="list-style:none;padding:0"></ul>
        <p id="status" class="muted">Connecting...</p>
    </div>
    <div>
        <form id="post-form" method="post" action="{{ route('posts.store') }}" class="card">
            @csrf
            <textarea name="body" id="body" maxlength="280" rows="2" placeholder="What's happening?" required style="width:100%"></textarea>
            @error('body')<div class="error">{{ $message }}</div>@enderror
            <button class="btn">Post</button>
        </form>
        <div id="posts">
            @foreach ($posts as $post)
                <div class="card post" data-id="{{ $post->id }}">
                    <strong>{{ $post->user->name }}</strong>
                    <span class="muted">{{ $post->created_at->diffForHumans() }}</span>
                    <p>{{ $post->body }}</p>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="/js/pusher.min.js"></script>
<script src="/js/echo.js"></script>
<script>
const token = document.querySelector('meta[name=csrf-token]').content;
const echo = new Echo({
    broadcaster: 'reverb',
    key: '{{ config('broadcasting.connections.reverb.key') }}',
    wsHost: '{{ config('broadcasting.connections.reverb.options.host') }}',
    wsPort: {{ config('broadcasting.connections.reverb.options.port') }},
    forceTLS: false,
    enabledTransports: ['ws'],
    auth: { headers: { 'X-CSRF-TOKEN': token } },
});

const online = document.getElementById('online');
const posts = document.getElementById('posts');
const status = document.getElementById('status');
const people = new Map();

function drawPeople() {
    online.innerHTML = '';
    people.forEach(name => {
        const li = document.createElement('li');
        li.textContent = name;
        online.appendChild(li);
    });
}

function addPost(p) {
    if (posts.querySelector('[data-id="' + p.id + '"]')) return;
    const card = document.createElement('div');
    card.className = 'card post';
    card.dataset.id = p.id;
    const who = document.createElement('strong');
    who.textContent = p.author;
    const when = document.createElement('span');
    when.className = 'muted';
    when.textContent = ' just now';
    const text = document.createElement('p');
    text.textContent = p.body;
    card.append(who, when, text);
    posts.prepend(card);
}

echo.join('feed')
    .here(users => { users.forEach(u => people.set(u.id, u.name)); drawPeople(); })
    .joining(u => { people.set(u.id, u.name); drawPeople(); })
    .leaving(u => { people.delete(u.id); drawPeople(); })
    .listen('PostCreated', addPost);

echo.connector.pusher.connection.bind('state_change', s => {
    status.textContent = s.current === 'connected' ? 'Live' : 'Connection: ' + s.current;
});

document.getElementById('post-form').addEventListener('submit', async e => {
    e.preventDefault();
    const body = document.getElementById('body');
    const res = await fetch(e.target.action, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-Socket-Id': echo.socketId() },
        body: JSON.stringify({ body: body.value }),
    });
    if (res.ok) body.value = '';
});
</script>
@endsection
