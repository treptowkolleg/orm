<?php

namespace TreptowKolleg\ORM\Field;

use TreptowKolleg\ORM\Attribute as DB;

/**
 * Trait PersonField
 *
 * Dieser Trait stellt die Felder und Methoden für persönliche Informationen bereit,
 * insbesondere den Vornamen und Nachnamen einer Person.
 */
trait PersonField
{

    /**
     * Der Vorname der Person.
     *
     * @var string|null
     */
    #[DB\Column]
    private ?string $firstname = null;

    /**
     * Der Nachname der Person.
     *
     * @var string|null
     */
    #[DB\Column]
    private ?string $lastname = null;

    /**
     * Gibt den Vornamen zurück.
     *
     * @return string|null Der Vorname oder null, falls er nicht gesetzt ist.
     */
    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    /**
     * Setzt den Vornamen der Person.
     *
     * @param string $firstname Der zu setzende Vorname.
     * @return void
     */
    public function setFirstname(string $firstname): void
    {
        $this->firstname = $firstname;
    }

    /**
     * Gibt den Nachnamen zurück.
     *
     * @return string|null Der Nachname oder null, falls er nicht gesetzt ist.
     */
    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    /**
     * Setzt den Nachnamen der Person.
     *
     * @param string $lastname Der zu setzende Nachname.
     * @return void
     */
    public function setLastname(string $lastname): void
    {
        $this->lastname = $lastname;
    }



}
