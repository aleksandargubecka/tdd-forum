<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CreateThreadsTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function guest_may_not_create_threads()
    {
        $this->withExceptionHandling();

        $this->get('/threads/create')
            ->assertRedirect('/login');

        $this->post('/threads')
            ->assertRedirect('/login');
    }

    /** @test */
    public function guest_cannot_see_the_crate_thread_page()
    {
        $this->withExceptionHandling()->get('threads/create')
            ->assertRedirect('/login');
    }

    /** @test */
    public function an_authenticated_user_can_create_new_forum_threads()
    {
        $this->singIn();

        $thread = make('App\Thread');

        $response = $this->post('/threads', $thread->toArray());

        $this->get($response->headers->get('location'))
            ->assertSee($thread->title)
            ->assertSee($thread->body);
    }

    /** @test */
    public function a_thread_requires_a_title()
    {
        $this->publishThread(['title' => null])->assertSessionHasErrors('title');
    }

    /** @test */
    public function a_thread_requires_a_body()
    {
        $this->publishThread(['body' => null])->assertSessionHasErrors('body');
    }

    /** @test */
    public function a_thread_requires_a_channel()
    {
        factory('App\Channel', 2)->create();

        $this->publishThread(['channel_id' => null])
            ->assertSessionHasErrors('channel_id');

        $this->publishThread(['channel_id' => 9999])
            ->assertSessionHasErrors('channel_id');
    }

    public function publishThread($overrides = [])
    {
        $this->withExceptionHandling()->singIn();

        $thread = make('App\Thread', $overrides);

        return $this->post('/threads', $thread->toArray());
    }
}
