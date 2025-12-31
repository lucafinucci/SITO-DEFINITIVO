<?php
/**
 * Customer Segmentation Engine
 * Sistema di clustering automatico basato su comportamento clienti
 * Algoritmo: K-means con normalizzazione e ottimizzazione cluster
 */

class CustomerSegmentation {
    private $pdo;

    // Numero cluster ottimale (può essere calcolato dinamicamente)
    private $optimalClusters = 5;

    // Feature weights per clustering
    private $featureWeights = [
        'ltv_score' => 0.25,
        'engagement_score' => 0.20,
        'usage_intensity' => 0.20,
        'service_diversity' => 0.15,
        'payment_reliability' => 0.10,
        'tenure' => 0.10
    ];

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Esegue clustering completo di tutti i clienti
     *
     * @param int $numClusters Numero di cluster (null = auto-detect)
     * @param int $maxIterations Max iterazioni K-means
     * @return array Risultati clustering con centroids e assignments
     */
    public function performClustering($numClusters = null, $maxIterations = 100) {
        // 1. Estrai features per tutti i clienti
        $customers = $this->extractAllCustomerFeatures();

        if (empty($customers)) {
            throw new Exception('Nessun cliente trovato per clustering');
        }

        // 2. Determina numero ottimale cluster se non specificato
        if ($numClusters === null) {
            $numClusters = $this->findOptimalClusters($customers);
        }

        // 3. Normalizza features (0-1 scale)
        $normalizedData = $this->normalizeFeatures($customers);

        // 4. Esegui K-means
        $clustering = $this->kmeans($normalizedData, $numClusters, $maxIterations);

        // 5. Profila ogni cluster (crea personas)
        $profiles = $this->profileClusters($clustering, $customers);

        // 6. Salva risultati nel database
        $this->saveClustering($clustering, $profiles);

        return [
            'num_clusters' => $numClusters,
            'total_customers' => count($customers),
            'centroids' => $clustering['centroids'],
            'assignments' => $clustering['assignments'],
            'profiles' => $profiles,
            'iterations' => $clustering['iterations']
        ];
    }

    /**
     * Estrae features comportamentali per tutti i clienti
     *
     * @return array Array di features per cliente
     */
    private function extractAllCustomerFeatures() {
        $stmt = $this->pdo->query("
            SELECT
                u.id as cliente_id,

                -- LTV Score (normalizzato)
                COALESCE(
                    (SELECT SUM(importo) FROM fatture WHERE cliente_id = u.id AND stato = 'pagata'),
                    0
                ) as lifetime_value,

                -- Engagement Score
                DATEDIFF(NOW(), u.last_login) as days_since_last_login,
                (SELECT COUNT(*) FROM audit_log WHERE user_id = u.id AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)) as actions_last_30d,

                -- Usage Intensity
                COALESCE(
                    (SELECT COUNT(*) FROM servizi_attivi WHERE cliente_id = u.id AND stato = 'attivo'),
                    0
                ) as active_services,
                COALESCE(
                    (SELECT SUM(usage_count) FROM service_usage WHERE cliente_id = u.id AND date >= DATE_SUB(NOW(), INTERVAL 30 DAY)),
                    0
                ) as usage_last_30d,

                -- Service Diversity (quanti tipi diversi di servizi)
                COALESCE(
                    (SELECT COUNT(DISTINCT s.tipo_servizio)
                     FROM servizi_attivi sa
                     JOIN servizi s ON sa.servizio_id = s.id
                     WHERE sa.cliente_id = u.id AND sa.stato = 'attivo'),
                    0
                ) as service_types,

                -- Payment Reliability
                COALESCE(
                    (SELECT COUNT(*) FROM fatture WHERE cliente_id = u.id AND stato = 'pagata' AND pagata_il <= scadenza),
                    0
                ) as on_time_payments,
                COALESCE(
                    (SELECT COUNT(*) FROM fatture WHERE cliente_id = u.id),
                    1
                ) as total_invoices,

                -- Tenure (anzianità)
                DATEDIFF(NOW(), u.created_at) as days_as_customer,

                -- Support interaction
                COALESCE(
                    (SELECT COUNT(*) FROM support_tickets WHERE cliente_id = u.id),
                    0
                ) as total_tickets,

                -- Churn risk (se disponibile)
                COALESCE(
                    (SELECT churn_probability FROM churn_predictions WHERE cliente_id = u.id),
                    0.5
                ) as churn_probability

            FROM utenti u
            WHERE u.ruolo = 'cliente'
            AND u.attivo = TRUE
        ");

        $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Calcola score compositi
        foreach ($customers as &$customer) {
            $customer['ltv_score'] = $this->calculateLTVScore($customer['lifetime_value']);
            $customer['engagement_score'] = $this->calculateEngagementScore($customer);
            $customer['usage_intensity'] = $this->calculateUsageIntensity($customer);
            $customer['service_diversity'] = $this->calculateServiceDiversity($customer);
            $customer['payment_reliability'] = $this->calculatePaymentReliability($customer);
            $customer['tenure_score'] = $this->calculateTenureScore($customer['days_as_customer']);
        }

        return $customers;
    }

    /**
     * Calcola LTV score (0-1)
     */
    private function calculateLTVScore($ltv) {
        // Normalizza con funzione logaritmica per gestire outlier
        if ($ltv <= 0) return 0;

        // Assumiamo LTV target = €10,000
        $target = 10000;
        $score = min(1.0, log($ltv + 1) / log($target + 1));

        return round($score, 4);
    }

    /**
     * Calcola engagement score (0-1)
     */
    private function calculateEngagementScore($customer) {
        $daysSinceLogin = (int)$customer['days_since_last_login'];
        $actions = (int)$customer['actions_last_30d'];

        // Penalizza login vecchi
        $loginScore = 1.0 - min(1.0, $daysSinceLogin / 30);

        // Premia azioni frequenti
        $actionScore = min(1.0, $actions / 100); // 100 actions = max score

        $score = ($loginScore * 0.6) + ($actionScore * 0.4);

        return round($score, 4);
    }

    /**
     * Calcola usage intensity (0-1)
     */
    private function calculateUsageIntensity($customer) {
        $services = (int)$customer['active_services'];
        $usage = (int)$customer['usage_last_30d'];

        // Score servizi attivi
        $serviceScore = min(1.0, $services / 5); // 5 servizi = max

        // Score utilizzo
        $usageScore = min(1.0, $usage / 1000); // 1000 uses = max

        $score = ($serviceScore * 0.4) + ($usageScore * 0.6);

        return round($score, 4);
    }

    /**
     * Calcola service diversity (0-1)
     */
    private function calculateServiceDiversity($customer) {
        $types = (int)$customer['service_types'];

        // Max 4 tipi di servizio
        $score = min(1.0, $types / 4);

        return round($score, 4);
    }

    /**
     * Calcola payment reliability (0-1)
     */
    private function calculatePaymentReliability($customer) {
        $onTime = (int)$customer['on_time_payments'];
        $total = (int)$customer['total_invoices'];

        if ($total === 0) return 0.5; // Neutro se nessuna fattura

        $score = $onTime / $total;

        return round($score, 4);
    }

    /**
     * Calcola tenure score (0-1)
     */
    private function calculateTenureScore($days) {
        // 365 giorni = score massimo
        $score = min(1.0, $days / 365);

        return round($score, 4);
    }

    /**
     * Normalizza features (min-max normalization)
     */
    private function normalizeFeatures($customers) {
        $features = array_keys($this->featureWeights);

        // Trova min/max per ogni feature
        $ranges = [];
        foreach ($features as $feature) {
            $values = array_column($customers, $feature);
            $ranges[$feature] = [
                'min' => min($values),
                'max' => max($values)
            ];
        }

        // Normalizza
        $normalized = [];
        foreach ($customers as $customer) {
            $point = ['id' => $customer['cliente_id']];

            foreach ($features as $feature) {
                $min = $ranges[$feature]['min'];
                $max = $ranges[$feature]['max'];
                $value = $customer[$feature];

                // Min-max normalization
                if ($max - $min > 0) {
                    $point[$feature] = ($value - $min) / ($max - $min);
                } else {
                    $point[$feature] = 0.5; // Valore neutro se tutti uguali
                }
            }

            $normalized[] = $point;
        }

        return $normalized;
    }

    /**
     * Algoritmo K-means clustering
     *
     * @param array $data Dati normalizzati
     * @param int $k Numero cluster
     * @param int $maxIterations Max iterazioni
     * @return array Centroids e assignments
     */
    private function kmeans($data, $k, $maxIterations = 100) {
        $features = array_keys($this->featureWeights);
        $n = count($data);

        // 1. Inizializza centroids randomicamente (K-means++)
        $centroids = $this->initializeCentroids($data, $k, $features);

        $assignments = array_fill(0, $n, 0);
        $converged = false;
        $iteration = 0;

        while (!$converged && $iteration < $maxIterations) {
            $oldAssignments = $assignments;

            // 2. Assignment step: assegna ogni punto al centroid più vicino
            foreach ($data as $i => $point) {
                $minDistance = PHP_FLOAT_MAX;
                $closestCluster = 0;

                foreach ($centroids as $clusterId => $centroid) {
                    $distance = $this->euclideanDistance($point, $centroid, $features);

                    if ($distance < $minDistance) {
                        $minDistance = $distance;
                        $closestCluster = $clusterId;
                    }
                }

                $assignments[$i] = $closestCluster;
            }

            // 3. Update step: ricalcola centroids
            $newCentroids = [];
            for ($clusterId = 0; $clusterId < $k; $clusterId++) {
                $clusterPoints = [];

                foreach ($data as $i => $point) {
                    if ($assignments[$i] === $clusterId) {
                        $clusterPoints[] = $point;
                    }
                }

                if (empty($clusterPoints)) {
                    // Cluster vuoto - reinizializza
                    $newCentroids[$clusterId] = $this->randomPoint($data, $features);
                } else {
                    // Calcola media
                    $newCentroids[$clusterId] = $this->calculateCentroid($clusterPoints, $features);
                }
            }

            $centroids = $newCentroids;

            // 4. Check convergenza
            $converged = ($oldAssignments === $assignments);
            $iteration++;
        }

        return [
            'centroids' => $centroids,
            'assignments' => $assignments,
            'iterations' => $iteration,
            'data' => $data
        ];
    }

    /**
     * Inizializza centroids con K-means++
     */
    private function initializeCentroids($data, $k, $features) {
        $centroids = [];
        $n = count($data);

        // Primo centroid casuale
        $centroids[0] = $data[array_rand($data)];

        // Successivi centroids con probabilità proporzionale a distanza^2
        for ($i = 1; $i < $k; $i++) {
            $distances = [];

            foreach ($data as $point) {
                $minDist = PHP_FLOAT_MAX;

                foreach ($centroids as $centroid) {
                    $dist = $this->euclideanDistance($point, $centroid, $features);
                    $minDist = min($minDist, $dist);
                }

                $distances[] = $minDist * $minDist;
            }

            // Selezione weighted random
            $totalDist = array_sum($distances);
            $rand = mt_rand() / mt_getrandmax() * $totalDist;
            $cumulative = 0;

            foreach ($data as $idx => $point) {
                $cumulative += $distances[$idx];
                if ($cumulative >= $rand) {
                    $centroids[$i] = $point;
                    break;
                }
            }
        }

        return $centroids;
    }

    /**
     * Calcola distanza euclidea tra due punti
     */
    private function euclideanDistance($point1, $point2, $features) {
        $sum = 0;

        foreach ($features as $feature) {
            $weight = $this->featureWeights[$feature];
            $diff = $point1[$feature] - $point2[$feature];
            $sum += $weight * ($diff * $diff);
        }

        return sqrt($sum);
    }

    /**
     * Calcola centroid (media) di un cluster
     */
    private function calculateCentroid($points, $features) {
        $n = count($points);
        $centroid = ['id' => null];

        foreach ($features as $feature) {
            $sum = 0;
            foreach ($points as $point) {
                $sum += $point[$feature];
            }
            $centroid[$feature] = $sum / $n;
        }

        return $centroid;
    }

    /**
     * Genera punto random
     */
    private function randomPoint($data, $features) {
        return $data[array_rand($data)];
    }

    /**
     * Trova numero ottimale di cluster con metodo Elbow
     *
     * @param array $customers Dati clienti
     * @return int Numero ottimale cluster
     */
    private function findOptimalClusters($customers) {
        $normalizedData = $this->normalizeFeatures($customers);
        $features = array_keys($this->featureWeights);

        $wcss = []; // Within-Cluster Sum of Squares
        $minK = 2;
        $maxK = min(10, floor(count($customers) / 5)); // Max 10 cluster o n/5

        for ($k = $minK; $k <= $maxK; $k++) {
            $clustering = $this->kmeans($normalizedData, $k, 50);
            $wcss[$k] = $this->calculateWCSS($clustering, $features);
        }

        // Trova "gomito" - maggiore riduzione marginal WCSS
        $optimalK = $minK;
        $maxDelta = 0;

        for ($k = $minK + 1; $k < $maxK; $k++) {
            $delta = abs($wcss[$k] - $wcss[$k - 1]) - abs($wcss[$k + 1] - $wcss[$k]);

            if ($delta > $maxDelta) {
                $maxDelta = $delta;
                $optimalK = $k;
            }
        }

        return $optimalK;
    }

    /**
     * Calcola Within-Cluster Sum of Squares
     */
    private function calculateWCSS($clustering, $features) {
        $wcss = 0;

        foreach ($clustering['data'] as $i => $point) {
            $clusterId = $clustering['assignments'][$i];
            $centroid = $clustering['centroids'][$clusterId];

            $distance = $this->euclideanDistance($point, $centroid, $features);
            $wcss += $distance * $distance;
        }

        return $wcss;
    }

    /**
     * Profila ogni cluster creando personas
     *
     * @param array $clustering Risultati clustering
     * @param array $customers Dati originali clienti
     * @return array Profili cluster
     */
    private function profileClusters($clustering, $customers) {
        $k = count($clustering['centroids']);
        $profiles = [];

        // Raggruppa clienti per cluster
        $clusters = array_fill(0, $k, []);
        foreach ($clustering['data'] as $i => $point) {
            $clusterId = $clustering['assignments'][$i];
            $clienteId = $point['id'];

            // Trova dati originali
            foreach ($customers as $customer) {
                if ($customer['cliente_id'] == $clienteId) {
                    $clusters[$clusterId][] = $customer;
                    break;
                }
            }
        }

        // Analizza ogni cluster
        foreach ($clusters as $clusterId => $clusterCustomers) {
            if (empty($clusterCustomers)) continue;

            $profile = $this->analyzeCluster($clusterCustomers, $clusterId);
            $profiles[$clusterId] = $profile;
        }

        return $profiles;
    }

    /**
     * Analizza un cluster per creare persona
     */
    private function analyzeCluster($customers, $clusterId) {
        $n = count($customers);

        // Calcola medie
        $avgLTV = array_sum(array_column($customers, 'lifetime_value')) / $n;
        $avgEngagement = array_sum(array_column($customers, 'engagement_score')) / $n;
        $avgUsage = array_sum(array_column($customers, 'usage_intensity')) / $n;
        $avgServices = array_sum(array_column($customers, 'active_services')) / $n;
        $avgChurn = array_sum(array_column($customers, 'churn_probability')) / $n;
        $avgTenure = array_sum(array_column($customers, 'days_as_customer')) / $n;

        // Determina caratteristiche dominanti
        $characteristics = [];

        if ($avgLTV > 5000) {
            $characteristics[] = 'high_value';
        } elseif ($avgLTV < 1000) {
            $characteristics[] = 'low_value';
        }

        if ($avgEngagement > 0.7) {
            $characteristics[] = 'highly_engaged';
        } elseif ($avgEngagement < 0.3) {
            $characteristics[] = 'low_engagement';
        }

        if ($avgUsage > 0.7) {
            $characteristics[] = 'power_user';
        } elseif ($avgUsage < 0.3) {
            $characteristics[] = 'light_user';
        }

        if ($avgChurn > 0.6) {
            $characteristics[] = 'at_risk';
        }

        if ($avgTenure > 180) {
            $characteristics[] = 'loyal';
        } elseif ($avgTenure < 60) {
            $characteristics[] = 'new_customer';
        }

        // Genera nome e descrizione persona
        $persona = $this->generatePersona($characteristics, [
            'ltv' => $avgLTV,
            'engagement' => $avgEngagement,
            'usage' => $avgUsage,
            'churn' => $avgChurn,
            'tenure' => $avgTenure
        ]);

        return [
            'cluster_id' => $clusterId,
            'size' => $n,
            'percentage' => 0, // Calcolato dopo

            // Medie
            'avg_ltv' => round($avgLTV, 2),
            'avg_engagement' => round($avgEngagement, 4),
            'avg_usage' => round($avgUsage, 4),
            'avg_services' => round($avgServices, 2),
            'avg_churn_risk' => round($avgChurn, 4),
            'avg_tenure_days' => round($avgTenure, 0),

            // Caratteristiche
            'characteristics' => $characteristics,

            // Persona
            'persona_name' => $persona['name'],
            'persona_description' => $persona['description'],
            'persona_icon' => $persona['icon'],

            // Raccomandazioni
            'recommendations' => $this->generateClusterRecommendations($characteristics, [
                'ltv' => $avgLTV,
                'engagement' => $avgEngagement,
                'churn' => $avgChurn
            ])
        ];
    }

    /**
     * Genera persona basata su caratteristiche
     */
    private function generatePersona($characteristics, $metrics) {
        // Mapping caratteristiche → personas
        if (in_array('high_value', $characteristics) && in_array('highly_engaged', $characteristics)) {
            return [
                'name' => 'VIP Champions',
                'description' => 'Clienti ad alto valore, altamente coinvolti. I tuoi migliori ambassador.',
                'icon' => '👑'
            ];
        }

        if (in_array('high_value', $characteristics) && in_array('at_risk', $characteristics)) {
            return [
                'name' => 'At-Risk VIPs',
                'description' => 'Clienti di valore ma a rischio abbandono. Necessitano attenzione urgente.',
                'icon' => '⚠️'
            ];
        }

        if (in_array('power_user', $characteristics) && in_array('low_value', $characteristics)) {
            return [
                'name' => 'Power Users Budget',
                'description' => 'Usano intensamente la piattaforma ma spendono poco. Opportunità upselling.',
                'icon' => '🚀'
            ];
        }

        if (in_array('new_customer', $characteristics)) {
            return [
                'name' => 'New Explorers',
                'description' => 'Clienti nuovi in fase di onboarding. Focus su adozione.',
                'icon' => '🌱'
            ];
        }

        if (in_array('loyal', $characteristics) && $metrics['engagement'] > 0.5) {
            return [
                'name' => 'Loyal Advocates',
                'description' => 'Clienti fedeli e soddisfatti. Candidati per referral e case study.',
                'icon' => '💎'
            ];
        }

        if (in_array('low_engagement', $characteristics)) {
            return [
                'name' => 'Hibernating',
                'description' => 'Clienti dormienti con basso engagement. Serve re-engagement.',
                'icon' => '😴'
            ];
        }

        // Default
        return [
            'name' => 'Standard Users',
            'description' => 'Clienti nella media, senza caratteristiche particolari.',
            'icon' => '👤'
        ];
    }

    /**
     * Genera raccomandazioni strategiche per cluster
     */
    private function generateClusterRecommendations($characteristics, $metrics) {
        $recommendations = [];

        if (in_array('at_risk', $characteristics)) {
            $recommendations[] = [
                'priority' => 'critical',
                'action' => 'retention_campaign',
                'message' => 'Avvia immediatamente campagna retention con contatto personale'
            ];
        }

        if (in_array('high_value', $characteristics) && !in_array('at_risk', $characteristics)) {
            $recommendations[] = [
                'priority' => 'high',
                'action' => 'upsell',
                'message' => 'Ottimi candidati per upselling a servizi premium'
            ];
        }

        if (in_array('power_user', $characteristics) && $metrics['ltv'] < 2000) {
            $recommendations[] = [
                'priority' => 'high',
                'action' => 'pricing_optimization',
                'message' => 'Considera upgrade o piano enterprise per heavy usage'
            ];
        }

        if (in_array('new_customer', $characteristics)) {
            $recommendations[] = [
                'priority' => 'medium',
                'action' => 'onboarding',
                'message' => 'Focus su onboarding e training per massimizzare adozione'
            ];
        }

        if (in_array('low_engagement', $characteristics)) {
            $recommendations[] = [
                'priority' => 'medium',
                'action' => 'reengagement',
                'message' => 'Email automation per riattivare clienti dormienti'
            ];
        }

        if (in_array('loyal', $characteristics) && $metrics['engagement'] > 0.7) {
            $recommendations[] = [
                'priority' => 'low',
                'action' => 'advocacy',
                'message' => 'Richiedi referral, recensioni, case study'
            ];
        }

        return $recommendations;
    }

    /**
     * Salva risultati clustering nel database
     */
    private function saveClustering($clustering, $profiles) {
        $this->pdo->beginTransaction();

        try {
            // 1. Cancella vecchie assegnazioni
            $this->pdo->exec("TRUNCATE TABLE customer_segments");
            $this->pdo->exec("TRUNCATE TABLE segment_profiles");

            // 2. Salva assegnazioni clienti
            $stmt = $this->pdo->prepare("
                INSERT INTO customer_segments (cliente_id, segment_id, assignment_date)
                VALUES (:cliente_id, :segment_id, NOW())
            ");

            foreach ($clustering['data'] as $i => $point) {
                $stmt->execute([
                    'cliente_id' => $point['id'],
                    'segment_id' => $clustering['assignments'][$i]
                ]);
            }

            // 3. Calcola percentuali
            $totalCustomers = count($clustering['data']);
            foreach ($profiles as $clusterId => &$profile) {
                $profile['percentage'] = round(($profile['size'] / $totalCustomers) * 100, 1);
            }

            // 4. Salva profili cluster
            $stmt = $this->pdo->prepare("
                INSERT INTO segment_profiles (
                    segment_id,
                    persona_name,
                    persona_description,
                    persona_icon,
                    size,
                    percentage,
                    avg_ltv,
                    avg_engagement,
                    avg_usage,
                    avg_churn_risk,
                    avg_tenure_days,
                    characteristics,
                    recommendations,
                    centroid_data,
                    created_at
                ) VALUES (
                    :segment_id,
                    :persona_name,
                    :persona_description,
                    :persona_icon,
                    :size,
                    :percentage,
                    :avg_ltv,
                    :avg_engagement,
                    :avg_usage,
                    :avg_churn_risk,
                    :avg_tenure_days,
                    :characteristics,
                    :recommendations,
                    :centroid_data,
                    NOW()
                )
            ");

            foreach ($profiles as $clusterId => $profile) {
                $stmt->execute([
                    'segment_id' => $clusterId,
                    'persona_name' => $profile['persona_name'],
                    'persona_description' => $profile['persona_description'],
                    'persona_icon' => $profile['persona_icon'],
                    'size' => $profile['size'],
                    'percentage' => $profile['percentage'],
                    'avg_ltv' => $profile['avg_ltv'],
                    'avg_engagement' => $profile['avg_engagement'],
                    'avg_usage' => $profile['avg_usage'],
                    'avg_churn_risk' => $profile['avg_churn_risk'],
                    'avg_tenure_days' => $profile['avg_tenure_days'],
                    'characteristics' => json_encode($profile['characteristics']),
                    'recommendations' => json_encode($profile['recommendations']),
                    'centroid_data' => json_encode($clustering['centroids'][$clusterId])
                ]);
            }

            $this->pdo->commit();

        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Assegna un nuovo cliente a un segmento esistente
     *
     * @param int $clienteId ID cliente
     * @return array Segmento assegnato
     */
    public function assignCustomerToSegment($clienteId) {
        // Estrai features cliente
        $customer = $this->extractCustomerFeatures($clienteId);

        if (!$customer) {
            throw new Exception("Cliente {$clienteId} non trovato");
        }

        // Normalizza
        $normalized = $this->normalizeFeatures([$customer])[0];

        // Carica centroids esistenti
        $stmt = $this->pdo->query("
            SELECT segment_id, centroid_data
            FROM segment_profiles
            ORDER BY segment_id
        ");

        $centroids = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $centroids[$row['segment_id']] = json_decode($row['centroid_data'], true);
        }

        if (empty($centroids)) {
            throw new Exception('Nessun segmento esistente. Eseguire clustering prima.');
        }

        // Trova centroid più vicino
        $features = array_keys($this->featureWeights);
        $minDistance = PHP_FLOAT_MAX;
        $assignedSegment = 0;

        foreach ($centroids as $segmentId => $centroid) {
            $distance = $this->euclideanDistance($normalized, $centroid, $features);

            if ($distance < $minDistance) {
                $minDistance = $distance;
                $assignedSegment = $segmentId;
            }
        }

        // Salva assegnazione
        $stmt = $this->pdo->prepare("
            INSERT INTO customer_segments (cliente_id, segment_id, assignment_date)
            VALUES (:cliente_id, :segment_id, NOW())
            ON DUPLICATE KEY UPDATE
                segment_id = :segment_id2,
                assignment_date = NOW()
        ");

        $stmt->execute([
            'cliente_id' => $clienteId,
            'segment_id' => $assignedSegment,
            'segment_id2' => $assignedSegment
        ]);

        // Ritorna profilo segmento
        $stmt = $this->pdo->prepare("
            SELECT * FROM segment_profiles WHERE segment_id = :segment_id
        ");
        $stmt->execute(['segment_id' => $assignedSegment]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Estrae features per un singolo cliente
     */
    private function extractCustomerFeatures($clienteId) {
        $customers = $this->extractAllCustomerFeatures();

        foreach ($customers as $customer) {
            if ($customer['cliente_id'] == $clienteId) {
                return $customer;
            }
        }

        return null;
    }

    /**
     * Ottieni statistiche segmentazione
     */
    public function getSegmentationStats() {
        $stmt = $this->pdo->query("
            SELECT
                COUNT(DISTINCT segment_id) as total_segments,
                COUNT(DISTINCT cliente_id) as total_customers,
                MAX(assignment_date) as last_update
            FROM customer_segments
        ");

        $stats = $stmt->fetch(PDO::FETCH_ASSOC);

        // Profili segmenti
        $stmt = $this->pdo->query("
            SELECT * FROM segment_profiles
            ORDER BY size DESC
        ");

        $stats['segments'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $stats;
    }
}
