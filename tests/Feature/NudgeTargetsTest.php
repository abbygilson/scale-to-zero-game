<?php

use App\Services\WakeTracker;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    Cache::flush();
    $this->freezeTime();

    config()->set('game.wake_cooldown', 90);
    config()->set('game.nudge_after', 180);
    config()->set('game.targets', [
        ['name' => 'One', 'url' => 'https://one.test', 'application_id' => 'app-1', 'environment_id' => 'env-1'],
        ['name' => 'Two', 'url' => 'https://two.test', 'application_id' => 'app-2', 'environment_id' => 'env-2'],
    ]);

    Http::fake();
});

it('wakes only targets idle past the nudge interval and holds them out of play', function () {
    $tracker = app(WakeTracker::class);

    $tracker->markWoken('env-1');
    $tracker->markWoken('env-2');
    $this->travel(179)->seconds();
    $tracker->markWoken('env-2');
    $this->travel(1)->seconds();

    $this->artisan('targets:nudge')->assertSuccessful();

    Http::assertSentCount(1);
    Http::assertSent(fn (Request $request): bool => $request->method() === 'HEAD' && $request->url() === 'https://one.test/wake');

    expect($tracker->isReady('env-1'))->toBeFalse()
        ->and($tracker->secondsUntilReady('env-1'))->toBe(90);
});

it('wakes targets that have never been woken', function () {
    $this->artisan('targets:nudge')->assertSuccessful();

    Http::assertSentCount(2);
});
