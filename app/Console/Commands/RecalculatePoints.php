<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class RecalculatePoints extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'points:recalculate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalculate total_points for all users from their point transactions';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $users = User::query()->withSum('pointTransactions', 'points')->get();

        $bar = $this->output->createProgressBar($users->count());
        $bar->start();

        $updated = 0;

        foreach ($users as $user) {
            $calculatedPoints = (int) $user->point_transactions_sum_points;

            if ($user->total_points !== $calculatedPoints) {
                $user->update(['total_points' => $calculatedPoints]);
                $updated++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        $this->info("Recalculated points for {$users->count()} users. Updated {$updated} users.");

        return Command::SUCCESS;
    }
}
