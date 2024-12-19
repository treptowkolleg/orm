<?php

namespace TreptowKolleg\ORM\Field;

use TreptowKolleg\ORM\Attribute as DB;

trait CreatedField
{

    #[DB\CreatedAt]
    private ?string $created = null;

    public function getCreated(): ?\DateTimeImmutable
    {
        if(!is_null($this->created)) {
            return \DateTimeImmutable::createFromFormat("Y-m-d H:i:s", $this->created);
        }
        return null;
    }

}