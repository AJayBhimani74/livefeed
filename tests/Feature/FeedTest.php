<?php

namespace Tests\Feature;

use App\Events\PostCreated;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class FeedTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_sent_to_login(): void
    {
        $this->get('/')->assertRedirect('/login');
        $this->post('/posts', ['body' => 'hi'])->assertRedirect('/login');
    }

    public function test_login_with_wrong_password_fails(): void
    {
        $user = User::factory()->create();

        $this->post('/login', ['email' => $user->email, 'password' => 'nope'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_posting_saves_and_broadcasts_on_the_feed_channel(): void
    {
        Event::fake([PostCreated::class]);
        $user = User::factory()->create();

        $this->actingAs($user)->postJson('/posts', ['body' => 'First!'])->assertCreated();

        $this->assertDatabaseHas('posts', ['user_id' => $user->id, 'body' => 'First!']);
        Event::assertDispatched(PostCreated::class, function ($event) {
            return $event->broadcastOn()[0]->name === 'presence-feed'
                && $event->broadcastWith()['body'] === 'First!';
        });
    }

    public function test_body_is_required_and_limited_to_280_characters(): void
    {
        Event::fake([PostCreated::class]);
        $user = User::factory()->create();

        $this->actingAs($user)->postJson('/posts', ['body' => ''])->assertJsonValidationErrors('body');
        $this->actingAs($user)->postJson('/posts', ['body' => str_repeat('a', 281)])->assertJsonValidationErrors('body');

        Event::assertNotDispatched(PostCreated::class);
        $this->assertDatabaseCount('posts', 0);
    }

    public function test_feed_shows_newest_posts_first(): void
    {
        $user = User::factory()->create();
        Post::factory()->create(['user_id' => $user->id, 'body' => 'older one']);
        Post::factory()->create(['user_id' => $user->id, 'body' => 'newer one']);

        $this->actingAs($user)->get('/')->assertSeeInOrder(['newer one', 'older one']);
    }

    public function test_channel_auth_requires_login_and_returns_user_info(): void
    {
        $this->postJson('/broadcasting/auth', ['socket_id' => '1.1', 'channel_name' => 'presence-feed'])
            ->assertForbidden();

        $user = User::factory()->create();
        $this->actingAs($user)
            ->postJson('/broadcasting/auth', ['socket_id' => '1.1', 'channel_name' => 'presence-feed'])
            ->assertOk()
            ->assertJsonStructure(['auth', 'channel_data']);
    }
}
