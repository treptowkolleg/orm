<?php

namespace TreptowKolleg\ORM\ORM;

#[\Attribute] class ManyToOne
{
    private string $targetEntity;
    private string $targetColumn;

    public function __construct(string $targetEntity, string $targetColumn = "id")
    {
        $this->targetEntity = $targetEntity;
        $this->targetColumn = $targetColumn;
    }

    public function getTargetEntity(): ?\ReflectionClass
    {
        try {
            return new \ReflectionClass($this->targetEntity);
        } catch (\ReflectionException $e) {

        }
        return null;
    }

    public function getTargetColumn(): string
    {
        return $this->targetColumn;
    }


}