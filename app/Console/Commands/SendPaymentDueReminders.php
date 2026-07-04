<?php

namespace App\Console\Commands;

use App\Models\Payment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendPaymentDueReminders extends Command
{
    protected $signature   = 'payments:send-due-reminders';
    protected $description = 'Notify tenant owners about member payments due today';

    public function handle(): void
    {
        $payments = Payment::with(['member', 'member.tenant'])
            ->whereDate('due_date', today())
            ->where('due_paise', '>', 0)
            ->where('is_partial', true)
            ->where('reminder_sent', false)
            ->where('status', 'active')
            ->get();

        foreach ($payments as $payment) {
            $tenant = $payment->member?->tenant;
            if (!$tenant || !$tenant->owner_email) {
                continue;
            }

            try {
                Mail::send([], [], function ($message) use ($payment, $tenant): void {
                    $due = number_format($payment->due_paise / 100, 2);
                    $message
                        ->to($tenant->owner_email, $tenant->gym_name)
                        ->subject("Payment Due Today â€” {$payment->member->name}")
                        ->html(
                            "<p>Hi,</p>"
                            . "<p><strong>{$payment->member->name}</strong> ({$payment->member->phone}) has a pending payment of "
                            . "<strong>â‚¹{$due}</strong> due today.</p>"
                            . "<p>Receipt: <strong>{$payment->receipt_number}</strong></p>"
                            . "<p>Please follow up and collect the balance amount.</p>"
                            . "<p>â€” {$tenant->gym_name}</p>"
                        );
                });

                $payment->update(['reminder_sent' => true]);
                $this->info("Reminder sent for {$payment->receipt_number} ({$payment->member->name})");
            } catch (\Throwable $e) {
                $this->error("Failed for {$payment->receipt_number}: " . $e->getMessage());
            }
        }

        $this->info("Done. Processed {$payments->count()} payment(s).");
    }
}

