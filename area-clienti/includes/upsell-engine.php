<?php
/**
 * Upselling & Cross-selling Intelligence Engine
 * Sistema di raccomandazioni automatiche per opportunità di upselling
 *
 * Analizza:
 * - Pattern di utilizzo servizi
 * - Comportamento simili clienti (collaborative filtering)
 * - Lifecycle stage del cliente
 * - Budget disponibile
 * - Success probability
 */

class UpsellEngine {
    private $pdo;

    // Pesi del modello
    private $weights = [
        'usage_pattern' => 0.30,      // Pattern utilizzo attuale
        'customer_health' => 0.25,    // Salute cliente (low churn)
        'budget_capacity' => 0.20,    // Capacità di spesa
        'similar_customers' => 0.15,  // Cosa comprano clienti simili
        'lifecycle_stage' => 0.10     // Maturità cliente
    ];

    // Soglie opportunità
    const SCORE_HIGH = 0.70;      // >= 70% probabilità successo
    const SCORE_MEDIUM = 0.40;    // 40-70%
    const SCORE_LOW = 0.00;       // < 40%

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Identifica opportunità upselling per un cliente
     *
     * @param int $clienteId
     * @return array Lista opportunità ordinate per score
     */
    public function findOpportunities($clienteId) {
        // 1. Ottieni servizi già posseduti
        $currentServices = $this->getCurrentServices($clienteId);

        // 2. Ottieni catalogo servizi disponibili
        $availableServices = $this->getAvailableServices($currentServices);

        // 3. Estrai features cliente
        $customerFeatures = $this->extractCustomerFeatures($clienteId);

        // 4. Calcola score per ogni servizio
        $opportunities = [];

        foreach ($availableServices as $service) {
            $score = $this->scoreOpportunity($clienteId, $service, $customerFeatures, $currentServices);

            if ($score['total_score'] >= 0.30) {  // Soglia minima
                $opportunities[] = [
                    'servizio_id' => $service['id'],
                    'servizio_nome' => $service['nome'],
                    'servizio_tipo' => $service['tipo'],
                    'servizio_prezzo' => $service['prezzo_mensile'],
                    'score' => $score['total_score'],
                    'opportunity_level' => $this->getOpportunityLevel($score['total_score']),
                    'reasoning' => $score['reasoning'],
                    'expected_value' => $this->calculateExpectedValue($service, $score['total_score']),
                    'suggested_pitch' => $this->generatePitch($service, $customerFeatures, $score),
                    'best_time_to_contact' => $this->suggestContactTime($customerFeatures),
                    'scores_breakdown' => $score['breakdown']
                ];
            }
        }

        // Ordina per score decrescente
        usort($opportunities, fn($a, $b) => $b['score'] <=> $a['score']);

        return $opportunities;
    }

    /**
     * Batch: Trova opportunità per tutti i clienti
     */
    public function findOpportunitiesBatch($limit = null) {
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

        $results = [];

        foreach ($clienti as $cliente) {
            $opportunities = $this->findOpportunities($cliente['id']);

            if (!empty($opportunities)) {
                $results[] = [
                    'cliente_id' => $cliente['id'],
                    'cliente_email' => $cliente['email'],
                    'cliente_nome' => $cliente['nome'] . ' ' . $cliente['cognome'],
                    'opportunities_count' => count($opportunities),
                    'top_opportunity' => $opportunities[0] ?? null,
                    'total_potential_value' => array_sum(array_column($opportunities, 'expected_value'))
                ];
            }
        }

        // Ordina per valore potenziale
        usort($results, fn($a, $b) => $b['total_potential_value'] <=> $a['total_potential_value']);

        return $results;
    }

    /**
     * Servizi già posseduti dal cliente
     */
    private function getCurrentServices($clienteId) {
        $stmt = $this->pdo->prepare("
            SELECT
                s.id,
                s.tipo_servizio,
                s.prezzo_mensile,
                sa.stato,
                sa.data_attivazione,
                DATEDIFF(NOW(), sa.data_attivazione) as giorni_attivo
            FROM servizi_attivi sa
            JOIN servizi s ON sa.servizio_id = s.id
            WHERE sa.cliente_id = :cliente_id
            AND sa.stato = 'attivo'
        ");

        $stmt->execute(['cliente_id' => $clienteId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Servizi disponibili (non già posseduti)
     */
    private function getAvailableServices($currentServices) {
        $currentIds = array_column($currentServices, 'id');

        $placeholders = $currentIds ? implode(',', array_fill(0, count($currentIds), '?')) : '0';

        $stmt = $this->pdo->prepare("
            SELECT
                id,
                nome,
                descrizione,
                tipo_servizio as tipo,
                prezzo_mensile,
                prezzo_annuale,
                categoria,
                target_cliente
            FROM servizi
            WHERE attivo = TRUE
            AND id NOT IN ($placeholders)
            ORDER BY prezzo_mensile ASC
        ");

        $stmt->execute($currentIds);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Estrai features cliente per scoring
     */
    private function extractCustomerFeatures($clienteId) {
        $features = [];

        // Profile & tenure
        $profile = $this->pdo->prepare("
            SELECT
                DATEDIFF(NOW(), created_at) as customer_age_days,
                last_login,
                DATEDIFF(NOW(), last_login) as days_since_login
            FROM utenti
            WHERE id = :id
        ");
        $profile->execute(['id' => $clienteId]);
        $features['profile'] = $profile->fetch(PDO::FETCH_ASSOC);

        // Revenue & budget
        $revenue = $this->pdo->prepare("
            SELECT
                SUM(importo) as lifetime_value,
                AVG(importo) as avg_invoice,
                COUNT(*) as total_invoices,
                SUM(CASE WHEN data_emissione >= DATE_SUB(NOW(), INTERVAL 3 MONTH) THEN importo ELSE 0 END) as revenue_3m
            FROM fatture
            WHERE cliente_id = :id
            AND stato = 'pagata'
        ");
        $revenue->execute(['id' => $clienteId]);
        $features['revenue'] = $revenue->fetch(PDO::FETCH_ASSOC);

        // Engagement
        $engagement = $this->pdo->prepare("
            SELECT
                COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as logins_30d
            FROM audit_log
            WHERE user_id = :id
            AND azione = 'login_success'
        ");
        $engagement->execute(['id' => $clienteId]);
        $features['engagement'] = $engagement->fetch(PDO::FETCH_ASSOC);

        // Churn risk (se disponibile)
        $churn = $this->pdo->prepare("
            SELECT churn_probability, risk_level
            FROM churn_predictions
            WHERE cliente_id = :id
        ");
        $churn->execute(['id' => $clienteId]);
        $features['churn'] = $churn->fetch(PDO::FETCH_ASSOC);

        // Services
        $services = $this->pdo->prepare("
            SELECT COUNT(*) as active_services
            FROM servizi_attivi
            WHERE cliente_id = :id
            AND stato = 'attivo'
        ");
        $services->execute(['id' => $clienteId]);
        $features['services'] = $services->fetch(PDO::FETCH_ASSOC);

        return $features;
    }

    /**
     * Score opportunità (0-1)
     */
    private function scoreOpportunity($clienteId, $service, $features, $currentServices) {
        $scores = [];

        // 1. Usage Pattern (30%)
        $scores['usage_pattern'] = $this->scoreUsagePattern($service, $currentServices);

        // 2. Customer Health (25%)
        $scores['customer_health'] = $this->scoreCustomerHealth($features);

        // 3. Budget Capacity (20%)
        $scores['budget_capacity'] = $this->scoreBudgetCapacity($service, $features);

        // 4. Similar Customers (15%)
        $scores['similar_customers'] = $this->scoreSimilarCustomers($clienteId, $service);

        // 5. Lifecycle Stage (10%)
        $scores['lifecycle_stage'] = $this->scoreLifecycleStage($features);

        // Total weighted score
        $totalScore = 0;
        foreach ($scores as $component => $score) {
            $totalScore += $score * $this->weights[$component];
        }

        // Reasoning
        $reasoning = $this->buildReasoning($scores, $service, $features);

        return [
            'total_score' => $totalScore,
            'breakdown' => $scores,
            'reasoning' => $reasoning
        ];
    }

    /**
     * Score basato su pattern utilizzo
     */
    private function scoreUsagePattern($service, $currentServices) {
        $score = 0.5; // Base score

        // Se ha servizi complementari
        $complementary = $this->getComplementaryServices($service['tipo']);
        foreach ($currentServices as $current) {
            if (in_array($current['tipo_servizio'], $complementary)) {
                $score += 0.3;
            }
        }

        // Se ha servizi di tier inferiore
        if ($this->isUpgrade($service, $currentServices)) {
            $score += 0.2;
        }

        return min(1.0, $score);
    }

    /**
     * Score salute cliente
     */
    private function scoreCustomerHealth($features) {
        $score = 0.5;

        // Basso churn = buona opportunità
        if (isset($features['churn']['churn_probability'])) {
            $churnProb = $features['churn']['churn_probability'];
            if ($churnProb < 0.3) {
                $score += 0.4;  // Cliente stabile
            } elseif ($churnProb < 0.5) {
                $score += 0.2;
            } else {
                $score -= 0.3;  // Alto churn = non upsellare ora
            }
        }

        // Login recenti
        $daysSinceLogin = $features['profile']['days_since_login'] ?? 999;
        if ($daysSinceLogin < 7) {
            $score += 0.1;
        }

        return max(0.0, min(1.0, $score));
    }

    /**
     * Score capacità budget
     */
    private function scoreBudgetCapacity($service, $features) {
        $score = 0;

        $ltv = $features['revenue']['lifetime_value'] ?? 0;
        $avgInvoice = $features['revenue']['avg_invoice'] ?? 0;
        $servicePrice = $service['prezzo_mensile'];

        // Se prezzo servizio < 30% dell'avg invoice
        if ($avgInvoice > 0 && $servicePrice < ($avgInvoice * 0.3)) {
            $score += 0.5;  // Facilmente accessibile
        } elseif ($avgInvoice > 0 && $servicePrice < ($avgInvoice * 0.5)) {
            $score += 0.3;  // Moderato
        } elseif ($avgInvoice > 0 && $servicePrice < $avgInvoice) {
            $score += 0.1;  // Stretch
        }

        // Revenue recente (growing customer)
        $revenue3m = $features['revenue']['revenue_3m'] ?? 0;
        if ($revenue3m > ($ltv * 0.4)) {  // 40% LTV negli ultimi 3 mesi
            $score += 0.2;  // Cliente in crescita
        }

        return min(1.0, $score);
    }

    /**
     * Score basato su cosa comprano clienti simili
     */
    private function scoreSimilarCustomers($clienteId, $service) {
        // Trova clienti simili (stesso range LTV, stessi servizi base)
        $stmt = $this->pdo->prepare("
            SELECT COUNT(DISTINCT sa.cliente_id) as count
            FROM servizi_attivi sa
            JOIN (
                -- Clienti con LTV simile
                SELECT u2.id
                FROM utenti u2
                JOIN (
                    SELECT SUM(importo) as ltv
                    FROM fatture
                    WHERE cliente_id = :cliente_id
                    AND stato = 'pagata'
                ) cliente_ltv ON 1=1
                JOIN (
                    SELECT u.id, SUM(f.importo) as ltv
                    FROM utenti u
                    LEFT JOIN fatture f ON u.id = f.cliente_id AND f.stato = 'pagata'
                    WHERE u.ruolo = 'cliente'
                    AND u.id != :cliente_id2
                    GROUP BY u.id
                ) similar ON similar.ltv BETWEEN (cliente_ltv.ltv * 0.7) AND (cliente_ltv.ltv * 1.3)
            ) similar_customers ON sa.cliente_id = similar_customers.id
            WHERE sa.servizio_id = :servizio_id
            AND sa.stato = 'attivo'
        ");

        $stmt->execute([
            'cliente_id' => $clienteId,
            'cliente_id2' => $clienteId,
            'servizio_id' => $service['id']
        ]);

        $count = $stmt->fetchColumn();

        // Normalizza (assumendo max 100 clienti simili)
        return min(1.0, $count / 100);
    }

    /**
     * Score lifecycle stage
     */
    private function scoreLifecycleStage($features) {
        $customerAge = $features['profile']['customer_age_days'] ?? 0;

        // Sweet spot: 90-365 giorni (passato onboarding, non troppo vecchio)
        if ($customerAge >= 90 && $customerAge <= 365) {
            return 0.8;
        } elseif ($customerAge >= 30 && $customerAge < 90) {
            return 0.5;  // Ancora in onboarding
        } elseif ($customerAge > 365) {
            return 0.6;  // Cliente maturo
        } else {
            return 0.2;  // Troppo nuovo
        }
    }

    /**
     * Servizi complementari
     */
    private function getComplementaryServices($serviceType) {
        $map = [
            'document_intelligence' => ['ocr', 'data_extraction', 'ai_training'],
            'ai_training' => ['document_intelligence', 'custom_models'],
            'api_access' => ['premium_support', 'sla_guarantee'],
            'basic' => ['premium', 'enterprise'],
        ];

        return $map[$serviceType] ?? [];
    }

    /**
     * Verifica se è upgrade di tier
     */
    private function isUpgrade($service, $currentServices) {
        $tiers = ['basic' => 1, 'standard' => 2, 'premium' => 3, 'enterprise' => 4];

        $serviceTier = $tiers[$service['tipo']] ?? 0;

        foreach ($currentServices as $current) {
            $currentTier = $tiers[$current['tipo_servizio']] ?? 0;
            if ($serviceTier > $currentTier) {
                return true;
            }
        }

        return false;
    }

    /**
     * Livello opportunità
     */
    private function getOpportunityLevel($score) {
        if ($score >= self::SCORE_HIGH) return 'high';
        if ($score >= self::SCORE_MEDIUM) return 'medium';
        return 'low';
    }

    /**
     * Expected value (score × prezzo × 12 mesi)
     */
    private function calculateExpectedValue($service, $score) {
        return round($score * $service['prezzo_mensile'] * 12, 2);
    }

    /**
     * Build reasoning text
     */
    private function buildReasoning($scores, $service, $features) {
        $reasons = [];

        if ($scores['customer_health'] > 0.7) {
            $reasons[] = "Cliente stabile e soddisfatto (low churn risk)";
        }

        if ($scores['budget_capacity'] > 0.6) {
            $reasons[] = "Budget disponibile per upgrade ({$service['prezzo_mensile']}€/mese accessibile)";
        }

        if ($scores['usage_pattern'] > 0.7) {
            $reasons[] = "Pattern di utilizzo indica necessità di questo servizio";
        }

        if ($scores['similar_customers'] > 0.5) {
            $reasons[] = "Clienti simili hanno già adottato questo servizio";
        }

        if ($scores['lifecycle_stage'] > 0.7) {
            $reasons[] = "Timing ideale nella lifecycle del cliente";
        }

        return $reasons;
    }

    /**
     * Genera pitch personalizzato
     */
    private function generatePitch($service, $features, $score) {
        $ltv = $features['revenue']['lifetime_value'] ?? 0;
        $activeServices = $features['services']['active_services'] ?? 0;

        $pitch = "Gentile cliente,\n\n";

        // Opening personalizzato
        if ($ltv > 10000) {
            $pitch .= "In qualità di nostro cliente premium, ";
        } elseif ($activeServices > 2) {
            $pitch .= "Visto il tuo utilizzo intensivo dei nostri servizi, ";
        } else {
            $pitch .= "Basandoci sulla tua esperienza con i nostri servizi, ";
        }

        // Value proposition
        $pitch .= "riteniamo che {$service['nome']} possa portare valore aggiunto al tuo business.\n\n";

        // Specific benefits (basati su score breakdown)
        if ($score['breakdown']['usage_pattern'] > 0.7) {
            $pitch .= "• Complementa perfettamente i servizi che già utilizzi\n";
        }

        $pitch .= "• Prezzo competitivo: {$service['prezzo_mensile']}€/mese\n";
        $pitch .= "• ROI atteso: 3-6 mesi\n";
        $pitch .= "• Setup incluso nel prezzo\n\n";

        // Call to action
        $pitch .= "Possiamo programmare una demo di 15 minuti?\n\n";
        $pitch .= "Cordialmente,\nIl Team Finch-AI";

        return $pitch;
    }

    /**
     * Suggerisci momento migliore per contattare
     */
    private function suggestContactTime($features) {
        $daysSinceLogin = $features['profile']['days_since_login'] ?? 999;

        if ($daysSinceLogin < 2) {
            return 'now';  // Cliente attivo recentemente
        } elseif ($daysSinceLogin < 7) {
            return 'this_week';
        } else {
            return 'after_reengagement';  // Prima ri-attiva, poi upsell
        }
    }

    /**
     * Salva opportunità nel database
     */
    public function saveOpportunity($clienteId, $opportunity) {
        $stmt = $this->pdo->prepare("
            INSERT INTO upsell_opportunities (
                cliente_id,
                servizio_id,
                opportunity_score,
                opportunity_level,
                expected_value,
                reasoning,
                suggested_pitch,
                best_time_to_contact,
                scores_breakdown,
                created_at
            ) VALUES (
                :cliente_id,
                :servizio_id,
                :score,
                :level,
                :expected_value,
                :reasoning,
                :pitch,
                :best_time,
                :breakdown,
                NOW()
            )
            ON DUPLICATE KEY UPDATE
                opportunity_score = VALUES(opportunity_score),
                opportunity_level = VALUES(opportunity_level),
                expected_value = VALUES(expected_value),
                reasoning = VALUES(reasoning),
                suggested_pitch = VALUES(suggested_pitch),
                best_time_to_contact = VALUES(best_time_to_contact),
                scores_breakdown = VALUES(scores_breakdown),
                updated_at = NOW()
        ");

        return $stmt->execute([
            'cliente_id' => $clienteId,
            'servizio_id' => $opportunity['servizio_id'],
            'score' => $opportunity['score'],
            'level' => $opportunity['opportunity_level'],
            'expected_value' => $opportunity['expected_value'],
            'reasoning' => json_encode($opportunity['reasoning']),
            'pitch' => $opportunity['suggested_pitch'],
            'best_time' => $opportunity['best_time_to_contact'],
            'breakdown' => json_encode($opportunity['scores_breakdown'])
        ]);
    }

    /**
     * Statistiche aggregate
     */
    public function getStats() {
        $stmt = $this->pdo->query("
            SELECT
                COUNT(DISTINCT cliente_id) as total_clienti_with_opportunities,
                COUNT(*) as total_opportunities,
                SUM(CASE WHEN opportunity_level = 'high' THEN 1 ELSE 0 END) as high_opportunities,
                SUM(CASE WHEN opportunity_level = 'medium' THEN 1 ELSE 0 END) as medium_opportunities,
                SUM(expected_value) as total_potential_revenue,
                AVG(opportunity_score) as avg_score
            FROM upsell_opportunities
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        ");

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
