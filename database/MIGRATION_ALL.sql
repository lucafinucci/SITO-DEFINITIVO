-- ===============================================
-- MIGRATION ALL - Area Clienti (Admin)
-- ===============================================

-- 1) Gestione Clienti Avanzata
SOURCE database/add_clienti_admin_tables.sql;

-- 2) Pipeline Vendite
SOURCE database/add_pipeline_trattative.sql;

-- 3) Preventivi
SOURCE database/add_preventivi_tables.sql;

-- 4) Rinnovi Contratti
SOURCE database/add_rinnovi_contratti.sql;

-- 5) Prezzi personalizzati
SOURCE database/add_prezzi_personalizzati.sql;

-- 6) Coupon e sconti temporanei
SOURCE database/add_coupon_sconti.sql;

-- 7) Pacchetti bundle
SOURCE database/add_pacchetti_bundles.sql;

-- 8) Versioning servizi
SOURCE database/add_servizi_versioni.sql;

-- 9) Quote utilizzo servizi
SOURCE database/add_servizi_quote.sql;

-- 10) Servizi on-demand
SOURCE database/add_servizi_on_demand.sql;

-- 11) Sistema Ticket Supporto
SOURCE database/add_ticketing.sql;
