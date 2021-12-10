<?php

namespace API\services;

use PDO;
use PDOException;

class Database
{
    public function getConnection(
        string $dsn,
        string $user,
        string $pass
    ): PDO {
        return new PDO($dsn, $user, $pass);
    }
}
