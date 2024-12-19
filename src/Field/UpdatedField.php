<?php

namespace TreptowKolleg\ORM\Field;

use TreptowKolleg\ORM\Attribute as DB;

trait UpdatedField
{

    #[DB\UpdatedAt]
    private ?string $updated = null;

    public function getUpdated(): ?\DateTimeImmutable
    {
        if(!is_null($this->updated)) {
            return \DateTimeImmutable::createFromFormat("Y-m-d H:i:s", $this->updated);
        }
        return null;
    }

}