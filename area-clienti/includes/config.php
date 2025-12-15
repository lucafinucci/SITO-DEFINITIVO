<?php
/**
 * Configuration Manager
 * Carica variabili d'ambiente da .env
 */

class Config {
    private static $config = [];
    private static $loaded = false;

    /**
     * Carica il file .env
     */
    public static function load() {
        if (self::$loaded) {
            return;
        }

        $envFile = __DIR__ . '/../../.env';

        if (!file_exists($envFile)) {
            // Fallback a valori di default per compatibilità
            self::setDefaults();
            self::$loaded = true;
            return;
        }

        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            // Salta commenti
            if (strpos(trim($line), '#') === 0) {
                continue;
            }

            // Parse KEY=VALUE
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);

                // Rimuovi quotes se presenti
                $value = trim($value, '"\'');

                self::$config[$key] = $value;

                // Imposta anche come variabile d'ambiente
                if (!getenv($key)) {
                    putenv("$key=$value");
                }
            }
        }

        self::$loaded = true;
    }

    /**
     * Imposta valori di default
     */
    private static function setDefaults() {
        self::$config = [
            'DB_HOST' => 'localhost',
            'DB_NAME' => 'finch_ai_clienti',
            'DB_USER' => 'root',
            'DB_PASS' => '',
            'SESSION_LIFETIME' => '7200',
            'CSRF_TOKEN_LIFETIME' => '3600',
            'LOGIN_MAX_ATTEMPTS' => '5',
            'LOGIN_LOCKOUT_TIME' => '900',
            'KPI_API_ENDPOINT' => 'https://app.finch-ai.it/api/kpi/documenti',
            'KPI_API_KEY' => '',
            'APP_ENV' => 'production',
            'APP_DEBUG' => 'false',
            'APP_URL' => 'http://localhost',
            'MFA_ISSUER' => 'Finch-AI',
            'MFA_DIGITS' => '6',
            'MFA_PERIOD' => '30',
        ];
    }

    /**
     * Ottieni valore di configurazione
     */
    public static function get($key, $default = null) {
        self::load();

        // Prova prima dalle variabili d'ambiente
        $envValue = getenv($key);
        if ($envValue !== false) {
            return $envValue;
        }

        // Poi dal config caricato
        return self::$config[$key] ?? $default;
    }

    /**
     * Verifica se siamo in debug mode
     */
    public static function isDebug() {
        return self::get('APP_DEBUG', 'false') === 'true';
    }

    /**
     * Verifica ambiente
     */
    public static function isProduction() {
        return self::get('APP_ENV', 'production') === 'production';
    }
}

// Auto-load
Config::load();
