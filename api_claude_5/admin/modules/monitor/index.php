<?php
if (!defined('API_ACCESS')) die('Access denied');

// Verificar autenticación admin (si existe)
// session_start() ya se llama en admin/index.php
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitor en Tiempo Real - API Claude 5</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: #f5f7fa;
            padding: 20px;
            color: #2c3e50;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
        }

        .header {
            background: white;
            padding: 20px 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h1 {
            font-size: 24px;
            color: #2c3e50;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .live-indicator {
            width: 10px;
            height: 10px;
            background: #10b981;
            border-radius: 50%;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        .status {
            font-size: 14px;
            color: #64748b;
        }

        .status.active {
            color: #10b981;
        }

        .metrics {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .metric-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .metric-card .label {
            font-size: 12px;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }

        .metric-card .value {
            font-size: 28px;
            font-weight: 700;
            color: #2c3e50;
        }

        .metric-card .subvalue {
            font-size: 12px;
            color: #94a3b8;
            margin-top: 4px;
        }

        .table-container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .table-header {
            padding: 20px 30px;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .table-header h2 {
            font-size: 18px;
            color: #2c3e50;
        }

        .table-controls {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .table-controls select,
        .table-controls input {
            padding: 6px 12px;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            font-size: 14px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            background: #f8fafc;
        }

        th {
            padding: 12px 20px;
            text-align: left;
            font-size: 12px;
            font-weight: 600;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 1px solid #e2e8f0;
        }

        td {
            padding: 16px 20px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 14px;
        }

        tbody tr:hover {
            background: #f8fafc;
        }

        .endpoint {
            font-family: 'Monaco', 'Courier New', monospace;
            font-size: 13px;
            color: #0ea5e9;
            font-weight: 500;
        }

        .model {
            font-family: 'Monaco', 'Courier New', monospace;
            font-size: 12px;
            background: #f1f5f9;
            padding: 4px 8px;
            border-radius: 4px;
            display: inline-block;
        }

        .tokens {
            font-family: 'Monaco', 'Courier New', monospace;
            font-size: 12px;
        }

        .tokens .in {
            color: #059669;
        }

        .tokens .out {
            color: #dc2626;
        }

        .cost {
            font-weight: 600;
            color: #7c3aed;
        }

        .license {
            font-size: 12px;
            color: #64748b;
        }

        .timestamp {
            font-size: 12px;
            color: #94a3b8;
        }

        .badge {
            font-size: 11px;
            padding: 3px 8px;
            border-radius: 4px;
            font-weight: 500;
            display: inline-block;
        }

        .badge.setup {
            background: #dbeafe;
            color: #1e40af;
        }

        .badge.cola {
            background: #fef3c7;
            color: #92400e;
        }

        .badge.contenido {
            background: #dcfce7;
            color: #166534;
        }

        .loading {
            text-align: center;
            padding: 40px;
            color: #64748b;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #64748b;
        }

        .empty-state svg {
            width: 64px;
            height: 64px;
            margin-bottom: 16px;
            opacity: 0.3;
        }

        .controls {
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
        }

        .btn {
            padding: 8px 16px;
            border: 1px solid #e2e8f0;
            background: white;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.2s;
        }

        .btn:hover {
            background: #f8fafc;
            border-color: #cbd5e1;
        }

        .btn.active {
            background: #0ea5e9;
            color: white;
            border-color: #0ea5e9;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>
                <span class="live-indicator"></span>
                Monitor en Tiempo Real
            </h1>
            <div class="status active" id="status">
                Actualizando cada 3s
            </div>
        </div>

        <!-- Metrics Cards -->
        <div class="metrics">
            <div class="metric-card">
                <div class="label">Requests (últimos 5min)</div>
                <div class="value" id="metric-requests">-</div>
                <div class="subvalue" id="metric-rpm">-</div>
            </div>

            <div class="metric-card">
                <div class="label">Tokens Procesados</div>
                <div class="value" id="metric-tokens">-</div>
                <div class="subvalue" id="metric-tpm">-</div>
            </div>

            <div class="metric-card">
                <div class="label">Coste Total (EUR)</div>
                <div class="value" id="metric-cost">-</div>
                <div class="subvalue" id="metric-cph">-</div>
            </div>

            <div class="metric-card">
                <div class="label">Modelo Más Usado</div>
                <div class="value" style="font-size: 16px; font-family: monospace;" id="metric-model">-</div>
                <div class="subvalue" id="metric-licenses">-</div>
            </div>
        </div>

        <!-- Table -->
        <div class="table-container">
            <div class="table-header">
                <h2>Operaciones Recientes</h2>
                <div class="table-controls">
                    <select id="time-range">
                        <option value="5">Últimos 5 min</option>
                        <option value="10">Últimos 10 min</option>
                        <option value="30">Últimos 30 min</option>
                        <option value="60">Última hora</option>
                    </select>
                </div>
            </div>

            <div id="table-content">
                <div class="loading">
                    Cargando datos...
                </div>
            </div>
        </div>
    </div>

    <script>
        let pollingInterval = null;
        let currentMinutes = 5;

        // Iniciar al cargar la página
        document.addEventListener('DOMContentLoaded', function() {
            fetchData();
            startPolling();

            // Listener para cambio de rango de tiempo
            document.getElementById('time-range').addEventListener('change', function(e) {
                currentMinutes = parseInt(e.target.value);
                fetchData();
            });
        });

        // Detener polling cuando se cierra/oculta la página
        document.addEventListener('visibilitychange', function() {
            if (document.hidden) {
                stopPolling();
            } else {
                startPolling();
            }
        });

        function startPolling() {
            if (pollingInterval) return; // Ya está activo

            pollingInterval = setInterval(fetchData, 3000); // Cada 3 segundos
            updateStatus('Actualizando cada 3s', true);
        }

        function stopPolling() {
            if (pollingInterval) {
                clearInterval(pollingInterval);
                pollingInterval = null;
                updateStatus('Pausado', false);
            }
        }

        function updateStatus(text, active) {
            const statusEl = document.getElementById('status');
            statusEl.textContent = text;
            statusEl.className = 'status' + (active ? ' active' : '');
        }

        async function fetchData() {
            try {
                const apiUrl = window.location.origin + window.location.pathname.replace('/admin/modules/monitor/', '') + '/?route=monitor/live&minutes=' + currentMinutes;

                const response = await fetch(apiUrl);
                const data = await response.json();

                if (data.success) {
                    updateMetrics(data.data.metrics);
                    updateTable(data.data.operations);
                } else {
                    console.error('Error en respuesta:', data);
                }
            } catch (error) {
                console.error('Error fetching data:', error);
                updateStatus('Error de conexión', false);
            }
        }

        function updateMetrics(metrics) {
            document.getElementById('metric-requests').textContent = metrics.total_requests;
            document.getElementById('metric-rpm').textContent = metrics.requests_per_minute + ' req/min';

            document.getElementById('metric-tokens').textContent = formatNumber(metrics.total_tokens);
            document.getElementById('metric-tpm').textContent = formatNumber(metrics.tokens_per_minute) + ' tokens/min';

            document.getElementById('metric-cost').textContent = '€' + metrics.total_cost_eur.toFixed(4);
            document.getElementById('metric-cph').textContent = '€' + metrics.cost_per_hour_eur.toFixed(4) + '/hora';

            document.getElementById('metric-model').textContent = metrics.top_model;
            document.getElementById('metric-licenses').textContent = metrics.unique_licenses + ' licencias activas';
        }

        function updateTable(operations) {
            const tableContent = document.getElementById('table-content');

            if (operations.length === 0) {
                tableContent.innerHTML = `
                    <div class="empty-state">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                        </svg>
                        <p>No hay operaciones en los últimos ${currentMinutes} minutos</p>
                    </div>
                `;
                return;
            }

            let html = `
                <table>
                    <thead>
                        <tr>
                            <th>Hora</th>
                            <th>Endpoint</th>
                            <th>Modelo</th>
                            <th>Tokens E/S</th>
                            <th>Coste E (EUR)</th>
                            <th>Coste S (EUR)</th>
                            <th>Coste Total (EUR)</th>
                            <th>Licencia</th>
                            <th>Tipo</th>
                        </tr>
                    </thead>
                    <tbody>
            `;

            operations.forEach(op => {
                const batchBadge = op.batch_type ?
                    `<span class="badge ${op.batch_type.toLowerCase()}">${op.batch_type}</span>` :
                    '';

                html += `
                    <tr>
                        <td class="timestamp">
                            ${formatTime(op.timestamp)}<br>
                            <small>${op.time_ago} ago</small>
                        </td>
                        <td><span class="endpoint">${op.endpoint}</span></td>
                        <td><span class="model">${op.model}</span></td>
                        <td class="tokens">
                            <span class="in">${formatNumber(op.tokens.input)}</span> /
                            <span class="out">${formatNumber(op.tokens.output)}</span>
                        </td>
                        <td class="cost">€${op.cost_eur.input.toFixed(4)}</td>
                        <td class="cost">€${op.cost_eur.output.toFixed(4)}</td>
                        <td class="cost">€${op.cost_eur.total.toFixed(4)}</td>
                        <td class="license">
                            #${op.license.id}<br>
                            <small>${op.license.email}</small>
                        </td>
                        <td>${batchBadge}</td>
                    </tr>
                `;
            });

            html += `
                    </tbody>
                </table>
            `;

            tableContent.innerHTML = html;
        }

        function formatNumber(num) {
            return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        }

        function formatTime(timestamp) {
            const date = new Date(timestamp);
            return date.toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
        }
    </script>
</body>
</html>
