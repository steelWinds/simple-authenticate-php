<?php

namespace API\models;

require_once("{$_SERVER['DOCUMENT_ROOT']}/vendor/autoload.php");

use API\services\Database;
use PDOException;
use PDO;
use Exception;

class User
{
    protected static $db_connect;

    private string $table_name = 'users';
    private string $dbname = 'test_rest_api';
    private array $errors = [];

    public int $id;
    public string $fullname;
    public string $login;
    public string $password;
    public string $token;

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

    public function create(array $user_data_array): string
    {

        $user_data_array = array_map(
            function ($value) {
                return htmlspecialchars(strip_tags(trim($value)));
            },
            $user_data_array
        );

        $query = "
            INSERT INTO {$this->table_name} (fullname, login, password, token)
            VALUES (:fullname, :login, :password, :token);
        ";

        try {
            $stmt = self::$db_connect->prepare($query);
        } catch (PDOException $error) {
            throw $error;
        }

        $token = bin2hex(random_bytes(16));
        $hash_password = password_hash($user_data_array['password'], PASSWORD_DEFAULT);

        if (!(filter_var($user_data_array['login'], FILTER_VALIDATE_EMAIL))) {
            http_response_code(401);

            die(json_encode([
                'error' => [
                    'error_code' => null,
                    'errors' => ['email is not valid']
                ]
            ]));
        }

        $stmt->bindParam(':fullname', $user_data_array['fullname']);
        $stmt->bindParam(':login', $user_data_array['login']);
        $stmt->bindParam(':password', $hash_password);
        $stmt->bindParam(':token', $token);

        if ($stmt->execute()) {
            return $token;
        }

        throw new PDOException('Execute error');
    }

    public function auth(array $user_data_array): string
    {
        $user_data_array = array_map(
            function ($value) {
                return htmlspecialchars(strip_tags(trim($value)));
            },
            $user_data_array
        );

        if (!(filter_var($user_data_array['login'], FILTER_VALIDATE_EMAIL))) {
            http_response_code(401);

            die(json_encode([
                'error' => [
                    'error_code' => null,
                    'errors' => ['email is not valid']
                ]
            ]));
        }

        $query = "
            SELECT password, token FROM {$this->table_name}
            WHERE login='{$user_data_array['login']}';
        ";

        try {
            $stmt = self::$db_connect->prepare($query);
        } catch (PDOException $error) {
            throw $error;
        }

        if ($stmt->execute()) {
            if ($stmt->rowCount() === 0) {
                http_response_code(400);

                die(json_encode([
                    'error' => [
                        'error_msg' => 'Not found this user'
                    ]
                ]));
            }

            $auth_data = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!(password_verify($user_data_array['password'], $auth_data['password']))) {
                http_response_code(400);

                die(json_encode([
                    'errors' => [
                        'error_msg' => 'Invalid data',
                        'errors' => 'password wrong'
                    ]
                ]));
            }

            return $auth_data['token'];
        }

        throw new PDOException('Execute error');
    }
}
