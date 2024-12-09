<?php

namespace NovaLite\Database\Relations;

use NovaLite\Database\Model;
use NovaLite\Database\Query\Builder;

readonly class HasMany
{
    public function __construct(
        private Model $related,
        private Model $instance,
        private string $table,
        private string $foreignKey,
        private string $localKey
    ) {
    }

    public function getRelated() : Model
    {
        return $this->related;
    }

    public function getForeignKey() : string
    {
        return $this->foreignKey;
    }

    public function getLocalKey() : string
    {
        return $this->localKey;
    }
    public function getResults() : array
    {
        $builder = new Builder($this->table);
        $builder->setInstance($this->related);
        return $builder->where($this->foreignKey, '=',$this->instance->{$this->localKey})->get();
    }
}