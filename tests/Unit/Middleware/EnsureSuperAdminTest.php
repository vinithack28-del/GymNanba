<?php

namespace Tests\Unit\Middleware;

use App\Http\Middleware\EnsureSuperAdmin;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class EnsureSuperAdminTest extends TestCase
{
    use RefreshDatabase;

    private EnsureSuperAdmin $middleware;

    protected function setUp(): void
    {
        parent::setUp();
        $this->middleware = new EnsureSuperAdmin;
    }

    public function test_super_admin_passes_through(): void
    {
        $user = User::factory()->create(['role' => 'super_admin']);

        $request = Request::create('/admin/dashboard', 'GET');
        $request->setUserResolver(fn () => $user);

        $response = $this->middleware->handle($request, fn ($req) => response('OK'));

        $this->assertSame('OK', $response->getContent());
    }

    public function test_tenant_owner_is_rejected(): void
    {
        $user = User::factory()->create(['role' => 'tenant_owner']);

        $request = Request::create('/admin/dashboard', 'GET');
        $request->setUserResolver(fn () => $user);

        $this->expectException(HttpException::class);

        $this->middleware->handle($request, fn ($req) => response('OK'));
    }

    public function test_guest_is_rejected(): void
    {
        $request = Request::create('/admin/dashboard', 'GET');
        $request->setUserResolver(fn () => null);

        $this->expectException(HttpException::class);

        $this->middleware->handle($request, fn ($req) => response('OK'));
    }
}
