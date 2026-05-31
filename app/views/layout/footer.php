<?php /**
 * Pie común a todas las vistas.
 *
 * Cierra las etiquetas <main>, <body> y <html>, pinta el footer textual
 * y carga el archivo JavaScript de la aplicación.
 */ ?>
</main>

<footer class="site-footer">
    <p><?= escapar(APP_NAME) ?> &mdash; Proyecto DAW &middot; PHP &middot; MySQL &middot; Apache</p>
</footer>

<script src="<?= escapar(BASE_URL) ?>assets/js/app.js?v=<?= filemtime(__DIR__ . '/../../../public/assets/js/app.js') ?>"></script>
</body>
</html>
