<?php

namespace App\Console\Commands;

use App\Models\Attendee;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('attendees:reset {--force : Confirm that every stored email should be deleted}')]
#[Description('Delete every stored attendee name and email (requires --force)')]
class ResetAttendees extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if (! $this->option('force')) {
            $this->error('This deletes every stored attendee email. Export the CSV first, then run: php artisan attendees:reset --force');

            return self::FAILURE;
        }

        $attendeesDeleted = Attendee::query()->delete();

        $this->info("Attendee list reset — {$attendeesDeleted} ".str('attendee')->plural($attendeesDeleted).' deleted.');

        return self::SUCCESS;
    }
}
