<?php

namespace TreptowKolleg\ORM\Field;

use DateTimeImmutable;
use TreptowKolleg\ORM\Attribute as DB;

/**
 * Trait CreatedField
 *
 * Dieser Trait fügt einer Klasse die Funktionalität hinzu, ein
 * "Created At"-Feld zu verwalten. Dieses Feld speichert das Datum
 * und die Uhrzeit, zu der ein Datensatz erstellt wurde.
 *
 * Attribute:
 * - `created`: Ein Zeitstempel-Feld im Format "Y-m-d H:i:s",
 *   das den Erstellungszeitpunkt speichert.
 *
 * Methoden:
 * - `getCreated()`: Gibt ein `DateTimeImmutable`-Objekt zurück, das den
 *   Erstellungszeitpunkt repräsentiert, oder `null`, falls kein Wert vorhanden ist.
 */
trait CreatedField
{

    #[DB\CreatedAt]
    private ?string $created = null;

    /**
     * Gibt den Erstellungszeitpunkt als `DateTimeImmutable` zurück.
     *
     * Wenn das Feld `$created` gesetzt ist, wird der Wert in ein `DateTimeImmutable`
     * umgewandelt. Andernfalls wird `null` zurückgegeben.
     *
     * @return DateTimeImmutable|null Das Datum und die Uhrzeit der Erstellung oder
     *                                  `null`, wenn kein Erstellungszeitpunkt vorhanden ist.
     */
    public function getCreated(): ?DateTimeImmutable
    {
        if(!is_null($this->created)) {
            return DateTimeImmutable::createFromFormat("Y-m-d H:i:s", $this->created);
        }
        return null;
    }

}