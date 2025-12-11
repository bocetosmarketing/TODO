jQuery(document).ready(function($) {
    let currentPeriod = 'current';
    let billingPeriod = null;
    let timelineChart = null;

    // Cargar datos iniciales
    loadStats();

    // Evento: cambiar período
    $('#stats-period').on('change', function() {
        currentPeriod = $(this).val();
        loadStats();
    });

    // Evento: refresh manual
    $('#refresh-stats').on('click', function() {
        loadStats();
    });

    function loadStats() {
        // Mostrar loading
        $('#plan-info').html('<div class="phsbot-loading">Cargando...</div>');
        $('#operations-table').html('<div class="phsbot-loading">Cargando...</div>');

        $.ajax({
            url: phsbotStats.ajax_url,
            method: 'POST',
            data: {
                action: 'phsbot_get_stats',
                nonce: phsbotStats.nonce,
                period: currentPeriod
            },
            success: function(response) {
                if (response.success) {
                    billingPeriod = response.data.billing_period;
                    renderPlanInfo(response.data.summary, response.data.plan);
                    renderLiquidAnimation(response.data.summary);
                    renderTimelineChart(response.data.daily_timeline);
                    renderOperationsTable(response.data.by_operation);
                } else {
                    $('#plan-info').html('<div class="phsbot-empty-state"><div class="phsbot-empty-state-text">Error: ' + (response.data.message || 'No se pudieron cargar las estadísticas') + '</div></div>');
                    $('#operations-table').html('<div class="phsbot-empty-state"><div class="phsbot-empty-state-text">No hay datos disponibles</div></div>');
                }
            },
            error: function() {
                $('#plan-info').html('<div class="phsbot-empty-state"><div class="phsbot-empty-state-text">Error al cargar las estadísticas</div></div>');
                $('#operations-table').html('<div class="phsbot-empty-state"><div class="phsbot-empty-state-text">Error al cargar las operaciones</div></div>');
            }
        });
    }

    function renderPlanInfo(summary, plan) {
        if (!plan) {
            $('#plan-info').html('<div class="phsbot-empty-state-text">No se pudo cargar información del plan</div>');
            return;
        }

        const renewalText = plan.renewal_date || 'No disponible';

        // Convertir tokens a créditos (1 crédito = 10,000 tokens)
        const credits_limit = tokensToCredits(summary.tokens_limit);

        let html = `
            <div class="phsbot-plan-row">
                <span class="phsbot-plan-label">Plan</span>
                <span class="phsbot-plan-value phsbot-plan-name">${escapeHtml(plan.name)}</span>
            </div>
            <div class="phsbot-plan-row">
                <span class="phsbot-plan-label">Límite mensual</span>
                <span class="phsbot-plan-value phsbot-number">${formatNumber(credits_limit)} créditos</span>
            </div>
            <div class="phsbot-plan-row">
                <span class="phsbot-plan-label">Renovación</span>
                <span class="phsbot-plan-value">${renewalText}</span>
            </div>
        `;

        if (plan.days_remaining !== undefined) {
            html += `<div class="phsbot-plan-row">
                <span class="phsbot-plan-label">Días restantes</span>
                <span class="phsbot-plan-value">${plan.days_remaining}</span>
            </div>`;
        }

        $('#plan-info').html(html);
    }

    function renderLiquidAnimation(summary) {
        const available = summary.tokens_available;
        const total = summary.tokens_limit;
        const percentage = summary.usage_percentage || 0;

        // Porcentaje de créditos DISPONIBLES (lo que queda)
        const availablePercentage = 100 - percentage;

        // Convertir tokens a créditos (1 crédito = 10,000 tokens) con decimales
        const creditsAvailable = tokensToCredits(available);

        // Mostrar número en AMBAS capas
        const formattedNumber = formatNumber(creditsAvailable);
        $('#tokens-number-blue').text(formattedNumber);
        $('#tokens-number-white').text(formattedNumber);

        // Animar líquido
        animateLiquid(availablePercentage);
    }

    function animateLiquid(targetPercentage) {
        const wave = document.getElementById('liquid-wave');
        if (!wave) return;

        // Empezar lleno (100%) y bajar hasta el targetPercentage
        let currentLevel = 100;
        const targetLevel = targetPercentage;

        // Calcular duración de animación según el volumen a vaciar
        const volumeToEmpty = 100 - targetPercentage;
        const duration = Math.max(2000, Math.min(5000, volumeToEmpty * 50)); // Entre 2 y 5 segundos
        const frameDuration = 1000 / 60; // 60 FPS
        const totalFrames = duration / frameDuration;
        const decrementPerFrame = volumeToEmpty / totalFrames;

        let animating = true;

        // Sistema de puntos de onda: cada punto oscila independientemente
        const numPoints = 50; // 50 puntos
        const wavePoints = [];

        // Inicializar cada punto con su estado de oscilación
        for (let i = 0; i < numPoints; i++) {
            wavePoints.push({
                height: 0, // Altura actual de la onda en este punto
                targetHeight: (Math.random() - 0.5) * 7.2, // Altura objetivo ±3.6px
                velocity: 0, // Velocidad actual de cambio
                changeInterval: Math.floor(Math.random() * 40) + 20, // Frames hasta cambiar objetivo
                frameCount: 0
            });
        }

        function animate() {
            // Bajar el nivel si estamos animando
            if (animating && currentLevel > targetLevel) {
                currentLevel -= decrementPerFrame;
                if (currentLevel <= targetLevel) {
                    currentLevel = targetLevel;
                    animating = false;
                }
            }

            // Calcular Y del nivel del líquido (0 = arriba, 100 = abajo)
            const liquidY = 100 - currentLevel;

            // Actualizar cada punto de onda
            wavePoints.forEach((point, index) => {
                point.frameCount++;

                // Cambiar objetivo aleatoriamente
                if (point.frameCount >= point.changeInterval) {
                    point.frameCount = 0;
                    point.changeInterval = Math.floor(Math.random() * 40) + 20;
                    point.targetHeight = (Math.random() - 0.5) * 7.2; // ±3.6 píxeles
                }

                // Física simple: mover hacia el objetivo con amortiguación
                const diff = point.targetHeight - point.height;
                point.velocity += diff * 0.02; // Aceleración hacia objetivo
                point.velocity *= 0.85; // Amortiguación (fricción)
                point.height += point.velocity;

                // Influencia de puntos vecinos (para crear continuidad)
                if (index > 0 && index < numPoints - 1) {
                    const avgNeighbor = (wavePoints[index - 1].height + wavePoints[index + 1].height) / 2;
                    point.height = point.height * 0.7 + avgNeighbor * 0.3;
                }
            });

            // Crear el path SVG con curvas cúbicas suaves (spline)
            let pathData = `M 0,100 L 0,${liquidY + wavePoints[0].height}`;

            // Usar curvas Bézier cúbicas para máxima suavidad
            for (let i = 0; i < numPoints - 1; i++) {
                const x0 = i > 0 ? ((i - 1) / (numPoints - 1)) * 100 : 0;
                const y0 = i > 0 ? liquidY + wavePoints[i - 1].height : liquidY + wavePoints[0].height;

                const x1 = (i / (numPoints - 1)) * 100;
                const y1 = liquidY + wavePoints[i].height;

                const x2 = ((i + 1) / (numPoints - 1)) * 100;
                const y2 = liquidY + wavePoints[i + 1].height;

                const x3 = i < numPoints - 2 ? ((i + 2) / (numPoints - 1)) * 100 : 100;
                const y3 = i < numPoints - 2 ? liquidY + wavePoints[i + 2].height : liquidY + wavePoints[numPoints - 1].height;

                // Calcular puntos de control para curva cúbica suave
                // Usando aproximación Catmull-Rom
                const tension = 0.3; // Menor = más suave

                const cp1x = x1 + (x2 - x0) * tension;
                const cp1y = y1 + (y2 - y0) * tension;

                const cp2x = x2 - (x3 - x1) * tension;
                const cp2y = y2 - (y3 - y1) * tension;

                // Curva cúbica Bézier: C cp1x,cp1y cp2x,cp2y x2,y2
                pathData += ` C ${cp1x},${cp1y} ${cp2x},${cp2y} ${x2},${y2}`;
            }

            // Cerrar el path
            pathData += ` L 100,100 Z`;

            wave.setAttribute('d', pathData);

            // Aplicar clip-path al texto blanco para que solo se vea donde hay líquido
            const whiteText = document.getElementById('tokens-available-white');
            if (whiteText) {
                const liquidPercentage = currentLevel;
                whiteText.style.clipPath = `inset(0 0 ${liquidPercentage}% 0)`;
            }

            requestAnimationFrame(animate);
        }

        animate();
    }

    function renderTimelineChart(timeline) {
        if (!timeline || timeline.length === 0) {
            return;
        }

        const ctx = document.getElementById('timeline-chart');
        if (!ctx) return;

        // Destruir gráfico anterior si existe
        if (timelineChart) {
            timelineChart.destroy();
        }

        const labels = timeline.map(d => d.date_formatted);
        // Convertir tokens a créditos (1 crédito = 10,000 tokens) con decimales
        const creditsData = timeline.map(d => tokensToCredits(d.tokens));
        const messagesData = timeline.map(d => d.messages);

        timelineChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Mensajes',
                        data: messagesData,
                        borderColor: '#059669',
                        backgroundColor: 'rgba(5, 150, 105, 0.1)',
                        borderWidth: 2,
                        fill: false,
                        tension: 0.4,
                        yAxisID: 'y-messages'
                    },
                    {
                        label: 'Créditos utilizados',
                        data: creditsData,
                        borderColor: '#000000',
                        backgroundColor: 'rgba(0, 0, 0, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4,
                        yAxisID: 'y-credits'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                        labels: {
                            usePointStyle: true,
                            padding: 15
                        }
                    }
                },
                scales: {
                    'y-messages': {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        title: {
                            display: true,
                            text: 'Mensajes'
                        },
                        beginAtZero: true
                    },
                    'y-credits': {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        title: {
                            display: true,
                            text: 'Créditos'
                        },
                        beginAtZero: true,
                        grid: {
                            drawOnChartArea: false
                        },
                        ticks: {
                            callback: function(value) {
                                return formatNumber(value);
                            }
                        }
                    }
                }
            }
        });
    }

    function renderOperationsTable(operations) {
        if (!operations || operations.length === 0) {
            $('#operations-table').html('<div class="phsbot-empty-state"><div class="phsbot-empty-state-text">No hay operaciones en este período</div></div>');
            return;
        }

        let html = '<table class="phsbot-operations-table"><thead><tr>';
        html += '<th style="width:40px;"></th>'; // Columna para el icono
        html += '<th>Tipo de Operación</th>';
        html += '<th class="phsbot-text-right">Operaciones</th>';
        html += '<th class="phsbot-text-right">Créditos</th>';
        html += '</tr></thead><tbody>';

        operations.forEach((op, index) => {
            // Convertir tokens a créditos con decimales
            const opCredits = tokensToCredits(op.tokens);

            html += '<tr>';
            html += '<td class="phsbot-toggle-cell"></td>'; // No hay toggle para operaciones simples
            html += '<td>';
            html += '<div class="phsbot-op-campaign">';
            html += '<span class="phsbot-campaign-pill">' + getOperationTypeName(op.type) + '</span>';
            html += '</div>';
            html += '</td>';
            html += '<td class="phsbot-text-right phsbot-number">' + formatNumber(op.count) + '</td>';
            html += '<td class="phsbot-text-right phsbot-number">' + formatNumber(opCredits) + '</td>';
            html += '</tr>';
        });

        html += '</tbody></table>';
        $('#operations-table').html(html);
    }

    function getOperationTypeName(type) {
        const names = {
            'chat': '💬 Chat de Usuario',
            'translate': '🌐 Traducción de Bienvenida',
            'kb': '📚 Generación de Base de Conocimiento',
            'list_models': '📋 Listado de Modelos'
        };

        return names[type] || type;
    }

    // Convertir tokens a créditos con decimales
    function tokensToCredits(tokens) {
        const credits = tokens / 10000;
        // Redondear a 1 decimal
        return Math.round(credits * 10) / 10;
    }

    function formatNumber(num) {
        if (num === null || num === undefined) return '0';
        return new Intl.NumberFormat('es-ES', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 1
        }).format(num);
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
});
