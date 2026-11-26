<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class WakeProbe
{
    /**
     * Wake the target and time it from this server, so the venue network stays out of the number.
     *
     * The first request to the sleeping app is the cold time. Three warm requests straight
     * after give the baseline: the network hop plus the app's steady-state request cost.
     *
     * @return array{cold_ms: int, latency_ms: int|null}|null
     */
    public function measure(string $url): ?array
    {
        $coldMs = $this->timeToFirstByte($url, 'cold');

        if ($coldMs === null) {
            return null;
        }

        $latencyMs = collect(['warm-1', 'warm-2', 'warm-3'])
            ->map(fn (string $tag): ?int => $this->timeToFirstByte($url, $tag))
            ->filter(fn (?int $ms): bool => $ms !== null)
            ->min();

        return ['cold_ms' => $coldMs, 'latency_ms' => $latencyMs];
    }

    /**
     * Time from sending a request to the first response byte, leaving out DNS, TCP, and TLS setup.
     */
    protected function timeToFirstByte(string $url, string $tag): ?int
    {
        try {
            $response = Http::timeout(config('game.round_timeout_ms') / 1000)
                ->head(rtrim($url, '/').'/wake', ['cs' => $tag.'-'.random_int(100000, 999999)]);
        } catch (ConnectionException) {
            return null;
        }

        if (! $response->successful()) {
            return null;
        }

        $stats = $response->handlerStats();

        return (int) round(max(0, $stats['starttransfer_time'] - $stats['pretransfer_time']) * 1000);
    }
}
