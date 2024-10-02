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
        $headerLanguage = $request->header('Accept-Language', '');

        if (strLen(trim($headerLanguage)) < 2) {
            return $next($request);
        }

        $locale = $this->parseHeader($headerLanguage);

        if (strlen($locale) === 2) {
            app()->setLocale($locale);
        }

        return $next($request);
    }

    protected function parseHeader(string $header): string
    {
        $acceptedLanguage = explode(',', $header);

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
