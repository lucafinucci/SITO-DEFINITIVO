<?php
/**
 * Unified Error Handler
 * Gestione centralizzata errori e logging
 */

require_once __DIR__ . '/config.php';

if (!class_exists('ErrorHandler')) {
class ErrorHandler {
    private static $errorLog = __DIR__ . '/../../logs/error.log';
    private static $accessLog = __DIR__ . '/../../logs/access.log';

    /**
     * Inizializza error handler
     */
    public static function init() {
        // Custom error handler
        set_error_handler([self::class, 'handleError']);

        // Custom exception handler
        set_exception_handler([self::class, 'handleException']);

        // Shutdown handler per fatal errors
        register_shutdown_function([self::class, 'handleShutdown']);

        // Configura error reporting basato sull'ambiente
        if (Config::isDebug()) {
            // Modalità debug: mostra tutti gli errori
            error_reporting(E_ALL);
            ini_set('display_errors', '1');
        } else {
            // Modalità produzione: logga errori ma non mostrarli
            error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
            ini_set('display_errors', '0');
            ini_set('log_errors', '1');
        }
    }

    /**
     * Handle PHP errors
     */
    public static function handleError($errno, $errstr, $errfile, $errline) {
        $errorType = self::getErrorType($errno);

        $message = sprintf(
            "[%s] %s: %s in %s on line %d",
            date('Y-m-d H:i:s'),
            $errorType,
            $errstr,
            $errfile,
            $errline
        );

        self::logError($message);

        // Non bloccare l'esecuzione per warning/notice in produzione
        if (!Config::isDebug() && in_array($errno, [E_WARNING, E_NOTICE, E_USER_WARNING, E_USER_NOTICE])) {
            return true;
        }

        return false;
    }

    /**
     * Handle exceptions
     */
    public static function handleException($exception) {
        $message = sprintf(
            "[%s] Exception: %s in %s on line %d\nStack trace:\n%s",
            date('Y-m-d H:i:s'),
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine(),
            $exception->getTraceAsString()
        );

        self::logError($message);

        if (Config::isDebug()) {
            self::displayError($exception->getMessage(), $exception->getFile(), $exception->getLine());
        } else {
            self::displayGenericError();
        }

        exit(1);
    }

    /**
     * Handle shutdown (fatal errors)
     */
    public static function handleShutdown() {
        $error = error_get_last();

        if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            $message = sprintf(
                "[%s] Fatal Error: %s in %s on line %d",
                date('Y-m-d H:i:s'),
                $error['message'],
                $error['file'],
                $error['line']
            );

            self::logError($message);

            if (!Config::isDebug()) {
                self::displayGenericError();
            }
        }
    }

    /**
     * Log error to file
     */
    public static function logError($message) {
        $logDir = dirname(self::$errorLog);

        if (!is_dir($logDir)) {
            @mkdir($logDir, 0755, true);
        }

        @error_log($message . PHP_EOL, 3, self::$errorLog);
    }

    /**
     * Log access/activity
     */
    public static function logAccess($message, $context = []) {
        $logDir = dirname(self::$accessLog);

        if (!is_dir($logDir)) {
            @mkdir($logDir, 0755, true);
        }

        $contextStr = !empty($context) ? ' | ' . json_encode($context) : '';
        $logMessage = sprintf("[%s] %s%s", date('Y-m-d H:i:s'), $message, $contextStr);

        @error_log($logMessage . PHP_EOL, 3, self::$accessLog);
    }

    /**
     * Display error in debug mode
     */
    private static function displayError($message, $file, $line) {
        http_response_code(500);
        header('Content-Type: text/html; charset=utf-8');

        echo '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Errore - Finch-AI</title>
    <style>
        body { font-family: system-ui; padding: 40px; background: #0b1220; color: #e5e7eb; }
        .error-box { background: #1f2937; border-left: 4px solid #ef4444; padding: 20px; border-radius: 8px; }
        h1 { color: #ef4444; margin: 0 0 10px 0; }
        code { background: #0f172a; padding: 2px 6px; border-radius: 4px; }
        .details { margin-top: 15px; color: #9ca3af; font-size: 14px; }
    </style>
</head>
<body>
    <div class="error-box">
        <h1>⚠️ Errore</h1>
        <p><strong>' . htmlspecialchars($message) . '</strong></p>
        <div class="details">
            <p>File: <code>' . htmlspecialchars($file) . '</code></p>
            <p>Linea: <code>' . $line . '</code></p>
        </div>
    </div>
</body>
</html>';
        exit;
    }

    /**
     * Display generic error in production
     */
    private static function displayGenericError() {
        http_response_code(500);
        header('Content-Type: text/html; charset=utf-8');

        echo '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Errore - Finch-AI</title>
    <style>
        body { font-family: system-ui; padding: 40px; background: #0b1220; color: #e5e7eb; text-align: center; }
        .error-box { max-width: 500px; margin: 0 auto; background: #1f2937; padding: 40px; border-radius: 16px; }
        h1 { color: #22d3ee; margin: 0 0 20px 0; }
        p { color: #9ca3af; line-height: 1.6; }
        a { color: #22d3ee; text-decoration: none; }
    </style>
</head>
<body>
    <div class="error-box">
        <h1>⚠️ Si è verificato un errore</h1>
        <p>Ci scusiamo per l\'inconveniente. Il nostro team è stato notificato.</p>
        <p style="margin-top: 30px;">
            <a href="/area-clienti/dashboard.php">← Torna alla Dashboard</a>
        </p>
    </div>
</body>
</html>';
        exit;
    }

    /**
     * Get error type name
     */
    private static function getErrorType($type) {
        $types = [
            E_ERROR => 'Error',
            E_WARNING => 'Warning',
            E_PARSE => 'Parse Error',
            E_NOTICE => 'Notice',
            E_CORE_ERROR => 'Core Error',
            E_CORE_WARNING => 'Core Warning',
            E_COMPILE_ERROR => 'Compile Error',
            E_COMPILE_WARNING => 'Compile Warning',
            E_USER_ERROR => 'User Error',
            E_USER_WARNING => 'User Warning',
            E_USER_NOTICE => 'User Notice',
            E_STRICT => 'Strict Notice',
            E_RECOVERABLE_ERROR => 'Recoverable Error',
            E_DEPRECATED => 'Deprecated',
            E_USER_DEPRECATED => 'User Deprecated',
        ];

        return $types[$type] ?? 'Unknown Error';
    }

    /**
     * Create JSON error response
     */
    public static function jsonError($message, $code = 500, $details = []) {
        http_response_code($code);
        header('Content-Type: application/json');

        $response = [
            'success' => false,
            'error' => $message,
        ];

        if (Config::isDebug() && !empty($details)) {
            $response['details'] = $details;
        }

        echo json_encode($response);
        exit;
    }
}
}

// Auto-init
ErrorHandler::init();
