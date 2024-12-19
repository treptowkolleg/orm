<?php

namespace TreptowKolleg\ORM\Field;

use TreptowKolleg\ORM\Attribute as DB;
use TreptowKolleg\ORM\Attribute\Type;

trait TextField
{

    #[DB\Column(type: Type::Text)]
    private ?string $text = null;

    public function setText(string $text): void
    {
        $this->text = $text;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

}
