<?php
// Blocca accesso diretto alla directory
http_response_code(403);
exit('Accesso negato');
