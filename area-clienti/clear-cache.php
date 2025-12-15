<?php
// Script per pulire la cache PHP OPcache
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "OPcache cleared successfully!<br>";
} else {
    echo "OPcache not enabled.<br>";
}

// Redirect alla pagina del servizio
header('Location: /area-clienti/servizio-dettaglio.php?id=1');
exit;
