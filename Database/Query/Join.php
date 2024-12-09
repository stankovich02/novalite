<?php

namespace NovaLite\Database\Query;

class Join
{
    private string $query;
    public function __construct(string $query)
    {
        $this->query = $query;
    }
    public function on(string $firstColumn, string $operator, string $secondColumn) : string
    {
        $this->query .= " $firstColumn $operator $secondColumn";

        return $this->query;
    }
    public function orOn(string $firstColumn, string $operator, string $secondColumn) : string
    {
        $this->query .= " OR $firstColumn $operator $secondColumn";

        return $this->query;
    }
}