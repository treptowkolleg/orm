<?php

namespace TreptowKolleg\ORM\Model;

use TreptowKolleg\ORM\Exception\EntityNotFoundException;
use TreptowKolleg\ORM\Exception\OrderByFormatException;
use TreptowKolleg\ORM\Exception\TypeNotSupportedException;

/**
 * @template T
 */
abstract class Repository implements RepositoryInterface
{

    /**
     * @var class-string<T>
     */
    private string $entityClass;
    private Database $db;

    /**
     * Konstruktor für die Repository-Klasse.
     *
     * Initialisiert ein neues Repository-Objekt für die angegebene Entity-Klasse und
     * erstellt eine neue Datenbankverbindung. Stellt sicher, dass die angegebene
     * Entity-Klasse existiert, bevor das Repository initialisiert wird.
     *
     * @param class-string<T> $entityClass Der vollqualifizierte Name der Entity-Klasse, die dieses Repository verwaltet.
     *
     * @throws EntityNotFoundException Falls die angegebene Entity-Klasse nicht existiert.
     *
     * @example
     * // Beispiel für die Erstellung eines Repositories für die User-Entity:
     * $userRepository = new UserRepository();
     *
     * @note Die Entity-Klasse muss autoload-fähig sein, damit sie erfolgreich gefunden werden kann.
     *
     */
    public function __construct(string $entityClass)
    {
        if (!class_exists($entityClass)) {
            throw new EntityNotFoundException("The specified entity class '$entityClass' does not exist. Please ensure the class name is correct and properly auto loaded.");
        }
        $this->entityClass = $entityClass;
        $this->db = new Database();
    }

    /**
     * Erstellt und gibt eine neue Instanz des QueryBuilders für die aktuelle Entity-Klasse zurück.
     *
     * Diese Methode initialisiert einen QueryBuilder mit der aktuellen Datenbankverbindung
     * und der im Repository verwalteten Entity-Klasse. Optional kann ein Alias für die Tabelle
     * angegeben werden, um komplexere SQL-Abfragen zu erstellen.
     *
     * @param string|null $alias Ein optionaler Alias für die Tabelle in der SQL-Abfrage.
     *                           Dieser Alias kann verwendet werden, um Abfragen mit Joins
     *                           übersichtlicher zu gestalten.
     *
     * @return QueryBuilder Eine neue Instanz des QueryBuilders, initialisiert mit der Datenbankverbindung
     *                      und der aktuellen Entity-Klasse.
     *
     * @example
     * // Beispiel für die Verwendung des QueryBuilders mit einem Alias:
     * $queryBuilder = $this->queryBuilder('u')
     *     ->select('u.id, u.name')
     *     ->andWhere('u.status = :status')
     *     ->setParameter('status', 'active');
     *
     * @note Der QueryBuilder wird mit der in der Repository-Klasse festgelegten Datenbankverbindung
     *       und Entity-Klasse erstellt.
     */
    protected function queryBuilder(string $alias = null): QueryBuilder
    {
        return new QueryBuilder($this->db->getConnection(), $this->entityClass, $alias);
    }

    /**
     * @throws TypeNotSupportedException
     */
    private function makeCondition(string $key, mixed $value): string
    {
        return match (true) {
            is_null($value) => "$key IS NULL",
            is_bool($value) => $value ? "$key IS TRUE" : "$key IS FALSE",
            is_array($value) => "$key IN (".implode(',', array_map(fn($v) => ":$key" . "_" . chr(97 + $v), array_keys($value))).")",
            is_int($value), is_float($value), is_string($value) => "$key = :$key",
            default => throw new TypeNotSupportedException("Unsupported type for condition value"),
        };
    }

    protected function generateSnakeTailString(string $value): string
    {
        $valueAsArray = preg_split('/(?=[A-Z])/', $value);
        return strtolower(ltrim(implode('_', $valueAsArray),'_'));
    }

    /**
     * Validiert das übergebene Sortier-Array für die ORDER BY-Klausel einer SQL-Abfrage.
     *
     * Diese Methode überprüft, ob die angegebenen Sortierrichtungen gültig sind. Die erlaubten
     * Richtungen sind 'ASC' (aufsteigend) oder 'DESC' (absteigend). Wenn eine ungültige Richtung
     * gefunden wird, wird eine `OrderByFormatException` geworfen.
     *
     * @param array $orderBy Ein assoziatives Array, bei dem der Schlüssel der Feldname und der Wert
     *                       die Sortierrichtung ('ASC' oder 'DESC') ist.
     *
     * @throws OrderByFormatException Wird ausgelöst, wenn eine Sortierrichtung ungültig ist.
     *
     * @example
     * // Gültiges Beispiel:
     * $this->validateOrderBy([
     *     'name' => 'ASC',
     *     'created_at' => 'DESC'
     * ]);
     *
     * // Ungültiges Beispiel (wirft eine OrderByFormatException):
     * $this->validateOrderBy([
     *     'name' => 'UPWARD' // Ungültige Sortierrichtung
     * ]);
     *
     * @note Diese Methode stellt sicher, dass nur gültige Sortierrichtungen verwendet werden,
     *       um SQL-Fehler zu vermeiden.
     */
    protected function validateOrderBy(array $orderBy): void
    {
        foreach ($orderBy as $field => $direction) {
            if (!in_array(strtoupper($direction), ['ASC', 'DESC'])) {
                throw new OrderByFormatException("Invalid order direction for $field: $direction. Use 'ASC' or 'DESC'.");
            }
        }
    }

    /**
     * Findet eine Entität anhand der übergebenen ID.
     *
     * Diese Methode sucht nach einem Datensatz in der entsprechenden Tabelle, der mit der angegebenen ID übereinstimmt.
     * Gibt die gefundene Entität zurück oder `null`, wenn kein entsprechender Eintrag vorhanden ist.
     *
     * @param int|string $id Die ID der zu suchenden Entität. Es kann ein Integer oder ein String sein, je nach Datenbankfeldtyp.
     *
     * @return null|T Gibt die gefundene Entität zurück oder `null`, falls kein Treffer gefunden wird.
     *
     * @example
     * // Beispiel für die Suche nach einer Entität mit der ID 5
     * $user = $repository->find(5);
     * if ($user !== null) {
     *     echo $user->getName();
     * } else {
     *     echo 'User not found';
     * }
     *
     * @note Diese Methode nutzt den QueryBuilder, um eine SELECT-ORM-Abfrage auszuführen und die entsprechende Entität zu laden.
     */
    public function find(int|string $id)
    {
        return $this->queryBuilder()
            ->selectOrm()
            ->andWhere('id = :id')
            ->setParameter('id',$id)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }

    /**
     * Findet eine einzelne Entität basierend auf bestimmten Kriterien.
     *
     * Diese Methode sucht nach einem Datensatz, der mit den angegebenen Kriterien übereinstimmt.
     * Gibt die gefundene Entität zurück oder `null`, wenn kein entsprechender Eintrag vorhanden ist.
     *
     * @param array $data Ein assoziatives Array, das die Spaltennamen als Schlüssel und die Suchwerte als Werte enthält.
     *                    Beispiel: ['username' => 'john_doe', 'email' => 'john@example.com']
     *
     * @return null|T Gibt die gefundene Entität zurück oder `null`, falls kein Treffer gefunden wird.
     *
     * @throws TypeNotSupportedException Wenn ein Datentyp in den Kriterien nicht unterstützt wird.
     *
     * @example
     * // Beispiel für die Suche nach einem Benutzer mit bestimmten Kriterien
     * $user = $repository->findOneBy(['username' => 'john_doe']);
     * if ($user !== null) {
     *     echo $user->getEmail();
     * } else {
     *     echo 'User not found';
     * }
     *
     * @note Diese Methode verwendet den QueryBuilder und `setFilters()`, um die Abfrage mit den angegebenen Filtern zu erstellen.
     */
    public function findOneBy(array $data)
    {
        $query = $this->queryBuilder()->selectOrm();

        $this->setFilters($data, $query);

        return $query->setMaxResults(1) ->getQuery()->getOneOrNullResult();
    }

    /**
     * Findet eine Liste von Entitäten basierend auf bestimmten Kriterien und optionalen Sortier- und Paginierungsparametern.
     *
     * Diese Methode führt eine Suche aus, die mehrere Datensätze zurückgibt, die den angegebenen Filterkriterien entsprechen.
     * Optional können die Ergebnisse nach bestimmten Feldern sortiert und durch Limit und Offset paginiert werden.
     * Die Rückgabe ist ein Array von Entitäten, die den Suchkriterien entsprechen.
     *
     * @param array $data Ein assoziatives Array, das die Spaltennamen als Schlüssel und die Suchwerte als Werte enthält.
     *                    Beispiel: ['status' => 'active', 'category' => 'electronics']
     *
     * @param array $orderBy Ein assoziatives Array, das die Spaltennamen als Schlüssel und die Sortierreihenfolge ('ASC' oder 'DESC') als Werte enthält.
     *                       Beispiel: ['created_at' => 'DESC', 'price' => 'ASC']
     *                       Wenn nicht angegeben, wird keine Sortierung angewendet.
     *
     * @param int|null $limit Die maximale Anzahl der zurückgegebenen Ergebnisse. Wenn nicht angegeben, gibt es keine Begrenzung.
     *
     * @param int|null $offset Der Startindex für die Abfrageergebnisse. Wenn nicht angegeben, wird keine Verschiebung angewendet.
     *
     * @return T[] Ein Array von Entitäten, die den Filterkriterien entsprechen.
     *
     * @throws TypeNotSupportedException Wenn ein Datentyp in den Kriterien nicht unterstützt wird.
     * @throws OrderByFormatException Wenn das Format der `orderBy`-Parameter ungültig ist.
     *
     * @example
     * // Beispiel für die Suche nach aktiven Produkten, die nach Preis aufsteigend sortiert sind
     * $products = $repository->findBy(
     *     ['status' => 'active', 'category' => 'electronics'],
     *     ['price' => 'ASC'],
     *     10, // Begrenze die Ergebnisse auf 10
     *     0   // Beginne bei den ersten Ergebnissen (Offset)
     * );
     *
     * // Alle gefundenen Produkte durchlaufen und ausgeben
     * foreach ($products as $product) {
     *     echo $product->getName() . "\n";
     * }
     */
    public function findBy(array $data, array $orderBy = [], int $limit = null, int $offset = null): array
    {
        $this->validateOrderBy($orderBy);
        $query = $this->queryBuilder()
            ->selectOrm()
            ->orderBy($orderBy)
        ;

        $this->setFilters($data, $query);

        if(null !== $limit)
        {
            $query->setMaxResults($limit);
        }

        if(null !== $offset)
        {
            $query->setFirstResult($offset);
        }

        return $query->getQuery()->getResult();
    }

    /**
     * Findet eine Entität basierend auf den angegebenen Suchkriterien unter Verwendung von LIKE-Operatoren.
     *
     * Diese Methode führt eine Suche aus, die eine einzelne Entität zurückgibt, deren Felder mit den angegebenen
     * Suchbegriffen übereinstimmen. Die Suche verwendet den SQL-`LIKE`-Operator, um eine teilweise Übereinstimmung
     * der Feldwerte zu finden. Die Parameterwerte werden automatisch mit `%` umschlossen, um eine "enthält"-Suche
     * zu ermöglichen.
     *
     * @param array $data Ein assoziatives Array von Feldern und den entsprechenden Suchwerten.
     *                    Beispiel: ['name' => 'John', 'email' => 'example']
     *                    Alle Felder werden mit dem `LIKE`-Operator abgefragt, wobei die Werte mit `%` umschlossen werden,
     *                    um eine unscharfe Suche zu ermöglichen.
     *
     * @return null|T Die Entität, die den Suchkriterien entspricht, oder `null`, wenn keine Entität gefunden wird.
     *
     * @example
     * // Beispiel für die Suche nach einem Benutzer, dessen Name "John" enthält und die E-Mail-Adresse "example" enthält
     * $user = $repository->findOneByLike(['name' => 'John', 'email' => 'example']);
     *
     * if ($user !== null) {
     *     echo $user->getName();
     * }
     */
    public function findOneByLike(array $data)
    {
        $query = $this->queryBuilder()->selectOrm();

        foreach ($data as $field => $value) {
            $query->andWhere($field.' LIKE :'.$field);
            $query->setParameter($field, '%'.$value.'%');
        }

        return $query->getQuery()->getOneOrNullResult();
    }

    /**
     * Findet eine Liste von Entitäten basierend auf den angegebenen Suchkriterien unter Verwendung von LIKE-Operatoren.
     *
     * Diese Methode führt eine Suche aus, bei der alle Entitäten zurückgegeben werden, deren Felder mit den angegebenen
     * Suchwerten übereinstimmen. Die Suche verwendet den SQL-`LIKE`-Operator, um eine teilweise Übereinstimmung der
     * Feldwerte zu finden. Die Parameterwerte werden automatisch mit `%` umschlossen, um eine "enthält"-Suche zu ermöglichen.
     * Es können auch Sortierung (`orderBy`), Paginierung (`limit` und `offset`) und Filterkriterien angegeben werden.
     *
     * @param array $data Ein assoziatives Array von Feldern und den entsprechenden Suchwerten.
     *                    Beispiel: ['name' => 'John', 'email' => 'example']
     *                    Alle Felder werden mit dem `LIKE`-Operator abgefragt, wobei die Werte mit `%` umschlossen werden,
     *                    um eine unscharfe Suche zu ermöglichen.
     * @param array $orderBy Ein assoziatives Array von Feldern und den gewünschten Sortierrichtungen.
     *                        Beispiel: ['name' => 'ASC', 'email' => 'DESC']
     *                        Die Richtung kann entweder 'ASC' (aufsteigend) oder 'DESC' (absteigend) sein.
     * @param int|null $limit Die maximale Anzahl von Ergebnissen, die zurückgegeben werden sollen.
     *                        Wenn nicht angegeben, wird keine Begrenzung gesetzt.
     * @param int|null $offset Die Anzahl der Ergebnisse, die übersprungen werden sollen, bevor mit der Rückgabe
     *                         der Ergebnisse begonnen wird. Wird für die Paginierung verwendet.
     *
     * @return T[] Eine Liste von Entitäten, die den Suchkriterien entsprechen.
     *              Falls keine Entitäten gefunden werden, wird ein leeres Array zurückgegeben.
     *
     * @throws OrderByFormatException Wenn das Format des `orderBy`-Arrays ungültig ist (z.B. falsche Sortierrichtung).
     *
     * @example
     * // Beispiel für die Suche nach Benutzern, deren Name "John" enthält und die E-Mail-Adresse "example" enthält
     * $users = $repository->findByLike(
     *     ['name' => 'John', 'email' => 'example'],
     *     ['name' => 'ASC'], // Sortierung nach Name aufsteigend
     *     10, // Begrenzung der Rückgabe auf maximal 10 Ergebnisse
     *     0   // Start bei der ersten Seite (Offset = 0)
     * );
     *
     * foreach ($users as $user) {
     *     echo $user->getName();
     * }
     */
    public function findByLike(array $data, array $orderBy = [], int $limit = null, int $offset = null): array
    {
        $this->validateOrderBy($orderBy);
        $query = $this->queryBuilder()
            ->selectOrm()
            ->orderBy($orderBy)
        ;

        foreach ($data as $field => $value) {
            $query->andWhere($field.' LIKE :'.$field);
            $query->setParameter($field, '%'.$value.'%');
        }

        if(null !== $limit)
        {
            $query->setMaxResults($limit);
        }

        if(null !== $offset)
        {
            $query->setFirstResult($offset);
        }

        return $query->getQuery()->getResult();
    }

    /**
     * Findet eine Entität basierend auf einem Wertebereich für ein bestimmtes Feld.
     *
     * Diese Methode führt eine Suche nach einer Entität durch, bei der das angegebene Feld im Bereich zwischen den
     * angegebenen Start- und Endwerten liegt. Die Suche verwendet den SQL-`BETWEEN`-Operator. Der Bereich ist inklusiv,
     * d.h. die Start- und Endwerte werden mit einbezogen.
     *
     * @param string $field Das Feld, nach dem gesucht werden soll (z.B. "age", "created_at").
     * @param string $startValue Der Startwert des Bereichs (inklusive).
     * @param string $endValue Der Endwert des Bereichs (inklusive).
     *
     * @return null|T Gibt die Entität zurück, die den Kriterien entspricht, oder `null`, wenn keine Übereinstimmung gefunden wurde.
     *
     * @example
     * // Beispiel: Suche nach einem Benutzer, dessen Alter zwischen 18 und 30 Jahren liegt
     * $user = $repository->findOneByRange('age', '18', '30');
     *
     * if ($user) {
     *     echo $user->getName();
     * } else {
     *     echo "Kein Benutzer gefunden.";
     * }
     */
    public function findOneByRange(string $field, string $startValue, string $endValue)
    {
        return $this->queryBuilder()
            ->selectOrm()
            ->andWhere("$field BETWEEN :start_value AND :end_value")
            ->setMaxResults(1)
            ->setParameter("start_value", $startValue)
            ->setParameter("end_value", $endValue)
            ->getQuery()->getOneOrNullResult()
            ;
    }

    /**
     * Findet eine Liste von Entitäten basierend auf einem Wertebereich für ein bestimmtes Feld.
     *
     * Diese Methode führt eine Suche nach Entitäten durch, bei denen das angegebene Feld im Bereich zwischen den
     * angegebenen Start- und Endwerten liegt. Die Suche verwendet den SQL-`BETWEEN`-Operator. Der Bereich ist inklusiv,
     * d.h. die Start- und Endwerte werden mit einbezogen. Die Ergebnisse können nach den angegebenen Feldern sortiert und
     * durch die Parameter `limit` und `offset` paginiert werden.
     *
     * @param string $field Das Feld, nach dem gesucht werden soll (z.B. "age", "created_at").
     * @param string $startValue Der Startwert des Bereichs (inklusive).
     * @param string $endValue Der Endwert des Bereichs (inklusive).
     * @param array $orderBy Ein Array mit den Feldern und der Sortierreihenfolge (z.B. ['age' => 'ASC']).
     * @param int|null $limit Die maximale Anzahl der Ergebnisse, die zurückgegeben werden sollen. Standardmäßig keine Begrenzung.
     * @param int|null $offset Der Offset für die Paginierung der Ergebnisse. Standardmäßig keine Paginierung.
     *
     * @return T[] Eine Liste von Entitäten, die den Kriterien entsprechen.
     *
     * @throws OrderByFormatException Wenn das Format der `orderBy`-Parameter ungültig ist.
     *
     * @example
     * // Beispiel: Suche nach Benutzern, deren Alter zwischen 18 und 30 Jahren liegt,
     * // sortiert nach Alter in absteigender Reihenfolge und mit einer Begrenzung auf 10 Ergebnisse.
     * $users = $repository->findByRange('age', '18', '30', ['age' => 'DESC'], 10);
     *
     * // Beispiel: Suche nach Benutzern, deren Erstellungsdatum zwischen zwei Daten liegt,
     * // ohne Begrenzung der Anzahl der Ergebnisse.
     * $users = $repository->findByRange('created_at', '2020-01-01', '2024-01-01');
     */
    public function findByRange(string $field, string $startValue, string $endValue, array $orderBy = [], int $limit = null, int $offset = null): array
    {
        $this->validateOrderBy($orderBy);
        $query = $this->queryBuilder()
            ->selectOrm()
            ->andWhere("$field BETWEEN :start_value AND :end_value")
            ->orderBy($orderBy)
        ;

        $query->setParameter("start_value", $startValue);
        $query->setParameter("end_value", $endValue);

        if(null !== $limit)
        {
            $query->setMaxResults($limit);
        }

        if(null !== $offset)
        {
            $query->setFirstResult($offset);
        }

        return $query->getQuery()->getResult();
    }

    /**
     * Gibt eine Liste aller Entitäten zurück.
     *
     * Diese Methode lädt alle Entitäten des Typs, der mit diesem Repository verknüpft ist. Es werden optionale Parameter
     * für die Sortierung, Paginierung (mit `limit` und `offset`) sowie für die maximale Anzahl der Ergebnisse unterstützt.
     * Die Ergebnisse können nach den angegebenen Feldern und Richtungen sortiert werden. Die `limit`- und `offset`-Parameter
     * ermöglichen eine paginierte Rückgabe von Ergebnissen.
     *
     * @param array $orderBy Ein Array mit den Feldern und der Sortierreihenfolge (z.B. ['field' => 'ASC']).
     * @param int|null $limit Die maximale Anzahl der Ergebnisse, die zurückgegeben werden sollen. Standardmäßig keine Begrenzung.
     * @param int|null $offset Der Offset für die Paginierung der Ergebnisse. Standardmäßig keine Paginierung.
     *
     * @return T[] Eine Liste von Entitäten, die dem Repository entsprechen.
     *
     * @throws OrderByFormatException Wenn das Format der `orderBy`-Parameter ungültig ist.
     *
     * @example
     * // Beispiel: Alle Entitäten des Typs, sortiert nach 'created_at' in absteigender Reihenfolge.
     * $entities = $repository->findAll(['created_at' => 'DESC']);
     *
     * // Beispiel: Alle Entitäten, begrenzt auf 10 Ergebnisse, sortiert nach 'name' in aufsteigender Reihenfolge.
     * $entities = $repository->findAll(['name' => 'ASC'], 10);
     *
     * // Beispiel: Alle Entitäten, mit einem Offset von 20 und ohne Sortierung.
     * $entities = $repository->findAll([], null, 20);
     */
    public function findAll(array $orderBy = [], int $limit = null, int $offset = null): array
    {
        $this->validateOrderBy($orderBy);
        $query = $this->queryBuilder()
            ->selectOrm()
            ->orderBy($orderBy)
        ;

        if(null !== $limit)
        {
            $query->setMaxResults($limit);
        }

        if(null !== $offset)
        {
            $query->setFirstResult($offset);
        }

        return $query->getQuery()->getResult();
    }

    /**
     * Setzt Filterbedingungen für eine Datenbankabfrage basierend auf den übergebenen Daten.
     *
     * Diese Methode durchläuft das `$data`-Array und fügt für jedes Feld eine entsprechende Filterbedingung in den QueryBuilder
     * ein. Der Filter wird durch die Methode `makeCondition` definiert, die eine Bedingung auf Basis des Feldnamens und des Werts
     * erstellt. Wenn der Wert ein Array ist, werden die entsprechenden Parameter für jedes Element des Arrays gesetzt.
     *
     * @param array $data Ein assoziatives Array mit Filterkriterien, wobei der Schlüssel der Feldname und der Wert der Filterwert ist.
     * @param QueryBuilder $query Der QueryBuilder, zu dem die Filterbedingungen hinzugefügt werden.
     *
     * @return void
     *
     * @throws TypeNotSupportedException Wenn ein Wert in `$data` einen nicht unterstützten Typ aufweist, wird eine Ausnahme geworfen.
     *
     * @example
     * // Beispiel: Filter für "name" und "status"
     * $repository->setFilters([
     *     'name' => 'John',
     *     'status' => ['active', 'pending']
     * ], $query);
     */
    protected function setFilters(array $data, QueryBuilder $query): void
    {
        if (!empty($data)) {
            foreach ($data as $key => $value) {
                $key = $this->generateSnakeTailString($key);
                $query->andWhere($this->makeCondition($key, $value));
                if (!is_bool($value) and !is_array($value)) {
                    $query->setParameter($key, $value);
                }
                if (is_array($value)) {
                    foreach ($value as $subKey => $subValue) {
                        $query->setParameter($key . "_" . chr(97 + $subKey), $subValue);
                    }
                }
            }
        }
    }

}