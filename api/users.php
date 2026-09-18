<?php

require_once '../config/database.php';

header('Content-Type: application/json');


/*
|--------------------------------------------------------------------------
| Get Bearer Token
|--------------------------------------------------------------------------
*/

function getBearerToken(): ?string
{
    $headers = getallheaders();

    $authorization = $headers['Authorization']
        ?? $headers['authorization']
        ?? null;

    if (!$authorization) {
        return null;
    }

    if (
        preg_match(
            '/Bearer\s+(.+)/i',
            $authorization,
            $matches
        )
    ) {
        return trim($matches[1]);
    }

    return null;
}


/*
|--------------------------------------------------------------------------
| Authenticate API Request
|--------------------------------------------------------------------------
*/

$token = getBearerToken();

if (!$token) {

    http_response_code(401);

    echo json_encode([
        'success' => false,
        'message' => 'Bearer token required.'
    ]);

    exit;
}


$stmt = $pdo->prepare(
    "SELECT id, name, email, role
     FROM users
     WHERE api_token = ?"
);

$stmt->execute([$token]);

$authenticatedUser = $stmt->fetch();


if (!$authenticatedUser) {

    http_response_code(401);

    echo json_encode([
        'success' => false,
        'message' => 'Invalid API token.'
    ]);

    exit;
}


/*
|--------------------------------------------------------------------------
| Current User
|--------------------------------------------------------------------------
*/

$authenticatedUserId =
    (int) $authenticatedUser['id'];

$authenticatedUserRole =
    $authenticatedUser['role'];


/*
|--------------------------------------------------------------------------
| GET - Get One User
|--------------------------------------------------------------------------
*/

if (
    $_SERVER['REQUEST_METHOD'] === 'GET' &&
    isset($_GET['id'])
) {

    $id = filter_input(
        INPUT_GET,
        'id',
        FILTER_VALIDATE_INT
    );

    if (!$id) {

        http_response_code(400);

        echo json_encode([
            'success' => false,
            'message' => 'Invalid user ID.'
        ]);

        exit;
    }


    $stmt = $pdo->prepare(
        "SELECT
            id,
            name,
            email,
            role,
            created_at
         FROM users
         WHERE id = ?"
    );

    $stmt->execute([$id]);

    $user = $stmt->fetch();


    if (!$user) {

        http_response_code(404);

        echo json_encode([
            'success' => false,
            'message' => 'User not found.'
        ]);

        exit;
    }


    echo json_encode([
        'success' => true,
        'data' => $user
    ]);

    exit;
}


/*
|--------------------------------------------------------------------------
| GET - Get All Users
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    $stmt = $pdo->query(
        "SELECT
            id,
            name,
            email,
            role,
            created_at
         FROM users
         ORDER BY id DESC"
    );

    $users = $stmt->fetchAll();


    echo json_encode([
        'success' => true,
        'data' => $users
    ]);

    exit;
}


/*
|--------------------------------------------------------------------------
| Admin Authorization
|--------------------------------------------------------------------------
*/

if ($authenticatedUserRole !== 'admin') {

    http_response_code(403);

    echo json_encode([
        'success' => false,
        'message' => 'Admin privileges required.'
    ]);

    exit;
}


/*
|--------------------------------------------------------------------------
| Read JSON Request Body
|--------------------------------------------------------------------------
*/

$input = json_decode(
    file_get_contents('php://input'),
    true
);


/*
|--------------------------------------------------------------------------
| POST - Create User
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!is_array($input)) {

        http_response_code(400);

        echo json_encode([
            'success' => false,
            'message' => 'Invalid JSON body.'
        ]);

        exit;
    }


    $name = trim(
        $input['name'] ?? ''
    );

    $email = trim(
        $input['email'] ?? ''
    );

    $password =
        $input['password'] ?? '';

    $role =
        $input['role'] ?? 'user';


    if (
        $name === '' ||
        $email === '' ||
        $password === ''
    ) {

        http_response_code(422);

        echo json_encode([
            'success' => false,
            'message' =>
                'Name, email and password are required.'
        ]);

        exit;
    }


    if (
        !filter_var(
            $email,
            FILTER_VALIDATE_EMAIL
        )
    ) {

        http_response_code(422);

        echo json_encode([
            'success' => false,
            'message' => 'Invalid email address.'
        ]);

        exit;
    }


    if (strlen($password) < 6) {

        http_response_code(422);

        echo json_encode([
            'success' => false,
            'message' =>
                'Password must be at least 6 characters.'
        ]);

        exit;
    }


    if (
        !in_array(
            $role,
            ['user', 'admin'],
            true
        )
    ) {

        http_response_code(422);

        echo json_encode([
            'success' => false,
            'message' => 'Invalid role.'
        ]);

        exit;
    }


    $stmt = $pdo->prepare(
        "SELECT id
         FROM users
         WHERE email = ?"
    );

    $stmt->execute([$email]);


    if ($stmt->fetch()) {

        http_response_code(409);

        echo json_encode([
            'success' => false,
            'message' => 'Email already exists.'
        ]);

        exit;
    }


    $hashedPassword = password_hash(
        $password,
        PASSWORD_DEFAULT
    );


    $stmt = $pdo->prepare(
        "INSERT INTO users
        (
            name,
            email,
            password,
            role
        )
        VALUES (?, ?, ?, ?)"
    );

    $stmt->execute([
        $name,
        $email,
        $hashedPassword,
        $role
    ]);


    $newUserId =
        (int) $pdo->lastInsertId();


    http_response_code(201);

    echo json_encode([
        'success' => true,
        'message' => 'User created successfully.',
        'data' => [
            'id' => $newUserId,
            'name' => $name,
            'email' => $email,
            'role' => $role
        ]
    ]);

    exit;
}


/*
|--------------------------------------------------------------------------
| PUT - Update User
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'PUT') {

    if (!is_array($input)) {

        http_response_code(400);

        echo json_encode([
            'success' => false,
            'message' => 'Invalid JSON body.'
        ]);

        exit;
    }


    $id = filter_var(
        $input['id'] ?? null,
        FILTER_VALIDATE_INT
    );


    if (!$id) {

        http_response_code(400);

        echo json_encode([
            'success' => false,
            'message' => 'User ID is required.'
        ]);

        exit;
    }


    $stmt = $pdo->prepare(
        "SELECT
            id,
            name,
            email,
            role
         FROM users
         WHERE id = ?"
    );

    $stmt->execute([$id]);

    $existingUser = $stmt->fetch();


    if (!$existingUser) {

        http_response_code(404);

        echo json_encode([
            'success' => false,
            'message' => 'User not found.'
        ]);

        exit;
    }


    $name = array_key_exists(
        'name',
        $input
    )
        ? trim($input['name'])
        : $existingUser['name'];


    $email = array_key_exists(
        'email',
        $input
    )
        ? trim($input['email'])
        : $existingUser['email'];


    $role = array_key_exists(
        'role',
        $input
    )
        ? $input['role']
        : $existingUser['role'];


    $password =
        $input['password'] ?? '';


    if ($name === '') {

        http_response_code(422);

        echo json_encode([
            'success' => false,
            'message' => 'Name cannot be empty.'
        ]);

        exit;
    }


    if (
        !filter_var(
            $email,
            FILTER_VALIDATE_EMAIL
        )
    ) {

        http_response_code(422);

        echo json_encode([
            'success' => false,
            'message' => 'Invalid email address.'
        ]);

        exit;
    }


    if (
        !in_array(
            $role,
            ['user', 'admin'],
            true
        )
    ) {

        http_response_code(422);

        echo json_encode([
            'success' => false,
            'message' => 'Invalid role.'
        ]);

        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | Prevent Admin From Removing Own Admin Role
    |--------------------------------------------------------------------------
    */

    if (
        $id === $authenticatedUserId &&
        $role !== 'admin'
    ) {

        http_response_code(403);

        echo json_encode([
            'success' => false,
            'message' =>
                'You cannot remove your own admin role.'
        ]);

        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | Check Duplicate Email
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare(
        "SELECT id
         FROM users
         WHERE email = ?
         AND id != ?"
    );

    $stmt->execute([
        $email,
        $id
    ]);


    if ($stmt->fetch()) {

        http_response_code(409);

        echo json_encode([
            'success' => false,
            'message' =>
                'Email already belongs to another user.'
        ]);

        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | Update With or Without Password
    |--------------------------------------------------------------------------
    */

    if ($password !== '') {

        if (strlen($password) < 6) {

            http_response_code(422);

            echo json_encode([
                'success' => false,
                'message' =>
                    'Password must be at least 6 characters.'
            ]);

            exit;
        }


        $hashedPassword = password_hash(
            $password,
            PASSWORD_DEFAULT
        );


        $stmt = $pdo->prepare(
            "UPDATE users
             SET
                name = ?,
                email = ?,
                password = ?,
                role = ?
             WHERE id = ?"
        );

        $stmt->execute([
            $name,
            $email,
            $hashedPassword,
            $role,
            $id
        ]);

    } else {

        $stmt = $pdo->prepare(
            "UPDATE users
             SET
                name = ?,
                email = ?,
                role = ?
             WHERE id = ?"
        );

        $stmt->execute([
            $name,
            $email,
            $role,
            $id
        ]);
    }


    echo json_encode([
        'success' => true,
        'message' => 'User updated successfully.',
        'data' => [
            'id' => (int) $id,
            'name' => $name,
            'email' => $email,
            'role' => $role
        ]
    ]);

    exit;
}


/*
|--------------------------------------------------------------------------
| DELETE - Delete User
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {

    $id = null;


    /*
    | ID can come from:
    | /api/users.php?id=5
    | or JSON body {"id":5}
    */

    if (isset($_GET['id'])) {

        $id = filter_var(
            $_GET['id'],
            FILTER_VALIDATE_INT
        );
    }


    if (
        !$id &&
        is_array($input) &&
        isset($input['id'])
    ) {

        $id = filter_var(
            $input['id'],
            FILTER_VALIDATE_INT
        );
    }


    if (!$id) {

        http_response_code(400);

        echo json_encode([
            'success' => false,
            'message' => 'User ID is required.'
        ]);

        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | Prevent Self Deletion
    |--------------------------------------------------------------------------
    */

    if (
        $id === $authenticatedUserId
    ) {

        http_response_code(403);

        echo json_encode([
            'success' => false,
            'message' =>
                'You cannot delete your own account.'
        ]);

        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | Check User Exists
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare(
        "SELECT id
         FROM users
         WHERE id = ?"
    );

    $stmt->execute([$id]);


    if (!$stmt->fetch()) {

        http_response_code(404);

        echo json_encode([
            'success' => false,
            'message' => 'User not found.'
        ]);

        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | Delete User
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare(
        "DELETE FROM users
         WHERE id = ?"
    );

    $stmt->execute([$id]);


    echo json_encode([
        'success' => true,
        'message' => 'User deleted successfully.',
        'data' => [
            'id' => (int) $id
        ]
    ]);

    exit;
}


/*
|--------------------------------------------------------------------------
| Unsupported HTTP Method
|--------------------------------------------------------------------------
*/

http_response_code(405);

echo json_encode([
    'success' => false,
    'message' => 'HTTP method not allowed.'
]);

exit;