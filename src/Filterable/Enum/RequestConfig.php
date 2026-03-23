<?php

declare(strict_types=1);

namespace Totem\SamSkeleton\Filterable\Enum;

enum RequestConfig: string
{
    case DELIMITER = ',';
    case RESOURCE_PREFIX = '*';
    case DEFAULT_FILTER = 'eq';
}
