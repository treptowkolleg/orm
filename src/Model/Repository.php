<?php

namespace TreptowKolleg\Model;

class Repository implements RepositoryInterface
{

    private string $entityClass;

    private QueryBuilder $qm;

    public function __construct(string $entityClass)
    {
        $this->entityClass = $entityClass;
    }

    protected function queryBuilder(string $alias = null): QueryBuilder
    {
        return $this->qm = new QueryBuilder($this->entityClass, $alias);
    }

    public function find(int|string $id)
    {
        // TODO: Implement find() method.
    }

    public function findOneBy(array $data)
    {
        // TODO: Implement findOneBy() method.
    }

    public function findBy(array $data, array $orderBy = [], int $limit = null, int $offset = null): array
    {
        // TODO: Implement findBy() method.
    }

    public function findAll(array $orderBy = [], int $limit = null, int $offset = null): array
    {
        // TODO: Implement findAll() method.
    }

}