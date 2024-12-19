<?php

namespace TreptowKolleg\ORM\Field;

use TreptowKolleg\ORM\Attribute as DB;

trait AddressField
{

    #[DB\Column]
    private ?string $street = null;

    #[DB\Column(nullable: true)]
    private ?string $streetNr = null;

    #[DB\Column(type: DB\Type::Integer)]
    private ?int $postalCode = null;

    #[DB\Column(type: DB\Type::String)]
    private ?string $city = null;

    #[DB\Column(type: DB\Type::String)]
    private ?string $country = null;

    public function getStreet(): ?string
    {
        return $this->street;
    }

    public function setStreet(string $street): void
    {
        $this->street = $street;
    }

    public function getStreetNr(): ?string
    {
        return $this->streetNr;
    }

    public function setStreetNr(?string $streetNr): void
    {
        $this->streetNr = $streetNr;
    }

    public function getPostalCode(): ?int
    {
        return $this->postalCode;
    }

    public function setPostalCode(int $postalCode): void
    {
        $this->postalCode = $postalCode;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): void
    {
        $this->city = $city;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(string $country): void
    {
        $this->country = $country;
    }

}
