<?php

namespace TreptowKolleg\ORM\Field;

use TreptowKolleg\ORM\Attribute as DB;

/**
 * Trait DescriptionField
 *
 * Dieser Trait stellt ein Feld und die zugehörigen Methoden für eine Beschreibung bereit.
 */
trait DescriptionField
{

    /**
     * Die Beschreibung des Elements.
     *
     * @var string|null
     */
    #[DB\Column]
    private ?string $description = null;

    /**
     * Setzt die Beschreibung des Elements.
     *
     * @param string $description Die zu setzende Beschreibung.
     * @return self
     */
    public function setDescription(string $description): static
    {
        $this->description = $description;
        return $this;
    }

    /**
     * Gibt die Beschreibung des Elements zurück.
     *
     * @return string|null Die Beschreibung oder null, falls sie nicht gesetzt ist.
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

}
