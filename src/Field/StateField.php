<?php

namespace TreptowKolleg\ORM\Field;

use TreptowKolleg\ORM\Attribute as DB;

trait StateField
{

    #[DB\Column(type: DB\Type::Boolean)]
    private bool $isActive = true;

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function deactivate(): static
    {
        $this->isActive = false;
        return $this;
    }

    public function activate(): static
    {
        $this->isActive = true;
        return $this;
    }

}