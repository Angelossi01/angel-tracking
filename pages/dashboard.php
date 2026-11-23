<?php

require_once __DIR__ . "/../config/conexao.php";

// Contar produtos cadastrados
$sqlProdutos = $pdo->query("SELECT COUNT(*) FROM produtos");
$totalProdutos = $sqlProdutos->fetchColumn();

// Contar transportadores cadastrados
$sqlTransportadores = $pdo->query("SELECT COUNT(*) FROM transportadores");
$totalTransportadores = $sqlTransportadores->fetchColumn();

// Contar fretes cadastrados
$sqlFretes = $pdo->query("SELECT COUNT(*) FROM fretes");
$totalFretes = $sqlFretes->fetchColumn();

// Contar fretes pendentes
$sqlPendentes = $pdo->query(
    "SELECT COUNT(*) FROM fretes WHERE status = 'Pendente'"
);
$totalPendentes = $sqlPendentes->fetchColumn();

?>

<div class="page-header">
    <div>
        <h2>Dashboard</h2>
        <p>Bem-vindo ao Angel Tracking!</p>
    </div>

    <div>
        <span><?= date('d/m/Y') ?></span>
    </div>
</div>

<div class="row g-4">

    <div class="col-md-6 col-xl-3">
        <div class="dashboard-card">
            <h6>Total de produtos</h6>
            <h3><?= $totalProdutos ?></h3>
            <small>Produtos cadastrados</small>
        </div>
    </div>

    <div class="col-md-6 col-xl-3">
        <div class="dashboard-card">
            <h6>Transportadores</h6>
            <h3><?= $totalTransportadores ?></h3>
            <small>Transportadores cadastrados</small>
        </div>
    </div>

    <div class="col-md-6 col-xl-3">
        <div class="dashboard-card">
            <h6>Fretes realizados</h6>
            <h3><?= $totalFretes ?></h3>
            <small>Total de fretes registrados</small>
        </div>
    </div>

    <div class="col-md-6 col-xl-3">
        <div class="dashboard-card">
            <h6>Fretes pendentes</h6>
            <h3><?= $totalPendentes ?></h3>
            <small>Aguardando conclusão</small>
        </div>
    </div>

</div>