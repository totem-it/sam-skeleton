<?php

declare(strict_types=1);

namespace  Totem\SamSkeleton\Tests;

use Illuminate\Http\Request;

function createLangRequest(string $locale = ''): Request
{
    return Request::create('/', 'GET', [], [], [], $locale ? ['HTTP_ACCEPT_LANGUAGE' => $locale] : []);
}

function createAcceptRequest(string $accept = ''): Request
{
    return Request::create('/', 'GET', [], [], [], $accept ? ['HTTP_ACCEPT' => $accept] : []);
}
