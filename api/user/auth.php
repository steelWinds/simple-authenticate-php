<?php

namespace API\user;

require_once("{$_SERVER['DOCUMENT_ROOT']}/vendor/autoload.php");

use API\models\User;
use API\services\Database;
use PDOException;

header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Max-Age: 3600');

function auth()
{
    // Post data invalids

    $post_data = json_decode(file_get_contents('php://input'), true);

    $invalid_errors = array_filter(
        $post_data,
        function ($value) {
            return empty(trim($value));
        }
    );

    if (count($invalid_errors) !== 0) {
        http_response_code(406);

        $errors = array_map(function ($key) {
            return "{$key} is invalid";
        }, array_keys($invalid_errors));

        die(json_encode(
            [
                'error' => [
                    'message' => 'Invalid data',
                    'errors' => $errors
                ]
            ]
        ));
    }

    // Authentication user

    $user_model = new User(new Database());

    try {
        $token = $user_model->auth($post_data);
    } catch (PDOException $error) {
        die(json_encode([
            'error' => [
                'error_code' => $error->getCode(),
                'errors' => [$error->getMessage()]
            ]
        ]));
    }

    echo(
        json_encode([
            'token' => $token
        ])
    );
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    auth();
}
