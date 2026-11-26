# Guess the Scale to Zero

A conference booth game for Laravel Cloud. Visitors enter their name and email, guess how many milliseconds a sleeping Laravel Cloud app takes to wake, and then the game wakes one and times it. The closest guess wins.

## Pages

| Path | Purpose |
| --- | --- |
| `/` | The game. Phones get one guess each. Booth screens reset for the next player 10 seconds after a result. |
| `/board` | The booth TV: the top 10 leaderboard and a feed of recent guesses. |
| `/organizers/attendees` | The attendee list and CSV export. The URL is signed; generate it with `php artisan attendees:url`. |

## How a round works

1. The game picks, among targets idle for at least the wake cooldown (150 seconds by default, `GAME_WAKE_COOLDOWN`), the one that has been asleep the longest, so rounds rotate evenly across the targets. It records the time it was woken.
2. The game **server** sends a `HEAD` request to the target's `/wake` route and times it from sending the request to the first response byte, leaving out DNS, TCP, and TLS setup. This is the **cold** time. Timing from the server, which runs in the same region as the targets, keeps the venue network out of the number.
3. It then sends three warm `HEAD` requests and keeps the fastest. This is the **baseline**.
4. The **wake time** is cold minus baseline. This is the number players guess against. A wake over 2 seconds (`GAME_MAX_WAKE_MS`) is voided as a network hiccup instead of scored.
5. The phone counts its stopwatch up to the result and loads the target's home page in the background as a preview. That load is not timed.

A scheduled `targets:nudge` command wakes any target idle for 5 minutes (`GAME_NUDGE_AFTER`), because an app that sleeps much longer wakes noticeably slower. The game app needs the scheduler turned on in Cloud for this.

The Laravel Cloud API does not reliably report when an environment has scaled to zero, so the game tracks the cooldown itself instead of asking the API. The API status check is off by default (`GAME_CLOUD_STATUS_ENABLED`).

## Targets

The targets are five Laravel Cloud apps in the **Abby Gilson** organization, region **eu-central-1 (Frankfurt)**, set up for WAWTech in Warsaw. They are configured in [`config/game.php`](config/game.php).

| Name | Cloud application | URL |
| --- | --- | --- |
| Frankfurt 1 | `scale-to-zero-target-1` | https://scale-to-zero-target-1-production-xaiici.laravel.cloud |
| Frankfurt 2 | `scale-to-zero-target-2` | https://scale-to-zero-target-2-production-r1t1s6.laravel.cloud |
| Frankfurt 3 | `scale-to-zero-target-3` | https://scale-to-zero-target-3-production-p7uwxk.laravel.cloud |
| Frankfurt 4 | `scale-to-zero-target-4` | https://scale-to-zero-target-4-production-kry72q.laravel.cloud |
| Frankfurt 5 | `scale-to-zero-target-5` | https://scale-to-zero-target-5-production-kvbnkp.laravel.cloud |

All five deploy from the [`target/`](target) folder of this repository, a plain Laravel app with one added `/wake` route. In Laravel Cloud, each app uses `target` as its root directory. Each target has these settings:

- Flex compute with Scale-to-Zero after **1 minute** without requests. This must be shorter than the game's 150-second cooldown, or players race apps that are still awake.
- `SESSION_DRIVER=cookie` and `CACHE_STORE=file`, so the app needs no database.
- The **Frame** response header set to allow all origins, so the preview can load in an iframe. Cloud's default, `deny`, blocks it.

The `/wake` route skips the `web` middleware so that sessions and cookies do not add to the timed response. It returns these headers:

- `Access-Control-Allow-Origin: *`
- `Timing-Allow-Origin: *`, without which the browser hides the timing data and the measurement falls back to a slower, less accurate clock.
- `Cache-Control: no-store`

### Keep the targets near the booth

Put the targets in the region closest to the event. A cold request crosses the network more times than a warm one, and subtracting the warm baseline does not remove that extra time. When this game ran from Brisbane against targets in Frankfurt, wake times measured 1.3–1.5 seconds. With targets in Sydney, they are well under a second.

To move the game to another event:

1. Create new target apps in a region near the event from this repository with the root directory `target`, with the settings above.
2. Update `targets` in `config/game.php` with each app's URL, application ID, and environment ID.

## The game app

The game itself deploys from the root of this repository. Scale-to-Zero is off for the game app, so visitors never wait for it to wake.

To clear the leaderboard and the attendee list before an event, run this command. It deletes all rounds **and** all attendees.

```bash
php artisan leaderboard:reset
```

## Local development

```bash
composer run setup
composer run dev
```

Run the tests with `php artisan test --compact`.
