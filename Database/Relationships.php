<?php

namespace NovaLite\Database;

use NovaLite\Database\Relations\BelongsTo;
use NovaLite\Database\Relations\BelongsToMany;
use NovaLite\Database\Relations\HasMany;
use NovaLite\Database\Relations\HasOne;
use Doctrine\Inflector\InflectorFactory;

trait Relationships
{
    use TableGuess;
    public function hasOne(string $related, $foreignKey = null, $localKey = null) : HasOne
    {
        $relatedInstance = new $related();
        $selfTable = $this->guessTableName($this);
        $relatedTable = $this->guessTableName($relatedInstance);
        $localKey = $localKey ?? $this->primaryKey;
        $foreignKey = $foreignKey ?? $this->guessForeignKey($selfTable,$localKey);

        return new HasOne($relatedInstance, $this, $relatedTable, $foreignKey, $localKey);
    }

    public function hasMany(string $related, $foreignKey = null, $localKey = null) : HasMany
    {
        $relatedInstance = new $related();
        $selfTable = $this->guessTableName($this);
        $relatedTable = $this->guessTableName($relatedInstance);
        $localKey = $localKey ?? $this->primaryKey;
        $foreignKey = $foreignKey ?? $this->guessForeignKey($selfTable,$localKey);

        return new HasMany($relatedInstance, $this, $relatedTable, $foreignKey, $localKey);
    }

    public function belongsTo(string $related, $foreignKey = null, $ownerKey = null) : BelongsTo
    {
        $relatedInstance = new $related();
        $ownerKey = $ownerKey ?? $relatedInstance->primaryKey;
        $relatedTable = $this->guessTableName($relatedInstance);
        $foreignKey = $foreignKey ?? $this->guessForeignKey($relatedTable,$ownerKey);

        return new BelongsTo($relatedInstance, $this, $relatedTable, $foreignKey, $ownerKey);
    }
    public function belongsToMany(string $related, string $table, $foreignPivotKey = null, $relatedPivotKey = null, $primaryKey = null,$relatedPrimaryKey = null) : BelongsToMany
    {
        $relatedInstance = new $related();
        $primaryKey = $primaryKey ?? $this->primaryKey;
        $selfTable = $this->guessTableName($this);
        $relatedTable = $this->guessTableName($relatedInstance);
        $foreignPivotKey = $foreignPivotKey ?? $this->guessForeignKey($selfTable,$primaryKey);
        $relatedPrimaryKey = $relatedPrimaryKey ?? $relatedInstance->primaryKey;
        $relatedPivotKey = $relatedPivotKey ?? $this->guessForeignKey($relatedTable,$relatedPrimaryKey);

        return new BelongsToMany($relatedInstance, $this, $table, $foreignPivotKey, $relatedPivotKey, $primaryKey, $relatedPrimaryKey);
    }

    private function guessForeignKey(string $table, string $primaryKey = 'id') : string
    {
        $inflector = InflectorFactory::create()->build();
        return $inflector->singularize($table) . '_' . $primaryKey;
    }}