<?php
/**
 * Chat RAG — logger anonimizzato
 *
 * Scrive una riga JSON per ogni evento (query, error, rate_limit) in
 * area-clienti/logs/chat-YYYY-MM.log. Nessun IP, nessuna email: la query è
 * hashata e troncata, restano solo dati utili a misurare qualità retrieval.
 */

if (!function_exists('chat_log_event')) {
    function chat_log_dir(): string {
        return __DIR__ . '/../../logs';
    }

    function chat_log_event(string $event, array $data = []): void {
        $dir = chat_log_dir();
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        $file = $dir . '/chat-' . date('Y-m') . '.log';
        $payload = array_merge([
            'ts' => date('c'),
            'event' => $event,
        ], $data);
        @file_put_contents($file, json_encode($payload, JSON_UNESCAPED_UNICODE) . "\n", FILE_APPEND | LOCK_EX);
    }

    /**
     * Hash deterministico ma non reversibile delle query (per misurare frequenza
     * senza memorizzare il testo originale).
     */
    function chat_hash_query(string $q): string {
        return substr(hash('sha256', $q), 0, 16);
    }
}
