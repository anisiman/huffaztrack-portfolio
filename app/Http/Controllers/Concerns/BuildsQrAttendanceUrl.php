<?php

namespace App\Http\Controllers\Concerns;

trait BuildsQrAttendanceUrl
{
    /**
     * Build the absolute URL to encode in an attendance QR code.
     *
     * Uses Laravel's route() helper, which resolves against config('app.url')
     * and therefore works correctly in any environment without touching APP_URL.
     *
     * For local development, set QR_BASE_URL (e.g. to an ngrok tunnel) so QR
     * codes are reachable from a phone that isn't on the same network as the
     * dev machine — this only ever applies when APP_ENV is "local".
     */
    private function buildQrUrl(string $routeName, array $parameters = []): string
    {
        $qrBaseUrl = config('app.qr_base_url');

        if ($qrBaseUrl && app()->environment('local')) {
            $relativeUrl = route($routeName, $parameters, absolute: false);

            return rtrim($qrBaseUrl, '/') . '/' . ltrim($relativeUrl, '/');
        }

        return route($routeName, $parameters);
    }
}
