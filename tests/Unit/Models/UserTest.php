<?php

namespace Tests\Unit\Models;

use App\Models\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UserTest extends TestCase
{
    #[Test]
    public function is_super_admin_returns_true_for_super_admin_role(): void
    {
        $user = new User;
        $user->role = 'super_admin';

        $this->assertTrue($user->isSuperAdmin());
    }

    #[Test]
    public function is_super_admin_returns_false_for_other_roles(): void
    {
        $user = new User;
        $user->role = 'tenant_owner';

        $this->assertFalse($user->isSuperAdmin());
    }

    #[Test]
    public function is_gym_owner_returns_true_for_tenant_owner_role(): void
    {
        $user = new User;
        $user->role = 'tenant_owner';

        $this->assertTrue($user->isGymOwner());
    }

    #[Test]
    public function is_gym_owner_returns_false_for_other_roles(): void
    {
        $user = new User;
        $user->role = 'super_admin';

        $this->assertFalse($user->isGymOwner());
    }

    #[Test]
    public function is_staff_member_returns_true_for_staff_role(): void
    {
        $user = new User;
        $user->role = 'staff';

        $this->assertTrue($user->isStaffMember());
    }

    #[Test]
    public function is_staff_member_returns_false_for_super_admin(): void
    {
        $user = new User;
        $user->role = 'super_admin';

        $this->assertFalse($user->isStaffMember());
    }

    #[Test]
    public function is_staff_member_returns_false_for_gym_owner(): void
    {
        $user = new User;
        $user->role = 'tenant_owner';

        $this->assertFalse($user->isStaffMember());
    }

    #[Test]
    public function can_access_returns_true_for_super_admin(): void
    {
        $user = new User;
        $user->role = 'super_admin';

        $this->assertTrue($user->canAccess('members.view'));
    }

    #[Test]
    public function can_access_returns_true_for_gym_owner(): void
    {
        $user = new User;
        $user->role = 'tenant_owner';

        $this->assertTrue($user->canAccess('members.view'));
    }

    #[Test]
    public function can_access_returns_false_for_staff_without_permissions(): void
    {
        $user = new User;
        $user->role = 'staff';
        $user->tenant_id = null;

        $this->assertFalse($user->canAccess('members.view'));
    }

    #[Test]
    public function effective_branch_id_returns_null_for_super_admin_without_session(): void
    {
        $user = new User;
        $user->role = 'super_admin';

        $this->assertNull($user->effectiveBranchId());
    }

    #[Test]
    public function effective_branch_id_returns_branch_id_for_staff(): void
    {
        $user = new User;
        $user->role = 'staff';
        $user->branch_id = 42;

        $this->assertSame(42, $user->effectiveBranchId());
    }
}
