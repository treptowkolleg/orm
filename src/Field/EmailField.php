<?php

namespace TreptowKolleg\ORM\Field;

use TreptowKolleg\ORM\Attribute as DB;

/**
 * Trait EmailField
 *
 * Dieser Trait stellt ein Feld und die zugehörigen Methoden für eine eindeutige E-Mail-Adresse bereit.
 */
trait EmailField
{

    /**
     * Die E-Mail-Adresse des Elements.
     *
     * @var string|null
     */
    #[DB\Column(unique: true)]
    private ?string $email = null;

    /**
     * Gibt die E-Mail-Adresse zurück.
     *
     * @return string|null Die E-Mail-Adresse oder null, falls sie nicht gesetzt ist.
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * Setzt die E-Mail-Adresse des Elements.
     *
     * @param string $email Die zu setzende E-Mail-Adresse.
     * @return void
     */
    public function setEmail(string $email): void
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException("Ungültige E-Mail-Adresse.");
        }
        $this->email = $email;
    }

}
