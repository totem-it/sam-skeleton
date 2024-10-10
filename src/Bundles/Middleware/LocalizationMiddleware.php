<?php

declare(strict_types=1);

namespace Totem\SamSkeleton\Bundles\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LocalizationMiddleware
{
    public function handle(Request $request, Closure $next): mixed
    {
        $headerLanguage = trim($request->header('Accept-Language', ''));

        if (strlen($headerLanguage) < 2) {
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

            $factor = $this->getFactor($quality);

            if (isset($extendedPreferredLanguages[$factor]) === false) {
                $extendedPreferredLanguages[$factor] = $this->getLocale($tag);
            }
        }

        krsort($extendedPreferredLanguages);

        return reset($extendedPreferredLanguages);
    }

    private function getFactor(string|null $quality): string
    {
        return $quality ? Str::after($quality, '=') : '1';
    }

    private function getLocale(string $tag): string
    {
        return trim(str_contains($tag, '-') ? strstr($tag, '-', true) : $tag);
    }
}
