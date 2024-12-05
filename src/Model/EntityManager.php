<?php

namespace TreptowKolleg\ORM\Model;

use TreptowKolleg\ORM\ORM\AutoGenerated;
use TreptowKolleg\ORM\ORM\Column;
use TreptowKolleg\ORM\ORM\Id;
use TreptowKolleg\ORM\ORM\ManyToOne;
use TreptowKolleg\ORM\ORM\Types;

class EntityManager
{

    private \PDO $db;
    private ?\ReflectionClass $reflectionClass;
    private ?object $entity;
    private ?string $tableName;
    private array $primaryKey = [];
    private array $columns = [];
    private array $query = [];
    private array $parameters = [];
    private array $tableColumns = [];
    private string $tpk = "";
    private array $fk = [];


    public function __construct()
    {
        $this->db = (new Database())->getConnection();
        $this->db->beginTransaction();
    }

    public function dropTable(object|string $entity): void
    {
        if(is_string($entity)) {
            $entity = new $entity();
        }
        try {
            $this->reflectionClass = new \ReflectionClass($entity);
            $this->setTableName();
            $query = "DROP TABLE IF EXISTS {$this->tableName}";
            $statement = $this->db->prepare($query);
            $statement->execute();
            $this->reset();
        } catch (\ReflectionException $e) {

        }
    }

    public function createTable(object|string $entity): void
    {
        if(is_string($entity)) {
            $entity = new $entity();
        }
        try {
            $this->reflectionClass = new \ReflectionClass($entity);
        } catch (\ReflectionException $e) {

        }
        $this->entity = $entity;
        if(!$this->db->inTransaction())
            $this->db->beginTransaction();
        $this->setTableName();
        foreach ($this->reflectionClass->getProperties() as $property) {
            $attributes = $property->getAttributes();
            $propertyName = $this->generateSnakeTailString($property->getName());
            foreach ($attributes as $attribute) {
                $attribute = $attribute->newInstance();
                if ($attribute instanceof Id) {
                    $this->tpk = "PRIMARY KEY ($propertyName)";
                }
                if ($attribute instanceof AutoGenerated) {
                    $this->tableColumns[$propertyName]["ai"] = "AUTO_INCREMENT";
                }
                if ($attribute instanceof Column) {
                    $this->tableColumns[$propertyName]["type"] = match ($attribute->getType()){
                        Types::Integer, Types::ManyToOne => "INT",
                        Types::OneToMany, Types::ManyToMany => null,
                        Types::String => "VARCHAR",
                        Types::Boolean => "TINYINT",
                        Types::DateTime => "DATETIME",
                        Types::Date => "DATE",
                        Types::Json => "JSON",
                    };
                    if(in_array($attribute->getType(),[Types::String, Types::Json])){
                        $this->tableColumns[$propertyName]["type"] .= "({$attribute->getLength()})";
                    }
                    $this->tableColumns[$propertyName]["null"] = $attribute->isNullable() ? "NULL" : "NOT NULL";
                }
                if ($attribute instanceof ManyToOne) {
                    $table = $this->generateSnakeTailString($attribute->getTargetEntity()->getShortName());
                    $column = $this->generateSnakeTailString($attribute->getTargetColumn());
                    $this->fk[$table]["fk"] = $column;
                    $this->fk[$table]["column"] = $propertyName;
                }
            }
        }

        $query = "CREATE TABLE IF NOT EXISTS $this->tableName (";
        foreach ($this->tableColumns as $name => $content) {
            $query .= "$name {$content["type"]} {$content["null"]}";
            if(array_key_exists("ai", $content)) {
                $query .= " {$content["ai"]}";
            }
            $query .= ",";
        }
        $query .= $this->tpk;
        foreach ($this->fk as $table => $column) {
            $query .= ", FOREIGN KEY ({$column["column"]}) REFERENCES $table({$column["fk"]})";
        }
        $query .= ")";
        $query .= "DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";

        echo "$query\n";
        $statement = $this->db->prepare($query);
        $statement->execute();
        $statement->closeCursor();

        $this->reset();
    }

    public function persist($entity): void
    {
        try {
            $this->reflectionClass = new \ReflectionClass($entity);
            $this->entity = $entity;
            $properties = $this->reflectionClass->getProperties();
            if(!$this->db->inTransaction())
                $this->db->beginTransaction();
            foreach ($properties as $property) {
                if(!empty($property->getAttributes(Id::class))) {
                    if ($property->isInitialized($entity)) {
                        $this->update();
                    } else {
                        $this->insert();
                    }
                    break;
                }
            }

        } catch (\ReflectionException $e) {

        }
    }

    private function reset(): void
    {
        $this->reflectionClass = null;
        $this->entity = null;
        $this->tableName = null;
        $this->primaryKey = [];
        $this->columns = [];
        $this->query = [];
        $this->parameters = [];
        $this->tableColumns = [];
        $this->tpk = "";
        $this->fk = [];
    }

    public function flush(): void
    {
        $this->db->commit();
    }

    public function rollback(): void
    {
        $this->db->rollBack();
    }

    private function update(): void
    {
        $this->setTableName();
        $this->setColumns(true);
        $this->createQuery(true);
    }

    private function insert(): void
    {
        $this->setTableName();
        $this->setColumns();
        $this->createQuery();
    }

    private function createQuery(bool $update = false): void
    {
        if(!$update) {
            $this->query[] = "INSERT INTO {$this->tableName}";
            $this->query[] = "(" . implode(", ", $this->columns) . ")";
            $this->query[] = "VALUES";
            $this->query[] = "(" . implode(", ", array_keys($this->parameters)) . ")";
        } else {
            $this->query[] = "UPDATE {$this->tableName} SET";
            $string = "";
            foreach($this->parameters as $key => $value) {
                $key = ltrim($key, ":");
                $string .= "$key=:$key, ";
            }
            $this->query[] = rtrim($string, ", ");
            $keys = array_keys($this->primaryKey); $keyName = array_shift($keys);
            $this->query[] = "WHERE $keyName=:$keyName";
        }
        $statement = $this->db->prepare($query =implode(" ", $this->query));

        echo "$query\n";

        foreach($this->parameters as $key => $value) {
            $statement->bindValue($key, $value);
        }
        if($update) $statement->bindValue(":" . key($this->primaryKey),$this->primaryKey[key($this->primaryKey)]);
        $statement->execute();
        $statement->closeCursor();

        $this->reset();
    }

    private function setTableName(): void
    {
        $this->tableName = $this->generateSnakeTailString($this->reflectionClass->getShortName());
    }

    private function setColumns(bool $update = false): void
    {
        foreach ($this->reflectionClass->getProperties() as $property) {
            if(!empty($property->getAttributes(Column::class)) and empty($property->getAttributes(AutoGenerated::class)))
            {
                if($property->isInitialized($this->entity)) {
                    $this->columns[] = $parameter = $this->generateSnakeTailString($property->getName());
                    $this->parameters[":$parameter"] = $property->getValue($this->entity);
                }
            } elseif ($update and !empty($property->getAttributes(Column::class)) and !empty($property->getAttributes(AutoGenerated::class)))
            {
                $keyName = $this->generateSnakeTailString($property->getName());
                $this->primaryKey[$keyName] = $property->getValue($this->entity);
            }
        }
    }

    public function generateSnakeTailString(string $value): string
    {
        $valueAsArray = preg_split('/(?=[A-Z])/', $value);
        return strtolower(ltrim(implode('_', $valueAsArray),'_'));
    }

}