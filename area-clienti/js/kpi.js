/**
 * KPI Data Loader
 * Carica dati KPI tramite proxy interno (evita CORS)
 */
(async function () {
  const map = {
    documenti: 'kpi-documenti',
    tempo: 'kpi-tempo',
    costo: 'kpi-costo',
    automazione: 'kpi-automazione',
    errori: 'kpi-errori',
    roi: 'kpi-roi',
  };

  const setKpi = (key, val) => {
    const el = document.getElementById(map[key]);
    if (el) el.textContent = val;
  };

  const showLoading = () => {
    Object.keys(map).forEach(key => setKpi(key, '...'));
  };

  const showError = () => {
    Object.keys(map).forEach(key => setKpi(key, '--'));
  };

  try {
    showLoading();

    // Usa proxy interno invece di API esterna
    const res = await fetch('/area-clienti/api/kpi-proxy.php', {
      method: 'GET',
      credentials: 'same-origin',
    });

    if (!res.ok) {
      throw new Error('KPI non disponibili');
    }

    const response = await res.json();

    if (!response.success) {
      throw new Error(response.error || 'Errore caricamento KPI');
    }

    const data = response.data;

    // Aggiorna KPI
    setKpi('documenti', data.documenti ?? 0);
    setKpi('tempo', data.tempo_risparmiato ?? '0h');
    setKpi('costo', data.costo_risparmiato ?? '€0');
    setKpi('automazione', data.automazione ?? '0%');
    setKpi('errori', data.errori_evitati ?? 0);
    setKpi('roi', data.roi ?? '0%');

    // Render chart se disponibile
    const ctx = document.getElementById('chart-trend');
    if (ctx && data.trend) {
      const labels = data.trend.mesi ?? [];
      new Chart(ctx, {
        type: 'line',
        data: {
          labels,
          datasets: [
            {
              label: 'Documenti',
              data: data.trend.documenti ?? [],
              borderColor: '#22d3ee',
              backgroundColor: 'rgba(34,211,238,0.12)',
              tension: 0.35,
              fill: true,
            },
            {
              label: 'Automazione %',
              data: data.trend.automazione ?? [],
              borderColor: '#3b82f6',
              backgroundColor: 'rgba(59,130,246,0.12)',
              tension: 0.35,
              fill: true,
            },
            {
              label: 'Ore risparmiate',
              data: data.trend.ore ?? [],
              borderColor: '#10b981',
              backgroundColor: 'rgba(16,185,129,0.12)',
              tension: 0.35,
              fill: true,
            },
          ],
        },
        options: {
          responsive: true,
          maintainAspectRatio: true,
          plugins: {
            legend: {
              labels: { color: '#e5e7eb' }
            }
          },
          scales: {
            x: {
              ticks: { color: '#9ca3af' },
              grid: { color: 'rgba(255,255,255,0.05)' }
            },
            y: {
              ticks: { color: '#9ca3af' },
              grid: { color: 'rgba(255,255,255,0.05)' }
            },
          },
        },
      });
    }

    // Log info in console (debug)
    if (response.cached) {
      console.log('📊 KPI caricati da cache');
    } else if (response.mock || response.fallback) {
      console.log('📊 KPI mockati (API non disponibile)');
    } else {
      console.log('📊 KPI caricati da API');
    }

  } catch (err) {
    console.error('Errore caricamento KPI:', err);
    showError();
  }
})();
