<?php

namespace TreptowKolleg\ORM\Model;

use PDO;
use ReflectionClass;
use ReflectionException;
use TreptowKolleg\ORM\Attribute\Column;
use TreptowKolleg\ORM\Attribute\CreatedAt;
use TreptowKolleg\ORM\Attribute\UpdatedAt;

/**
 * @template T
 */
class QueryBuilder
{

    /**
     * @var class-string<T>
     */
    private string $entity;
    private ReflectionClass $reflectionClass;
    private ?string $alias;

    protected string $limit = "";
    protected string $offset = "";
    protected array $projection = [];
    protected array $conditions = [];
    protected array $groupConditions = [];
    protected array $parameters = [];
    protected array $query = [];
    protected array $joins = [];
    protected array $groupBy = [];
    protected array $orderBy = [];

    protected \PDOStatement $statement;
    protected PDO $pdo;

    /**
     * @param PDO $pdo
     * @param class-string<T> $entity
     * @param string|null $alias
     */

    public function __construct(PDO $pdo, string $entity, string $alias = null)
    {
        $this->entity = $entity;
        try {
            $this->reflectionClass = $this->setEntityClass($entity);
        } catch (ReflectionException $e) {
        }
        $this->alias = $alias;
        $this->pdo = $pdo;
    }

    /**
     * Setzt die Entitätsklasse für den QueryBuilder und gibt ein ReflectionClass-Objekt zurück.
     *
     * @param string $entity Der Name der Entitätsklasse.
     * @return ReflectionClass Das ReflectionClass-Objekt der Entität.
     * @throws ReflectionException Wenn die Entitätsklasse nicht gefunden wird.
     */
    private function setEntityClass(string $entity): ReflectionClass
    {
        if (!class_exists($entity)) {
            throw new ReflectionException();
        }
        return new ReflectionClass($entity);
    }

    /**
     * Gibt den Tabellennamen basierend auf dem Entitätsnamen im Snake-Tail-Format zurück.
     *
     * @return string Der Tabellennamen im Snake-Tail-Format.
     */
    private function getTableNameFromEntity(): string
    {
        return $this->generateSnakeTailString($this->reflectionClass->getShortName());
    }

    /**
     * Gibt eine durch Kommas getrennte Liste der Spalten aus der Entität zurück.
     *
     * @return string Eine durch Kommas getrennte Liste der Spaltennamen.
     */
    private function getColumns(): string
    {
        $entityProperties = $this->reflectionClass->getProperties();
        $columns = false;
        foreach ($entityProperties as $property) {
            if(!empty($property->getAttributes(Column::class)) or !empty($property->getAttributes(CreatedAt::class)) or !empty($property->getAttributes(UpdatedAt::class)))
            {
                $propertyNameAsArray = preg_split('/(?=[A-Z])/', $property->getName());
                $propertyNameAsSnakeTail = strtolower(implode('_', $propertyNameAsArray));
                if(null !== $this->alias)
                {
                    $columns .= "{$this->alias}.{$propertyNameAsSnakeTail} AS {$property->getName()},";
                } else {
                    $columns .= "{$propertyNameAsSnakeTail} AS {$property->getName()},";
                }

            }
        }
        return rtrim($columns, ',');
    }

    /**
     * Wandelt eine Zeichenkette im CamelCase-Format in eine Snake-Tail-Zeichenkette um.
     *
     * @param string $value Der zu konvertierende String.
     * @return string Der konvertierte String im Snake-Tail-Format.
     */
    public function generateSnakeTailString(string $value): string
    {
        $valueAsArray = preg_split('/(?=[A-Z])/', $value);
        return strtolower(ltrim(implode('_', $valueAsArray),'_'));
    }

    /*
     * Beginn der Builder-Methoden für die SQL-Queries
     */

    /**
     * Fügt eine SELECT-Klausel mit den angegebenen Feldern zur Abfrage hinzu.
     *
     * @param string $fields Die auszuwählenden Felder.
     * @return $this Der QueryBuilder, um eine Methode in einer Kette weiter aufzurufen.
     */
    public function select(string $fields): QueryBuilder
    {
        $this->projection[] = $fields;
        return $this;
    }

    /**
     * Fügt eine SELECT-Klausel für alle Klasseneigenschaften mit den Attributen Column, CreatedAt oder UpdatedAt hinzu.
     *
     * @return $this Der QueryBuilder, um eine Methode in einer Kette weiter aufzurufen.
     */
    public function selectOrm(): QueryBuilder
    {
        $this->projection[] = $this->getColumns();
        return $this;
    }

    /**
     * Fügt eine SELECT DISTINCT-Klausel mit den angegebenen Feldern zur Abfrage hinzu.
     *
     * @param string $fields Die auszuwählenden Felder.
     * @return $this Der QueryBuilder, um eine Methode in einer Kette weiter aufzurufen.
     */
    public function selectDistinct(string $fields): QueryBuilder
    {
        $this->projection[] = "DISTINCT {$fields}";
        return $this;
    }

    /**
     * Fügt eine INNER JOIN-Klausel mit den angegebenen Parametern hinzu.
     *
     * @param string $table Der Name der zu joinenden Tabelle.
     * @param string $alias Das Alias für die Tabelle.
     * @param string $condition Die ON-Bedingung für den JOIN.
     * @return $this Der QueryBuilder, um eine Methode in einer Kette weiter aufzurufen.
     */
    public function join(string $table, string $alias, string $condition): QueryBuilder
    {
        $this->joins[] = "INNER JOIN $table $alias ON ($condition)";
        return $this;
    }

    /**
     * Fügt eine LEFT JOIN-Klausel mit den angegebenen Parametern hinzu.
     *
     * @param string $table Der Name der zu joinenden Tabelle.
     * @param string $alias Das Alias für die Tabelle.
     * @param string $condition Die ON-Bedingung für den JOIN.
     * @return $this Der QueryBuilder, um eine Methode in einer Kette weiter aufzurufen.
     */
    public function leftJoin(string $table, string $alias, string $condition): QueryBuilder
    {
        $this->joins[] = "LEFT JOIN $table $alias ON ($condition)";
        return $this;
    }

    /**
     * Fügt eine WHERE-Klausel mit der angegebenen Bedingung hinzu (AND-Verknüpfung).
     *
     * @param string $condition Die WHERE-Bedingung.
     * @return $this Der QueryBuilder, um eine Methode in einer Kette weiter aufzurufen.
     */
    public function andWhere(string $condition): QueryBuilder
    {
        if(empty($this->conditions))
        {
            $this->conditions[] = "$condition";
        } else {
            $this->conditions[] = "AND $condition";
        }
        return $this;
    }

    /**
     * Fügt eine WHERE-Klausel mit der angegebenen Bedingung hinzu (OR-Verknüpfung).
     *
     * @param string $condition Die WHERE-Bedingung.
     * @return $this Der QueryBuilder, um eine Methode in einer Kette weiter aufzurufen.
     */
    public function orWhere(string $condition): QueryBuilder
    {
        if(empty($this->conditions))
        {
            $this->conditions[] = "$condition";
        } else {
            $this->conditions[] = "OR $condition";
        }
        return $this;
    }

    /**
     * Setzt einen Parameter für die Abfrage.
     *
     * @param string $key Der Name des Parameters.
     * @param string $value Der Wert des Parameters.
     * @return $this Der QueryBuilder, um eine Methode in einer Kette weiter aufzurufen.
     */
    public function setParameter(string $key, string $value): QueryBuilder
    {
        $this->parameters[$key] = $value;
        return $this;
    }

    /**
     * Setzt die Zeilenzahl für die OFFSET-Klausel der Abfrage.
     *
     * @param int $row Die zu überspringende Anzahl von Zeilen.
     * @return $this Der QueryBuilder, um eine Methode in einer Kette weiter aufzurufen.
     */
    public function setFirstResult(int $row): QueryBuilder
    {
        $this->offset = "OFFSET $row";
        return $this;
    }

    /**
     * Setzt die maximale Anzahl von Ergebnissen für die LIMIT-Klausel der Abfrage.
     *
     * @param int $amount Die maximale Anzahl der Ergebnisse.
     * @return $this Der QueryBuilder, um eine Methode in einer Kette weiter aufzurufen.
     */
    public function setMaxResults(int $amount): QueryBuilder
    {
        $this->limit = "LIMIT $amount";
        return $this;
    }

    /**
     * Fügt eine ORDER BY-Klausel mit den angegebenen Feldern und der Sortierreihenfolge hinzu.
     *
     * @param array|string $data Die zu sortierenden Felder oder eine einzelne Sortierbedingung.
     * @param string $direction Die Richtung der Sortierung, entweder "ASC" oder "DESC".
     * @return $this Der QueryBuilder, um eine Methode in einer Kette weiter aufzurufen.
     */
    public function orderBy(array|string $data, string $direction = 'asc'): QueryBuilder
    {
        if (is_array($data) and !empty($data))
        {
            foreach ($data as $field => $direction)
            {
                $this->orderBy[$field] = $direction;
            }
            return $this;
        }

        if(is_string($data))
        {
            $this->orderBy[$data] = $direction;
        }

        return $this;
    }

    /**
     * Fügt eine GROUP BY-Klausel mit dem angegebenen Feld zur Abfrage hinzu.
     *
     * @param string $field Das Feld für die GROUP BY-Klausel.
     * @return $this Der QueryBuilder, um eine Methode in einer Kette weiter aufzurufen.
     */
    public function groupBy(string $field): QueryBuilder
    {
        $this->groupBy[] = $field;
        return $this;
    }

    /**
     * Fügt eine HAVING-Klausel mit der angegebenen Bedingung hinzu (AND-Verknüpfung).
     *
     * @param string $condition Die HAVING-Bedingung.
     * @return $this Der QueryBuilder, um eine Methode in einer Kette weiter aufzurufen.
     */
    public function andHaving(string $condition): QueryBuilder
    {
        if(empty($this->groupConditions))
        {
            $this->groupConditions[] = "$condition";
        } else {
            $this->groupConditions[] = "AND $condition";
        }
        return $this;
    }

    /**
     * Fügt eine HAVING-Klausel mit der angegebenen Bedingung hinzu (OR-Verknüpfung).
     *
     * @param string $condition Die HAVING-Bedingung.
     * @return $this Der QueryBuilder, um eine Methode in einer Kette weiter aufzurufen.
     */
    public function orHaving(string $condition): QueryBuilder
    {
        if(empty($this->groupConditions))
        {
            $this->groupConditions[] = "$condition";
        } else {
            $this->groupConditions[] = "OR $condition";
        }
        return $this;
    }

    /**
     * Generiert die SQL-Abfrage mit den festgelegten Parametern.
     *
     * @return $this Der QueryBuilder, um eine Methode in einer Kette weiter aufzurufen.
     */
    public function getQuery(): QueryBuilder
    {

        if(!empty($this->projection))
        {
            $this->query['projection'] = "SELECT " . implode(',',$this->projection);
        } else {
            $this->query['projection'] = "SELECT *";
        }

        $this->query['table'] = "FROM {$this->getTableNameFromEntity()}";

        if(null !== $this->alias)
        {
            $this->query['table'] .= " $this->alias ";
        }

        if (!empty($this->joins))
        {
            $this->query['joins'] = implode(' ',$this->joins);
        }

        if (!empty($this->conditions))
        {
            $this->query['condition'] = "WHERE " . implode(' ',$this->conditions);
        }

        if (!empty($this->groupBy))
        {
            $this->query['group'] = "GROUP BY " . implode(',',$this->groupBy);
        }

        if (!empty($this->groupConditions))
        {
            $this->query['group_condition'] = "HAVING " . implode(' ',$this->groupConditions);
        }

        if (!empty($this->orderBy))
        {
            $order = "ORDER BY ";
            foreach ($this->orderBy as $key => $value)
            {
                $value = strtoupper($value);
                $order .= "$key $value,";
            }
            $this->query['order'] = rtrim($order,',');

        }

        $this->query['limit'] = $this->limit;

        $this->query['offset'] = $this->offset;

        $this->statement = $this->pdo->prepare( $query = implode(' ',$this->query));

        if(DEBUG) echo "$query\n";

        foreach ($this->parameters as $key => $value)
        {
            $this->statement->bindValue(":{$key}", $value);
        }

        return $this;
    }

    /**
     * Führt die SQL-Abfrage aus und gibt ein Array der Ergebnisse zurück.
     *
     * @return T[] Ein Array von Objekten der Entität.
     */
    public function getResult(): array
    {
        $result = [];
        if($this->statement->execute())
        {
            $result = $this->statement->fetchAll($this->pdo::FETCH_CLASS, $this->entity);
        }
        return $result;
    }

    /**
     * Führt die SQL-Abfrage aus und gibt ein einzelnes Ergebnis als Objekt zurück.
     *
     * @return null|T Ein einzelnes Entitätsobjekt oder null.
     */
    public function getOneOrNullResult()
    {
        $result = null;
        if($this->statement->execute())
        {
            $result =  $this->statement->fetchObject($this->entity);
        }
        return $result;
    }

    /**
     * Führt die SQL-Abfrage aus und gibt einen einzelnen Skalaren Wert zurück.
     *
     * @return null|mixed Der einzelne Skalare Wert oder null.
     */
    public function getSingleScalarResult(): mixed
    {
        $result = null;
        if($this->statement->execute())
        {
            if($this->statement->rowCount() == 1)
            {
                $result = $this->statement->fetchColumn();
            }
        }
        return $result;
    }

    /**
     * Führt die SQL-Abfrage aus und gibt die Anzahl der betroffenen Zeilen zurück.
     *
     * @return false|int Die Anzahl der betroffenen Zeilen oder false bei einem Fehler.
     */
    public function getCountResult(): bool|int
    {
        if($this->statement->execute())
        {
            //die(print_r($this->query));
            return $this->statement->rowCount();
        }
        return false;
    }

}