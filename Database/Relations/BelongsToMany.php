<?php

namespace NovaLite\Database\Relations;

use NovaLite\Database\Database;
use NovaLite\Database\Model;
use NovaLite\Database\Query\Builder;

readonly class BelongsToMany
{
    public function __construct(
        private Model $related,
        private Model $instance,
        private string $table,
        private string $foreignPivotKey,
        private string $relatedPivotKey,
        private string $primaryKey,
        private string $relatedPrimaryKey
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

    public function getForeignPivotKey() : string
    {
        return $this->foreignPivotKey;
    }

    public function getRelatedPivotKey() : string
    {
        return $this->relatedPivotKey;
    }
    public function getRelatedPrimaryKey() : string
    {
        return $this->relatedPrimaryKey;
    }
    public function getResults() : array
    {
        $builder = new Builder($this->table);
        $builder->setInstance($this->related);
        return $builder->where($this->foreignPivotKey, '=',$this->instance->{$this->primaryKey})->get();
    }
}