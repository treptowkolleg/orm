<?php

namespace TreptowKolleg\ORM\Field;

use TreptowKolleg\ORM\Attribute as DB;

/**
 * Trait AddressField
 *
 * Dieser Trait stellt Felder und zugehörige Methoden für Adressinformationen bereit.
 */
trait AddressField
{

    /**
     * Die Straße der Adresse.
     *
     * @var string|null
     */
    #[DB\Column]
    private ?string $street = null;

    /**
     * Die Hausnummer der Adresse (optional).
     *
     * @var string|null
     */
    #[DB\Column(nullable: true)]
    private ?string $streetNr = null;

    /**
     * Die Postleitzahl der Adresse.
     *
     * @var int|null
     */
    #[DB\Column(type: DB\Type::Integer)]
    private ?int $postalCode = null;

    /**
     * Die Stadt der Adresse.
     *
     * @var string|null
     */
    #[DB\Column(type: DB\Type::String)]
    private ?string $city = null;

    /**
     * Das Land der Adresse.
     *
     * @var string|null
     */
    #[DB\Column(type: DB\Type::String)]
    private ?string $country = null;

    /**
     * Gibt die Straße zurück.
     *
     * @return string|null Die Straße oder null, falls nicht gesetzt.
     */
    public function getStreet(): ?string
    {
        return $this->street;
    }

    /**
     * Setzt die Straße der Adresse.
     *
     * @param string $street Die Straße.
     * @return void
     */
    public function setStreet(string $street): void
    {
        $this->street = $street;
    }

    /**
     * Gibt die Hausnummer zurück.
     *
     * @return string|null Die Hausnummer oder null, falls nicht gesetzt.
     */
    public function getStreetNr(): ?string
    {
        return $this->streetNr;
    }

    /**
     * Setzt die Hausnummer der Adresse.
     *
     * @param string|null $streetNr Die Hausnummer oder null.
     * @return void
     */
    public function setStreetNr(?string $streetNr): void
    {
        $this->streetNr = $streetNr;
    }

    /**
     * Gibt die Postleitzahl zurück.
     *
     * @return int|null Die Postleitzahl oder null, falls nicht gesetzt.
     */
    public function getPostalCode(): ?int
    {
        return $this->postalCode;
    }

    /**
     * Setzt die Postleitzahl der Adresse.
     *
     * @param int $postalCode Die Postleitzahl.
     * @return void
     */
    public function setPostalCode(int $postalCode): void
    {
        $this->postalCode = $postalCode;
    }

    /**
     * Gibt die Stadt zurück.
     *
     * @return string|null Die Stadt oder null, falls nicht gesetzt.
     */
    public function getCity(): ?string
    {
        return $this->city;
    }

    /**
     * Setzt die Stadt der Adresse.
     *
     * @param string $city Die Stadt.
     * @return void
     */
    public function setCity(string $city): void
    {
        $this->city = $city;
    }

    /**
     * Gibt das Land zurück.
     *
     * @return string|null Das Land oder null, falls nicht gesetzt.
     */
    public function getCountry(): ?string
    {
        return $this->country;
    }

    /**
     * Setzt das Land der Adresse.
     *
     * @param string $country Das Land.
     * @return void
     */
    public function setCountry(string $country): void
    {
        $this->country = $country;
    }

}
