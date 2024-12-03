<?php

namespace TreptowKolleg\Model;

use ReflectionClass;
use ReflectionException;
use TreptowKolleg\ORM\Column;

class QueryBuilder
{

    private string $entity;
    private ReflectionClass $reflectionClass;
    private ?string $alias;

    public function __construct(string $entity, string $alias = null)
    {
        $this->entity = $entity;
        try {
            $this->reflectionClass = $this->setEntityClass($entity);
        } catch (ReflectionException $e) {
        }
        $this->alias = $alias;
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

}