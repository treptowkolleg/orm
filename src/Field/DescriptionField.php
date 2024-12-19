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
     * @return void
     */
    public function setDescription(string $description): void
    {
        $this->description = $description;
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
