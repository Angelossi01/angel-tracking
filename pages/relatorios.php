```php
<?php
require_once __DIR__ . '/../config/conexao.php';

// ==========================================
// FILTROS DE DATA
// ==========================================

$dataInicial = $_GET['data_inicial'] ?? '';
$dataFinal = $_GET['data_final'] ?? '';

$erroFiltro = '';

if (
    ($dataInicial !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dataInicial)) ||
    ($dataFinal !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dataFinal))
) {
    $erroFiltro = "Informe datas válidas.";
    $dataInicial = '';
    $dataFinal = '';
}

if (
    $dataInicial !== '' &&
    $dataFinal !== '' &&
    $dataInicial > $dataFinal
) {
    $erroFiltro = "A data inicial não pode ser maior que a data final.";
    $dataInicial = '';
    $dataFinal = '';
}

// ==========================================
// CONDIÇÕES DO FILTRO
// ==========================================

$where = [];
$parametros = [];

if ($dataInicial !== '') {
    $where[] = "data_frete >= ?";
    $parametros[] = $dataInicial;
}

if ($dataFinal !== '') {
    $where[] = "data_frete <= ?";
    $parametros[] = $dataFinal;
}

$filtroSQL = '';

if (!empty($where)) {
    $filtroSQL = ' WHERE ' . implode(' AND ', $where);
}

// ==========================================
// CONSULTAR INDICADORES
// ==========================================

$sqlIndicadores = "
    SELECT
        COUNT(*) AS total_fretes,

        SUM(CASE
            WHEN status = 'Pendente' THEN 1
            ELSE 0
        END) AS pendentes,

        SUM(CASE
            WHEN status = 'Em andamento' THEN 1
            ELSE 0
        END) AS andamento,

        SUM(CASE
            WHEN status = 'Concluído' THEN 1
            ELSE 0
        END) AS concluidos,

        COALESCE(SUM(valor), 0) AS valor_total

    FROM fretes
    $filtroSQL
";

$stmt = $pdo->prepare($sqlIndicadores);
$stmt->execute($parametros);

$indicadores = $stmt->fetch(PDO::FETCH_ASSOC);

// ==========================================
// CONSULTAR FRETES DETALHADOS
// ==========================================

$sqlRelatorios = "
    SELECT
        f.*,
        origem.nome AS origem,
        destino.nome AS destino,
        t.nome AS transportador,
        COUNT(fp.id) AS total_produtos

    FROM fretes f

    INNER JOIN enderecos origem
        ON f.origem_id = origem.id

    INNER JOIN enderecos destino
        ON f.destino_id = destino.id

    INNER JOIN transportadores t
        ON f.transportador_id = t.id

    LEFT JOIN frete_produtos fp
        ON f.id = fp.frete_id

    $filtroSQL

    GROUP BY f.id

    ORDER BY f.data_frete DESC, f.id DESC
";

$stmt = $pdo->prepare($sqlRelatorios);
$stmt->execute($parametros);

$relatorios = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!-- ======================================
     CABEÇALHO
======================================= -->

<div class="page-header">

    <div>
        <h2>Relatórios</h2>
        <p>Acompanhe os resultados e o desempenho dos fretes.</p>
    </div>

    <button
        class="btn btn-dark"
        onclick="window.print()"
    >
        <i class="bi bi-printer"></i>
        Imprimir relatório
    </button>

</div>


<!-- ======================================
     MENSAGEM DE ERRO
======================================= -->

<?php if ($erroFiltro): ?>

    <div class="alert alert-danger">
        <?php echo htmlspecialchars($erroFiltro); ?>
    </div>

<?php endif; ?>


<!-- ======================================
     FILTRO DE DATAS
======================================= -->

<div class="dashboard-card mb-4">

    <h5 class="mb-3">
        <i class="bi bi-calendar-range"></i>
        Filtrar por período
    </h5>

    <form method="GET" action="index.php">

        <input type="hidden" name="page" value="relatorios">

        <div class="row align-items-end">

            <div class="col-md-4 mb-3">

                <label class="form-label">
                    Data inicial
                </label>

                <input
                    type="date"
                    name="data_inicial"
                    class="form-control"
                    value="<?php echo htmlspecialchars($dataInicial); ?>"
                >

            </div>

            <div class="col-md-4 mb-3">

                <label class="form-label">
                    Data final
                </label>

                <input
                    type="date"
                    name="data_final"
                    class="form-control"
                    value="<?php echo htmlspecialchars($dataFinal); ?>"
                >

            </div>

            <div class="col-md-4 mb-3">

                <button type="submit" class="btn btn-dark">
                    <i class="bi bi-search"></i>
                    Filtrar
                </button>

                <a
                    href="index.php?page=relatorios"
                    class="btn btn-outline-secondary"
                >
                    Limpar
                </a>

            </div>

        </div>

    </form>

</div>


<!-- ======================================
     CARTÕES DE INDICADORES
======================================= -->

<div class="row g-3 mb-4">


    <!-- TOTAL DE FRETES -->

    <div class="col-md-4">

        <div class="dashboard-card h-100">

            <h6>
                <i class="bi bi-box-seam"></i>
                Total de fretes
            </h6>

            <h3>
                <?php echo $indicadores['total_fretes']; ?>
            </h3>

        </div>

    </div>


    <!-- PENDENTES -->

    <div class="col-md-4">

        <div class="dashboard-card h-100">

            <h6>
                <i class="bi bi-clock-history"></i>
                Fretes pendentes
            </h6>

            <h3>
                <?php echo $indicadores['pendentes'] ?? 0; ?>
            </h3>

        </div>

    </div>


    <!-- EM ANDAMENTO -->

    <div class="col-md-4">

        <div class="dashboard-card h-100">

            <h6>
                <i class="bi bi-truck"></i>
                Em andamento
            </h6>

            <h3>
                <?php echo $indicadores['andamento'] ?? 0; ?>
            </h3>

        </div>

    </div>


    <!-- CONCLUÍDOS -->

    <div class="col-md-4">

        <div class="dashboard-card h-100">

            <h6>
                <i class="bi bi-check-circle"></i>
                Fretes concluídos
            </h6>

            <h3>
                <?php echo $indicadores['concluidos'] ?? 0; ?>
            </h3>

        </div>

    </div>


    <!-- VALOR TOTAL -->

    <div class="col-md-8">

        <div class="dashboard-card h-100">

            <h6>
                <i class="bi bi-currency-dollar"></i>
                Valor total dos fretes
            </h6>

            <h3>
                R$
                <?php
                echo number_format(
                    $indicadores['valor_total'],
                    2,
                    ',',
                    '.'
                );
                ?>
            </h3>

        </div>

    </div>

</div>


<!-- ======================================
     TABELA DE RELATÓRIOS
======================================= -->

<div class="dashboard-card">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <h5 class="mb-0">
            Detalhamento dos fretes
        </h5>

        <span class="text-muted">
            <?php echo count($relatorios); ?> registro(s)
        </span>

    </div>

    <div class="table-responsive">

        <table class="table table-hover align-middle">

            <thead>

                <tr>
                    <th>ID</th>
                    <th>Origem</th>
                    <th>Destino</th>
                    <th>Transportador</th>
                    <th>Produtos</th>
                    <th>Data</th>
                    <th>Valor</th>
                    <th>Status</th>
                </tr>

            </thead>

            <tbody>

                <?php if (count($relatorios) > 0): ?>

                    <?php foreach ($relatorios as $frete): ?>

                        <tr>

                            <td>
                                #<?php echo $frete['id']; ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($frete['origem']); ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($frete['destino']); ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($frete['transportador']); ?>
                            </td>

                            <td>
                                <?php echo $frete['total_produtos']; ?> produto(s)
                            </td>

                            <td>
                                <?php
                                echo date(
                                    'd/m/Y',
                                    strtotime($frete['data_frete'])
                                );
                                ?>
                            </td>

                            <td>
                                R$
                                <?php
                                echo number_format(
                                    $frete['valor'],
                                    2,
                                    ',',
                                    '.'
                                );
                                ?>
                            </td>

                            <td>

                                <?php

                                $classe = 'secondary';

                                if ($frete['status'] === 'Pendente') {
                                    $classe = 'warning';
                                } elseif ($frete['status'] === 'Em andamento') {
                                    $classe = 'primary';
                                } elseif ($frete['status'] === 'Concluído') {
                                    $classe = 'success';
                                }

                                ?>

                                <span class="badge bg-<?php echo $classe; ?>">
                                    <?php echo htmlspecialchars($frete['status']); ?>
                                </span>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                <?php else: ?>

                    <tr>

                        <td colspan="8" class="text-center py-4">

                            Nenhum frete encontrado para o período selecionado.

                        </td>

                    </tr>

                <?php endif; ?>

            </tbody>

        </table>

    </div>

</div>


<!-- ======================================
     ESTILO DE IMPRESSÃO
======================================= -->

<style>

@media print {

    .sidebar,
    .page-header button,
    form,
    .btn {
        display: none !important;
    }

    .main-content {
        margin-left: 0 !important;
        padding: 10px !important;
    }

    body {
        background: white !important;
    }

    .dashboard-card {
        box-shadow: none !important;
        border: 1px solid #ddd !important;
        break-inside: avoid;
    }

}

</style>
```