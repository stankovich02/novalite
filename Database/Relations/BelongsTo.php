<?php

namespace NovaLite\Database\Relations;

use NovaLite\Database\Model;
use NovaLite\Database\Query\Builder;

readonly class BelongsTo
{
    public function __construct(
        private Model  $related,
        private Model  $instance,
        private string $table,
        private string $foreignKey,
        private string $ownerKey
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

    public function getOwnerKey() : string
    {
        return $this->ownerKey;
    }
    public function getResults() : Model
    {
        $builder = new Builder($this->table);
        $builder->setInstance($this->related);
        return $builder->where($this->ownerKey, '=',$this->instance->{$this->foreignKey})->first();
    }
}