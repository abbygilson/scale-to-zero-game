<?php

namespace App\Console\Commands;

use App\Services\WakeTracker;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

#[Signature('targets:nudge')]
#[Description('Wake targets that have slept too long, so every round wakes a recently slept app')]
class NudgeTargets extends Command
{
    public function handle(WakeTracker $tracker): int
    {
        foreach (config('game.targets') as $target) {
            $idleSeconds = $tracker->secondsSinceWoken($target['environment_id']);

            if ($idleSeconds !== null && $idleSeconds < config('game.nudge_after')) {
                continue;
            }

            // Start the cooldown before the request, so no round picks this
            // target until it has gone back to sleep.
            $tracker->markWoken($target['environment_id']);

            try {
                Http::timeout(30)->head(rtrim($target['url'], '/').'/wake');
                $this->line("Nudged {$target['name']}.");
            } catch (ConnectionException) {
                $this->warn("Could not reach {$target['name']}.");
            }
        }

        return self::SUCCESS;
    }
}
