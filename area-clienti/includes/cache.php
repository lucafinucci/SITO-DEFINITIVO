<?php
/**
 * Simple File-based Cache System
 * Per query frequenti e ridurre carico database
 */

class Cache {
    private static $cacheDir = __DIR__ . '/../../cache';
    private static $defaultTTL = 300; // 5 minuti

    /**
     * Inizializza cache directory
     */
    private static function init() {
        if (!is_dir(self::$cacheDir)) {
            @mkdir(self::$cacheDir, 0755, true);
        }
    }

    /**
     * Ottieni valore dalla cache
     */
    public static function get($key, $default = null) {
        self::init();

        $file = self::getCacheFile($key);

        if (!file_exists($file)) {
            return $default;
        }

        $data = @file_get_contents($file);
        if ($data === false) {
            return $default;
        }

        $cache = @unserialize($data);

        if (!$cache || !isset($cache['expires']) || !isset($cache['value'])) {
            return $default;
        }

        // Verifica scadenza
        if (time() > $cache['expires']) {
            @unlink($file);
            return $default;
        }

        return $cache['value'];
    }

    /**
     * Salva valore in cache
     */
    public static function set($key, $value, $ttl = null) {
        self::init();

        $ttl = $ttl ?? self::$defaultTTL;
        $file = self::getCacheFile($key);

        $cache = [
            'value' => $value,
            'expires' => time() + $ttl,
            'created' => time(),
        ];

        $data = serialize($cache);
        return @file_put_contents($file, $data, LOCK_EX) !== false;
    }

    /**
     * Verifica se chiave esiste e non è scaduta
     */
    public static function has($key) {
        return self::get($key) !== null;
    }

    /**
     * Elimina valore dalla cache
     */
    public static function delete($key) {
        $file = self::getCacheFile($key);

        if (file_exists($file)) {
            return @unlink($file);
        }

        return false;
    }

    /**
     * Pulisci tutta la cache
     */
    public static function clear() {
        self::init();

        $files = glob(self::$cacheDir . '/*.cache');

        foreach ($files as $file) {
            @unlink($file);
        }

        return true;
    }

    /**
     * Pulisci cache scaduta
     */
    public static function clearExpired() {
        self::init();

        $files = glob(self::$cacheDir . '/*.cache');
        $deleted = 0;

        foreach ($files as $file) {
            $data = @file_get_contents($file);
            if ($data === false) continue;

            $cache = @unserialize($data);

            if (!$cache || !isset($cache['expires'])) {
                @unlink($file);
                $deleted++;
                continue;
            }

            if (time() > $cache['expires']) {
                @unlink($file);
                $deleted++;
            }
        }

        return $deleted;
    }

    /**
     * Remember pattern: get from cache or execute callback
     */
    public static function remember($key, $callback, $ttl = null) {
        $value = self::get($key);

        if ($value !== null) {
            return $value;
        }

        $value = $callback();
        self::set($key, $value, $ttl);

        return $value;
    }

    /**
     * Ottieni path file cache
     */
    private static function getCacheFile($key) {
        $hash = md5($key);
        return self::$cacheDir . '/' . $hash . '.cache';
    }

    /**
     * Genera chiave cache per query
     */
    public static function queryKey($sql, $params = []) {
        return 'query_' . md5($sql . serialize($params));
    }

    /**
     * Cache per utente
     */
    public static function userKey($userId, $key) {
        return "user_{$userId}_{$key}";
    }

    /**
     * Invalida cache per utente
     */
    public static function invalidateUser($userId) {
        self::init();

        $pattern = self::getCacheFile("user_{$userId}_");
        $prefix = substr($pattern, 0, strrpos($pattern, '_'));

        $files = glob(self::$cacheDir . '/*.cache');

        foreach ($files as $file) {
            $basename = basename($file, '.cache');
            if (strpos($basename, md5("user_{$userId}_")) === 0) {
                @unlink($file);
            }
        }
    }
}
