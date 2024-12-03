<?php

namespace TreptowKolleg\Model;

class Repository implements RepositoryInterface
{

    private string $entityClass;

    public function __construct(string $entityClass)
    {
        $this->entityClass = $entityClass;
    }

    protected function queryBuilder(string $alias = null): QueryBuilder
    {
        return new QueryBuilder($this->entityClass, $alias);
    }

    private function makeCondition(string $key, $value): string
    {
        return match (strtoupper(gettype($value))) {
            'NULL' => "$key IS NULL",
            'BOOLEAN' => ($value) ? "$key IS NOT NULL" : "$key IS NULL",
            default => "$key = :$key",
        };
    }

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

    public function findOneBy(array $data)
    {
        $query = $this->queryBuilder()->selectOrm();

        if(0 !== count($data))
        {
            foreach ($data as $key => $value)
            {
                $query->andWhere($this->makeCondition($key, $value));
                if(!is_bool($value))
                {
                    $query->setParameter($key, $value);
                }
            }
        }

        return $query->setMaxResults(1) ->getQuery()->getOneOrNullResult();
    }

    public function findBy(array $data, array $orderBy = [], int $limit = null, int $offset = null): array
    {
        $query = $this->queryBuilder()
            ->selectOrm()
            ->orderBy($orderBy)
        ;

        if(0 !== count($data))
        {
            foreach ($data as $key => $value)
            {
                $query->andWhere($this->makeCondition($key, $value));
                if(!is_bool($value))
                {
                    $query->setParameter($key, $value);
                }
            }
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

    public function findAll(array $orderBy = [], int $limit = null, int $offset = null): array
    {
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

}