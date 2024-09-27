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
        $headerLanguage = $request->header('Accept-Language');

        if ($headerLanguage === null) {
            return $next($request);
        }

        $locale = $this->parseHeader($request);

        if (strlen($locale) === 2) {
            app()->setLocale($locale);
        }

        return $next($request);
    }

    private function parseHeader(Request $request): string
    {
        $acceptedLanguage = explode(',', $request->header('Accept-Language'));

        $extendedPreferredLanguages = [];

        foreach ($acceptedLanguage as $language) {
            [$tag, $quality] = explode(';', $language) + [null, null];

            $locale = trim(str_contains($tag, '-')
                ? strstr($tag, '-', true)
                : $tag);

            $factor = $quality ? Str::after($quality, '=') : '1';

            if (isset($extendedPreferredLanguages[$factor]) === false) {
                $extendedPreferredLanguages[$factor] = $locale;
            }
        }
        krsort($extendedPreferredLanguages);

        return reset($extendedPreferredLanguages);
    }
}
