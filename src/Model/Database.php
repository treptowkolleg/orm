<?php

namespace TreptowKolleg\ORM\Model;

use TreptowKolleg\Environment\DatabaseContainer;
use TreptowKolleg\Environment\Environment;

class Database
{

    private \PDO $db;

    public function __construct(\PDO $db = null)
    {
        if (null == $db) {
            $env = new Environment();
            $env->addContainer(new DatabaseContainer());
            $this->db = $env->getDatabaseObject();
        } else {
            $this->db = $db;
        }
        $this->db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $this->db->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_CLASS);
        $this->db->setAttribute(\PDO::ATTR_CASE, \PDO::CASE_NATURAL);
    }

    public function getConnection(): ?\PDO
    {
        return $this->db;
    }

}