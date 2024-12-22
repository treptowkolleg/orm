<?php

namespace TreptowKolleg\ORM\Field;

use TreptowKolleg\ORM\Attribute as DB;
use TreptowKolleg\ORM\Attribute\Type;

/**
 * Trait UserIdentifierField
 *
 * Dieser Trait stellt die Felder und Methoden zur Verwaltung von Benutzeridentifikatoren bereit,
 * darunter ein Benutzername und ein Passwort. Der Benutzername ist eindeutig in der Datenbank.
 */
trait UserIdentifierField
{

    /**
     * Der eindeutige Benutzername des Benutzers.
     *
     * @var string|null
     */
    #[DB\Column(unique: true)]
    private ?string $username = null;

    /**
     * Das Passwort des Benutzers (gespeichert als Hash).
     *
     * @var string|null
     */
    #[DB\Column(type: Type::Password)]
    private ?string $password = null;

    /**
     * Gibt den Benutzernamen zurück.
     *
     * @return string|null Der Benutzername oder null, falls er nicht gesetzt ist.
     */
    public function getUsername(): ?string
    {
        return $this->username;
    }

    /**
     * Setzt den Benutzernamen.
     *
     * @param string $username Der zu setzende Benutzername.
     * @return self
     */
    public function setUsername(string $username): static
    {
        $this->username = $username;
        return $this;
    }

    /**
     * Gibt das Passwort zurück.
     *
     * @return string|null Das Passwort oder null, falls es nicht gesetzt ist.
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * Setzt das Passwort.
     *
     * **Achtung:** Das Passwort sollte vor dem Speichern gehasht werden.
     *
     * @param string $password Das zu setzende Passwort.
     * @return self
     */
    public function setPassword(string $password): static
    {
        $this->password = $password;
        return $this;
    }

}
