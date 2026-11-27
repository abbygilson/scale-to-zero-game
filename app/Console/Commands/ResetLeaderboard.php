<?php

namespace App\Console\Commands;

use App\Models\Round;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('leaderboard:reset')]
#[Description('Delete all rounds so the leaderboard starts fresh, keeping the attendee list')]
class ResetLeaderboard extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $roundsDeleted = Round::query()->delete();

        $this->info("Leaderboard reset — {$roundsDeleted} ".str('round')->plural($roundsDeleted).' deleted. Attendees were kept.');

        return self::SUCCESS;
    }
}
