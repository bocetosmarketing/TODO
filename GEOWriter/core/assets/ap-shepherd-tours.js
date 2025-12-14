/**
 * Shepherd.js Tours para GEOWriter
 * Tours guiados para configuración y uso del plugin
 */

(function($) {
    'use strict';

    // Configuración base de Shepherd
    const defaultOptions = {
        useModalOverlay: true,
        exitOnEsc: true,
        keyboardNavigation: true,
        defaultStepOptions: {
            scrollTo: { behavior: 'smooth', block: 'center' },
            cancelIcon: {
                enabled: true
            },
            classes: 'ap-shepherd-theme',
            modalOverlayOpeningPadding: 8,
            modalOverlayOpeningRadius: 8
        }
    };

    // ==========================================
    // TOUR 1: AUTOPILOT - CREAR CAMPAÑA
    // ==========================================
    window.AP_Tours = window.AP_Tours || {};

    AP_Tours.autopilot = function() {
        const tour = new Shepherd.Tour(defaultOptions);

        tour.addStep({
            id: 'welcome',
            title: '🚀 Bienvenido al Autopilot',
            text: 'Este asistente te guiará paso a paso para crear tu primera campaña de contenido automático con IA. ¡Vamos a empezar!',
            buttons: [
                {
                    text: 'Saltar',
                    action: tour.cancel,
                    classes: 'shepherd-button-secondary'
                },
                {
                    text: 'Siguiente',
                    action: tour.next
                }
            ]
        });

        tour.addStep({
            id: 'campaign-name',
            title: '📝 Nombre de la Campaña',
            text: 'Dale un nombre descriptivo a tu campaña. Por ejemplo: "Blog Medicina 2025" o "Marketing Digital Q1".',
            attachTo: {
                element: '#campaign_name',
                on: 'bottom'
            },
            buttons: [
                {
                    text: 'Atrás',
                    action: tour.back,
                    classes: 'shepherd-button-secondary'
                },
                {
                    text: 'Siguiente',
                    action: tour.next
                }
            ]
        });

        tour.addStep({
            id: 'domain',
            title: '🌐 Dominio',
            text: 'Este es el dominio de tu sitio web. Se detecta automáticamente pero puedes modificarlo si es necesario.',
            attachTo: {
                element: '#domain',
                on: 'bottom'
            },
            buttons: [
                {
                    text: 'Atrás',
                    action: tour.back,
                    classes: 'shepherd-button-secondary'
                },
                {
                    text: 'Siguiente',
                    action: tour.next
                }
            ]
        });

        tour.addStep({
            id: 'niche',
            title: '🎯 Nicho de Contenido',
            text: 'Selecciona el nicho o temática principal de tu contenido. Esto ayuda a la IA a generar contenido más relevante y especializado.',
            attachTo: {
                element: '#niche',
                on: 'bottom'
            },
            buttons: [
                {
                    text: 'Atrás',
                    action: tour.back,
                    classes: 'shepherd-button-secondary'
                },
                {
                    text: 'Siguiente',
                    action: tour.next
                }
            ]
        });

        tour.addStep({
            id: 'num-posts',
            title: '📊 Número de Posts',
            text: 'Define cuántos artículos quieres generar en esta campaña. Puedes usar el slider o escribir el número directamente.',
            attachTo: {
                element: '.ap-slider-container',
                on: 'bottom'
            },
            buttons: [
                {
                    text: 'Atrás',
                    action: tour.back,
                    classes: 'shepherd-button-secondary'
                },
                {
                    text: 'Siguiente',
                    action: tour.next
                }
            ]
        });

        tour.addStep({
            id: 'category',
            title: '📁 Categoría',
            text: 'Selecciona la categoría de WordPress donde se publicarán los posts. Si no existe, créala antes desde el menú de WordPress.',
            attachTo: {
                element: '#category',
                on: 'bottom'
            },
            buttons: [
                {
                    text: 'Atrás',
                    action: tour.back,
                    classes: 'shepherd-button-secondary'
                },
                {
                    text: 'Siguiente',
                    action: tour.next
                }
            ]
        });

        tour.addStep({
            id: 'schedule',
            title: '📅 Programación',
            text: 'Define cuándo quieres que se publiquen los artículos: fecha de inicio, hora y días de la semana. Los artículos se programarán automáticamente.',
            attachTo: {
                element: '#start_date',
                on: 'bottom'
            },
            buttons: [
                {
                    text: 'Atrás',
                    action: tour.back,
                    classes: 'shepherd-button-secondary'
                },
                {
                    text: 'Siguiente',
                    action: tour.next
                }
            ]
        });

        tour.addStep({
            id: 'summary',
            title: '📋 Resumen de Configuración',
            text: 'Este resumen muestra todos los parámetros de tu campaña. Asegúrate de que todo esté correcto antes de continuar.',
            attachTo: {
                element: '#summary-card',
                on: 'top'
            },
            buttons: [
                {
                    text: 'Atrás',
                    action: tour.back,
                    classes: 'shepherd-button-secondary'
                },
                {
                    text: 'Siguiente',
                    action: tour.next
                }
            ]
        });

        tour.addStep({
            id: 'preview',
            title: '👁️ Vista Previa',
            text: 'En el panel lateral derecho puedes ver un resumen de tu configuración antes de crear la campaña. Revísala cuidadosamente.',
            attachTo: {
                element: '.ap-sidebar',
                on: 'left'
            },
            buttons: [
                {
                    text: 'Atrás',
                    action: tour.back,
                    classes: 'shepherd-button-secondary'
                },
                {
                    text: 'Siguiente',
                    action: tour.next
                }
            ]
        });

        tour.addStep({
            id: 'start-autopilot',
            title: '✅ Iniciar Autopilot',
            text: '¡Perfecto! Cuando estés listo, haz clic en "Iniciar Autopilot" para que la IA analice tu negocio, genere keywords y cree la configuración completa automáticamente.',
            attachTo: {
                element: '#start-autopilot',
                on: 'top'
            },
            buttons: [
                {
                    text: 'Atrás',
                    action: tour.back,
                    classes: 'shepherd-button-secondary'
                },
                {
                    text: '¡Entendido!',
                    action: tour.complete
                }
            ]
        });

        return tour;
    };

    // ==========================================
    // TOUR 2: COLA DE PROCESAMIENTO
    // ==========================================
    AP_Tours.queue = function() {
        const tour = new Shepherd.Tour(defaultOptions);

        tour.addStep({
            id: 'queue-intro',
            title: '⏱️ Cola de Procesamiento',
            text: 'Aquí se muestran todas las campañas que están siendo procesadas por la IA. Puedes monitorear el progreso en tiempo real.',
            buttons: [
                {
                    text: 'Saltar',
                    action: tour.cancel,
                    classes: 'shepherd-button-secondary'
                },
                {
                    text: 'Siguiente',
                    action: tour.next
                }
            ]
        });

        tour.addStep({
            id: 'queue-status',
            title: '📊 Estados de la Cola',
            text: 'Los posts pueden estar: Pendiente (esperando), Procesando (generándose), Completado (publicado) o Con Error. Los colores te ayudan a identificar rápidamente el estado de cada uno.',
            buttons: [
                {
                    text: 'Atrás',
                    action: tour.back,
                    classes: 'shepherd-button-secondary'
                },
                {
                    text: 'Siguiente',
                    action: tour.next
                }
            ]
        });

        tour.addStep({
            id: 'queue-actions',
            title: '🎮 Acciones Disponibles',
            text: 'Puedes pausar, reanudar o cancelar campañas desde los botones de acción. También puedes ver detalles y errores si los hay.',
            buttons: [
                {
                    text: 'Atrás',
                    action: tour.back,
                    classes: 'shepherd-button-secondary'
                },
                {
                    text: '¡Entendido!',
                    action: tour.complete
                }
            ]
        });

        return tour;
    };

    // ==========================================
    // TOUR 3: VER/EDITAR CAMPAÑAS
    // ==========================================
    AP_Tours.campaigns = function() {
        const tour = new Shepherd.Tour(defaultOptions);

        tour.addStep({
            id: 'campaigns-intro',
            title: '📋 Gestión de Campañas',
            text: 'Aquí puedes ver todas tus campañas creadas, editarlas, duplicarlas o eliminarlas.',
            buttons: [
                {
                    text: 'Saltar',
                    action: tour.cancel,
                    classes: 'shepherd-button-secondary'
                },
                {
                    text: 'Siguiente',
                    action: tour.next
                }
            ]
        });

        tour.addStep({
            id: 'campaigns-list',
            title: '📝 Lista de Campañas',
            text: 'Cada campaña muestra: nombre, nicho, número de posts, progreso y fecha de creación. Puedes editarlas, duplicarlas o eliminarlas desde las acciones.',
            buttons: [
                {
                    text: 'Atrás',
                    action: tour.back,
                    classes: 'shepherd-button-secondary'
                },
                {
                    text: 'Siguiente',
                    action: tour.next
                }
            ]
        });

        tour.addStep({
            id: 'campaigns-actions',
            title: '⚙️ Acciones de Campaña',
            text: 'Desde la toolbar superior puedes crear nuevas campañas o eliminar varias a la vez seleccionándolas con el checkbox.',
            buttons: [
                {
                    text: 'Atrás',
                    action: tour.back,
                    classes: 'shepherd-button-secondary'
                },
                {
                    text: '¡Entendido!',
                    action: tour.complete
                }
            ]
        });

        return tour;
    };

    // ==========================================
    // INICIALIZACIÓN Y BOTONES DE AYUDA
    // ==========================================

    // Guardar estado del tour en localStorage
    function getTourStatus(tourName) {
        return localStorage.getItem(`ap_tour_${tourName}_completed`) === 'true';
    }

    function markTourCompleted(tourName) {
        localStorage.setItem(`ap_tour_${tourName}_completed`, 'true');
    }

    // Detectar módulo actual
    function detectCurrentModule() {
        if ($('#autopilot-form').length) return 'autopilot';
        if ($('#queue-table, .ap-queue-wrapper').length) return 'queue';
        if ($('.ap-campaigns-wrapper').length) return 'campaigns';
        return null;
    }

    // Agregar botones de ayuda
    function addHelpButtons() {
        const currentModule = detectCurrentModule();

        if (!currentModule) return;

        let buttonId, buttonText;

        switch(currentModule) {
            case 'autopilot':
                buttonId = 'start-autopilot-tour';
                buttonText = 'Tutorial Autopilot';
                break;
            case 'queue':
                buttonId = 'start-queue-tour';
                buttonText = 'Tutorial Cola';
                break;
            case 'campaigns':
                buttonId = 'start-campaigns-tour';
                buttonText = 'Tutorial Campañas';
                break;
        }

        const helpBtn = $(`
            <button type="button" class="ap-help-tour-btn" id="${buttonId}" title="Ver tutorial guiado">
                <span class="dashicons dashicons-info"></span>
                ${buttonText}
            </button>
        `);

        $('.ap-module-header').first().append(helpBtn);
    }

    // Inicializar cuando el DOM esté listo
    $(document).ready(function() {
        // Verificar que Shepherd esté cargado
        if (typeof Shepherd === 'undefined') {
            console.warn('Shepherd.js no está cargado');
            return;
        }

        // Agregar botones de ayuda
        addHelpButtons();

        // Event listeners para los botones
        $('#start-autopilot-tour').on('click', function(e) {
            e.preventDefault();
            const tour = AP_Tours.autopilot();
            tour.on('complete', function() {
                markTourCompleted('autopilot');
            });
            tour.start();
        });

        $('#start-queue-tour').on('click', function(e) {
            e.preventDefault();
            const tour = AP_Tours.queue();
            tour.on('complete', function() {
                markTourCompleted('queue');
            });
            tour.start();
        });

        $('#start-campaigns-tour').on('click', function(e) {
            e.preventDefault();
            const tour = AP_Tours.campaigns();
            tour.on('complete', function() {
                markTourCompleted('campaigns');
            });
            tour.start();
        });

        // Auto-iniciar tour de Autopilot si es la primera vez
        const currentModule = detectCurrentModule();
        if (currentModule === 'autopilot' && !getTourStatus('autopilot')) {
            // Esperar 1.5 segundos para que el usuario vea la página primero
            setTimeout(function() {
                const tour = AP_Tours.autopilot();
                tour.on('complete', function() {
                    markTourCompleted('autopilot');
                });
                tour.start();
            }, 1500);
        }
    });

})(jQuery);
