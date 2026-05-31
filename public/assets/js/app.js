/**
 * JavaScript de Friends4You.
 *
 * Pequeñas interacciones del cliente:
 *   - Abrir y cerrar el menú de navegación en pantallas pequeñas.
 *   - Pedir confirmación antes de enviar formularios con data-confirm.
 *   - Ocultar gradualmente los avisos pasados unos segundos.
 *   - Filtrar la lista de eventos por interés con AJAX (fetch).
 */

document.addEventListener('DOMContentLoaded', function () {

    // ----- Menú móvil -----
    var boton_menu = document.querySelector('[data-menu-toggle]');
    var menu = document.querySelector('[data-main-nav]');

    if (boton_menu && menu) {
        boton_menu.addEventListener('click', function () {
            menu.classList.toggle('is-open');
        });
    }

    // ----- Confirmación de formularios -----
    var formularios_confirmacion = document.querySelectorAll('[data-confirm]');
    formularios_confirmacion.forEach(function (formulario) {
        formulario.addEventListener('submit', function (evento) {
            if (!confirm(formulario.getAttribute('data-confirm'))) {
                evento.preventDefault();
            }
        });
    });

    // ----- Ocultar avisos pasados 6 segundos -----
    var avisos = document.querySelectorAll('.alert');
    avisos.forEach(function (aviso) {
        setTimeout(function () {
            aviso.classList.add('alert--fade');
        }, 6000);
    });

    // ----- Filtro de eventos con AJAX -----
    var filtro_interes = document.getElementById('filtro-interes');
    var lista_eventos = document.getElementById('js-lista-eventos');

    if (filtro_interes && lista_eventos) {
        var meta = document.querySelector('meta[name="base-url"]');
        var url_base = meta ? meta.content : '';

        filtro_interes.addEventListener('change', function () {
            var id = encodeURIComponent(filtro_interes.value);
            var enlace = url_base + 'index.php?page=api&accion=eventos&id_interes=' + id;

            lista_eventos.innerHTML = '<p class="muted">Cargando...</p>';

            fetch(enlace)
                .then(function (respuesta) { return respuesta.json(); })
                .then(function (eventos) {
                    if (!eventos.length) {
                        lista_eventos.innerHTML = '<p class="muted">No hay eventos activos con ese filtro.</p>';
                        return;
                    }

                    // El HTML generado debe ser igual que el de events.php
                    // para que los estilos (etiqueta de interés, separadores)
                    // se apliquen igual al filtrar con AJAX.
                    var html = '<ul class="event-list">';
                    eventos.forEach(function (evento) {
                        var enlace_evento = url_base + 'index.php?page=events&id_evento=' + encodeURIComponent(evento.id_evento);
                        html += '<li>' +
                            '<a href="' + enlace_evento + '"><strong>' + escapar_html(evento.nombre) + '</strong></a>' +
                            '<span>' + escapar_html(formatear_fecha(evento.fecha_hora)) + ' · <span class="tag tag-flush">' + escapar_html(evento.interes) + '</span></span>' +
                            '<span class="muted">' + escapar_html(evento.punto_encuentro) + ' · <strong>' + escapar_html(evento.asistentes) + '</strong> asistentes</span>' +
                            '</li>';
                    });
                    html += '</ul>';
                    lista_eventos.innerHTML = html;
                })
                .catch(function () {
                    lista_eventos.innerHTML = '<p class="muted">Error al cargar los eventos. Recarga la página.</p>';
                });
        });
    }

    /**
     * Escapa un texto para meterlo en HTML sin riesgo de XSS.
     *
     * Usa el navegador como sandbox: crea un div, le asigna el texto como
     * textContent y devuelve su innerHTML, que ya está escapado.
     *
     * @param {string|number|null} texto Texto que se quiere meter en HTML.
     * @returns {string} El mismo texto con los caracteres especiales escapados.
     */
    function escapar_html(texto) {
        var div = document.createElement('div');
        div.textContent = texto == null ? '' : String(texto);
        return div.innerHTML;
    }

    /**
     * Da formato dd/mm/aaaa hh:mm a una fecha "2026-09-12 18:00:00".
     *
     * Si la cadena no se puede convertir a Date, devuelve la original tal cual
     * para no romper la interfaz.
     *
     * @param {string} valor Fecha en formato MySQL.
     * @returns {string} Fecha con formato español o cadena vacía si no hay fecha.
     */
    function formatear_fecha(valor) {
        if (!valor) { return ''; }
        var fecha = new Date(valor.replace(' ', 'T'));
        if (isNaN(fecha)) { return valor; }
        function dos_digitos(numero) { return (numero < 10 ? '0' : '') + numero; }
        return dos_digitos(fecha.getDate()) + '/' + dos_digitos(fecha.getMonth() + 1) + '/' + fecha.getFullYear() +
            ' ' + dos_digitos(fecha.getHours()) + ':' + dos_digitos(fecha.getMinutes());
    }

});
