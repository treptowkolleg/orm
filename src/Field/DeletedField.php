<?php

namespace TreptowKolleg\ORM\Field;

use DateTimeImmutable;
use TreptowKolleg\ORM\Attribute as DB;

/**
 * Trait DeletedField
 *
 * Fügt einem Datensatz ein Feld hinzu, das Soft-Deletes ermöglicht.
 * Dieses Feld speichert den Zeitpunkt der Löschung, ohne den Datensatz tatsächlich zu entfernen.
 */
trait DeletedField
{

    /**
     * @var string|null $deleted
     *
     * Speichert den Löschzeitpunkt als String im Format "Y-m-d H:i:s".
     * Wenn der Wert `null` ist, wurde der Datensatz nicht gelöscht.
     */
    #[DB\Column(type: DB\Type::DateTime, nullable: true)]
    private ?string $deleted = null;


    /**
     * Gibt den Löschzeitpunkt als DateTimeImmutable-Objekt zurück.
     *
     * @return DateTimeImmutable|null Der Löschzeitpunkt oder `null`, wenn der Datensatz nicht gelöscht wurde.
     */
    public function getDeleted(): ?DateTimeImmutable
    {
        if(!is_null($this->deleted)) {
            return DateTimeImmutable::createFromFormat("Y-m-d H:i:s", $this->deleted);
        }
        return null;
    }

    /**
     * Setzt den Löschzeitpunkt des Datensatzes.
     *
     * @param DateTimeImmutable $deleted Das Datum und die Uhrzeit der Löschung.
     */
    public function setDeleted(DateTimeImmutable $deleted): void
    {
        $this->deleted = $deleted->format("Y-m-d H:i:s");
    }

    /**
     * Prüft, ob der Datensatz als gelöscht markiert wurde.
     *
     * @return bool `true`, wenn der Datensatz gelöscht wurde, ansonsten `false`.
     */
    public function isDeleted(): bool
    {
        return (bool) $this->deleted;
    }

}