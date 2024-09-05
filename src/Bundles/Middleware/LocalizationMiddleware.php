<?php

declare(strict_types=1);

namespace Totem\SamSkeleton\Bundles\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LocalizationMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $locale = $this->parseHeader($request);

        if (in_array($locale, ['', '*'], true) === false) {
            app()->setLocale($locale);
        }

        return $next($request);
    }

    private function parseHeader(Request $request)
    {
        $acceptedLanguage = explode(',', $request->header('Accept-Language', ''));

        $extendedPreferredLanguages = [];

        foreach ($acceptedLanguage as $language) {
            $parts = explode(';', $language, 2);

            $locale = trim(str_contains($parts[0], '-')
                ? strstr($parts[0], '-', true)
                : $parts[0]);
            $factor = isset($parts[1]) ? Str::after($parts[1], '=') : '1';

            if (isset($extendedPreferredLanguages[$factor]) === false) {
                $extendedPreferredLanguages[$factor] = $locale;
            }
        }
        krsort($extendedPreferredLanguages);

        return reset($extendedPreferredLanguages);
    }
}
