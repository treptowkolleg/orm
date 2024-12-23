<?php

namespace TreptowKolleg\ORM\Field;

use TreptowKolleg\ORM\Attribute as DB;
use TreptowKolleg\ORM\Attribute\Type;

/**
 * Trait StateField
 *
 * Fügt ein Feld hinzu, um den Zustand eines Objekts zu speichern.
 */
trait SnapshotField
{

    /**
     * @var string|null $state
     *
     * Ein serialisierter Zustand des Objekts.
     */
    #[DB\Column(type: Type::Text)]
    private ?string $state = null;

    /**
     * Speichert den aktuellen Zustand des Objekts.
     *
     * @param object $object
     * @return self
     */
    public function setState(object $object): static
    {
        $this->state = serialize($object);
        return $this;
    }

    /**
     * Gibt den gespeicherten Zustand des Objekts zurück.
     *
     * @return null|object
     */
    public function getState(): ?object
    {
        return $this->state !== null ? unserialize($this->state) : null;
    }

}
