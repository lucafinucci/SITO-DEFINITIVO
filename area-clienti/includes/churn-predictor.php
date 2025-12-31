<?php
/**
 * Churn Prediction Engine
 * Sistema di analisi predittiva per identificare clienti a rischio abbandono
 *
 * Utilizza un modello di scoring basato su:
 * - Engagement (accessi, utilizzo servizi)
 * - Comportamento pagamenti
 * - Trend di utilizzo
 * - Supporto richiesto
 * - Sentiment analisi (opzionale)
 */

class ChurnPredictor {
    private $pdo;

    // Pesi del modello (tunable)
    private $weights = [
        'engagement' => 0.30,
        'payment_behavior' => 0.25,
        'usage_trend' => 0.20,
        'support_tickets' => 0.15,
        'contract_status' => 0.10
    ];

    // Soglie rischio
    const RISK_HIGH = 0.70;      // >= 70% probabilità churn
    const RISK_MEDIUM = 0.40;    // 40-70% probabilità
    const RISK_LOW = 0.00;       // < 40% probabilità

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Calcola churn score per singolo cliente
     *
     * @param int $clienteId
     * @return array [score, risk_level, factors, recommendations]
     */
    public function predictChurn($clienteId) {
        // Estrai features
        $features = $this->extractFeatures($clienteId);

        // Calcola score componenti
        $scores = [
            'engagement' => $this->scoreEngagement($features),
            'payment_behavior' => $this->scorePaymentBehavior($features),
            'usage_trend' => $this->scoreUsageTrend($features),
            'support_tickets' => $this->scoreSupportTickets($features),
            'contract_status' => $this->scoreContractStatus($features)
        ];

        // Churn probability (weighted sum)
        $churnProbability = 0;
        foreach ($scores as $component => $score) {
            $churnProbability += $score * $this->weights[$component];
        }

        // Determina risk level
        $riskLevel = $this->getRiskLevel($churnProbability);

        // Identifica fattori principali
        $topFactors = $this->getTopFactors($scores);

        // Genera raccomandazioni
        $recommendations = $this->generateRecommendations($scores, $features);

        return [
            'cliente_id' => $clienteId,
            'churn_probability' => round($churnProbability, 4),
            'churn_percentage' => round($churnProbability * 100, 2),
            'risk_level' => $riskLevel,
            'scores' => $scores,
            'top_risk_factors' => $topFactors,
            'recommendations' => $recommendations,
            'features' => $features,
            'calculated_at' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Batch prediction per tutti i clienti attivi
     */
    public function predictBatch($limit = null) {
        $sql = "
            SELECT id, email, nome, cognome
            FROM utenti
            WHERE ruolo = 'cliente'
            AND attivo = TRUE
        ";

        if ($limit) {
            $sql .= " LIMIT " . (int)$limit;
        }

        $stmt = $this->pdo->query($sql);
        $clienti = $stmt->fetchAll();

        $predictions = [];

        foreach ($clienti as $cliente) {
            $prediction = $this->predictChurn($cliente['id']);
            $prediction['cliente_email'] = $cliente['email'];
            $prediction['cliente_nome'] = $cliente['nome'] . ' ' . $cliente['cognome'];

            $predictions[] = $prediction;
        }

        // Ordina per rischio decrescente
        usort($predictions, function($a, $b) {
            return $b['churn_probability'] <=> $a['churn_probability'];
        });

        return $predictions;
    }

    /**
     * Estrae features per il modello
     */
    private function extractFeatures($clienteId) {
        $features = [];

        // 1. ENGAGEMENT METRICS
        $engagement = $this->pdo->prepare("
            SELECT
                -- Login activity
                COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 END) as logins_last_7d,
                COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as logins_last_30d,
                MAX(created_at) as last_login,
                DATEDIFF(NOW(), MAX(created_at)) as days_since_last_login,

                -- Session duration (se tracked)
                AVG(TIMESTAMPDIFF(MINUTE, created_at,
                    LEAD(created_at) OVER (ORDER BY created_at)
                )) as avg_session_minutes

            FROM audit_log
            WHERE user_id = :cliente_id
            AND azione = 'login_success'
        ");
        $engagement->execute(['cliente_id' => $clienteId]);
        $features['engagement'] = $engagement->fetch(PDO::FETCH_ASSOC);

        // 2. PAYMENT BEHAVIOR
        $payments = $this->pdo->prepare("
            SELECT
                COUNT(*) as total_fatture,
                SUM(CASE WHEN stato = 'pagata' THEN 1 ELSE 0 END) as fatture_pagate,
                SUM(CASE WHEN stato = 'scaduta' THEN 1 ELSE 0 END) as fatture_scadute,
                SUM(CASE WHEN stato = 'in_attesa' THEN 1 ELSE 0 END) as fatture_pending,

                -- Payment timing
                AVG(DATEDIFF(data_pagamento, data_emissione)) as avg_days_to_pay,
                MAX(DATEDIFF(NOW(), data_scadenza)) as max_overdue_days,

                -- Revenue
                SUM(CASE WHEN stato = 'pagata' THEN importo ELSE 0 END) as total_revenue,
                AVG(CASE WHEN stato = 'pagata' THEN importo ELSE NULL END) as avg_invoice_amount,

                -- Late payments
                SUM(CASE WHEN data_pagamento > data_scadenza THEN 1 ELSE 0 END) as late_payments

            FROM fatture
            WHERE cliente_id = :cliente_id
            AND data_emissione >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
        ");
        $payments->execute(['cliente_id' => $clienteId]);
        $features['payments'] = $payments->fetch(PDO::FETCH_ASSOC);

        // 3. SERVICE USAGE
        $services = $this->pdo->prepare("
            SELECT
                COUNT(*) as total_services,
                SUM(CASE WHEN stato = 'attivo' THEN 1 ELSE 0 END) as active_services,
                SUM(CASE WHEN stato = 'sospeso' THEN 1 ELSE 0 END) as suspended_services,

                -- Service tenure
                AVG(DATEDIFF(NOW(), data_attivazione)) as avg_service_age_days,
                MIN(data_attivazione) as first_service_date,
                MAX(data_attivazione) as last_service_date,

                -- Recent changes
                SUM(CASE
                    WHEN data_disattivazione >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                    THEN 1 ELSE 0
                END) as services_cancelled_30d

            FROM servizi_attivi
            WHERE cliente_id = :cliente_id
        ");
        $services->execute(['cliente_id' => $clienteId]);
        $features['services'] = $services->fetch(PDO::FETCH_ASSOC);

        // 4. SUPPORT INTERACTION
        // (Assumendo tabella support_tickets)
        $support = $this->pdo->prepare("
            SELECT
                COUNT(*) as total_tickets,
                SUM(CASE WHEN stato = 'aperto' THEN 1 ELSE 0 END) as open_tickets,
                SUM(CASE WHEN stato = 'risolto' THEN 1 ELSE 0 END) as resolved_tickets,
                SUM(CASE WHEN priorita = 'alta' THEN 1 ELSE 0 END) as high_priority_tickets,

                -- Recent activity
                COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as tickets_last_30d,

                -- Resolution time
                AVG(DATEDIFF(resolved_at, created_at)) as avg_resolution_days,

                -- Sentiment (se disponibile)
                AVG(customer_satisfaction) as avg_satisfaction

            FROM support_tickets
            WHERE cliente_id = :cliente_id
            AND created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
        ");

        try {
            $support->execute(['cliente_id' => $clienteId]);
            $features['support'] = $support->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Tabella support_tickets non esiste - usa default
            $features['support'] = [
                'total_tickets' => 0,
                'open_tickets' => 0,
                'resolved_tickets' => 0,
                'high_priority_tickets' => 0,
                'tickets_last_30d' => 0,
                'avg_resolution_days' => null,
                'avg_satisfaction' => null
            ];
        }

        // 5. CUSTOMER PROFILE
        $profile = $this->pdo->prepare("
            SELECT
                DATEDIFF(NOW(), created_at) as customer_age_days,
                last_login,
                DATEDIFF(NOW(), last_login) as days_since_last_login
            FROM utenti
            WHERE id = :cliente_id
        ");
        $profile->execute(['cliente_id' => $clienteId]);
        $features['profile'] = $profile->fetch(PDO::FETCH_ASSOC);

        return $features;
    }

    /**
     * Score engagement (0-1, 1 = alto rischio)
     */
    private function scoreEngagement($features) {
        $eng = $features['engagement'];
        $score = 0;

        // Days since last login
        $daysSinceLogin = $eng['days_since_last_login'] ?? 999;
        if ($daysSinceLogin > 30) {
            $score += 0.5;
        } elseif ($daysSinceLogin > 14) {
            $score += 0.3;
        } elseif ($daysSinceLogin > 7) {
            $score += 0.1;
        }

        // Login frequency
        $logins30d = $eng['logins_last_30d'] ?? 0;
        if ($logins30d == 0) {
            $score += 0.4;
        } elseif ($logins30d < 5) {
            $score += 0.2;
        } elseif ($logins30d < 10) {
            $score += 0.1;
        }

        // Trend (comparing 7d vs 30d)
        $logins7d = $eng['logins_last_7d'] ?? 0;
        $expectedWeekly = ($logins30d / 4.3);
        if ($logins7d < ($expectedWeekly * 0.5)) {
            $score += 0.1; // Declining trend
        }

        return min(1.0, $score);
    }

    /**
     * Score payment behavior (0-1, 1 = alto rischio)
     */
    private function scorePaymentBehavior($features) {
        $pay = $features['payments'];
        $score = 0;

        // Overdue invoices
        $overdueRatio = $pay['total_fatture'] > 0
            ? ($pay['fatture_scadute'] / $pay['total_fatture'])
            : 0;

        $score += $overdueRatio * 0.4;

        // Late payment pattern
        $lateRatio = $pay['total_fatture'] > 0
            ? ($pay['late_payments'] / $pay['total_fatture'])
            : 0;

        $score += $lateRatio * 0.3;

        // Max overdue days
        $maxOverdue = $pay['max_overdue_days'] ?? 0;
        if ($maxOverdue > 60) {
            $score += 0.3;
        } elseif ($maxOverdue > 30) {
            $score += 0.2;
        } elseif ($maxOverdue > 15) {
            $score += 0.1;
        }

        // Pending invoices
        if ($pay['fatture_pending'] > 0) {
            $score += 0.1;
        }

        return min(1.0, $score);
    }

    /**
     * Score usage trend (0-1, 1 = alto rischio)
     */
    private function scoreUsageTrend($features) {
        $srv = $features['services'];
        $score = 0;

        // No active services
        if ($srv['active_services'] == 0) {
            return 1.0;
        }

        // Suspended services
        $suspendedRatio = $srv['total_services'] > 0
            ? ($srv['suspended_services'] / $srv['total_services'])
            : 0;

        $score += $suspendedRatio * 0.5;

        // Recent cancellations
        if ($srv['services_cancelled_30d'] > 0) {
            $score += 0.3;
        }

        // Service diversity (solo 1 servizio = più rischio)
        if ($srv['active_services'] == 1) {
            $score += 0.2;
        }

        return min(1.0, $score);
    }

    /**
     * Score support tickets (0-1, 1 = alto rischio)
     */
    private function scoreSupportTickets($features) {
        $sup = $features['support'];
        $score = 0;

        // Open tickets (segnale di insoddisfazione)
        if ($sup['open_tickets'] > 3) {
            $score += 0.4;
        } elseif ($sup['open_tickets'] > 1) {
            $score += 0.2;
        }

        // High frequency (molti ticket = problemi)
        if ($sup['tickets_last_30d'] > 5) {
            $score += 0.3;
        } elseif ($sup['tickets_last_30d'] > 2) {
            $score += 0.15;
        }

        // Low satisfaction
        $satisfaction = $sup['avg_satisfaction'];
        if ($satisfaction !== null) {
            if ($satisfaction < 2.0) {
                $score += 0.3;
            } elseif ($satisfaction < 3.5) {
                $score += 0.1;
            }
        }

        return min(1.0, $score);
    }

    /**
     * Score contract status (0-1, 1 = alto rischio)
     */
    private function scoreContractStatus($features) {
        $score = 0;

        // Customer age (nuovi clienti = più volatili)
        $customerAge = $features['profile']['customer_age_days'] ?? 0;
        if ($customerAge < 90) {
            $score += 0.5;  // Primi 3 mesi critici
        } elseif ($customerAge < 180) {
            $score += 0.3;
        }

        // Service age
        $serviceAge = $features['services']['avg_service_age_days'] ?? 0;
        if ($serviceAge < 30) {
            $score += 0.3;
        }

        return min(1.0, $score);
    }

    /**
     * Determina risk level da probability
     */
    private function getRiskLevel($probability) {
        if ($probability >= self::RISK_HIGH) {
            return 'high';
        } elseif ($probability >= self::RISK_MEDIUM) {
            return 'medium';
        } else {
            return 'low';
        }
    }

    /**
     * Identifica top 3 fattori di rischio
     */
    private function getTopFactors($scores) {
        arsort($scores);
        return array_slice(array_keys($scores), 0, 3);
    }

    /**
     * Genera raccomandazioni personalizzate
     */
    private function generateRecommendations($scores, $features) {
        $recommendations = [];

        // Engagement
        if ($scores['engagement'] > 0.5) {
            $daysSince = $features['engagement']['days_since_last_login'] ?? 0;
            if ($daysSince > 30) {
                $recommendations[] = [
                    'priority' => 'high',
                    'category' => 'engagement',
                    'action' => 'Contatto urgente',
                    'message' => "Cliente inattivo da {$daysSince} giorni. Chiamata telefonica immediata.",
                    'suggested_actions' => [
                        'Chiamata personale dal CSM',
                        'Email personalizzata con offerta speciale',
                        'Verifica se ci sono problemi tecnici'
                    ]
                ];
            } else {
                $recommendations[] = [
                    'priority' => 'medium',
                    'category' => 'engagement',
                    'action' => 'Re-engagement campaign',
                    'message' => 'Attività in calo. Invia contenuti educativi e best practices.',
                    'suggested_actions' => [
                        'Newsletter con case study',
                        'Invito a webinar',
                        'Demo nuove funzionalità'
                    ]
                ];
            }
        }

        // Payment issues
        if ($scores['payment_behavior'] > 0.5) {
            $overdue = $features['payments']['fatture_scadute'] ?? 0;
            if ($overdue > 0) {
                $recommendations[] = [
                    'priority' => 'high',
                    'category' => 'payment',
                    'action' => 'Risoluzione pagamenti',
                    'message' => "{$overdue} fatture scadute. Proponi piano di pagamento.",
                    'suggested_actions' => [
                        'Contatto diretto per piano rateale',
                        'Verifica problemi con metodo pagamento',
                        'Offri assistenza fatturazione'
                    ]
                ];
            }
        }

        // Service usage
        if ($scores['usage_trend'] > 0.5) {
            $cancelled = $features['services']['services_cancelled_30d'] ?? 0;
            if ($cancelled > 0) {
                $recommendations[] = [
                    'priority' => 'high',
                    'category' => 'retention',
                    'action' => 'Intervento retention',
                    'message' => "Cancellati {$cancelled} servizi recentemente. Exit interview urgente.",
                    'suggested_actions' => [
                        'Chiamata CSM per capire motivazioni',
                        'Offerta upgrade/downgrade alternativo',
                        'Sconto retention (max 20%)'
                    ]
                ];
            }
        }

        // Support issues
        if ($scores['support_tickets'] > 0.5) {
            $openTickets = $features['support']['open_tickets'] ?? 0;
            if ($openTickets > 0) {
                $recommendations[] = [
                    'priority' => 'medium',
                    'category' => 'support',
                    'action' => 'Escalation supporto',
                    'message' => "{$openTickets} ticket aperti. Assegna CSM dedicato.",
                    'suggested_actions' => [
                        'Escalation ticket a senior support',
                        'Follow-up giornaliero fino a risoluzione',
                        'Survey soddisfazione post-risoluzione'
                    ]
                ];
            }
        }

        // New customer onboarding
        if ($scores['contract_status'] > 0.5) {
            $customerAge = $features['profile']['customer_age_days'] ?? 0;
            if ($customerAge < 90) {
                $recommendations[] = [
                    'priority' => 'high',
                    'category' => 'onboarding',
                    'action' => 'Onboarding intensivo',
                    'message' => 'Cliente nuovo (< 90gg). Periodo critico per retention.',
                    'suggested_actions' => [
                        'Check-in settimanale CSM',
                        'Training personalizzato',
                        'Quick wins: configurazione servizi',
                        'Success plan a 30/60/90 giorni'
                    ]
                ];
            }
        }

        // Default fallback
        if (empty($recommendations)) {
            $recommendations[] = [
                'priority' => 'low',
                'category' => 'monitoring',
                'action' => 'Monitoraggio standard',
                'message' => 'Cliente a basso rischio. Mantieni engagement routinario.',
                'suggested_actions' => [
                    'Newsletter mensile',
                    'Check-in trimestrale',
                    'Survey NPS semestrale'
                ]
            ];
        }

        return $recommendations;
    }

    /**
     * Salva prediction nel database per tracking
     */
    public function savePrediction($prediction) {
        $stmt = $this->pdo->prepare("
            INSERT INTO churn_predictions (
                cliente_id,
                churn_probability,
                risk_level,
                scores_json,
                top_risk_factors,
                recommendations_json,
                features_json,
                created_at
            ) VALUES (
                :cliente_id,
                :churn_probability,
                :risk_level,
                :scores,
                :factors,
                :recommendations,
                :features,
                NOW()
            )
            ON DUPLICATE KEY UPDATE
                churn_probability = VALUES(churn_probability),
                risk_level = VALUES(risk_level),
                scores_json = VALUES(scores_json),
                top_risk_factors = VALUES(top_risk_factors),
                recommendations_json = VALUES(recommendations_json),
                features_json = VALUES(features_json),
                updated_at = NOW()
        ");

        return $stmt->execute([
            'cliente_id' => $prediction['cliente_id'],
            'churn_probability' => $prediction['churn_probability'],
            'risk_level' => $prediction['risk_level'],
            'scores' => json_encode($prediction['scores']),
            'factors' => implode(',', $prediction['top_risk_factors']),
            'recommendations' => json_encode($prediction['recommendations']),
            'features' => json_encode($prediction['features'])
        ]);
    }

    /**
     * Ottieni statistiche aggregate churn
     */
    public function getChurnStats() {
        $stmt = $this->pdo->query("
            SELECT
                COUNT(*) as total_clienti,
                SUM(CASE WHEN risk_level = 'high' THEN 1 ELSE 0 END) as high_risk,
                SUM(CASE WHEN risk_level = 'medium' THEN 1 ELSE 0 END) as medium_risk,
                SUM(CASE WHEN risk_level = 'low' THEN 1 ELSE 0 END) as low_risk,
                AVG(churn_probability) as avg_churn_probability,
                MAX(updated_at) as last_updated
            FROM churn_predictions
        ");

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
