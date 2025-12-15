<?php
/**
 * Redirect from /area-clienti to /area-clienti/login.php
 * This fixes the navigation link from the homepage SPA
 */

header('Location: /area-clienti/login.php', true, 302);
exit;
