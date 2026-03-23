<?php

declare(strict_types=1);

namespace Totem\SamSkeleton\Filterable\Enum;

use Totem\SamSkeleton\Filterable\Exceptions\InvalidFilterOperator;
use ValueError;

enum FilterOperator: string
{
    case EQUAL = 'eq';
    case NOT_EQUAL = 'neq';
    case LESS_THAN = 'lt';
    case GREATER_THAN = 'gt';
    case LESS_THAN_OR_EQUAL = 'lte';
    case GREATER_THAN_OR_EQUAL = 'gte';
    case LIKE = 'like';
    case LIKE_START = 'start';
    case LIKE_END = 'end';
    case NOT_LIKE = 'nlike';
    case NOT_LIKE_START = 'nstart';
    case NOT_LIKE_END = 'nend';
    case IS_NULL = 'null';
    case NOT_IN = 'nin';
    case BETWEEN = 'bt';
    case NOT_BETWEEN = 'nbt';

    public function comparison(): string
    {
        return match ($this) {
            self::EQUAL => '=',
            self::NOT_EQUAL => '<>',
            self::LESS_THAN => '<',
            self::GREATER_THAN => '>',
            self::LESS_THAN_OR_EQUAL => '<=',
            self::GREATER_THAN_OR_EQUAL => '>=',
            default => ''
        };
    }

    public static function fromCode(string $code = ''): self
    {
        try {
            return self::from($code);
        } catch (ValueError) {
            return match ($code) {
                '', 'in' => self::EQUAL,
                '-eq' => self::NOT_EQUAL,
                'contains' => self::LIKE,
                '-like' => self::NOT_LIKE,
                'nstart', '-start' => self::NOT_LIKE_START,
                'nend', '-end' => self::NOT_LIKE_END,
                '-in', => self::NOT_IN,
                'between' => self::BETWEEN,
                '-bt', '-between' => self::NOT_BETWEEN,
                default => throw new InvalidFilterOperator($code),
            };
        }
    }
}
