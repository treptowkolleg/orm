<?php

namespace TreptowKolleg\Model;

use ReflectionClass;
use ReflectionException;
use TreptowKolleg\Environment;
use TreptowKolleg\ORM\Column;

class QueryBuilder
{

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
    protected \PDO $pdo;

    public function __construct(string $entity, string $alias = null)
    {
        $this->entity = $entity;
        try {
            $this->reflectionClass = $this->setEntityClass($entity);
        } catch (ReflectionException $e) {
        }
        $this->alias = $alias;

        $environment = new Environment();
        $this->pdo = $environment->getDatabaseObject();
    }

    /**
     * @throws ReflectionException
     */
    private function setEntityClass($entity): ReflectionClass
    {
        if (!class_exists($entity)) {
            throw new ReflectionException();
        }
        return new ReflectionClass($entity);
    }

    private function getTableNameFromEntity(): string
    {
        return $this->generateSnakeTailString($this->reflectionClass->getShortName());
    }

    private function getColumns(): string
    {
        $entityProperties = $this->reflectionClass->getProperties();
        $columns = false;
        foreach ($entityProperties as $property) {
            if(!empty($property->getAttributes(Column::class)))
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

    private function generateSnakeTailString(string $value): string
    {
        $valueAsArray = preg_split('/(?=[A-Z])/', $value);
        return strtolower(ltrim(implode('_', $valueAsArray),'_'));
    }

    /*
     * Beginn der Builder-Methoden für die SQL-Queries
     */

    /**
     * @param string $fields
     * @return $this
     */
    public function select(string $fields): QueryBuilder
    {
        $this->projection[] = $fields;
        return $this;
    }

    /**
     * @return $this Übergibt alle Klasseneigenschaften der Sichtbarkeit "protected, public" als kommagetrennte
     * Snake-Tail-Zeichenkette an die SQL-Abfrage
     */
    public function selectOrm(): QueryBuilder
    {
        $this->projection[] = $this->getColumns();
        return $this;
    }

    /**
     * @param string $fields
     * @return $this
     */
    public function selectDistinct(string $fields): QueryBuilder
    {
        $this->projection[] = "DISTINCT {$fields}";
        return $this;
    }

    /**
     * @param string $table
     * @param string $alias
     * @param string $condition
     * @return $this
     */
    public function join(string $table, string $alias, string $condition): QueryBuilder
    {
        $this->joins[] = "INNER JOIN $table $alias ON ($condition)";
        return $this;
    }

    /**
     * @param string $table
     * @param string $alias
     * @return $this
     */
    public function leftJoin(string $table, string $alias, string $condition): QueryBuilder
    {
        $this->joins[] = "LEFT JOIN $table $alias ON ($condition)";
        return $this;
    }

    /**
     * @param string $condition
     * @return $this
     */
    public function andWhere(string $condition): QueryBuilder
    {
        if(0 == count($this->conditions))
        {
            $this->conditions[] = "$condition";
        } else {
            $this->conditions[] = "AND $condition";
        }
        return $this;
    }

    /**
     * @param string $condition
     * @return $this
     */
    public function orWhere(string $condition): QueryBuilder
    {
        if(0 == count($this->conditions))
        {
            $this->conditions[] = "$condition";
        } else {
            $this->conditions[] = "OR $condition";
        }
        return $this;
    }

    /**
     * @param string $key
     * @param string $value
     * @return $this
     */
    public function setParameter(string $key, string $value): QueryBuilder
    {
        $this->parameters[$key] = $value;
        return $this;
    }

    /**
     * @param int $row
     * @return $this
     */
    public function setFirstResult(int $row): QueryBuilder
    {
        $this->offset = "OFFSET $row";
        return $this;
    }

    /**
     * @param int $amount
     * @return $this
     */
    public function setMaxResults(int $amount): QueryBuilder
    {
        $this->limit = "LIMIT $amount";
        return $this;
    }

    /**
     * @param array|string $data Entweder ein assoziatives Array mit Spalten und Sortierrichtung oder eine
     * Zeichenkette mit der sortierenden Spalte
     * @param string $direction Laufrichtung der Sortierung entweder ASC oder DESC
     * @return $this
     */
    public function orderBy(array|string $data, string $direction = 'asc'): QueryBuilder
    {
        if (is_array($data) and 0 !== count($data))
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

    public function groupBy(string $field): QueryBuilder
    {
        $this->groupBy[] = $field;
        return $this;
    }

    public function andHaving(string $condition): QueryBuilder
    {
        if(0 == count($this->groupConditions))
        {
            $this->groupConditions[] = "$condition";
        } else {
            $this->groupConditions[] = "AND $condition";
        }
        return $this;
    }

    public function orHaving(string $condition): QueryBuilder
    {
        if(0 == count($this->groupConditions))
        {
            $this->groupConditions[] = "$condition";
        } else {
            $this->groupConditions[] = "OR $condition";
        }
        return $this;
    }

    /**
     * @return $this
     */
    public function getQuery(): QueryBuilder
    {

        if(0 !== count($this->projection))
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

        if (0 !== count($this->joins))
        {
            $this->query['joins'] = implode(' ',$this->joins);
        }

        if (0 !== count($this->conditions))
        {
            $this->query['condition'] = "WHERE " . implode(' ',$this->conditions);
        }

        if (0 !== count($this->groupBy))
        {
            $this->query['group'] = "GROUP BY " . implode(',',$this->groupBy);
        }

        if (0 !== count($this->groupConditions))
        {
            $this->query['group_condition'] = "HAVING " . implode(' ',$this->groupConditions);
        }

        if (0 !== count($this->orderBy))
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

        $this->statement = $this->pdo->prepare( implode(' ',$this->query));

        foreach ($this->parameters as $key => $value)
        {
            $this->statement->bindValue(":{$key}", $value);
        }

        return $this;
    }

    /**
     * @return array Returns array containing designated objects
     */
    public function getResult(): array
    {
        if($this->statement->execute())
        {
            return $this->statement->fetchAll($this->pdo::FETCH_CLASS, $this->entity);
        }
        return [];
    }

    /**
     * @return false|mixed Returns single row as object
     */
    public function getOneOrNullResult(): mixed
    {
        if($this->statement->execute())
        {
            return $this->statement->fetchObject($this->entity);
        }
        return false;
    }

    /**
     * @return false|mixed Returns single scalar element
     */
    public function getSingleScalarResult(): mixed
    {
        if($this->statement->execute())
        {
            if($this->statement->rowCount() == 1)
            {
                return $this->statement->fetchColumn();
            }
        }
        return false;
    }

    /**
     * @return false|int Returns row count
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