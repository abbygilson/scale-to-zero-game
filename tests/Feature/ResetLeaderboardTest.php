<?php

use App\Models\Attendee;
use App\Models\Round;

it('clears the leaderboard but keeps every attendee', function () {
    $player = Attendee::factory()->create();

    Round::factory()->count(3)->for($player)->create();
    Attendee::factory()->create();

    $this->artisan('leaderboard:reset')
        ->expectsOutputToContain('3 rounds deleted')
        ->assertSuccessful();

    expect(Round::count())->toBe(0)
        ->and(Attendee::count())->toBe(2);
});

it('refuses to delete attendees without --force', function () {
    Attendee::factory()->count(2)->create();

    $this->artisan('attendees:reset')->assertFailed();

    expect(Attendee::count())->toBe(2);
});

it('deletes attendees with --force and keeps their rounds on the leaderboard', function () {
    $player = Attendee::factory()->create();
    Round::factory()->for($player)->create();

    $this->artisan('attendees:reset --force')
        ->expectsOutputToContain('1 attendee deleted')
        ->assertSuccessful();

    expect(Attendee::count())->toBe(0)
        ->and(Round::sole()->attendee_id)->toBeNull();
});
