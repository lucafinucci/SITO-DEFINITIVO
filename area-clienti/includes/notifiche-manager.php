<?php
/**
 * Notifiche Manager - Sistema di notifiche smart per admin e clienti
 * Supporta notifiche multi-canale: browser, email, SMS
 */

class NotificheManager {
    private $pdo;
    private $emailManager;
    private $smsManager;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Lazy load EmailManager
     */
    private function getEmailManager() {
        if (!$this->emailManager) {
            require_once __DIR__ . '/email-manager.php';
            $this->emailManager = new EmailManager($this->pdo);
        }
        return $this->emailManager;
    }

    /**
     * Lazy load SMSManager
     */
    private function getSMSManager() {
        if (!$this->smsManager) {
            require_once __DIR__ . '/sms-manager.php';
            $this->smsManager = new SMSManager($this->pdo);
        }
        return $this->smsManager;
    }

    /**
     * Crea nuova notifica con supporto multi-canale
     */
    public function crea($dati) {
        $stmt = $this->pdo->prepare('
            INSERT INTO notifiche (
                utente_id,
                tipo,
                titolo,
                messaggio,
                icona,
                priorita,
                link_azione,
                label_azione,
                cliente_id,
                fattura_id,
                richiesta_id,
                dati_extra,
                canale
            ) VALUES (
                :utente_id,
                :tipo,
                :titolo,
                :messaggio,
                :icona,
                :priorita,
                :link_azione,
                :label_azione,
                :cliente_id,
                :fattura_id,
                :richiesta_id,
                :dati_extra,
                :canale
            )
        ');

        $stmt->execute([
            'utente_id' => $dati['utente_id'],
            'tipo' => $dati['tipo'],
            'titolo' => $dati['titolo'],
            'messaggio' => $dati['messaggio'],
            'icona' => $dati['icona'] ?? $this->getIconaDefault($dati['tipo']),
            'priorita' => $dati['priorita'] ?? 'normale',
            'link_azione' => $dati['link_azione'] ?? null,
            'label_azione' => $dati['label_azione'] ?? null,
            'cliente_id' => $dati['cliente_id'] ?? null,
            'fattura_id' => $dati['fattura_id'] ?? null,
            'richiesta_id' => $dati['richiesta_id'] ?? null,
            'dati_extra' => isset($dati['dati_extra']) ? json_encode($dati['dati_extra']) : null,
            'canale' => $dati['canale'] ?? 'browser'
        ]);

        $notificaId = $this->pdo->lastInsertId();

        // Invia tramite canali aggiuntivi se specificato
        $this->inviaMultiCanale($notificaId, $dati);

        return $notificaId;
    }

    /**
     * Invia notifica tramite canali configurati (email/SMS)
     */
    private function inviaMultiCanale($notificaId, $dati) {
        try {
            // Recupera preferenze utente
            $preferenze = $this->getPreferenze($dati['utente_id']);

            if (!$preferenze) {
                return; // Nessuna preferenza configurata
            }

            // Determina canali da usare in base al tipo notifica
            $canali = $this->determinaCanali($dati['tipo'], $preferenze);

            // Invia via email
            if (in_array('email', $canali) && $preferenze['email_enabled']) {
                $this->inviaViaEmail($dati, $preferenze);
            }

            // Invia via SMS
            if (in_array('sms', $canali) && $preferenze['sms_enabled'] && $preferenze['telefono_sms']) {
                $this->inviaViaSMS($dati, $preferenze, $notificaId);
            }

        } catch (Exception $e) {
            error_log("Errore invio multi-canale notifica {$notificaId}: " . $e->getMessage());
        }
    }

    /**
     * Determina canali in base a tipo notifica e preferenze
     */
    private function determinaCanali($tipo, $preferenze) {
        $campoCanale = $tipo . '_canale';

        // Se non esiste preferenza specifica, usa default
        if (!isset($preferenze[$campoCanale])) {
            return ['browser'];
        }

        $scelta = $preferenze[$campoCanale];

        switch ($scelta) {
            case 'email':
                return ['email'];
            case 'sms':
                return ['sms'];
            case 'entrambi':
                return ['email', 'sms'];
            case 'nessuno':
                return [];
            default:
                return ['browser'];
        }
    }

    /**
     * Invia notifica via email
     */
    private function inviaViaEmail($dati, $preferenze) {
        $emailManager = $this->getEmailManager();

        // Determina template email in base al tipo
        $templateCode = $this->getEmailTemplatePerTipo($dati['tipo']);

        if (!$templateCode) {
            return; // Nessun template disponibile
        }

        // Prepara variabili email
        $variabili = $dati['dati_extra'] ?? [];
        $variabili['titolo_notifica'] = $dati['titolo'];
        $variabili['messaggio_notifica'] = $dati['messaggio'];
        $variabili['link_azione'] = $dati['link_azione'] ?? '';

        // Recupera info utente
        $stmt = $this->pdo->prepare('SELECT email, azienda, nome, cognome FROM utenti WHERE id = :id');
        $stmt->execute(['id' => $dati['utente_id']]);
        $utente = $stmt->fetch();

        if ($utente) {
            $emailManager->sendFromTemplate(
                $templateCode,
                ['email' => $utente['email'], 'nome' => $utente['azienda']],
                $variabili,
                [
                    'cliente_id' => $dati['cliente_id'] ?? $dati['utente_id'],
                    'priorita' => $dati['priorita'] ?? 'normale'
                ]
            );
        }
    }

    /**
     * Invia notifica via SMS
     */
    private function inviaViaSMS($dati, $preferenze, $notificaId) {
        $smsManager = $this->getSMSManager();

        // Determina template SMS in base al tipo
        $templateCode = $this->getSMSTemplatePerTipo($dati['tipo']);

        if (!$templateCode) {
            // Fallback: SMS generico
            $messaggio = substr($dati['titolo'] . ': ' . $dati['messaggio'], 0, 160);

            $smsManager->send(
                $preferenze['telefono_sms'],
                $messaggio,
                [
                    'notifica_id' => $notificaId,
                    'cliente_id' => $dati['cliente_id'] ?? $dati['utente_id']
                ]
            );
        } else {
            // Usa template
            $variabili = $dati['dati_extra'] ?? [];

            $smsManager->sendFromTemplate(
                $templateCode,
                [
                    'telefono' => $preferenze['telefono_sms'],
                    'nome' => $preferenze['azienda'] ?? ''
                ],
                $variabili,
                [
                    'notifica_id' => $notificaId,
                    'cliente_id' => $dati['cliente_id'] ?? $dati['utente_id']
                ]
            );
        }
    }

    /**
     * Recupera preferenze utente
     */
    private function getPreferenze($utenteId) {
        $stmt = $this->pdo->prepare('
            SELECT np.*, u.email, u.azienda
            FROM notifiche_preferenze np
            JOIN utenti u ON np.utente_id = u.id
            WHERE np.utente_id = :utente_id
        ');
        $stmt->execute(['utente_id' => $utenteId]);
        return $stmt->fetch();
    }

    /**
     * Mappa tipo notifica -> template email
     */
    private function getEmailTemplatePerTipo($tipo) {
        $map = [
            'servizio_attivato' => 'servizio-attivato',
            'servizio_disattivato' => 'servizio-disattivato',
            'fattura_emessa' => 'fattura-emessa',
            'fattura_in_scadenza' => 'sollecito-primo',
            'pagamento_confermato' => 'pagamento-ricevuto'
        ];

        return $map[$tipo] ?? null;
    }

    /**
     * Mappa tipo notifica -> template SMS
     */
    private function getSMSTemplatePerTipo($tipo) {
        $map = [
            'servizio_attivato' => 'servizio-attivato-sms',
            'servizio_disattivato' => 'servizio-disattivato-sms',
            'fattura_emessa' => 'fattura-emessa-sms',
            'fattura_in_scadenza' => 'fattura-scadenza-sms',
            'pagamento_confermato' => 'pagamento-confermato-sms',
            'manutenzione_sistema' => 'manutenzione-sms'
        ];

        return $map[$tipo] ?? null;
    }

    /**
     * Notifica tutti gli admin
     */
    public function notificaAdmin($tipo, $titolo, $messaggio, $opzioni = []) {
        // Recupera tutti gli admin
        $stmt = $this->pdo->prepare('SELECT id FROM utenti WHERE ruolo = "admin"');
        $stmt->execute();
        $admins = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $notificheCreate = [];

        foreach ($admins as $adminId) {
            // Verifica preferenze admin
            if (!$this->controllaPreferenze($adminId, $tipo)) {
                continue; // Admin ha disabilitato questo tipo di notifica
            }

            $notificaId = $this->crea([
                'utente_id' => $adminId,
                'tipo' => $tipo,
                'titolo' => $titolo,
                'messaggio' => $messaggio,
                'icona' => $opzioni['icona'] ?? null,
                'priorita' => $opzioni['priorita'] ?? 'normale',
                'link_azione' => $opzioni['link_azione'] ?? null,
                'label_azione' => $opzioni['label_azione'] ?? null,
                'cliente_id' => $opzioni['cliente_id'] ?? null,
                'fattura_id' => $opzioni['fattura_id'] ?? null,
                'richiesta_id' => $opzioni['richiesta_id'] ?? null,
                'dati_extra' => $opzioni['dati_extra'] ?? null
            ]);

            $notificheCreate[] = $notificaId;
        }

        return $notificheCreate;
    }

    /**
     * Segna notifica come letta
     */
    public function marcaComeLetta($notificaId, $utenteId = null) {
        $sql = 'UPDATE notifiche SET letta = TRUE, letta_at = CURRENT_TIMESTAMP WHERE id = :id';
        $params = ['id' => $notificaId];

        if ($utenteId) {
            $sql .= ' AND utente_id = :utente_id';
            $params['utente_id'] = $utenteId;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->rowCount() > 0;
    }

    /**
     * Segna tutte le notifiche come lette
     */
    public function marcaTutteComeLette($utenteId) {
        $stmt = $this->pdo->prepare('
            UPDATE notifiche
            SET letta = TRUE, letta_at = CURRENT_TIMESTAMP
            WHERE utente_id = :utente_id AND letta = FALSE
        ');
        $stmt->execute(['utente_id' => $utenteId]);

        return $stmt->rowCount();
    }

    /**
     * Archivia notifica
     */
    public function archivia($notificaId, $utenteId = null) {
        $sql = 'UPDATE notifiche SET archiviata = TRUE WHERE id = :id';
        $params = ['id' => $notificaId];

        if ($utenteId) {
            $sql .= ' AND utente_id = :utente_id';
            $params['utente_id'] = $utenteId;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->rowCount() > 0;
    }

    /**
     * Recupera notifiche utente
     */
    public function getNotifiche($utenteId, $opzioni = []) {
        $limit = $opzioni['limit'] ?? 50;
        $soloNonLette = $opzioni['solo_non_lette'] ?? false;
        $tipo = $opzioni['tipo'] ?? null;

        $where = ['utente_id = :utente_id', 'archiviata = FALSE'];
        $params = ['utente_id' => $utenteId];

        if ($soloNonLette) {
            $where[] = 'letta = FALSE';
        }

        if ($tipo) {
            $where[] = 'tipo = :tipo';
            $params['tipo'] = $tipo;
        }

        $whereSql = implode(' AND ', $where);

        $stmt = $this->pdo->prepare("
            SELECT
                *,
                CASE
                    WHEN TIMESTAMPDIFF(MINUTE, created_at, NOW()) < 60 THEN
                        CONCAT(TIMESTAMPDIFF(MINUTE, created_at, NOW()), ' min fa')
                    WHEN TIMESTAMPDIFF(HOUR, created_at, NOW()) < 24 THEN
                        CONCAT(TIMESTAMPDIFF(HOUR, created_at, NOW()), ' ore fa')
                    ELSE
                        CONCAT(TIMESTAMPDIFF(DAY, created_at, NOW()), ' giorni fa')
                END AS tempo_relativo
            FROM notifiche
            WHERE $whereSql
            ORDER BY
                CASE priorita
                    WHEN 'urgente' THEN 1
                    WHEN 'alta' THEN 2
                    WHEN 'normale' THEN 3
                    WHEN 'bassa' THEN 4
                END,
                created_at DESC
            LIMIT :limit
        ");

        foreach ($params as $key => $value) {
            $type = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
            $stmt->bindValue(':' . $key, $value, $type);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Conta notifiche non lette
     */
    public function contaNonLette($utenteId) {
        $stmt = $this->pdo->prepare('
            SELECT COUNT(*) FROM notifiche
            WHERE utente_id = :utente_id
              AND letta = FALSE
              AND archiviata = FALSE
        ');
        $stmt->execute(['utente_id' => $utenteId]);

        return (int)$stmt->fetchColumn();
    }

    /**
     * Ottieni statistiche notifiche
     */
    public function getStatistiche($utenteId) {
        $stmt = $this->pdo->prepare('
            SELECT * FROM v_notifiche_statistiche
            WHERE utente_id = :utente_id
        ');
        $stmt->execute(['utente_id' => $utenteId]);

        return $stmt->fetch() ?: [
            'totale_notifiche' => 0,
            'non_lette' => 0,
            'lette' => 0,
            'urgenti_non_lette' => 0,
            'ultima_notifica' => null
        ];
    }

    /**
     * Controlla preferenze utente per tipo notifica
     */
    private function controllaPreferenze($utenteId, $tipo) {
        $stmt = $this->pdo->prepare('
            SELECT browser_enabled, ' . $tipo . '_enabled
            FROM notifiche_preferenze
            WHERE utente_id = :utente_id
        ');
        $stmt->execute(['utente_id' => $utenteId]);
        $prefs = $stmt->fetch();

        if (!$prefs) {
            return true; // Default: abilitate
        }

        $tipoEnabled = $tipo . '_enabled';
        return $prefs['browser_enabled'] && ($prefs[$tipoEnabled] ?? true);
    }

    /**
     * Icona di default per tipo
     */
    private function getIconaDefault($tipo) {
        $icone = [
            'nuovo_cliente' => '👤',
            'pagamento_ricevuto' => '💰',
            'richiesta_addestramento' => '🤖',
            'fattura_scaduta' => '⚠️',
            'servizio_attivato' => '✅',
            'servizio_disattivato' => '❌',
            'sollecito_inviato' => '📧',
            'errore_sistema' => '🔴',
            'altro' => '📌'
        ];

        return $icone[$tipo] ?? '📌';
    }
}

/**
 * Notifiche Helper Functions
 */

/**
 * Notifica nuovo cliente
 */
function notificaNuovoCliente($pdo, $cliente) {
    $nm = new NotificheManager($pdo);

    return $nm->notificaAdmin(
        'nuovo_cliente',
        'Nuovo Cliente Registrato',
        "Nuovo cliente: {$cliente['azienda']} ({$cliente['email']})",
        [
            'priorita' => 'normale',
            'link_azione' => '/area-clienti/admin/gestione-servizi.php',
            'label_azione' => 'Visualizza Clienti',
            'cliente_id' => $cliente['id'],
            'dati_extra' => [
                'azienda' => $cliente['azienda'],
                'email' => $cliente['email'],
                'nome' => $cliente['nome'] . ' ' . $cliente['cognome']
            ]
        ]
    );
}

/**
 * Notifica pagamento ricevuto
 */
function notificaPagamentoRicevuto($pdo, $fattura, $importo, $metodo) {
    $nm = new NotificheManager($pdo);

    return $nm->notificaAdmin(
        'pagamento_ricevuto',
        'Pagamento Ricevuto',
        "Ricevuto pagamento di €" . number_format($importo, 2, ',', '.') .
        " per fattura {$fattura['numero_fattura']} via {$metodo}",
        [
            'priorita' => 'alta',
            'link_azione' => "/area-clienti/admin/fatture.php",
            'label_azione' => 'Visualizza Fatture',
            'fattura_id' => $fattura['id'],
            'dati_extra' => [
                'importo' => $importo,
                'metodo' => $metodo,
                'numero_fattura' => $fattura['numero_fattura']
            ]
        ]
    );
}

/**
 * Notifica richiesta addestramento
 */
function notificaRichiestaAddestramento($pdo, $richiesta) {
    $nm = new NotificheManager($pdo);

    return $nm->notificaAdmin(
        'richiesta_addestramento',
        'Nuova Richiesta Addestramento',
        "Richiesta addestramento da {$richiesta['azienda']}: {$richiesta['tipo_documento']} ({$richiesta['numero_documenti']} documenti)",
        [
            'priorita' => 'alta',
            'link_azione' => "/area-clienti/admin/richieste-addestramento.php",
            'label_azione' => 'Visualizza Richiesta',
            'richiesta_id' => $richiesta['id'],
            'cliente_id' => $richiesta['user_id'],
            'dati_extra' => [
                'tipo_documento' => $richiesta['tipo_documento'],
                'numero_documenti' => $richiesta['numero_documenti'],
                'azienda' => $richiesta['azienda']
            ]
        ]
    );
}

/**
 * Notifica fattura scaduta
 */
function notificaFatturaScaduta($pdo, $fattura, $cliente) {
    $nm = new NotificheManager($pdo);

    $giorniRitardo = (new DateTime())->diff(new DateTime($fattura['data_scadenza']))->days;

    return $nm->notificaAdmin(
        'fattura_scaduta',
        'Fattura Scaduta',
        "Fattura {$fattura['numero_fattura']} di {$cliente['azienda']} scaduta da {$giorniRitardo} giorni (€" . number_format($fattura['totale'], 2, ',', '.') . ")",
        [
            'priorita' => 'alta',
            'link_azione' => "/area-clienti/admin/scadenzario.php",
            'label_azione' => 'Visualizza Scadenzario',
            'fattura_id' => $fattura['id'],
            'cliente_id' => $cliente['id'],
            'dati_extra' => [
                'numero_fattura' => $fattura['numero_fattura'],
                'importo' => $fattura['totale'],
                'giorni_ritardo' => $giorniRitardo,
                'azienda' => $cliente['azienda']
            ]
        ]
    );
}

/**
 * Notifica errore sistema
 */
function notificaErroreSistema($pdo, $titolo, $messaggio, $priorita = 'urgente') {
    $nm = new NotificheManager($pdo);

    return $nm->notificaAdmin(
        'errore_sistema',
        $titolo,
        $messaggio,
        [
            'priorita' => $priorita,
            'icona' => '🔴'
        ]
    );
}

/**
 * ========================================
 * NOTIFICHE CLIENTI - Helper Functions
 * ========================================
 */

/**
 * Notifica cliente: Servizio attivato
 */
function notificaClienteServizioAttivato($pdo, $clienteId, $servizio) {
    $nm = new NotificheManager($pdo);

    return $nm->crea([
        'utente_id' => $clienteId,
        'tipo' => 'servizio_attivato',
        'titolo' => 'Servizio Attivato',
        'messaggio' => "Il servizio {$servizio['nome']} è stato attivato con successo!",
        'icona' => '✅',
        'priorita' => 'normale',
        'link_azione' => '/area-clienti/servizio-dettaglio.php?id=' . $servizio['id'],
        'label_azione' => 'Visualizza Servizio',
        'cliente_id' => $clienteId,
        'dati_extra' => [
            'nome_servizio' => $servizio['nome'],
            'descrizione_servizio' => $servizio['descrizione'],
            'prezzo_mensile' => $servizio['prezzo_mensile'],
            'data_attivazione' => date('d/m/Y')
        ]
    ]);
}

/**
 * Notifica cliente: Servizio disattivato
 */
function notificaClienteServizioDisattivato($pdo, $clienteId, $servizio, $motivazione = null) {
    $nm = new NotificheManager($pdo);

    $messaggio = "Il servizio {$servizio['nome']} è stato disattivato";
    if ($motivazione) {
        $messaggio .= ". Motivazione: $motivazione";
    }

    return $nm->crea([
        'utente_id' => $clienteId,
        'tipo' => 'servizio_disattivato',
        'titolo' => 'Servizio Disattivato',
        'messaggio' => $messaggio,
        'icona' => '❌',
        'priorita' => 'normale',
        'link_azione' => '/area-clienti/dashboard.php',
        'label_azione' => 'Vai alla Dashboard',
        'cliente_id' => $clienteId,
        'dati_extra' => [
            'nome_servizio' => $servizio['nome'],
            'motivazione' => $motivazione,
            'data_disattivazione' => date('d/m/Y')
        ]
    ]);
}

/**
 * Notifica cliente: Fattura emessa
 */
function notificaClienteFatturaEmessa($pdo, $clienteId, $fattura) {
    $nm = new NotificheManager($pdo);

    return $nm->crea([
        'utente_id' => $clienteId,
        'tipo' => 'fattura_emessa',
        'titolo' => 'Nuova Fattura Emessa',
        'messaggio' => "Nuova fattura {$fattura['numero_fattura']} per €" . number_format($fattura['totale'], 2, ',', '.') . ". Scadenza: " . date('d/m/Y', strtotime($fattura['data_scadenza'])),
        'icona' => '📄',
        'priorita' => 'alta',
        'link_azione' => '/area-clienti/fatture.php?id=' . $fattura['id'],
        'label_azione' => 'Visualizza Fattura',
        'cliente_id' => $clienteId,
        'fattura_id' => $fattura['id'],
        'dati_extra' => [
            'numero_fattura' => $fattura['numero_fattura'],
            'importo' => $fattura['totale'],
            'data_scadenza' => date('d/m/Y', strtotime($fattura['data_scadenza'])),
            'link_pagamento' => 'https://finch-ai.it/area-clienti/pagamento.php?fattura=' . $fattura['id']
        ]
    ]);
}

/**
 * Notifica cliente: Fattura in scadenza (promemoria)
 */
function notificaClienteFatturaInScadenza($pdo, $clienteId, $fattura, $giorniMancanti) {
    $nm = new NotificheManager($pdo);

    return $nm->crea([
        'utente_id' => $clienteId,
        'tipo' => 'fattura_in_scadenza',
        'titolo' => 'Promemoria Scadenza Fattura',
        'messaggio' => "La fattura {$fattura['numero_fattura']} scade tra $giorniMancanti giorni. Importo: €" . number_format($fattura['totale'], 2, ',', '.'),
        'icona' => '⏰',
        'priorita' => 'alta',
        'link_azione' => '/area-clienti/pagamento.php?fattura=' . $fattura['id'],
        'label_azione' => 'Paga Ora',
        'cliente_id' => $clienteId,
        'fattura_id' => $fattura['id'],
        'dati_extra' => [
            'numero_fattura' => $fattura['numero_fattura'],
            'importo' => $fattura['totale'],
            'data_scadenza' => date('d/m/Y', strtotime($fattura['data_scadenza'])),
            'giorni_mancanti' => $giorniMancanti,
            'link_pagamento' => 'https://finch-ai.it/area-clienti/pagamento.php?fattura=' . $fattura['id']
        ]
    ]);
}

/**
 * Notifica cliente: Pagamento confermato
 */
function notificaClientePagamentoConfermato($pdo, $clienteId, $fattura, $importo, $metodo) {
    $nm = new NotificheManager($pdo);

    return $nm->crea([
        'utente_id' => $clienteId,
        'tipo' => 'pagamento_confermato',
        'titolo' => 'Pagamento Confermato',
        'messaggio' => "Pagamento di €" . number_format($importo, 2, ',', '.') . " per fattura {$fattura['numero_fattura']} confermato via $metodo",
        'icona' => '✅',
        'priorita' => 'normale',
        'link_azione' => '/area-clienti/fatture.php?id=' . $fattura['id'],
        'label_azione' => 'Visualizza Ricevuta',
        'cliente_id' => $clienteId,
        'fattura_id' => $fattura['id'],
        'dati_extra' => [
            'numero_fattura' => $fattura['numero_fattura'],
            'importo' => $importo,
            'data_pagamento' => date('d/m/Y H:i'),
            'metodo_pagamento' => $metodo
        ]
    ]);
}

/**
 * Notifica cliente: Aggiornamento servizio
 */
function notificaClienteAggiornamentoServizio($pdo, $clienteId, $servizio, $tipoAggiornamento, $dettagli) {
    $nm = new NotificheManager($pdo);

    return $nm->crea([
        'utente_id' => $clienteId,
        'tipo' => 'aggiornamento_servizio',
        'titolo' => "Aggiornamento: {$servizio['nome']}",
        'messaggio' => $dettagli,
        'icona' => '🔄',
        'priorita' => 'normale',
        'link_azione' => '/area-clienti/servizio-dettaglio.php?id=' . $servizio['id'],
        'label_azione' => 'Scopri di più',
        'cliente_id' => $clienteId,
        'dati_extra' => [
            'nome_servizio' => $servizio['nome'],
            'tipo_aggiornamento' => $tipoAggiornamento,
            'dettagli' => $dettagli
        ]
    ]);
}

/**
 * Notifica cliente: Manutenzione programmata
 */
function notificaClienteManutenzione($pdo, $clienteId, $dataOra, $durataStimata, $dettagli) {
    $nm = new NotificheManager($pdo);

    return $nm->crea([
        'utente_id' => $clienteId,
        'tipo' => 'manutenzione_sistema',
        'titolo' => 'Manutenzione Programmata',
        'messaggio' => "Manutenzione programmata per il " . date('d/m/Y', strtotime($dataOra)) . " alle " . date('H:i', strtotime($dataOra)) . ". Durata stimata: $durataStimata. $dettagli",
        'icona' => '🛠️',
        'priorita' => 'alta',
        'cliente_id' => $clienteId,
        'dati_extra' => [
            'data_manutenzione' => date('d/m/Y', strtotime($dataOra)),
            'ora_inizio' => date('H:i', strtotime($dataOra)),
            'ora_fine' => date('H:i', strtotime($dataOra . ' +' . $durataStimata)),
            'durata_stimata' => $durataStimata,
            'dettagli' => $dettagli
        ]
    ]);
}

/**
 * Broadcast: Notifica tutti i clienti attivi
 */
function broadcastNotificaClienti($pdo, $tipo, $titolo, $messaggio, $opzioni = []) {
    $nm = new NotificheManager($pdo);

    // Recupera tutti i clienti attivi
    $stmt = $pdo->prepare('
        SELECT DISTINCT u.id
        FROM utenti u
        JOIN utenti_servizi us ON u.id = us.user_id
        WHERE u.ruolo = "cliente"
          AND us.stato = "attivo"
    ');
    $stmt->execute();
    $clienti = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $notificheCreate = [];

    foreach ($clienti as $clienteId) {
        $notificaId = $nm->crea([
            'utente_id' => $clienteId,
            'tipo' => $tipo,
            'titolo' => $titolo,
            'messaggio' => $messaggio,
            'icona' => $opzioni['icona'] ?? null,
            'priorita' => $opzioni['priorita'] ?? 'normale',
            'link_azione' => $opzioni['link_azione'] ?? null,
            'label_azione' => $opzioni['label_azione'] ?? null,
            'cliente_id' => $clienteId,
            'dati_extra' => $opzioni['dati_extra'] ?? null
        ]);

        $notificheCreate[] = $notificaId;
    }

    return $notificheCreate;
}
