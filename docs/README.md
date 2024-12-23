# Object-Relation-Mapper

## Installation

``composer require treptowkolleg/orm:dev-master``

## Verwendung

### Entity anlegen

#### Benutzerdefiniert

Über die seit ``php 8.1`` eingeführten Attribut-Klassen, können wir unsere Datenbank-Relation
direkt über die Properties unserer Klasse definieren.

````php
<?php

use TreptowKolleg\ORM\Attribute;

class MyEntity
{
    #[Attribute\Id]
    #[Attribute\AutoGenerated]
    #[Attribute\Column(type: Attribute\Type::Integer)]
    private ?int $id = null
    
    #[Attribute\Column]
    private string $name;
    
    // getter & setter ...
}
````

#### Vordefiniert

Für eine vielzahl vordefinierte und häufig vorkommender Fälle kannst du auf "Textbausteine"
zurückgreifen, die alles für dich inklusive getter und setter erledigen.

````php
<?php

use TreptowKolleg\ORM\Attribute;
use TreptowKolleg\ORM\Field;

class MyEntity
{
    // fügt id sowie getter hinzu
    use Field\IdField;
    
    // fügt Vor- und Nachname sowie getter und setter hinzu 
    use Field\PersonField;
    
    // fügt Felder für automatisches Datum/Uhrzeit für Erstellungs- und Update-Feld hinzu
    use Field\CreatedAndUpdatedField;
    
}
````

### Tabelle in DB anlegen

Tabellen werden simpel über den EntityManager erstellt.

````php

require 'vendor/autoload.php';

$entityManager = new \TreptowKolleg\ORM\Model\EntityManager();
$entityManager->createTableIfNotExists(MyEntity:class);

````

### Objekte speichern

Auch ``INSERT`` und ``UPDATE`` von Objekten erfolgt mittels EntityManager.

````php

require 'vendor/autoload.php';

$entityManager = new \TreptowKolleg\ORM\Model\EntityManager();

$myEntity = new Entity();
$myEntity->setName('John Doe');

// Änderung einreihen
$entityManager->persist($myEntity);

// Alle eingereihten Änderungen auf Datenbank anwenden
$entityManager->flush();

````

### Repository anlegen

Um Datensätze (Tupel) aus den Relationen zu laden, benötigen wir eine auf unsere
Entity-Klasse zugeschnittene Repository-Klasse. Anbei die Minimalkonfiguration.

````php
<?php

use TreptowKolleg\ORM\Attribute;
use TreptowKolleg\ORM\Model\Repository;

/**
 * @extends Repository<MyEntity>
 */
class MyEntityRepository extends Repository
{
    public function __construct() {
        parent::__construct(MyEntity::class)
    }
    
    // custom find methods ...
}
````

#### Tupel (Datensätze) holen

Im Repository verfügbare Methoden zum Laden von Tupeln. In unserer erstellten
Repository-Unterklasse können wir noch weitere spezialisierte Methoden entwerfen.

Da das Repository über unsere Entity-Klasse Bescheid weiß, werden dankenswerterweise
keine assoziativen Arrays mit den Daten zurückgeliefert, sonden direkt Instanzen
unserer Entity-Klasse bzw. ein Array mit Entity-Instanzen, wenn nach mehreren Tupeln
gesucht wird:

````php

require 'vendor/autoload.php';

$repository = new MyEntityRepository();

// Alle Tupel
$objects = $repository->findAll();

// Alle Tupel, die Bedingung erfüllen
$objects = $repository->findBy(['name' => 'John Doe']);

// Alle Tupel, die bestimmte Werte enthalten (unscharfe Suche)
$objects = $repository->findByLike('name' => 'John');

// Ein Tupel, das die Bedingung erfüllt
$objects = $repository->findOneBy(['name' => 'John Doe']);

// Ein Tupel mit unscharfer Suche finden
$objects = $repository->findOneByLike(['name' => 'Doe']);

// Ein Tupel mit id 'x' finden
$objects = $repository->find(23);

````