<?php

namespace TreptowKolleg\ORM\Field;

use TreptowKolleg\ORM\Attribute as DB;

/**
 * Trait TitleField
 *
 * Dieser Trait stellt das Feld und die Methoden für einen Titel bereit.
 */
trait TitleField
{

    /**
     * Der Titel des Elements.
     *
     * @var string|null
     */
    #[DB\Column]
    private ?string $title = null;

    /**
     * Setzt den Titel des Elements.
     *
     * @param string $title Der zu setzende Titel.
     * @return self
     */
    public function setTitle(string $title): static
    {
        $this->title = $title;
        return $this;
    }

    /**
     * Gibt den Titel des Elements zurück.
     *
     * @return string|null Der Titel oder null, falls er nicht gesetzt ist.
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

}
