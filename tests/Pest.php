<?php

declare(strict_types=1);

namespace Totem\SamSkeleton\Tests;

use Illuminate\Http\Request;
use Orchestra\Testbench\TestCase;

pest()->extend(TestCase::class)->in(__DIR__);

function createLangRequest(string|null $locale = null): Request
{
    return Request::create('/', 'GET', [], [], [], $locale !== null ? ['HTTP_ACCEPT_LANGUAGE' => $locale] : []);
}

function createAcceptRequest(string $accept = ''): Request
{
    return Request::create('/', 'GET', [], [], [], $accept ? ['HTTP_ACCEPT' => $accept] : []);
}
