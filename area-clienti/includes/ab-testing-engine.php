<?php
/**
 * A/B Testing Engine
 * Framework per test scientifici su prezzi, offerte, comunicazioni
 * Include statistical significance, traffic splitting, conversion tracking
 */

class ABTestingEngine {
    private $pdo;

    // Confidence levels
    const CONFIDENCE_90 = 1.645;  // Z-score per 90%
    const CONFIDENCE_95 = 1.960;  // Z-score per 95%
    const CONFIDENCE_99 = 2.576;  // Z-score per 99%

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Crea nuovo A/B test
     *
     * @param array $config Test configuration
     * @return int Test ID
     */
    public function createTest($config) {
        // Validazione
        $required = ['name', 'test_type', 'variants', 'success_metric'];
        foreach ($required as $field) {
            if (empty($config[$field])) {
                throw new Exception("Campo $field richiesto");
            }
        }

        if (count($config['variants']) < 2) {
            throw new Exception('Minimo 2 varianti richieste (A/B)');
        }

        // Verifica traffic allocation = 100%
        $totalTraffic = 0;
        foreach ($config['variants'] as $variant) {
            $totalTraffic += $variant['traffic_allocation'];
        }

        if (abs($totalTraffic - 100) > 0.01) {
            throw new Exception("Traffic allocation deve sommare a 100% (attuale: $totalTraffic%)");
        }

        $this->pdo->beginTransaction();

        try {
            // 1. Crea test
            $stmt = $this->pdo->prepare("
                INSERT INTO ab_tests (
                    name,
                    description,
                    test_type,
                    success_metric,
                    target_audience,
                    sample_size_target,
                    confidence_level,
                    start_date,
                    end_date,
                    status,
                    created_by
                ) VALUES (
                    :name,
                    :description,
                    :test_type,
                    :success_metric,
                    :target_audience,
                    :sample_size,
                    :confidence_level,
                    :start_date,
                    :end_date,
                    :status,
                    :created_by
                )
            ");

            $stmt->execute([
                'name' => $config['name'],
                'description' => $config['description'] ?? null,
                'test_type' => $config['test_type'],
                'success_metric' => $config['success_metric'],
                'target_audience' => $config['target_audience'] ?? 'all',
                'sample_size' => $config['sample_size_target'] ?? 1000,
                'confidence_level' => $config['confidence_level'] ?? 95,
                'start_date' => $config['start_date'] ?? date('Y-m-d'),
                'end_date' => $config['end_date'] ?? null,
                'status' => 'draft',
                'created_by' => $_SESSION['cliente_id'] ?? null
            ]);

            $testId = $this->pdo->lastInsertId();

            // 2. Crea varianti
            foreach ($config['variants'] as $variant) {
                $this->createVariant($testId, $variant);
            }

            $this->pdo->commit();

            return $testId;

        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Crea variante test
     */
    private function createVariant($testId, $variantConfig) {
        $stmt = $this->pdo->prepare("
            INSERT INTO ab_variants (
                test_id,
                variant_name,
                variant_key,
                is_control,
                traffic_allocation,
                config_json
            ) VALUES (
                :test_id,
                :variant_name,
                :variant_key,
                :is_control,
                :traffic_allocation,
                :config_json
            )
        ");

        $stmt->execute([
            'test_id' => $testId,
            'variant_name' => $variantConfig['name'],
            'variant_key' => $variantConfig['key'] ?? strtolower(str_replace(' ', '_', $variantConfig['name'])),
            'is_control' => $variantConfig['is_control'] ?? false,
            'traffic_allocation' => $variantConfig['traffic_allocation'],
            'config_json' => json_encode($variantConfig['config'] ?? [])
        ]);

        return $this->pdo->lastInsertId();
    }

    /**
     * Assegna utente a una variante (traffic splitting)
     *
     * @param int $testId Test ID
     * @param int $userId User ID (cliente_id)
     * @return array Variante assegnata
     */
    public function assignVariant($testId, $userId) {
        // 1. Verifica se utente già assegnato
        $stmt = $this->pdo->prepare("
            SELECT v.*
            FROM ab_assignments aa
            JOIN ab_variants v ON aa.variant_id = v.id
            WHERE aa.test_id = :test_id
            AND aa.user_id = :user_id
        ");

        $stmt->execute([
            'test_id' => $testId,
            'user_id' => $userId
        ]);

        $existing = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($existing) {
            return $existing; // Consistency: sempre stessa variante
        }

        // 2. Carica test
        $stmt = $this->pdo->prepare("
            SELECT * FROM ab_tests WHERE id = :test_id
        ");
        $stmt->execute(['test_id' => $testId]);
        $test = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$test) {
            throw new Exception("Test $testId non trovato");
        }

        if ($test['status'] !== 'running') {
            throw new Exception("Test non attivo (status: {$test['status']})");
        }

        // 3. Verifica target audience
        if (!$this->matchesTargetAudience($userId, $test['target_audience'])) {
            return null; // Utente non nel target
        }

        // 4. Carica varianti
        $stmt = $this->pdo->prepare("
            SELECT * FROM ab_variants
            WHERE test_id = :test_id
            ORDER BY id ASC
        ");
        $stmt->execute(['test_id' => $testId]);
        $variants = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 5. Assegna variante con hash-based splitting (deterministic)
        $variant = $this->selectVariantDeterministic($userId, $testId, $variants);

        // 6. Salva assignment
        $stmt = $this->pdo->prepare("
            INSERT INTO ab_assignments (
                test_id,
                variant_id,
                user_id,
                assigned_at
            ) VALUES (
                :test_id,
                :variant_id,
                :user_id,
                NOW()
            )
        ");

        $stmt->execute([
            'test_id' => $testId,
            'variant_id' => $variant['id'],
            'user_id' => $userId
        ]);

        return $variant;
    }

    /**
     * Seleziona variante con hash-based deterministic splitting
     */
    private function selectVariantDeterministic($userId, $testId, $variants) {
        // Hash deterministico: stesso user + test = sempre stessa variante
        $hash = crc32("{$userId}:{$testId}");
        $percentage = ($hash % 10000) / 100; // 0.00 - 99.99

        // Weighted random selection
        $cumulative = 0;
        foreach ($variants as $variant) {
            $cumulative += $variant['traffic_allocation'];
            if ($percentage < $cumulative) {
                return $variant;
            }
        }

        // Fallback (non dovrebbe succedere)
        return $variants[0];
    }

    /**
     * Verifica se utente matcha target audience
     */
    private function matchesTargetAudience($userId, $targetAudience) {
        if ($targetAudience === 'all') {
            return true;
        }

        // Parse JSON target rules
        $rules = json_decode($targetAudience, true);
        if (!$rules) {
            return true; // Default: tutti
        }

        // Carica dati utente
        $stmt = $this->pdo->prepare("
            SELECT
                u.*,
                cs.segment_id,
                COALESCE(
                    (SELECT SUM(importo) FROM fatture WHERE cliente_id = u.id AND stato = 'pagata'),
                    0
                ) as lifetime_value
            FROM utenti u
            LEFT JOIN customer_segments cs ON u.id = cs.cliente_id
            WHERE u.id = :user_id
        ");

        $stmt->execute(['user_id' => $userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) return false;

        // Valuta regole
        foreach ($rules as $rule) {
            $field = $rule['field'];
            $operator = $rule['operator'];
            $value = $rule['value'];

            $userValue = $user[$field] ?? null;

            if (!$this->evaluateCondition($userValue, $operator, $value)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Valuta condizione singola
     */
    private function evaluateCondition($userValue, $operator, $targetValue) {
        switch ($operator) {
            case '=':
            case 'equals':
                return $userValue == $targetValue;

            case '!=':
            case 'not_equals':
                return $userValue != $targetValue;

            case '>':
            case 'greater_than':
                return $userValue > $targetValue;

            case '>=':
            case 'greater_equal':
                return $userValue >= $targetValue;

            case '<':
            case 'less_than':
                return $userValue < $targetValue;

            case '<=':
            case 'less_equal':
                return $userValue <= $targetValue;

            case 'in':
                return in_array($userValue, (array)$targetValue);

            case 'not_in':
                return !in_array($userValue, (array)$targetValue);

            case 'contains':
                return strpos($userValue, $targetValue) !== false;

            default:
                return false;
        }
    }

    /**
     * Traccia evento conversione
     *
     * @param int $testId Test ID
     * @param int $userId User ID
     * @param string $eventType Tipo evento (view, click, conversion, purchase)
     * @param float $value Valore monetario (opzionale)
     * @param array $metadata Extra data
     */
    public function trackEvent($testId, $userId, $eventType, $value = null, $metadata = []) {
        // Verifica assignment
        $stmt = $this->pdo->prepare("
            SELECT aa.*, v.variant_key
            FROM ab_assignments aa
            JOIN ab_variants v ON aa.variant_id = v.id
            WHERE aa.test_id = :test_id
            AND aa.user_id = :user_id
        ");

        $stmt->execute([
            'test_id' => $testId,
            'user_id' => $userId
        ]);

        $assignment = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$assignment) {
            throw new Exception("User $userId non assegnato al test $testId");
        }

        // Salva evento
        $stmt = $this->pdo->prepare("
            INSERT INTO ab_events (
                test_id,
                variant_id,
                user_id,
                event_type,
                event_value,
                metadata,
                event_timestamp
            ) VALUES (
                :test_id,
                :variant_id,
                :user_id,
                :event_type,
                :event_value,
                :metadata,
                NOW()
            )
        ");

        $stmt->execute([
            'test_id' => $testId,
            'variant_id' => $assignment['variant_id'],
            'user_id' => $userId,
            'event_type' => $eventType,
            'event_value' => $value,
            'metadata' => json_encode($metadata)
        ]);

        // Aggiorna stats variante
        $this->updateVariantStats($assignment['variant_id'], $eventType, $value);
    }

    /**
     * Aggiorna statistiche variante
     */
    private function updateVariantStats($variantId, $eventType, $value) {
        $updates = [];
        $params = ['variant_id' => $variantId];

        switch ($eventType) {
            case 'view':
            case 'impression':
                $updates[] = "total_views = total_views + 1";
                break;

            case 'click':
                $updates[] = "total_clicks = total_clicks + 1";
                break;

            case 'conversion':
                $updates[] = "total_conversions = total_conversions + 1";
                break;

            case 'purchase':
                $updates[] = "total_purchases = total_purchases + 1";
                if ($value !== null) {
                    $updates[] = "total_revenue = total_revenue + :revenue";
                    $params['revenue'] = $value;
                }
                break;
        }

        if (empty($updates)) return;

        // Aggiorna conversion_rate
        $updates[] = "conversion_rate = CASE WHEN total_views > 0 THEN total_conversions / total_views ELSE 0 END";

        $sql = "UPDATE ab_variants SET " . implode(', ', $updates) . " WHERE id = :variant_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
    }

    /**
     * Calcola risultati test con statistical significance
     *
     * @param int $testId Test ID
     * @return array Risultati analisi
     */
    public function calculateResults($testId) {
        // 1. Carica test
        $stmt = $this->pdo->prepare("SELECT * FROM ab_tests WHERE id = :test_id");
        $stmt->execute(['test_id' => $testId]);
        $test = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$test) {
            throw new Exception("Test $testId non trovato");
        }

        // 2. Carica varianti
        $stmt = $this->pdo->prepare("
            SELECT * FROM ab_variants
            WHERE test_id = :test_id
            ORDER BY is_control DESC, id ASC
        ");
        $stmt->execute(['test_id' => $testId]);
        $variants = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 3. Identifica control
        $control = null;
        foreach ($variants as $variant) {
            if ($variant['is_control']) {
                $control = $variant;
                break;
            }
        }

        if (!$control) {
            $control = $variants[0]; // Default: prima variante
        }

        // 4. Calcola metriche per ogni variante
        $results = [];
        foreach ($variants as $variant) {
            $metrics = $this->calculateVariantMetrics($variant);

            // Statistical significance vs control
            if ($variant['id'] !== $control['id']) {
                $controlMetrics = $this->calculateVariantMetrics($control);
                $significance = $this->calculateSignificance(
                    $controlMetrics,
                    $metrics,
                    $test['confidence_level']
                );

                $metrics['vs_control'] = $significance;
            }

            $results[] = $metrics;
        }

        // 5. Determina winner
        $winner = $this->determineWinner($results, $test['success_metric']);

        return [
            'test' => $test,
            'variants' => $results,
            'control' => $control,
            'winner' => $winner,
            'is_conclusive' => $winner !== null
        ];
    }

    /**
     * Calcola metriche per variante
     */
    private function calculateVariantMetrics($variant) {
        $views = (int)$variant['total_views'];
        $conversions = (int)$variant['total_conversions'];
        $revenue = (float)$variant['total_revenue'];

        $conversionRate = $views > 0 ? $conversions / $views : 0;
        $revenuePerVisitor = $views > 0 ? $revenue / $views : 0;
        $revenuePerConversion = $conversions > 0 ? $revenue / $conversions : 0;

        // Confidence interval per conversion rate
        $ci = $this->calculateConfidenceInterval($conversions, $views, 95);

        return [
            'variant_id' => $variant['id'],
            'variant_name' => $variant['variant_name'],
            'variant_key' => $variant['variant_key'],
            'is_control' => (bool)$variant['is_control'],

            // Counts
            'views' => $views,
            'clicks' => (int)$variant['total_clicks'],
            'conversions' => $conversions,
            'purchases' => (int)$variant['total_purchases'],

            // Rates
            'conversion_rate' => round($conversionRate, 4),
            'conversion_rate_percentage' => round($conversionRate * 100, 2),

            // Revenue
            'total_revenue' => $revenue,
            'revenue_per_visitor' => round($revenuePerVisitor, 2),
            'revenue_per_conversion' => round($revenuePerConversion, 2),

            // Confidence
            'confidence_interval' => $ci,

            // Config
            'config' => json_decode($variant['config_json'], true)
        ];
    }

    /**
     * Calcola confidence interval per conversion rate
     * Usa Wilson score interval (più accurato di binomiale normale)
     */
    private function calculateConfidenceInterval($conversions, $views, $confidenceLevel = 95) {
        if ($views === 0) {
            return ['lower' => 0, 'upper' => 0];
        }

        $p = $conversions / $views;

        // Z-score
        $z = $confidenceLevel == 90 ? self::CONFIDENCE_90 :
             ($confidenceLevel == 95 ? self::CONFIDENCE_95 : self::CONFIDENCE_99);

        // Wilson score interval
        $denominator = 1 + ($z * $z) / $views;

        $center = $p + ($z * $z) / (2 * $views);
        $spread = $z * sqrt(($p * (1 - $p) / $views) + ($z * $z) / (4 * $views * $views));

        $lower = ($center - $spread) / $denominator;
        $upper = ($center + $spread) / $denominator;

        return [
            'lower' => max(0, round($lower, 4)),
            'upper' => min(1, round($upper, 4))
        ];
    }

    /**
     * Calcola statistical significance tra due varianti
     * Usa Z-test per proporzioni
     */
    private function calculateSignificance($controlMetrics, $variantMetrics, $confidenceLevel) {
        $n1 = $controlMetrics['views'];
        $n2 = $variantMetrics['views'];
        $p1 = $controlMetrics['conversion_rate'];
        $p2 = $variantMetrics['conversion_rate'];

        if ($n1 === 0 || $n2 === 0) {
            return [
                'is_significant' => false,
                'p_value' => null,
                'z_score' => null,
                'lift' => 0,
                'message' => 'Sample size insufficiente'
            ];
        }

        // Pooled proportion
        $p_pool = (($p1 * $n1) + ($p2 * $n2)) / ($n1 + $n2);

        // Standard error
        $se = sqrt($p_pool * (1 - $p_pool) * ((1 / $n1) + (1 / $n2)));

        if ($se === 0) {
            return [
                'is_significant' => false,
                'p_value' => null,
                'z_score' => null,
                'lift' => 0,
                'message' => 'Nessuna variazione'
            ];
        }

        // Z-score
        $z = ($p2 - $p1) / $se;

        // P-value (two-tailed test)
        $pValue = 2 * (1 - $this->normalCDF(abs($z)));

        // Z threshold
        $zThreshold = $confidenceLevel == 90 ? self::CONFIDENCE_90 :
                      ($confidenceLevel == 95 ? self::CONFIDENCE_95 : self::CONFIDENCE_99);

        $isSignificant = abs($z) >= $zThreshold;

        // Lift percentage
        $lift = $p1 > 0 ? (($p2 - $p1) / $p1) * 100 : 0;

        return [
            'is_significant' => $isSignificant,
            'p_value' => round($pValue, 4),
            'z_score' => round($z, 4),
            'lift' => round($lift, 2),
            'lift_absolute' => round($p2 - $p1, 4),
            'message' => $isSignificant
                ? ($lift > 0 ? 'Miglioramento significativo' : 'Peggioramento significativo')
                : 'Differenza non significativa'
        ];
    }

    /**
     * Cumulative Distribution Function per distribuzione normale standard
     * Approssimazione accurata
     */
    private function normalCDF($x) {
        $t = 1 / (1 + 0.2316419 * abs($x));
        $d = 0.3989423 * exp(-$x * $x / 2);
        $p = $d * $t * (0.3193815 + $t * (-0.3565638 + $t * (1.781478 + $t * (-1.821256 + $t * 1.330274))));

        return $x > 0 ? 1 - $p : $p;
    }

    /**
     * Determina winner del test
     */
    private function determineWinner($results, $successMetric) {
        $winner = null;
        $bestValue = null;

        foreach ($results as $result) {
            // Skip non-significant vs control
            if (isset($result['vs_control']) && !$result['vs_control']['is_significant']) {
                continue;
            }

            $value = $result[$successMetric] ?? 0;

            if ($bestValue === null || $value > $bestValue) {
                $bestValue = $value;
                $winner = $result;
            }
        }

        return $winner;
    }

    /**
     * Avvia test
     */
    public function startTest($testId) {
        $stmt = $this->pdo->prepare("
            UPDATE ab_tests
            SET status = 'running',
                start_date = COALESCE(start_date, CURDATE())
            WHERE id = :test_id
        ");

        $stmt->execute(['test_id' => $testId]);

        return $stmt->rowCount() > 0;
    }

    /**
     * Pausa test
     */
    public function pauseTest($testId) {
        $stmt = $this->pdo->prepare("
            UPDATE ab_tests
            SET status = 'paused'
            WHERE id = :test_id
        ");

        $stmt->execute(['test_id' => $testId]);

        return $stmt->rowCount() > 0;
    }

    /**
     * Completa test
     */
    public function completeTest($testId, $winnerVariantId = null) {
        $stmt = $this->pdo->prepare("
            UPDATE ab_tests
            SET status = 'completed',
                end_date = CURDATE(),
                winner_variant_id = :winner_variant_id
            WHERE id = :test_id
        ");

        $stmt->execute([
            'test_id' => $testId,
            'winner_variant_id' => $winnerVariantId
        ]);

        return $stmt->rowCount() > 0;
    }

    /**
     * Ottieni variante per utente (wrapper conveniente)
     */
    public function getVariantForUser($testId, $userId) {
        return $this->assignVariant($testId, $userId);
    }

    /**
     * Ottieni configurazione variante assegnata
     */
    public function getVariantConfig($testId, $userId, $configKey = null) {
        $variant = $this->assignVariant($testId, $userId);

        if (!$variant) {
            return null;
        }

        $config = json_decode($variant['config_json'], true);

        if ($configKey !== null) {
            return $config[$configKey] ?? null;
        }

        return $config;
    }
}
