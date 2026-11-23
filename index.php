```php
<?php

require_once "includes/header.php";

require_once "includes/sidebar.php";

?>

<main class="main-content">

    <?php

    $pagina = $_GET['page'] ?? 'dashboard';

    $paginasPermitidas = [
        'dashboard',
        'produtos',
        'enderecos',
        'transportadores',
        'fretes',
        'relatorios'
    ];

    if (in_array($pagina, $paginasPermitidas)) {

        require "pages/" . $pagina . ".php";

    } else {

        echo "<h2>Página não encontrada.</h2>";

    }

    ?>

</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
```