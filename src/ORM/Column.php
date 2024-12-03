<?php

namespace TreptowKolleg\ORM;

#[\Attribute] class Column
{

    private ?string $name;
    private Types $type;
    private int $length;
    private bool $nullable;

    public function __construct(string $name = null, Types $type = Types::String, int $length = 255, bool $nullable = true)
    {
        $this->name = $name;
        $this->type = $type;
        $this->length = $length;
        $this->nullable = $nullable;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getType(): Types
    {
        return $this->type;
    }

    public function getLength(): int
    {
        return $this->length;
    }

    public function isNullable(): bool
    {
        return $this->nullable;
    }

}