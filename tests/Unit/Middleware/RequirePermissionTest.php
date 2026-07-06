<?php

namespace Tests\Unit\Middleware;

use App\Http\Middleware\RequirePermission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class RequirePermissionTest extends TestCase
{
    use RefreshDatabase;

    private RequirePermission $middleware;

    protected function setUp(): void
    {
        parent::setUp();
        $this->middleware = new RequirePermission;
    }

    public function test_super_admin_bypasses_all_permissions(): void
    {
        $user = User::factory()->create(['role' => 'super_admin']);

        $request = Request::create('/test', 'GET');
        $request->setUserResolver(fn () => $user);

        $response = $this->middleware->handle($request, fn ($req) => response('OK'), 'members.view');

        $this->assertSame('OK', $response->getContent());
    }

    public function test_gym_owner_bypasses_all_permissions(): void
    {
        $user = User::factory()->create(['role' => 'tenant_owner']);

        $request = Request::create('/test', 'GET');
        $request->setUserResolver(fn () => $user);

        $response = $this->middleware->handle($request, fn ($req) => response('OK'), 'members.view');

        $this->assertSame('OK', $response->getContent());
    }

    public function test_guest_gets_401(): void
    {
        $request = Request::create('/test', 'GET');
        $request->setUserResolver(fn () => null);

        $this->expectException(HttpException::class);

        $this->middleware->handle($request, fn ($req) => response('OK'), 'members.view');
    }

    public function test_owner_only_blocks_non_owners(): void
    {
        $user = User::factory()->create(['role' => 'staff']);

        $request = Request::create('/test', 'GET');
        $request->setUserResolver(fn () => $user);

        $this->expectException(HttpException::class);

        $this->middleware->handle($request, fn ($req) => response('OK'), 'owner_only');
    }

    public function test_owner_only_allows_gym_owner(): void
    {
        $user = User::factory()->create(['role' => 'tenant_owner']);

        $request = Request::create('/test', 'GET');
        $request->setUserResolver(fn () => $user);

        $response = $this->middleware->handle($request, fn ($req) => response('OK'), 'owner_only');

        $this->assertSame('OK', $response->getContent());
    }
}
