<?php

namespace App\Console\Commands;

use App\Models\Member;
use Illuminate\Console\Command;

class ProcessMemberStatuses extends Command
{
    protected $signature   = 'members:process-statuses';
    protected $description = 'Auto-unfreeze members whose freeze period ended; deactivate members whose membership expired';

    public function handle(): void
    {
        $today = now()->toDateString();

        // Auto-unfreeze: status=frozen AND frozen_until IS NOT NULL AND frozen_until < today
        $unfrozen = Member::where('status', 'frozen')
            ->whereNotNull('frozen_until')
            ->where('frozen_until', '<', $today)
            ->update(['status' => 'active', 'frozen_until' => null]);

        if ($unfrozen > 0) {
            $this->info("Unfrozen {$unfrozen} member(s) whose freeze period ended.");
        }

        // Auto-deactivate: status=active AND expiry_date IS NOT NULL AND expiry_date < today
        $expired = Member::where('status', 'active')
            ->whereNotNull('expiry_date')
            ->where('expiry_date', '<', $today)
            ->update(['status' => 'inactive']);

        if ($expired > 0) {
            $this->info("Deactivated {$expired} member(s) whose membership expired.");
        }

        $this->info('Member status processing complete.');
    }
}
