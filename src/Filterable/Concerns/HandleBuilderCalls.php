<?php

declare(strict_types=1);

namespace Totem\SamSkeleton\Filterable\Concerns;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Traits\ForwardsCalls;

trait HandleBuilderCalls
{
    use ForwardsCalls;

    /**
     * @return array
     */
    public function getEagerLoads(): array
    {
        $this->pipelineQuery();

        return $this->scopedBuilder->getEagerLoads();
    }

    public function toSql(): string
    {
        $this->pipelineQuery();

        return $this->scopedBuilder->toSql();
    }

    public function toRawSql(): string
    {
        $this->pipelineQuery();

        return $this->scopedBuilder->toRawSql();
    }

    /**
     * @param string[] $columns
     */
    public function get(array $columns = ['*']): Collection
    {
        $this->pipelineQuery();

        return $this->scopedBuilder->get($columns);
    }

    public function __call(string $name, array $arguments): mixed
    {
        $this->pipelineQuery();

        $forwardedCall = $this->forwardCallTo($this->scopedBuilder, $name, $arguments);

        if ($forwardedCall === $this->scopedBuilder) {
            return $this;
        }

        return $forwardedCall;
    }
}
