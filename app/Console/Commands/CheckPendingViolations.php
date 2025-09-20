<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Transaction;
use App\Models\Notification;
use App\Mail\POSUEmail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

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
                    // Notify Violator
                    Notification::create([
                        'sender_id'      => null,
                        'sender_role'    => 'System',
                        'target_type'    => 'Violator',
                        'violator_id'    => $violator->id,
                        'transaction_id' => $transaction->id,
                        'title'          => 'Payment Reminder',
                        'message'        => "Your violation (Ticket #{$transaction->ticket_number}) is unpaid for {$daysPending} days. Please settle to avoid legal action.",
                        'type'           => 'reminder',
                    ]);

                    // Send email reminder if violator has email
                    if ($violator->email) {
                        try {
                            $violatorName = trim($violator->first_name . ' ' . ($violator->middle_name ? $violator->middle_name . ' ' : '') . $violator->last_name);
                            $vehicleInfo = $transaction->vehicle ? $transaction->vehicle->make . ' ' . $transaction->vehicle->model . ' (' . $transaction->vehicle->color . ')' : 'N/A';
                            
                            Mail::to($violator->email)->send(
                                new POSUEmail('payment_reminder', [
                                    'violator_name' => $violatorName,
                                    'ticket_number' => $transaction->ticket_number ?? 'CT-' . date('Y') . '-' . str_pad($transaction->id, 6, '0', STR_PAD_LEFT),
                                    'violation_type' => $transaction->violation->name,
                                    'fine_amount' => $transaction->fine_amount,
                                    'days_pending' => $daysPending,
                                    'violation_date' => $transaction->date_time->format('F j, Y'),
                                    'location' => $transaction->location,
                                    'license_number' => $violator->license_number,
                                    'vehicle_info' => $vehicleInfo,
                                    'plate_number' => $transaction->vehicle ? $transaction->vehicle->plate_number : 'N/A',
                                    'reminder_type' => '3-day',
                                ])
                            );
                        } catch (\Exception $emailError) {
                            Log::error('Failed to send 3-day reminder email: ' . $emailError->getMessage());
                        }
                    }

                    // Notify Management
                    Notification::create([
                        'sender_id'      => null,
                        'sender_role'    => 'System',
                        'target_type'    => 'Management',
                        'transaction_id' => $transaction->id,
                        'title'          => 'Violation Reminder Sent',
                        'message'        => "{$violator->first_name} {$violator->last_name} has an unpaid violation (Ticket #{$transaction->ticket_number}) for {$daysPending} days. Reminder sent.",
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
                    // Notify Violator
                    Notification::create([
                        'sender_id'      => null,
                        'sender_role'    => 'System',
                        'target_type'    => 'Violator',
                        'violator_id'    => $violator->id,
                        'transaction_id' => $transaction->id,
                        'title'          => 'Payment Warning',
                        'message'        => "Your violation (Ticket #{$transaction->ticket_number}) is unpaid for {$daysPending} days. Immediate action required!",
                        'type'           => 'warning',
                    ]);

                    // Send email warning if violator has email
                    if ($violator->email) {
                        try {
                            $violatorName = trim($violator->first_name . ' ' . ($violator->middle_name ? $violator->middle_name . ' ' : '') . $violator->last_name);
                            $vehicleInfo = $transaction->vehicle ? $transaction->vehicle->make . ' ' . $transaction->vehicle->model . ' (' . $transaction->vehicle->color . ')' : 'N/A';
                            
                            Mail::to($violator->email)->send(
                                new POSUEmail('payment_reminder', [
                                    'violator_name' => $violatorName,
                                    'ticket_number' => $transaction->ticket_number ?? 'CT-' . date('Y') . '-' . str_pad($transaction->id, 6, '0', STR_PAD_LEFT),
                                    'violation_type' => $transaction->violation->name,
                                    'fine_amount' => $transaction->fine_amount,
                                    'days_pending' => $daysPending,
                                    'violation_date' => $transaction->date_time->format('F j, Y'),
                                    'location' => $transaction->location,
                                    'license_number' => $violator->license_number,
                                    'vehicle_info' => $vehicleInfo,
                                    'plate_number' => $transaction->vehicle ? $transaction->vehicle->plate_number : 'N/A',
                                    'reminder_type' => '5-day',
                                ])
                            );
                        } catch (\Exception $emailError) {
                            Log::error('Failed to send 5-day warning email: ' . $emailError->getMessage());
                        }
                    }

                    // ✅ Notify Management
                    Notification::create([
                        'sender_id'      => null,
                        'sender_role'    => 'System',
                        'target_type'    => 'Management',
                        'transaction_id' => $transaction->id,
                        'title'          => 'Payment Warning Issued',
                        'message'        => "{$violator->first_name} {$violator->last_name} has not paid Ticket #{$transaction->ticket_number} for {$daysPending} days. Warning issued.",
                        'type'           => 'warning',
                    ]);
                }
            }

            // --- 7-day court filing ---
            if ($daysPending >= 7 && !$transaction->court_filed_at) {
                $transaction->court_filed_at = $now;
                $transaction->save();

                // Notify Violator
                Notification::create([
                    'sender_id'      => null,
                    'sender_role'    => 'System',
                    'target_type'    => 'Violator',
                    'violator_id'    => $violator->id,
                    'transaction_id' => $transaction->id,
                    'title'          => 'Court Case Filed',
                    'message'        => "Your violation (Ticket #{$transaction->ticket_number}) has been escalated to court due to non-payment.",
                    'type'           => 'alert',
                ]);

                // Send email alert if violator has email
                if ($violator->email) {
                    try {
                        $violatorName = trim($violator->first_name . ' ' . ($violator->middle_name ? $violator->middle_name . ' ' : '') . $violator->last_name);
                        $vehicleInfo = $transaction->vehicle ? $transaction->vehicle->make . ' ' . $transaction->vehicle->model . ' (' . $transaction->vehicle->color . ')' : 'N/A';
                        
                        Mail::to($violator->email)->send(
                            new POSUEmail('payment_reminder', [
                                'violator_name' => $violatorName,
                                'ticket_number' => $transaction->ticket_number ?? 'CT-' . date('Y') . '-' . str_pad($transaction->id, 6, '0', STR_PAD_LEFT),
                                'violation_type' => $transaction->violation->name,
                                'fine_amount' => $transaction->fine_amount,
                                'days_pending' => $daysPending,
                                'violation_date' => $transaction->date_time->format('F j, Y'),
                                'location' => $transaction->location,
                                'license_number' => $violator->license_number,
                                'vehicle_info' => $vehicleInfo,
                                'plate_number' => $transaction->vehicle ? $transaction->vehicle->plate_number : 'N/A',
                                'reminder_type' => '7-day',
                            ])
                        );
                    } catch (\Exception $emailError) {
                        Log::error('Failed to send 7-day court filing email: ' . $emailError->getMessage());
                    }
                }

                // Notify Management
                Notification::create([
                    'sender_id'      => null,
                    'sender_role'    => 'System',
                    'target_type'    => 'Management',
                    'transaction_id' => $transaction->id,
                    'title'          => 'Court Case Filed',
                    'message'        => "{$violator->first_name} {$violator->last_name}'s violation (Ticket #{$transaction->ticket_number}) has been escalated to court after {$daysPending} days of non-payment.",
                    'type'           => 'alert',
                ]);
            }
        }

        $this->info('Pending violations checked successfully.');
    }
}
