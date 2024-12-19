<?php

namespace TreptowKolleg\ORM\Field;

use TreptowKolleg\ORM\Attribute as DB;
use TreptowKolleg\ORM\Attribute\Type;

/**
 * Trait TextField
 *
 * Dieser Trait stellt das Feld und die Methoden für einen längeren Text bereit.
 */
trait TextField
{

    /**
     * Der Textinhalt des Elements.
     *
     * @var string|null
     */
    #[DB\Column(type: Type::Text)]
    private ?string $text = null;

    /**
     * Setzt den Textinhalt des Elements.
     *
     * @param string $text Der zu setzende Text.
     * @return void
     */
    public function setText(string $text): void
    {
        $this->text = $text;
    }

    /**
     * Gibt den Textinhalt des Elements zurück.
     *
     * @return string|null Der Text oder null, falls er nicht gesetzt ist.
     */
    public function getText(): ?string
    {
        return $this->text;
    }

}
