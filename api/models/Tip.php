<?php

namespace API\models;

require_once("{$_SERVER['DOCUMENT_ROOT']}/vendor/autoload.php");

use API\services\Database;
use PDOException;
use PDO;
use Exception;

class Tip
{
    protected static $db_connect;

    private string $table_name = 'tips';
    private string $related_table_name = 'users';
    private string $dbname = 'test_rest_api';
    private array $errors = [];

    public int $id;
    public string $content;


    public function __construct(
        Database $db
    ) {
        $dsn = "pgsql: host=localhost; dbname={$this->dbname}";
        $db_password = 'admin666';
        $db_admin = 'kirill';

        self::$db_connect = $db->getConnection(
            $dsn,
            $db_admin,
            $db_password
        );
    }

    public function read(string $token): array
    {
        $query = "
            SELECT 
                {$this->related_table_name}.fullname as user_id, 
                {$this->table_name}.id as tip_id, 
                {$this->table_name}.content as tip_content
            FROM {$this->related_table_name}
            LEFT JOIN {$this->table_name}
            ON {$this->related_table_name}.id = {$this->table_name}.user_id
            WHERE {$this->related_table_name}.token = '{$token}';
        ";

        try {
            $stmt = self::$db_connect->prepare($query);
        } catch (PDOException $error) {
            die(json_encode([
                'error' => [
                    'error_code' => $error->getCode(),
                    'error_message' => $error->getMessage()
                ]
            ]));
        }

        if (!$stmt->execute()) {
            throw new PDOException('Failed execute');
        }

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
