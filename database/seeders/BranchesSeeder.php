<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Member;
use App\Models\Tenant;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BranchesSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $tenant = Tenant::first();

        if (!$tenant) {
            return;
        }

        Branch::where('tenant_id', $tenant->id)->delete();

        $defaultHours = fn(bool $sundayClosed = false) => [
            'mon' => ['open' => '06:00', 'close' => '22:00', 'closed' => false],
            'tue' => ['open' => '06:00', 'close' => '22:00', 'closed' => false],
            'wed' => ['open' => '06:00', 'close' => '22:00', 'closed' => false],
            'thu' => ['open' => '06:00', 'close' => '22:00', 'closed' => false],
            'fri' => ['open' => '06:00', 'close' => '22:00', 'closed' => false],
            'sat' => ['open' => '07:00', 'close' => '20:00', 'closed' => false],
            'sun' => ['open' => '08:00', 'close' => '14:00', 'closed' => $sundayClosed],
        ];

        $branches = [
            [
                'name'         => 'Main Branch',
                'address1'     => '12 Anna Salai, Thousand Lights',
                'address2'     => 'Near Spencer Plaza',
                'city'         => 'Chennai',
                'state'        => 'Tamil Nadu',
                'pin'          => '600002',
                'phone'        => '+914422001234',
                'email'        => 'main@zerogravity.in',
                'manager_name' => 'Sathish Kumar',
                'amenities'    => ['ac', 'locker', 'parking', 'wifi'],
                'status'       => 'active',
                'is_primary'   => true,
                'operating_hours' => $defaultHours(false),
            ],
            [
                'name'         => 'OMR Branch',
                'address1'     => '87 Old Mahabalipuram Road',
                'address2'     => 'Sholinganallur Junction',
                'city'         => 'Chennai',
                'state'        => 'Tamil Nadu',
                'pin'          => '600119',
                'phone'        => '+914422005678',
                'email'        => 'omr@zerogravity.in',
                'manager_name' => null,
                'amenities'    => ['ac', 'locker', 'steam', 'parking'],
                'status'       => 'active',
                'is_primary'   => false,
                'operating_hours' => $defaultHours(true),
            ],
        ];

        $createdBranches = [];
        foreach ($branches as $data) {
            $createdBranches[] = Branch::create([
                ...$data,
                'tenant_id' => $tenant->id,
            ]);
        }

        // Distribute existing members across branches
        $members = Member::where('tenant_id', $tenant->id)->get();
        foreach ($members as $index => $member) {
            $branchIndex = $index % count($createdBranches);
            $member->update(['branch_id' => $createdBranches[$branchIndex]->id]);
        }
    }
}
