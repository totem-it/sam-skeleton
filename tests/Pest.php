<?php

declare(strict_types=1);

namespace Totem\SamSkeleton\Tests;

use Illuminate\Http\Request;

function createLangRequest(string|null $locale = null): Request
{
    return Request::create('/', 'GET', [], [], [], $locale !== null ? ['HTTP_ACCEPT_LANGUAGE' => $locale] : []);
}

function createAcceptRequest(string $accept = ''): Request
{
    return Request::create('/', 'GET', [], [], [], $accept ? ['HTTP_ACCEPT' => $accept] : []);
}

function createQueryFieldRequest(array|string $fields = []): Request
{
    return new Request([
        'fields' => $fields,
    ]);
}

function createQuerySortRequest(string $sorts = ''): Request
{
    return new Request([
        'sort' => $sorts,
    ]);
}

function createQueryFilterRequest(array $filters = []): Request
{
    return new Request([
        'filter' => $filters,
    ]);
}
function createQueryIncludeRequest(string $include = ''): Request
{
    return new Request([
        'include' => $include,
    ]);
}
