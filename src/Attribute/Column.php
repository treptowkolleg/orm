<?php

namespace TreptowKolleg\ORM\Attribute;

#[\Attribute] class Column
{

    private ?string $name;
    private Type $type;
    private int $length;
    private bool $unique;
    private bool $nullable;


    public function __construct(string $name = null, Type $type = Type::String, int $length = 255, bool $unique = false, bool $nullable = false)
    {
        $this->name = $name;
        $this->type = $type;
        $this->length = $length;
        $this->unique = $unique;
        $this->nullable = $nullable;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getType(): Type
    {
        return $this->type;
    }

    public function getLength(): int
    {
        return $this->length;
    }

    public function isUnique(): bool
    {
        return $this->unique;
    }

    public function isNullable(): bool
    {
        return $this->nullable;
    }

}