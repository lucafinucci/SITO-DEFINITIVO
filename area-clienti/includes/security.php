<?php
/**
 * Security Utilities
 * CSRF Protection, Rate Limiting, Input Validation
 */

require_once __DIR__ . '/config.php';

if (!class_exists('Security')) {
class Security {
    /**
     * ===========================
     * CSRF PROTECTION
     * ===========================
     */

    /**
     * Genera token CSRF
     */
    public static function generateCSRFToken() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $token = bin2hex(random_bytes(32));
        $lifetime = (int) Config::get('CSRF_TOKEN_LIFETIME', 3600);

        $_SESSION['csrf_token'] = $token;
        $_SESSION['csrf_token_time'] = time();

        return $token;
    }

    /**
     * Verifica token CSRF
     */
    public static function verifyCSRFToken($token) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['csrf_token']) || !isset($_SESSION['csrf_token_time'])) {
            return false;
        }

        $lifetime = (int) Config::get('CSRF_TOKEN_LIFETIME', 3600);

        // Verifica scadenza
        if (time() - $_SESSION['csrf_token_time'] > $lifetime) {
            unset($_SESSION['csrf_token'], $_SESSION['csrf_token_time']);
            return false;
        }

        // Verifica token (timing-safe)
        return hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Ottieni token CSRF (genera se non esiste)
     */
    public static function getCSRFToken() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['csrf_token'])) {
            return self::generateCSRFToken();
        }

        return $_SESSION['csrf_token'];
    }

    /**
     * Campo hidden CSRF per form
     */
    public static function csrfField() {
        $token = self::getCSRFToken();
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }

    /**
     * ===========================
     * RATE LIMITING
     * ===========================
     */

    /**
     * Verifica rate limiting
     */
    public static function checkRateLimit($identifier, $maxAttempts = null, $lockoutTime = null) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $maxAttempts = $maxAttempts ?? (int) Config::get('LOGIN_MAX_ATTEMPTS', 5);
        $lockoutTime = $lockoutTime ?? (int) Config::get('LOGIN_LOCKOUT_TIME', 900);

        $key = 'rate_limit_' . md5($identifier);

        if (!isset($_SESSION[$key])) {
            $_SESSION[$key] = [
                'attempts' => 0,
                'first_attempt' => time(),
                'locked_until' => null,
            ];
        }

        $data = &$_SESSION[$key];

        // Se in lockout, verifica se è scaduto
        if ($data['locked_until'] && time() < $data['locked_until']) {
            $remainingTime = $data['locked_until'] - time();
            return [
                'allowed' => false,
                'locked' => true,
                'remaining_time' => $remainingTime,
                'attempts' => $data['attempts'],
            ];
        }

        // Reset lockout se scaduto
        if ($data['locked_until'] && time() >= $data['locked_until']) {
            $data['attempts'] = 0;
            $data['first_attempt'] = time();
            $data['locked_until'] = null;
        }

        return [
            'allowed' => true,
            'locked' => false,
            'attempts' => $data['attempts'],
            'max_attempts' => $maxAttempts,
        ];
    }

    /**
     * Registra tentativo fallito
     */
    public static function recordFailedAttempt($identifier, $maxAttempts = null, $lockoutTime = null) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $maxAttempts = $maxAttempts ?? (int) Config::get('LOGIN_MAX_ATTEMPTS', 5);
        $lockoutTime = $lockoutTime ?? (int) Config::get('LOGIN_LOCKOUT_TIME', 900);

        $key = 'rate_limit_' . md5($identifier);

        if (!isset($_SESSION[$key])) {
            $_SESSION[$key] = [
                'attempts' => 0,
                'first_attempt' => time(),
                'locked_until' => null,
            ];
        }

        $data = &$_SESSION[$key];
        $data['attempts']++;

        if ($data['attempts'] >= $maxAttempts) {
            $data['locked_until'] = time() + $lockoutTime;
        }
    }

    /**
     * Reset rate limiting
     */
    public static function resetRateLimit($identifier) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $key = 'rate_limit_' . md5($identifier);
        unset($_SESSION[$key]);
    }

    /**
     * ===========================
     * INPUT VALIDATION
     * ===========================
     */

    /**
     * Valida email
     */
    public static function validateEmail($email) {
        $email = filter_var($email, FILTER_VALIDATE_EMAIL);
        if (!$email) {
            return ['valid' => false, 'error' => 'Email non valida'];
        }

        // Verifica lunghezza
        if (strlen($email) > 255) {
            return ['valid' => false, 'error' => 'Email troppo lunga'];
        }

        return ['valid' => true, 'value' => strtolower($email)];
    }

    /**
     * Valida password
     */
    public static function validatePassword($password, $minLength = 8) {
        if (strlen($password) < $minLength) {
            return ['valid' => false, 'error' => "La password deve essere di almeno {$minLength} caratteri"];
        }

        // Verifica complessità (almeno una lettera e un numero)
        if (!preg_match('/[a-zA-Z]/', $password)) {
            return ['valid' => false, 'error' => 'La password deve contenere almeno una lettera'];
        }

        if (!preg_match('/[0-9]/', $password)) {
            return ['valid' => false, 'error' => 'La password deve contenere almeno un numero'];
        }

        return ['valid' => true, 'value' => $password];
    }

    /**
     * Sanitizza stringa generica
     */
    public static function sanitizeString($input, $maxLength = 255) {
        $input = trim($input);
        $input = strip_tags($input);

        if (strlen($input) > $maxLength) {
            return ['valid' => false, 'error' => "Testo troppo lungo (max {$maxLength} caratteri)"];
        }

        return ['valid' => true, 'value' => $input];
    }

    /**
     * Valida telefono
     */
    public static function validatePhone($phone) {
        $phone = preg_replace('/[^0-9+\s()-]/', '', $phone);

        if (strlen($phone) < 8 || strlen($phone) > 20) {
            return ['valid' => false, 'error' => 'Numero di telefono non valido'];
        }

        return ['valid' => true, 'value' => $phone];
    }

    /**
     * Valida TOTP code
     */
    public static function validateTOTPCode($code) {
        $code = preg_replace('/[^0-9]/', '', $code);

        if (strlen($code) !== 6) {
            return ['valid' => false, 'error' => 'Il codice deve essere di 6 cifre'];
        }

        return ['valid' => true, 'value' => $code];
    }

    /**
     * ===========================
     * SECURITY HELPERS
     * ===========================
     */

    /**
     * Ottieni IP del client
     */
    public static function getClientIP() {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';

        // Considera proxy
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $ip = trim($ips[0]);
        } elseif (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        }

        return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : 'UNKNOWN';
    }

    /**
     * Ottieni User Agent
     */
    public static function getUserAgent() {
        return $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN';
    }

    /**
     * Hash sicuro per identificatori
     */
    public static function hashIdentifier($identifier) {
        return hash('sha256', $identifier);
    }
}
}
