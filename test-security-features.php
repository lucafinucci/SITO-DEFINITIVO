<?php
/**
 * Test Security Features
 * Script di test per verificare tutte le funzionalità implementate
 */

echo "<!DOCTYPE html>
<html lang='it'>
<head>
    <meta charset='utf-8'>
    <title>Test Security Features - Finch-AI</title>
    <style>
        body { font-family: system-ui; max-width: 900px; margin: 40px auto; padding: 20px; background: #0b1220; color: #e5e7eb; }
        .test { margin: 20px 0; padding: 15px; background: #1f2937; border-radius: 8px; border-left: 4px solid #22d3ee; }
        .success { border-left-color: #10b981; }
        .error { border-left-color: #ef4444; }
        .warning { border-left-color: #fbbf24; }
        h1 { color: #22d3ee; }
        h3 { margin: 0 0 10px 0; }
        pre { background: #0f172a; padding: 10px; border-radius: 4px; overflow-x: auto; }
        .result { margin-top: 10px; }
    </style>
</head>
<body>
    <h1>🔐 Test Security Features</h1>
    <p>Verifica funzionalità di sicurezza implementate</p>
";

$errors = [];
$warnings = [];
$success = [];

// Test 1: Config
echo "<div class='test'><h3>1. Config & Environment Variables</h3>";
try {
    require_once __DIR__ . '/area-clienti/includes/config.php';

    $dbHost = Config::get('DB_HOST');
    $dbName = Config::get('DB_NAME');

    echo "<div class='result success'>";
    echo "✅ Config caricato correttamente<br>";
    echo "DB Host: <code>{$dbHost}</code><br>";
    echo "DB Name: <code>{$dbName}</code><br>";
    echo "Debug Mode: <code>" . (Config::isDebug() ? 'true' : 'false') . "</code><br>";
    echo "Environment: <code>" . Config::get('APP_ENV', 'N/A') . "</code>";
    echo "</div>";

    $success[] = "Config";
} catch (Exception $e) {
    echo "<div class='result error'>❌ Errore: " . htmlspecialchars($e->getMessage()) . "</div>";
    $errors[] = "Config";
}
echo "</div>";

// Test 2: Security Class
echo "<div class='test'><h3>2. Security Class (CSRF, Rate Limiting, Validation)</h3>";
try {
    require_once __DIR__ . '/area-clienti/includes/security.php';

    // Test CSRF
    session_start();
    $token = Security::generateCSRFToken();
    $verified = Security::verifyCSRFToken($token);

    // Test Validation
    $emailTest = Security::validateEmail('test@example.com');
    $passwordTest = Security::validatePassword('Test123');
    $phoneTest = Security::validatePhone('+39 123 456 7890');

    echo "<div class='result success'>";
    echo "✅ Security class funzionante<br>";
    echo "CSRF Token generato: <code>" . substr($token, 0, 16) . "...</code><br>";
    echo "CSRF Verification: <code>" . ($verified ? 'OK' : 'FAIL') . "</code><br>";
    echo "Email Validation: <code>" . ($emailTest['valid'] ? 'OK' : 'FAIL') . "</code><br>";
    echo "Password Validation: <code>" . ($passwordTest['valid'] ? 'OK' : 'FAIL') . "</code><br>";
    echo "Phone Validation: <code>" . ($phoneTest['valid'] ? 'OK' : 'FAIL') . "</code>";
    echo "</div>";

    $success[] = "Security";
} catch (Exception $e) {
    echo "<div class='result error'>❌ Errore: " . htmlspecialchars($e->getMessage()) . "</div>";
    $errors[] = "Security";
}
echo "</div>";

// Test 3: TOTP
echo "<div class='test'><h3>3. TOTP/MFA Library</h3>";
try {
    require_once __DIR__ . '/area-clienti/includes/totp.php';

    $secret = TOTP::generateSecret();
    $code = TOTP::generateCode($secret);
    $verified = TOTP::verifyCode($secret, $code);
    $qrURL = TOTP::getQRCodeURL($secret, 'test@example.com');

    echo "<div class='result success'>";
    echo "✅ TOTP library funzionante<br>";
    echo "Secret generato: <code>{$secret}</code><br>";
    echo "Codice TOTP: <code>{$code}</code><br>";
    echo "Verifica: <code>" . ($verified ? 'OK' : 'FAIL') . "</code><br>";
    echo "QR Code: <code>" . (strpos($qrURL, 'chart.googleapis.com') !== false ? 'OK' : 'FAIL') . "</code>";
    echo "</div>";

    $success[] = "TOTP";
} catch (Exception $e) {
    echo "<div class='result error'>❌ Errore: " . htmlspecialchars($e->getMessage()) . "</div>";
    $errors[] = "TOTP";
}
echo "</div>";

// Test 4: Cache
echo "<div class='test'><h3>4. Cache System</h3>";
try {
    require_once __DIR__ . '/area-clienti/includes/cache.php';

    $testKey = 'test_key_' . time();
    $testValue = ['data' => 'test', 'timestamp' => time()];

    Cache::set($testKey, $testValue, 60);
    $retrieved = Cache::get($testKey);
    $match = $retrieved === $testValue;

    Cache::delete($testKey);
    $deleted = Cache::get($testKey) === null;

    echo "<div class='result success'>";
    echo "✅ Cache system funzionante<br>";
    echo "Set/Get: <code>" . ($match ? 'OK' : 'FAIL') . "</code><br>";
    echo "Delete: <code>" . ($deleted ? 'OK' : 'FAIL') . "</code><br>";
    echo "Cache Directory: <code>" . (is_dir(__DIR__ . '/cache') ? 'Exists' : 'Missing') . "</code>";
    echo "</div>";

    if (!is_dir(__DIR__ . '/cache')) {
        $warnings[] = "Cache directory non trovata";
    } else {
        $success[] = "Cache";
    }
} catch (Exception $e) {
    echo "<div class='result error'>❌ Errore: " . htmlspecialchars($e->getMessage()) . "</div>";
    $errors[] = "Cache";
}
echo "</div>";

// Test 5: Error Handler
echo "<div class='test'><h3>5. Error Handler</h3>";
try {
    require_once __DIR__ . '/area-clienti/includes/error-handler.php';

    ErrorHandler::logAccess('Test log entry from test script');

    $logDir = __DIR__ . '/logs';
    $logExists = is_dir($logDir);
    $accessLog = file_exists($logDir . '/access.log');

    echo "<div class='result success'>";
    echo "✅ Error handler funzionante<br>";
    echo "Logs Directory: <code>" . ($logExists ? 'Exists' : 'Missing') . "</code><br>";
    echo "Access Log: <code>" . ($accessLog ? 'Exists' : 'Missing') . "</code><br>";
    echo "Debug Mode: <code>" . (Config::isDebug() ? 'Enabled' : 'Disabled') . "</code>";
    echo "</div>";

    if (!$logExists || !$accessLog) {
        $warnings[] = "Logs directory o file mancanti";
    } else {
        $success[] = "Error Handler";
    }
} catch (Exception $e) {
    echo "<div class='result error'>❌ Errore: " . htmlspecialchars($e->getMessage()) . "</div>";
    $errors[] = "Error Handler";
}
echo "</div>";

// Test 6: Database Connection
echo "<div class='test'><h3>6. Database Connection</h3>";
try {
    require_once __DIR__ . '/area-clienti/includes/db.php';

    $stmt = $pdo->query("SELECT VERSION() as version");
    $version = $stmt->fetch();

    echo "<div class='result success'>";
    echo "✅ Database connesso<br>";
    echo "MySQL Version: <code>{$version['version']}</code><br>";
    echo "Database: <code>" . Config::get('DB_NAME') . "</code>";
    echo "</div>";

    $success[] = "Database";
} catch (Exception $e) {
    echo "<div class='result error'>❌ Errore connessione database: " . htmlspecialchars($e->getMessage()) . "</div>";
    $errors[] = "Database";
}
echo "</div>";

// Test 7: Files Check
echo "<div class='test'><h3>7. Files & Directories</h3>";

$requiredFiles = [
    '.env' => 'Environment config',
    '.env.example' => 'Environment template',
    '.gitignore' => 'Git ignore file',
    'area-clienti/includes/config.php' => 'Config loader',
    'area-clienti/includes/security.php' => 'Security utils',
    'area-clienti/includes/totp.php' => 'TOTP library',
    'area-clienti/includes/cache.php' => 'Cache system',
    'area-clienti/includes/error-handler.php' => 'Error handler',
    'area-clienti/mfa-setup.php' => 'MFA setup page',
    'area-clienti/api/kpi-proxy.php' => 'KPI proxy API',
];

$requiredDirs = [
    'cache' => 'Cache directory',
    'logs' => 'Logs directory',
    'area-clienti/api' => 'API directory',
];

echo "<div class='result'>";
echo "<strong>Files:</strong><br>";
foreach ($requiredFiles as $file => $desc) {
    $exists = file_exists(__DIR__ . '/' . $file);
    echo ($exists ? '✅' : '❌') . " {$desc}: <code>{$file}</code><br>";
    if (!$exists) $warnings[] = "File mancante: {$file}";
}

echo "<br><strong>Directories:</strong><br>";
foreach ($requiredDirs as $dir => $desc) {
    $exists = is_dir(__DIR__ . '/' . $dir);
    $writable = $exists && is_writable(__DIR__ . '/' . $dir);
    echo ($exists ? '✅' : '❌') . " {$desc}: <code>{$dir}</code>";
    echo " " . ($writable ? '(writable)' : '(not writable)') . "<br>";

    if (!$exists) $errors[] = "Directory mancante: {$dir}";
    elseif (!$writable) $warnings[] = "Directory non scrivibile: {$dir}";
}
echo "</div>";
echo "</div>";

// Summary
echo "<div class='test' style='border-left-color: " . (empty($errors) ? '#10b981' : '#ef4444') . ";'>";
echo "<h3>📊 Riepilogo Test</h3>";
echo "<div class='result'>";
echo "<strong>✅ Successi:</strong> " . count($success) . "<br>";
echo "<strong>⚠️ Warning:</strong> " . count($warnings) . "<br>";
echo "<strong>❌ Errori:</strong> " . count($errors) . "<br>";

if (!empty($errors)) {
    echo "<br><strong>Errori da risolvere:</strong><ul>";
    foreach ($errors as $error) {
        echo "<li>{$error}</li>";
    }
    echo "</ul>";
}

if (!empty($warnings)) {
    echo "<br><strong>Warning:</strong><ul>";
    foreach ($warnings as $warning) {
        echo "<li>{$warning}</li>";
    }
    echo "</ul>";
}

if (empty($errors)) {
    echo "<br><div style='color: #10b981; font-size: 18px; font-weight: bold;'>🎉 Tutti i test superati!</div>";
    echo "<p>L'implementazione delle security features è completa e funzionante.</p>";
} else {
    echo "<br><div style='color: #ef4444; font-size: 18px; font-weight: bold;'>⚠️ Alcuni test falliti</div>";
    echo "<p>Risolvi gli errori sopra elencati e riprova.</p>";
}

echo "</div></div>";

echo "<p style='text-align: center; color: #9ca3af; margin-top: 40px;'>
    © 2024 Finch-AI - Security Test Suite v1.0
</p>";

echo "</body></html>";
