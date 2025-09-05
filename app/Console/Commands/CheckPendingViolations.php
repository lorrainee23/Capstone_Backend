<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Transaction;
use App\Models\Notification;
use App\Models\Violator;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CheckPendingViolations extends Command
{
    protected $signature = 'violations:check-pending';
    protected $description = 'Check pending violations and send reminders, warnings, court filings, or license suspensions';

    public function handle()
    
    {   
        $now = Carbon::now();
        $transactions = Transaction::where('status', 'Pending')
            ->where('date_time', '<=', $now)
            ->with('violator')
            ->get();

        foreach ($transactions as $transaction) {
            $violator = $transaction->violator;

            if (!$violator) {
                continue;
            }

            $daysPending = Carbon::parse($transaction->date_time)->diffInDays($now);

            // --- 3-day reminder ---
            if ($daysPending >= 3 && $daysPending < 5) {
                if (!Notification::where('transaction_id', $transaction->id)
                    ->where('type', 'reminder')
                    ->exists()
                ) {
                    Notification::create([
                        'sender_id'      => null,
                        'sender_role'    => 'System',
                        'target_role'    => 'Violator',
                        'violator_id'    => $violator->id,
                        'transaction_id' => $transaction->id,
                        'title'          => 'Payment Reminder',
                        'message'        => "Your violation (Ticket #{$transaction->ticket_number}) is unpaid for {$daysPending} days. Please settle to avoid legal action.",
                        'type'           => 'reminder',
                    ]);
                }
            }

            // --- 5-day warning ---
            if ($daysPending >= 5 && $daysPending < 7) {
                if (!Notification::where('transaction_id', $transaction->id)
                    ->where('type', 'warning')
                    ->exists()
                ) {
                    Notification::create([
                        'sender_id'      => null,
                        'sender_role'    => 'System',
                        'target_role'    => 'Violator',
                        'violator_id'    => $violator->id,
                        'transaction_id' => $transaction->id,
                        'title'          => 'Payment Warning',
                        'message'        => "Your violation (Ticket #{$transaction->ticket_number}) is unpaid for {$daysPending} days. Immediate action required!",
                        'type'           => 'warning',
                    ]);
                }
            }

            // --- 7-day court filing ---
            if ($daysPending >= 7 && !$transaction->court_filed_at) {
                $transaction->court_filed_at = $now;
                $transaction->save();

                Notification::create([
                    'sender_id'      => null,
                    'sender_role'    => 'System',
                    'target_role'    => 'Violator',
                    'violator_id'    => $violator->id,
                    'transaction_id' => $transaction->id,
                    'title'          => 'Court Case Filed',
                    'message'        => "Your violation (Ticket #{$transaction->ticket_number}) has been escalated to court due to non-payment.",
                    'type'           => 'alert',
                ]);
            }
        }
$this->info('Pending violations checked successfully.');
    }
}
