<?php

namespace TreptowKolleg\ORM\Field;

use DateTimeImmutable;
use TreptowKolleg\ORM\Attribute as DB;

/**
 * Trait UpdatedField
 *
 * Dieser Trait fügt einer Klasse die Funktionalität hinzu, ein
 * "Updated At"-Feld zu verwalten. Dieses Feld speichert das Datum
 * und die Uhrzeit, zu der ein Datensatz zuletzt aktualisiert wurde.
 *
 * Attribute:
 * - `updated`: Ein optionales Zeitstempel-Feld im Format "Y-m-d H:i:s",
 *   das den letzten Aktualisierungszeitpunkt speichert.
 *
 * Methoden:
 * - `getUpdated()`: Gibt ein `DateTimeImmutable`-Objekt zurück, das den
 *   letzten Aktualisierungszeitpunkt repräsentiert, oder `null`, falls
 *   kein Wert vorhanden ist.
 */
trait UpdatedField
{

    #[DB\UpdatedAt]
    private ?string $updated = null;

    /**
     * Gibt den letzten Aktualisierungszeitpunkt als `DateTimeImmutable` zurück.
     *
     * Wenn das Feld `$updated` gesetzt ist, wird der Wert in ein `DateTimeImmutable`
     * umgewandelt. Andernfalls wird `null` zurückgegeben.
     *
     * @return DateTimeImmutable|null Das Datum und die Uhrzeit der letzten
     *                                  Aktualisierung oder `null`, wenn keine
     *                                  Aktualisierung erfolgt ist.
     */
    public function getUpdated(): ?DateTimeImmutable
    {
        if(!is_null($this->updated)) {
            return DateTimeImmutable::createFromFormat("Y-m-d H:i:s", $this->updated);
        }
        return null;
    }

}