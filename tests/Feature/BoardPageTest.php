<?php

use App\Models\Round;
use Livewire\Livewire;

it('renders the board without the guess form', function () {
    $this->get('/board')
        ->assertOk()
        ->assertSee('Scan to play')
        ->assertDontSee('Wake an app');
});

it('shows the closest guesses on the big leaderboard', function () {
    Round::factory()->create(['player_name' => 'Closest', 'delta_ms' => 5]);
    Round::factory()->create(['player_name' => 'Furthest', 'delta_ms' => 5000]);

    Livewire::test('pages::board')
        ->assertSeeInOrder(['Closest', 'Furthest']);
});

it('celebrates only when a new round takes the #1 spot', function () {
    Round::factory()->create(['player_name' => 'Old Leader', 'delta_ms' => 50]);

    $board = Livewire::test('pages::board')
        ->call('checkLeader')
        ->assertNotDispatched('new-leader');

    Round::factory()->create(['player_name' => 'Runner Up', 'delta_ms' => 80]);
    $board->call('checkLeader')->assertNotDispatched('new-leader');

    Round::factory()->create(['player_name' => 'New Leader', 'delta_ms' => 10]);
    $board->call('checkLeader')->assertDispatched('new-leader', name: 'New Leader', deltaMs: 10);
});

it('only feeds fresh rounds into the recent guesses', function () {
    $stale = Round::factory()->create(['created_at' => now()->subMinutes(6)]);
    $fresh = Round::factory()->create(['created_at' => now()]);

    Livewire::test('pages::board')
        ->assertSeeHtml("recent-guess-{$fresh->id}")
        ->assertDontSeeHtml("recent-guess-{$stale->id}");
});
