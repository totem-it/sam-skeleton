<?php

declare(strict_types=1);

namespace Totem\SamSkeleton\Tests;

//arch()->preset()->php();

//arch()->preset()->security();

arch('no debug')
    ->expect('Totem\SamSkeleton')
    ->not->toUse(['die', 'dd', 'dump', 'var_dump']);

arch('strict types')
    ->expect('Totem\SamSkeleton')
    ->toUseStrictTypes();

arch('strict equality')
    ->expect('Totem\SamSkeleton')
    ->toUseStrictEquality();
