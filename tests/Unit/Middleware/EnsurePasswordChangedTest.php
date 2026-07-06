<?php

namespace Tests\Unit\Middleware;

use App\Http\Middleware\EnsurePasswordChanged;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class EnsurePasswordChangedTest extends TestCase
{
    use RefreshDatabase;

    private EnsurePasswordChanged $middleware;

    protected function setUp(): void
    {
        parent::setUp();
        $this->middleware = new EnsurePasswordChanged;
    }

    public function test_user_who_must_change_password_is_redirected(): void
    {
        $user = User::factory()->create(['must_change_password' => true]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertRedirect();
    }

    public function test_user_who_already_changed_password_passes(): void
    {
        $user = User::factory()->create(['must_change_password' => false]);

        $request = Request::create('/dashboard', 'GET');
        $request->setUserResolver(fn () => $user);

        $response = $this->middleware->handle($request, fn ($req) => response('OK'));

        $this->assertSame('OK', $response->getContent());
    }

    public function test_guest_passes_through(): void
    {
        $request = Request::create('/login', 'GET');
        $request->setUserResolver(fn () => null);

        $response = $this->middleware->handle($request, fn ($req) => response('OK'));

        $this->assertSame('OK', $response->getContent());
    }
}
