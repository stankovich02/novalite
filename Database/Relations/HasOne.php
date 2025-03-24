<?php

namespace NovaLite\Database\Relations;

use NovaLite\Database\Model;
use NovaLite\Database\Query\Builder;

readonly class HasOne
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

    public function getTable() : string
    {
        return $this->table;
    }

    public function getForeignKey() : string
    {
        return $this->foreignKey;
    }

    public function getLocalKey() : string
    {
        return $this->localKey;
    }

    public function getResults() : Model
    {
        $builder = new Builder($this->table);
        $builder->setInstance($this->related);
        var_dump($this->instance->{$this->localKey});
        return $builder->where($this->foreignKey, '=',$this->instance->{$this->localKey})->first();
    }
}