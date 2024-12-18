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
     * @param class-string<T> $entityClass
     * @throws EntityNotFoundException
     */
    public function __construct(string $entityClass)
    {
        if (!class_exists($entityClass)) {
            throw new EntityNotFoundException("The specified entity class '$entityClass' does not exist. Please ensure the class name is correct and properly auto loaded.");
        }
        $this->entityClass = $entityClass;
        $this->db = new Database();
    }

    protected function queryBuilder(string $alias = null): QueryBuilder
    {
        return new QueryBuilder($this->db->getConnection(), $this->entityClass, $alias);
    }

    /**
     * @throws TypeNotSupportedException
     */
    private function makeCondition(string $key, mixed $value): string
    {
        return match (gettype($value)) {
            'NULL' => "$key IS NULL",
            'boolean' => $value ? "$key IS NOT NULL" : "$key IS NULL",
            'array' => "$key IN (".implode(',', array_map(fn($v) => ":$v", array_keys($value))).")",
            'integer', 'double', 'string' => "$key = :$key",
            default => throw new TypeNotSupportedException("Unsupported type for condition value"),
        };
    }

    protected function generateSnakeTailString(string $value): string
    {
        $valueAsArray = preg_split('/(?=[A-Z])/', $value);
        return strtolower(ltrim(implode('_', $valueAsArray),'_'));
    }

    /**
     * @throws OrderByFormatException
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
     * @return null|T
     */
    public function find(int|string $id): ?object
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
     * @return null|T
     * @throws TypeNotSupportedException
     */
    public function findOneBy(array $data): ?object
    {
        $query = $this->queryBuilder()->selectOrm();

        $this->setFilters($data, $query);

        return $query->setMaxResults(1) ->getQuery()->getOneOrNullResult();
    }

    /**
     * @return T[]
     * @throws TypeNotSupportedException
     * @throws OrderByFormatException
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
     * @return T[]
     * @throws OrderByFormatException
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
     * @param array $data
     * @param QueryBuilder $query
     * @return void
     * @throws TypeNotSupportedException
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
                        $query->setParameter($subKey, $subValue);
                    }
                }
            }
        }
    }

}