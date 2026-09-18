<?php

require_once 'config/database.php';
require_once 'includes/auth.php';
require_once 'includes/csrf.php';

requireAdmin();

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    verifyCsrfToken();

    if (
        !isset($_FILES['file']) ||
        $_FILES['file']['error'] !== UPLOAD_ERR_OK
    ) {

        $error = 'Please select a valid file.';

    } else {

        $file = $_FILES['file'];

        $originalName = $file['name'];
        $tmpName = $file['tmp_name'];
        $fileSize = $file['size'];

        $maxSize = 5 * 1024 * 1024;

        if ($fileSize > $maxSize) {

            $error = 'File is too large. Maximum size is 5 MB.';

        } else {

            $allowedExtensions = [
                'pdf',
                'jpg',
                'jpeg',
                'png',
                'doc',
                'docx'
            ];

            $extension = strtolower(
                pathinfo($originalName, PATHINFO_EXTENSION)
            );

            if (
                !in_array(
                    $extension,
                    $allowedExtensions,
                    true
                )
            ) {

                $error = 'File type is not allowed.';

            } else {

                $storedName = bin2hex(
                    random_bytes(16)
                ) . '.' . $extension;

                $uploadDirectory = __DIR__ . '/uploads/';

                $filePath = $uploadDirectory . $storedName;

                if (
                    !move_uploaded_file(
                        $tmpName,
                        $filePath
                    )
                ) {

                    $error = 'Failed to save the uploaded file.';

                } else {

                    $fileType = mime_content_type(
                        $filePath
                    );

                    $relativePath =
                        'uploads/' . $storedName;

                    $stmt = $pdo->prepare(
                        "INSERT INTO uploaded_files
                        (
                            user_id,
                            original_name,
                            stored_name,
                            file_path,
                            file_size,
                            file_type
                        )
                        VALUES (?, ?, ?, ?, ?, ?)"
                    );

                    $stmt->execute([
                        $_SESSION['user_id'],
                        $originalName,
                        $storedName,
                        $relativePath,
                        $fileSize,
                        $fileType
                    ]);

                    $message =
                        'File uploaded successfully.';
                }
            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Upload File | SoftgeniousDev</title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: Arial, sans-serif;
            background:
                linear-gradient(
                    135deg,
                    #0f172a,
                    #1e293b,
                    #2563eb
                );
            color: #111827;
        }

        /* =========================
           NAVBAR
        ========================= */

        .navbar {
            background: rgba(15, 23, 42, 0.96);
            padding: 16px 5%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow:
                0 2px 15px rgba(0, 0, 0, 0.2);
        }

        .brand {
            color: white;
            text-decoration: none;
            font-size: 22px;
            font-weight: 700;
        }

        .brand span {
            color: #60a5fa;
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 22px;
        }

        .nav-links a {
            color: #cbd5e1;
            text-decoration: none;
            font-size: 14px;
        }

        .nav-links a:hover {
            color: white;
        }

        /* =========================
           PAGE
        ========================= */

        .page {
            width: 90%;
            max-width: 850px;
            margin: 55px auto;
        }

        .page-header {
            text-align: center;
            color: white;
            margin-bottom: 30px;
        }

        .page-header h1 {
            margin: 0 0 10px;
            font-size: 34px;
        }

        .page-header p {
            margin: 0;
            color: #cbd5e1;
            font-size: 15px;
        }

        /* =========================
           CARD
        ========================= */

        .card {
            background: white;
            border-radius: 18px;
            padding: 40px;
            box-shadow:
                0 20px 50px rgba(0, 0, 0, 0.25);
        }

        /* =========================
           MESSAGES
        ========================= */

        .message {
            padding: 13px 15px;
            border-radius: 9px;
            margin-bottom: 25px;
            font-size: 14px;
        }

        .success {
            background: #dcfce7;
            color: #166534;
            border: 1px solid #bbf7d0;
        }

        .error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        /* =========================
           UPLOAD BOX
        ========================= */

        .upload-box {
            border: 2px dashed #cbd5e1;
            border-radius: 15px;
            background: #f8fafc;
            padding: 45px 25px;
            text-align: center;
            transition: 0.2s;
            cursor: pointer;
        }

        .upload-box:hover,
        .upload-box.dragging {
            border-color: #2563eb;
            background: #eff6ff;
        }

        .upload-icon {
            width: 70px;
            height: 70px;
            margin: 0 auto 18px;
            border-radius: 50%;
            background: #dbeafe;
            color: #2563eb;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 34px;
            font-weight: bold;
        }

        .upload-box h2 {
            margin: 0 0 8px;
            font-size: 21px;
            color: #111827;
        }

        .upload-box p {
            margin: 0 0 22px;
            color: #6b7280;
            font-size: 14px;
        }

        /* =========================
           FILE INPUT
        ========================= */

        #file {
            display: none;
        }

        .browse-button {
            display: inline-block;
            padding: 12px 22px;
            border-radius: 8px;
            background: #2563eb;
            color: white;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.2s;
        }

        .browse-button:hover {
            background: #1d4ed8;
        }

        /* =========================
           SELECTED FILE
        ========================= */

        .selected-file {
            display: none;
            margin-top: 20px;
            padding: 13px 15px;
            border-radius: 9px;
            background: #eff6ff;
            color: #1e40af;
            font-size: 14px;
            word-break: break-word;
        }

        /* =========================
           UPLOAD BUTTON
        ========================= */

        .upload-button {
            width: 100%;
            margin-top: 25px;
            padding: 14px;
            border: none;
            border-radius: 9px;
            background: #111827;
            color: white;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.2s;
        }

        .upload-button:hover {
            background: #1f2937;
            transform: translateY(-1px);
        }

        /* =========================
           INFORMATION
        ========================= */

        .file-details {
            display: grid;
            grid-template-columns:
                repeat(3, 1fr);
            gap: 15px;
            margin-top: 25px;
        }

        .detail {
            background: #f8fafc;
            padding: 17px 12px;
            border-radius: 10px;
            text-align: center;
        }

        .detail strong {
            display: block;
            color: #111827;
            font-size: 13px;
            margin-bottom: 6px;
        }

        .detail span {
            color: #6b7280;
            font-size: 12px;
        }

        /* =========================
           FOOTER
        ========================= */

        .footer {
            text-align: center;
            color: #cbd5e1;
            font-size: 12px;
            margin-top: 25px;
        }

        /* =========================
           MOBILE
        ========================= */

        @media (max-width: 700px) {

            .navbar {
                flex-direction: column;
                gap: 12px;
            }

            .nav-links {
                gap: 14px;
                flex-wrap: wrap;
                justify-content: center;
            }

            .page {
                width: 94%;
                margin: 30px auto;
            }

            .card {
                padding: 25px 18px;
            }

            .page-header h1 {
                font-size: 28px;
            }

            .file-details {
                grid-template-columns: 1fr;
            }

        }

    </style>

</head>

<body>


<!-- =========================
     NAVIGATION
========================= -->

<nav class="navbar">

    <a
        href="dashboard.php"
        class="brand"
    >
        Softgenious<span>Dev</span>
    </a>

    <div class="nav-links">

        <a href="dashboard.php">
            Dashboard
        </a>

        <a href="users.php">
            Users
        </a>

        <a href="files.php">
            Files
        </a>

        <a href="logout.php">
            Logout
        </a>

    </div>

</nav>


<!-- =========================
     MAIN CONTENT
========================= -->

<main class="page">

    <div class="page-header">

        <h1>
            Upload File
        </h1>

        <p>
            Securely upload and manage your project files
        </p>

    </div>


    <div class="card">


        <!-- SUCCESS MESSAGE -->

        <?php if ($message): ?>

            <div class="message success">

                <?= htmlspecialchars($message) ?>

            </div>

        <?php endif; ?>


        <!-- ERROR MESSAGE -->

        <?php if ($error): ?>

            <div class="message error">

                <?= htmlspecialchars($error) ?>

            </div>

        <?php endif; ?>


        <form
            method="POST"
            enctype="multipart/form-data"
        >

            <?= csrfField() ?>


            <!-- UPLOAD AREA -->

            <div
                class="upload-box"
                id="uploadBox"
            >

                <div class="upload-icon">
                    ↑
                </div>

                <h2>
                    Select a file to upload
                </h2>

                <p>
                    Drag and drop your file here or browse your computer
                </p>

                <label
                    for="file"
                    class="browse-button"
                >
                    Browse Files
                </label>

                <input
                    type="file"
                    name="file"
                    id="file"
                    accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                    required
                >

                <div
                    class="selected-file"
                    id="selectedFile"
                ></div>

            </div>


            <!-- UPLOAD BUTTON -->

            <button
                type="submit"
                class="upload-button"
            >
                Upload File
            </button>


        </form>


        <!-- FILE INFORMATION -->

        <div class="file-details">

            <div class="detail">

                <strong>
                    Maximum Size
                </strong>

                <span>
                    5 MB
                </span>

            </div>


            <div class="detail">

                <strong>
                    File Types
                </strong>

                <span>
                    PDF, JPG, PNG, DOC, DOCX
                </span>

            </div>


            <div class="detail">

                <strong>
                    Protection
                </strong>

                <span>
                    CSRF & MIME validation
                </span>

            </div>

        </div>

    </div>


    <div class="footer">

        © <?= date('Y') ?> SoftgeniousDev

    </div>

</main>


<script>

const fileInput =
    document.getElementById('file');

const selectedFile =
    document.getElementById('selectedFile');

const uploadBox =
    document.getElementById('uploadBox');


/*
|----------------------------------------------------------------------
| File Selected
|----------------------------------------------------------------------
*/

fileInput.addEventListener(
    'change',
    function () {

        if (fileInput.files.length > 0) {

            const file =
                fileInput.files[0];

            selectedFile.style.display =
                'block';

            selectedFile.textContent =
                'Selected file: ' + file.name;

        }

    }
);


/*
|----------------------------------------------------------------------
| Drag Over
|----------------------------------------------------------------------
*/

uploadBox.addEventListener(
    'dragover',
    function (event) {

        event.preventDefault();

        uploadBox.classList.add(
            'dragging'
        );

    }
);


/*
|----------------------------------------------------------------------
| Drag Leave
|----------------------------------------------------------------------
*/

uploadBox.addEventListener(
    'dragleave',
    function () {

        uploadBox.classList.remove(
            'dragging'
        );

    }
);


/*
|----------------------------------------------------------------------
| Drop
|----------------------------------------------------------------------
*/

uploadBox.addEventListener(
    'drop',
    function (event) {

        event.preventDefault();

        uploadBox.classList.remove(
            'dragging'
        );

        if (
            event.dataTransfer.files.length > 0
        ) {

            fileInput.files =
                event.dataTransfer.files;

            const file =
                fileInput.files[0];

            selectedFile.style.display =
                'block';

            selectedFile.textContent =
                'Selected file: ' + file.name;

        }

    }
);

</script>

</body>

</html>