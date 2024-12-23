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
     * @return self
     */
    public function setStreet(string $street): static
    {
        $this->street = $street;
        return $this;
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
     * @return self
     */
    public function setStreetNr(?string $streetNr): static
    {
        $this->streetNr = $streetNr;
        return $this;
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
     * @return self
     */
    public function setPostalCode(int $postalCode): static
    {
        $this->postalCode = $postalCode;
        return $this;
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
     * @return self
     */
    public function setCity(string $city): static
    {
        $this->city = $city;
        return $this;
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
     * @return self
     */
    public function setCountry(string $country): static
    {
        $this->country = $country;
        return $this;
    }

}
