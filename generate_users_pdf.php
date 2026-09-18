<?php

require_once 'config/database.php';
require_once 'includes/auth.php';
require_once 'vendor/autoload.php';

requireAdmin();

/*
|--------------------------------------------------------------------------
| Get Users
|--------------------------------------------------------------------------
*/

$stmt = $pdo->query(
    "SELECT id, name, email, role, created_at
     FROM users
     ORDER BY id DESC"
);

$users = $stmt->fetchAll();

/*
|--------------------------------------------------------------------------
| Create PDF
|--------------------------------------------------------------------------
*/

$pdf = new TCPDF();

$pdf->SetCreator('Bayan Auth System');
$pdf->SetAuthor('Bayan Auth System');
$pdf->SetTitle('Users Report');
$pdf->SetSubject('User Management Report');

$pdf->SetMargins(15, 15, 15);
$pdf->SetAutoPageBreak(true, 15);

$pdf->AddPage();

/*
|--------------------------------------------------------------------------
| PDF Heading
|--------------------------------------------------------------------------
*/

$html = '
<h1 style="text-align:center;">Users Report</h1>

<p>
Generated from the Bayan Auth System.
</p>

<table border="1" cellpadding="6">
    <thead>
        <tr>
            <th width="10%"><b>ID</b></th>
            <th width="25%"><b>Name</b></th>
            <th width="35%"><b>Email</b></th>
            <th width="15%"><b>Role</b></th>
            <th width="15%"><b>Created</b></th>
        </tr>
    </thead>
    <tbody>
';

/*
|--------------------------------------------------------------------------
| Add Users
|--------------------------------------------------------------------------
*/

foreach ($users as $user) {

    $html .= '
        <tr>
            <td>' . htmlspecialchars($user['id']) . '</td>
            <td>' . htmlspecialchars($user['name']) . '</td>
            <td>' . htmlspecialchars($user['email']) . '</td>
            <td>' . htmlspecialchars($user['role']) . '</td>
            <td>' . htmlspecialchars($user['created_at']) . '</td>
        </tr>
    ';
}

$html .= '
    </tbody>
</table>
';

/*
|--------------------------------------------------------------------------
| Write PDF
|--------------------------------------------------------------------------
*/

$pdf->writeHTML($html, true, false, true, false, '');

$pdf->Output(
    'users-report.pdf',
    'I'
);