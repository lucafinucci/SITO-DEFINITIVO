<?php
/**
 * Chat RAG — configurazione runtime
 *
 * Riusa la classe Config esistente per leggere .env, espone costanti specifiche
 * del chatbot e risolve il path dell'indice RAG (dev locale vs deploy Aruba).
 */

require_once __DIR__ . '/../../includes/config.php';

if (!defined('CHAT_CONFIG_LOADED')) {
    define('CHAT_CONFIG_LOADED', true);

    define('CHAT_OPENAI_API_KEY', (string) Config::get('OPENAI_API_KEY', ''));
    define('CHAT_RATE_LIMIT_PER_MIN', (int) Config::get('CHAT_RATE_LIMIT_PER_MIN', 20));
    define('CHAT_RATE_LIMIT_PER_DAY', (int) Config::get('CHAT_RATE_LIMIT_PER_DAY', 100));

    $originsRaw = (string) Config::get('CHAT_ALLOWED_ORIGINS', 'https://finch-ai.it,https://www.finch-ai.it');
    define('CHAT_ALLOWED_ORIGINS', array_filter(array_map('trim', explode(',', $originsRaw))));

    define('CHAT_EMBED_MODEL', 'text-embedding-3-small');
    define('CHAT_COMPLETION_MODEL', 'gpt-4o-mini');
    define('CHAT_TOP_K', 5);
    define('CHAT_MIN_SCORE', 0.30);
    define('CHAT_MAX_INPUT_CHARS', 1500);
    define('CHAT_MAX_HISTORY_TURNS', 6);

    /**
     * Risolvi il path di chunks.json: in produzione su Aruba sta in
     * /www.finch-ai.it/rag/chunks.json, in dev locale in /public/rag/chunks.json.
     */
    function chat_resolve_index_path(): ?string {
        $candidates = [
            Config::get('RAG_INDEX_PATH', null),
            __DIR__ . '/../../../rag/chunks.json',
            __DIR__ . '/../../../public/rag/chunks.json',
        ];
        foreach ($candidates as $p) {
            if ($p && is_file($p) && is_readable($p)) {
                return $p;
            }
        }
        return null;
    }

    define('CHAT_INDEX_PATH', chat_resolve_index_path());
}
