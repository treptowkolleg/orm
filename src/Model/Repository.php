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
        return match (true) {
            is_null($value) => "$key IS NULL",
            is_bool($value) => $value ? "$key IS NOT NULL" : "$key IS NULL",
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
     * @return null|T
     */
    public function findOneByLike(array $data): ?object
    {
        $query = $this->queryBuilder()->selectOrm();

        foreach ($data as $field => $value) {
            $query->andWhere($field.' LIKE :'.$field);
            $query->setParameter($field, '%'.$value.'%');
        }

        return $query->getQuery()->getOneOrNullResult();
    }

    /**
     * @return T[]
     * @throws OrderByFormatException
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
     * @param string $field
     * @param string $startValue
     * @param string $endValue
     * @return null|T
     */
    public function findOneByRange(string $field, string $startValue, string $endValue): ?object
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
     * @param string $field
     * @param string $startValue
     * @param string $endValue
     * @param array $orderBy
     * @param int|null $limit
     * @param int|null $offset
     * @return T[]
     * @throws OrderByFormatException
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
                        $query->setParameter($key . "_" . chr(97 + $subKey), $subValue);
                    }
                }
            }
        }
    }

}