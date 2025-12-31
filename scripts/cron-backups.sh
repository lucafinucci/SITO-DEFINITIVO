#!/bin/bash
# ============================================================================
# Cron Jobs per Backup Automatici
# ============================================================================
#
# Installa con:
#   chmod +x /path/to/scripts/cron-backups.sh
#   crontab -e
#   # Poi aggiungi le righe sotto
#
# ============================================================================

# Directory script
SCRIPT_DIR="/var/www/finch-ai/scripts"
PHP="/usr/bin/php"
LOG_DIR="/var/www/finch-ai/backups/logs"

# Email notifiche
ADMIN_EMAIL="admin@finch-ai.it"

# ============================================================================
# BACKUP DATABASE
# ============================================================================

# Backup completo database - Ogni giorno alle 02:00
# 0 2 * * * $PHP $SCRIPT_DIR/backup-database.php --type=full --compress 2>&1 | tee -a $LOG_DIR/database-$(date +\%Y-\%m-\%d).log

# Backup incrementale database - Ogni 6 ore
# 0 */6 * * * $PHP $SCRIPT_DIR/backup-database.php --type=incremental --compress 2>&1 | tee -a $LOG_DIR/database-inc-$(date +\%Y-\%m-\%d).log

# Backup completo con crittografia - Ogni domenica alle 03:00
# 0 3 * * 0 $PHP $SCRIPT_DIR/backup-database.php --type=full --compress --encrypt 2>&1 | tee -a $LOG_DIR/database-encrypted-$(date +\%Y-\%m-\%d).log

# ============================================================================
# BACKUP FILE
# ============================================================================

# Backup completo file - Ogni giorno alle 03:00
# 0 3 * * * $PHP $SCRIPT_DIR/backup-files.php --type=full --compress 2>&1 | tee -a $LOG_DIR/files-$(date +\%Y-\%m-\%d).log

# Backup incrementale file - Ogni ora
# 0 * * * * $PHP $SCRIPT_DIR/backup-files.php --type=incremental --compress 2>&1 | tee -a $LOG_DIR/files-inc-$(date +\%Y-\%m-\%d).log

# ============================================================================
# CLEANUP
# ============================================================================

# Pulizia backup vecchi - Ogni domenica alle 04:00
# 0 4 * * 0 $PHP $SCRIPT_DIR/backup-database.php --type=cleanup 2>&1 | tee -a $LOG_DIR/cleanup-$(date +\%Y-\%m-\%d).log
# 0 4 * * 0 $PHP $SCRIPT_DIR/backup-files.php --type=cleanup 2>&1 | tee -a $LOG_DIR/cleanup-$(date +\%Y-\%m-\%d).log

# ============================================================================
# VERIFICA & NOTIFICHE
# ============================================================================

# Verifica integrità backup - Ogni lunedì alle 05:00
# 0 5 * * 1 $PHP $SCRIPT_DIR/verify-backups.php 2>&1 | mail -s "Backup Verification Report" $ADMIN_EMAIL

# ============================================================================
# CONFIGURAZIONE CONSIGLIATA PER PRODUZIONE
# ============================================================================
#
# OPZIONE 1: Backup Frequente (consigliato per e-commerce/SaaS)
# -------------------------------------------------------------------------
# 0 2 * * *     Backup completo DB giornaliero
# 0 */6 * * *   Backup incrementale DB ogni 6 ore
# 0 3 * * *     Backup completo file giornaliero
# 0 * * * *     Backup incrementale file ogni ora
# 0 4 * * 0     Cleanup settimanale
#
#
# OPZIONE 2: Backup Moderato (consigliato per siti corporate)
# -------------------------------------------------------------------------
# 0 2 * * *     Backup completo DB giornaliero
# 0 */12 * * *  Backup incrementale DB ogni 12 ore
# 0 3 * * *     Backup completo file giornaliero
# 0 4 * * 0     Cleanup settimanale
#
#
# OPZIONE 3: Backup Leggero (consigliato per siti piccoli)
# -------------------------------------------------------------------------
# 0 2 * * *     Backup completo DB giornaliero
# 0 3 * * *     Backup completo file giornaliero
# 0 4 * * 0     Cleanup settimanale
#
# ============================================================================

# ============================================================================
# ESEMPIO CRON COMPLETO
# ============================================================================
# Copia questo in crontab -e:

# Backup Database - Full giornaliero
0 2 * * * /usr/bin/php /var/www/finch-ai/scripts/backup-database.php --type=full --compress 2>&1 | tee -a /var/www/finch-ai/backups/logs/database-$(date +\%Y-\%m-\%d).log

# Backup Database - Incrementale ogni 6 ore
0 */6 * * * /usr/bin/php /var/www/finch-ai/scripts/backup-database.php --type=incremental --compress 2>&1 | tee -a /var/www/finch-ai/backups/logs/database-inc-$(date +\%Y-\%m-\%d).log

# Backup File - Full giornaliero
0 3 * * * /usr/bin/php /var/www/finch-ai/scripts/backup-files.php --type=full --compress 2>&1 | tee -a /var/www/finch-ai/backups/logs/files-$(date +\%Y-\%m-\%d).log

# Backup File - Incrementale ogni ora
0 * * * * /usr/bin/php /var/www/finch-ai/scripts/backup-files.php --type=incremental --compress 2>&1 | tee -a /var/www/finch-ai/backups/logs/files-inc-$(date +\%Y-\%m-\%d).log

# Cleanup settimanale
0 4 * * 0 /usr/bin/php /var/www/finch-ai/scripts/backup-database.php --type=cleanup 2>&1 | tee -a /var/www/finch-ai/backups/logs/cleanup-$(date +\%Y-\%m-\%d).log
5 4 * * 0 /usr/bin/php /var/www/finch-ai/scripts/backup-files.php --type=cleanup 2>&1 | tee -a /var/www/finch-ai/backups/logs/cleanup-$(date +\%Y-\%m-\%d).log

# ============================================================================
# WINDOWS TASK SCHEDULER (alternativa a cron)
# ============================================================================
#
# 1. Apri Task Scheduler
# 2. Create Task...
# 3. General: Nome "Finch-AI Backup Database"
# 4. Triggers: Daily, 02:00
# 5. Actions:
#    - Program: C:\xampp\php\php.exe
#    - Arguments: C:\Users\oneno\Desktop\SITO\scripts\backup-database.php --type=full --compress
#    - Start in: C:\Users\oneno\Desktop\SITO\scripts
# 6. Ripeti per altri backup
#
# ============================================================================
