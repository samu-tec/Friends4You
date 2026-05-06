// JavaScript de Friends4You
//  - Menu de navegacion en movil.
//  - Confirmacion antes de enviar formularios marcados con data-confirm.
//  - Ocultar los mensajes flash pasados unos segundos.
//  - Filtrado de eventos por interes con AJAX (fetch).

document.addEventListener('DOMContentLoaded', function () {

    // ----- Menu movil -----
    var botonMenu = document.querySelector('[data-menu-toggle]');
    var menu = document.querySelector('[data-main-nav]');

    if (botonMenu && menu) {
        botonMenu.addEventListener('click', function () {
            menu.classList.toggle('is-open');
        });
    }

    // ----- Confirmacion de formularios -----
    var formulariosConfirm = document.querySelectorAll('[data-confirm]');
    formulariosConfirm.forEach(function (form) {
        form.addEventListener('submit', function (evento) {
            if (!confirm(form.getAttribute('data-confirm'))) {
                evento.preventDefault();
            }
        });
    });

    // ----- Ocultar mensajes flash -----
    var alertas = document.querySelectorAll('.alert:not([data-static])');
    alertas.forEach(function (alerta) {
        setTimeout(function () {
            alerta.classList.add('alert--fade');
        }, 6000);
    });

    // ----- Filtro de eventos con AJAX -----
    var filtroInteres = document.getElementById('filtro-interes');
    var listaEventos = document.getElementById('js-event-list');

    if (filtroInteres && listaEventos) {
        var meta = document.querySelector('meta[name="base-url"]');
        var baseUrl = meta ? meta.content : '';

        filtroInteres.addEventListener('change', function () {
            var id = encodeURIComponent(filtroInteres.value);
            var url = baseUrl + 'index.php?page=api&action=events&id_interes=' + id;

            listaEventos.innerHTML = '<p class="muted">Cargando...</p>';

            fetch(url)
                .then(function (respuesta) { return respuesta.json(); })
                .then(function (eventos) {
                    if (!eventos.length) {
                        listaEventos.innerHTML = '<p class="muted">No hay eventos activos con ese filtro.</p>';
                        return;
                    }

                    var html = '<ul class="event-list">';
                    eventos.forEach(function (ev) {
                        var enlace = baseUrl + 'index.php?page=events&id_evento=' + encodeURIComponent(ev.id_evento);
                        html += '<li>' +
                            '<a href="' + enlace + '"><strong>' + escapar(ev.nombre) + '</strong></a>' +
                            '<span>' + escapar(formatearFecha(ev.fecha_hora)) + ' - ' + escapar(ev.interes) + '</span>' +
                            '<span class="muted">' + escapar(ev.punto_encuentro) + ' - ' + escapar(ev.asistentes) + ' asistentes</span>' +
                            '</li>';
                    });
                    html += '</ul>';
                    listaEventos.innerHTML = html;
                })
                .catch(function () {
                    listaEventos.innerHTML = '<p class="muted">Error al cargar los eventos. Recarga la pagina.</p>';
                });
        });
    }

    // Escapa texto para meterlo en HTML sin riesgo.
    function escapar(texto) {
        var div = document.createElement('div');
        div.textContent = texto == null ? '' : String(texto);
        return div.innerHTML;
    }

    // Pasa una fecha "2026-09-12 18:00:00" a "12/09/2026 18:00".
    function formatearFecha(fecha) {
        if (!fecha) { return ''; }
        var d = new Date(fecha.replace(' ', 'T'));
        if (isNaN(d)) { return fecha; }
        function dos(n) { return (n < 10 ? '0' : '') + n; }
        return dos(d.getDate()) + '/' + dos(d.getMonth() + 1) + '/' + d.getFullYear() +
               ' ' + dos(d.getHours()) + ':' + dos(d.getMinutes());
    }

});
