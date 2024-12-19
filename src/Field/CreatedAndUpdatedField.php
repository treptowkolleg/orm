<?php

namespace TreptowKolleg\ORM\Field;

use TreptowKolleg\ORM\Attribute as DB;

/**
 * Trait CreatedAndUpdatedField
 *
 * Dieser Trait kombiniert die Funktionalität der Traits `CreatedField` und `UpdatedField`.
 * Er fügt einer Klasse sowohl ein "Created At"-Feld als auch ein "Updated At"-Feld hinzu,
 * um Erstellungs- und Aktualisierungszeitpunkte zu verwalten.
 *
 * Enthaltene Traits:
 * - `CreatedField`: Bietet ein `$created`-Attribut und die Methode `getCreated()`, um
 *   den Erstellungszeitpunkt abzurufen.
 * - `UpdatedField`: Bietet ein `$updated`-Attribut und die Methode `getUpdated()`, um
 *   den letzten Aktualisierungszeitpunkt abzurufen.
 *
 * Beispiele für die Nutzung:
 * ```php
 * $timestamp = $entity->getCreated();
 * $lastUpdated = $entity->getUpdated();
 * ```
 */
trait CreatedAndUpdatedField
{

    use CreatedField;
    use UpdatedField;

}