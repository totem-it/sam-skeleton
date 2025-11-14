<?php

declare(strict_types=1);

namespace Totem\SamSkeleton\Repository;

use Symfony\Component\HttpKernel\Exception\HttpException;

trait UseRepository
{
    protected function throwNotFound(string $message): void
    {
        throw new HttpException(404, $message);
    }

    protected function throwMissing(string $message): void
    {
        throw HttpException::fromStatusCode(422, $message);
    }

    protected function throwLocked(string $message): void
    {
        throw HttpException::fromStatusCode(423, $message);
    }
}
