# How Livefeed works

## The flow

1. You post from the form. The browser sends it with `fetch` to `POST /posts`.
2. `FeedController@store` validates (required, max 280 characters), saves the `Post`, and dispatches `PostCreated`.
3. `PostCreated` implements `ShouldBroadcast`, so Laravel puts a job on the queue instead of doing the network call during the request.
4. The queue worker picks the job up and sends the event to the Reverb server.
5. Reverb pushes it over open WebSocket connections to everyone subscribed to the `feed` channel.
6. In each browser, Echo calls the `.listen('PostCreated')` handler and the post is added to the page.

## The pieces

- `app/Events/PostCreated.php`: the event. `broadcastOn()` picks the channel, `broadcastWith()` picks exactly which data is sent (not the whole model).
- `routes/channels.php`: decides who may join `feed`. A guest is refused (403). A user is allowed, and the returned array becomes the person's entry in the online list.
- `resources/views/feed.blade.php`: the Echo setup. `echo.join('feed')` gives `here` (who is online now), `joining` and `leaving`.
- Presence channels are named `presence-feed` on the wire. Laravel adds the prefix to the name given in the event.
- The browser authorizes through `POST /broadcasting/auth` (session cookie + CSRF token). Reverb never sees the password or session.

## Things I ran into

- Echo's browser file exposes the class as `Echo.default`, not `Echo`.
- Port 8085 was already used by Docker on my machine, so Reverb runs on 8086.
- phpunit uses the `reverb` connection (not `null`), otherwise the channel auth test passes for guests, which hides a real failure.

## Likely interview questions

**Why WebSockets and not polling?**
Polling asks "anything new?" every few seconds, which wastes requests and is always a little late. A WebSocket stays open and the server pushes when something happens.

**Why queue the broadcast?**
Sending to Reverb is a network call. If Reverb is slow or down, the user's request would hang or fail. On the queue the post is already saved and the request returns; failed broadcasts can be retried.

**What is a presence channel?**
A private channel that also tells everyone who is currently in it. Joining needs authorization, and the authorization callback returns the user data that others see.

**How do you stop someone listening to a channel they should not see?**
Private and presence channels call `/broadcasting/auth` first. The callback in `routes/channels.php` decides. A public channel has no check at all, so never put private data on one.

**What does this not handle?**
Missed posts: if a browser is offline when a post is sent, it only gets it on the next page load (the page loads the latest 30 from the database). Scaling to many servers needs Reverb's Redis scaling option. There is no rate limiting on posting.

**How would you scale it?**
Run several Reverb servers behind a load balancer with `REVERB_SCALING_ENABLED` so they share events through Redis, and run more queue workers.
