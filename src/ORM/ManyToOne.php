<?php

namespace TreptowKolleg\ORM\ORM;

#[\Attribute] class ManyToOne
{
    private string $targetEntity;

    public function __construct(string $targetEntity)
    {
        $this->targetEntity = $targetEntity;
    }

    public function getTargetEntity(): string
    {
        return $this->targetEntity;
    }

}