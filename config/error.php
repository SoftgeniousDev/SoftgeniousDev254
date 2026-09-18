<?php

ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');

ini_set('log_errors', '1');

ini_set(
    'error_log',
    __DIR__ . '/../logs/app.log'
);

error_reporting(E_ALL);

set_error_handler(function (
    int $severity,
    string $message,
    string $file,
    int $line
): bool {

    error_log(
        "PHP Error: {$message} in {$file} on line {$line}"
    );

    return true;
});

set_exception_handler(function (Throwable $exception): void {

    error_log(
        "Uncaught Exception: "
        . $exception->getMessage()
        . " in "
        . $exception->getFile()
        . " on line "
        . $exception->getLine()
    );

    http_response_code(500);

    echo "Something went wrong. Please try again later.";
});

register_shutdown_function(function (): void {

    $error = error_get_last();

    if ($error !== null) {

        $fatalErrors = [
            E_ERROR,
            E_PARSE,
            E_CORE_ERROR,
            E_COMPILE_ERROR
        ];

        if (in_array(
            $error['type'],
            $fatalErrors,
            true
        )) {

            error_log(
                "Fatal Error: "
                . $error['message']
                . " in "
                . $error['file']
                . " on line "
                . $error['line']
            );
        }
    }
});