<?php

namespace API\user;

require_once("{$_SERVER['DOCUMENT_ROOT']}/vendor/autoload.php");

use API\models\User;
use API\services\Database;
use PDOException;
use Exception;

header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Max-Age: 3600');

function auth()
{
    // Post data invalids

    try {
        $post_data = json_decode(file_get_contents('php://input'), true);

        if (empty($post_data)) {
            throw new Exception('Empty post data');
        }
    } catch (Exception $error) {
        http_response_code(400);

        die(json_encode([
            'error' => [
                'error_msg' => $error->getMessage()
            ]
        ]));
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
