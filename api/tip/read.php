<?php

namespace API\user;

require("{$_SERVER['DOCUMENT_ROOT']}/vendor/autoload.php");

use API\models\Tip;
use API\services\Database;
use PDOException;
use Exception;

header('Content-Type: application/json; charset=UTF-8');
header('Allow-Control-Access-Origin: *');
header('Allow-Control-Access-Methods: GET');
header('Allow-Control-Access-Headers: Authorization');

function read()
{
    $errors = [];
    $auth_token_segments = null;

    try {
        $auth_token_segments = explode(
            ' ',
            apache_request_headers()['Authorization']
        );

        if (count($auth_token_segments) !== 2 || $auth_token_segments[0] !== 'Token') {
            array_push($errors, 'Empty Authorization: Token header');
        }

        if (strlen($auth_token_segments[1]) <= 1) {
            array_push($errors, 'Token is very short');
        }

        if (count($errors) !== 0) {
            throw new Exception('Error');
        }
    } catch (Exception $error) {
        die(json_encode([
            'error' => [
                'errors' => $errors
            ]
        ]));
    }

    $auth_token = $auth_token_segments[1];
    $tip_model = new Tip(new Database());

    $all_tips = null;

    try {
        $all_tips = $tip_model->read($auth_token);
    } catch (PDOException $error) {
        die(json_encode([
            'error' => [
                'error_code' => $error->getCode(),
                'error_msg' => $error->getMessage()
            ]
        ]));
    }

    echo json_encode($all_tips);
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    read();
}
