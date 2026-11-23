```php
<?php
require_once __DIR__ . '/../config/conexao.php';

$mensagem = "";
$tipoMensagem = "";

// ==========================================
// PROCESSAR AÇÕES
// ==========================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $acao = $_POST['acao'] ?? '';

    // ======================================
    // CADASTRAR OU EDITAR FRETE
    // ======================================

    if ($acao === 'salvar') {

        $freteId = $_POST['frete_id'] ?? '';
        $origem = $_POST['origem_id'] ?? '';
        $destino = $_POST['destino_id'] ?? '';
        $transportador = $_POST['transportador_id'] ?? '';
        $dataFrete = $_POST['data_frete'] ?? '';
        $valor = $_POST['valor'] ?? '';
        $status = $_POST['status'] ?? '';
        $produtosSelecionados = $_POST['produtos'] ?? [];
        $quantidades = $_POST['quantidades'] ?? [];

        $statusPermitidos = [
            'Pendente',
            'Em andamento',
            'Concluído'
        ];

        if (
            empty($origem) ||
            empty($destino) ||
            empty($transportador) ||
            empty($dataFrete) ||
            $valor === '' ||
            empty($produtosSelecionados)
        ) {

            $mensagem = "Preencha todos os campos obrigatórios e selecione pelo menos um produto.";
            $tipoMensagem = "danger";

        } elseif ($origem == $destino) {

            $mensagem = "A origem e o destino não podem ser iguais.";
            $tipoMensagem = "danger";

        } elseif (!is_numeric($valor) || $valor < 0) {

            $mensagem = "Informe um valor válido para o frete.";
            $tipoMensagem = "danger";

        } elseif (!in_array($status, $statusPermitidos, true)) {

            $mensagem = "Status inválido.";
            $tipoMensagem = "danger";

        } else {

            try {

                $pdo->beginTransaction();

                // Verificar quantidades e cadastrar produtos relacionados
                $produtosValidados = [];

                foreach ($produtosSelecionados as $produtoId) {

                    $quantidade = $quantidades[$produtoId] ?? 0;

                    if (
                        !filter_var($produtoId, FILTER_VALIDATE_INT) ||
                        $produtoId <= 0 ||
                        !filter_var($quantidade, FILTER_VALIDATE_INT) ||
                        $quantidade < 1
                    ) {
                        throw new Exception("Verifique os produtos e suas quantidades.");
                    }

                    $produtosValidados[] = [
                        'id' => $produtoId,
                        'quantidade' => $quantidade
                    ];
                }

                if (!empty($freteId)) {

                    // Atualizar frete existente
                    $sql = "UPDATE fretes SET
                            origem_id = ?,
                            destino_id = ?,
                            transportador_id = ?,
                            data_frete = ?,
                            valor = ?,
                            status = ?
                            WHERE id = ?";

                    $stmt = $pdo->prepare($sql);

                    $stmt->execute([
                        $origem,
                        $destino,
                        $transportador,
                        $dataFrete,
                        $valor,
                        $status,
                        $freteId
                    ]);

                    // Remover produtos antigos do frete
                    $stmt = $pdo->prepare(
                        "DELETE FROM frete_produtos WHERE frete_id = ?"
                    );

                    $stmt->execute([$freteId]);

                    $idFreteSalvo = $freteId;

                } else {

                    // Cadastrar novo frete
                    $sql = "INSERT INTO fretes
                            (origem_id, destino_id, transportador_id, data_frete, valor, status)
                            VALUES (?, ?, ?, ?, ?, ?)";

                    $stmt = $pdo->prepare($sql);

                    $stmt->execute([
                        $origem,
                        $destino,
                        $transportador,
                        $dataFrete,
                        $valor,
                        $status
                    ]);

                    $idFreteSalvo = $pdo->lastInsertId();
                }

                // Inserir os produtos selecionados
                $sqlProduto = "INSERT INTO frete_produtos
                               (frete_id, produto_id, quantidade)
                               VALUES (?, ?, ?)";

                $stmtProduto = $pdo->prepare($sqlProduto);

                foreach ($produtosValidados as $produto) {

                    $stmtProduto->execute([
                        $idFreteSalvo,
                        $produto['id'],
                        $produto['quantidade']
                    ]);
                }

                $pdo->commit();

                $destinoPagina = !empty($freteId) ? 'editado' : 'sucesso';

                header("Location: index.php?page=fretes&resultado=" . $destinoPagina);
                exit;

            } catch (Exception $erro) {

                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }

                $mensagem = "Erro ao salvar frete: " . $erro->getMessage();
                $tipoMensagem = "danger";
            }
        }
    }

    // ======================================
    // EXCLUIR FRETE
    // ======================================

    if ($acao === 'excluir') {

        $freteId = $_POST['frete_id'] ?? '';

        if (!filter_var($freteId, FILTER_VALIDATE_INT) || $freteId <= 0) {

            $mensagem = "Frete inválido.";
            $tipoMensagem = "danger";

        } else {

            try {

                $pdo->beginTransaction();

                // Excluir produtos relacionados
                $stmt = $pdo->prepare(
                    "DELETE FROM frete_produtos WHERE frete_id = ?"
                );

                $stmt->execute([$freteId]);

                // Excluir o frete
                $stmt = $pdo->prepare(
                    "DELETE FROM fretes WHERE id = ?"
                );

                $stmt->execute([$freteId]);

                $pdo->commit();

                header("Location: index.php?page=fretes&resultado=excluido");
                exit;

            } catch (Exception $erro) {

                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }

                $mensagem = "Erro ao excluir frete: " . $erro->getMessage();
                $tipoMensagem = "danger";
            }
        }
    }
}


// ==========================================
// MENSAGENS DE RETORNO
// ==========================================

if (isset($_GET['resultado'])) {

    switch ($_GET['resultado']) {

        case 'sucesso':
            $mensagem = "Frete cadastrado com sucesso!";
            $tipoMensagem = "success";
            break;

        case 'editado':
            $mensagem = "Frete atualizado com sucesso!";
            $tipoMensagem = "success";
            break;

        case 'excluido':
            $mensagem = "Frete excluído com sucesso!";
            $tipoMensagem = "success";
            break;
    }
}


// ==========================================
// BUSCAR DADOS PARA OS FORMULÁRIOS
// ==========================================

$enderecos = $pdo->query(
    "SELECT * FROM enderecos ORDER BY nome ASC"
)->fetchAll(PDO::FETCH_ASSOC);

$transportadores = $pdo->query(
    "SELECT * FROM transportadores ORDER BY nome ASC"
)->fetchAll(PDO::FETCH_ASSOC);

$produtos = $pdo->query(
    "SELECT * FROM produtos ORDER BY nome ASC"
)->fetchAll(PDO::FETCH_ASSOC);


// ==========================================
// BUSCAR FRETES CADASTRADOS
// ==========================================

$sqlFretes = "
    SELECT
        f.*,
        origem.nome AS origem,
        destino.nome AS destino,
        t.nome AS transportador,
        COUNT(fp.id) AS total_produtos,
        GROUP_CONCAT(
            CONCAT(fp.produto_id, ':', fp.quantidade)
            SEPARATOR ','
        ) AS produtos_detalhes

    FROM fretes f

    INNER JOIN enderecos origem
        ON f.origem_id = origem.id

    INNER JOIN enderecos destino
        ON f.destino_id = destino.id

    INNER JOIN transportadores t
        ON f.transportador_id = t.id

    LEFT JOIN frete_produtos fp
        ON f.id = fp.frete_id

    GROUP BY f.id

    ORDER BY f.id DESC
";

$fretes = $pdo->query($sqlFretes)->fetchAll(PDO::FETCH_ASSOC);

?>

<!-- ======================================
     CABEÇALHO DA PÁGINA
======================================= -->

<div class="page-header">

    <div>
        <h2>Fretes</h2>
        <p>Gerencie os transportes cadastrados no sistema.</p>
    </div>

    <button
        class="btn btn-dark"
        data-bs-toggle="modal"
        data-bs-target="#modalFrete"
        id="btnNovoFrete"
    >
        <i class="bi bi-plus-circle"></i>
        Novo Frete
    </button>

</div>


<!-- ======================================
     MENSAGENS
======================================= -->

<?php if ($mensagem): ?>

    <div class="alert alert-<?php echo $tipoMensagem; ?> alert-dismissible fade show">

        <?php echo htmlspecialchars($mensagem); ?>

        <button
            type="button"
            class="btn-close"
            data-bs-dismiss="alert"
        ></button>

    </div>

<?php endif; ?>


<!-- ======================================
     TABELA DE FRETES
======================================= -->

<div class="dashboard-card">

    <h5 class="mb-4">Fretes cadastrados</h5>

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
                    <th>Ações</th>
                </tr>

            </thead>

            <tbody>

                <?php if (count($fretes) > 0): ?>

                    <?php foreach ($fretes as $frete): ?>

                        <tr>

                            <td>#<?php echo $frete['id']; ?></td>

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
                                <?php echo date('d/m/Y', strtotime($frete['data_frete'])); ?>
                            </td>

                            <td>
                                R$ <?php echo number_format($frete['valor'], 2, ',', '.'); ?>
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

                            <td>

                                <!-- Botão editar -->

                                <button
                                    type="button"
                                    class="btn btn-sm btn-outline-primary btn-editar"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalFrete"
                                    data-id="<?php echo $frete['id']; ?>"
                                    data-origem="<?php echo $frete['origem_id']; ?>"
                                    data-destino="<?php echo $frete['destino_id']; ?>"
                                    data-transportador="<?php echo $frete['transportador_id']; ?>"
                                    data-data="<?php echo htmlspecialchars($frete['data_frete']); ?>"
                                    data-valor="<?php echo htmlspecialchars($frete['valor']); ?>"
                                    data-status="<?php echo htmlspecialchars($frete['status']); ?>"
                                    data-produtos="<?php echo htmlspecialchars($frete['produtos_detalhes'] ?? '', ENT_QUOTES); ?>"
                                >
                                    <i class="bi bi-pencil"></i>
                                </button>


                                <!-- Botão excluir -->

                                <form
                                    method="POST"
                                    class="d-inline"
                                    onsubmit="return confirm('Tem certeza que deseja excluir este frete?');"
                                >

                                    <input type="hidden" name="acao" value="excluir">

                                    <input
                                        type="hidden"
                                        name="frete_id"
                                        value="<?php echo $frete['id']; ?>"
                                    >

                                    <button
                                        type="submit"
                                        class="btn btn-sm btn-outline-danger"
                                    >
                                        <i class="bi bi-trash"></i>
                                    </button>

                                </form>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                <?php else: ?>

                    <tr>

                        <td colspan="9" class="text-center py-4">
                            Nenhum frete cadastrado até o momento.
                        </td>

                    </tr>

                <?php endif; ?>

            </tbody>

        </table>

    </div>

</div>


<!-- ======================================
     MODAL CADASTRAR / EDITAR FRETE
======================================= -->

<div class="modal fade" id="modalFrete" tabindex="-1">

    <div class="modal-dialog modal-lg modal-dialog-centered">

        <div class="modal-content">

            <form method="POST" id="formFrete">

                <input type="hidden" name="acao" value="salvar">

                <input type="hidden" name="frete_id" id="frete_id">

                <div class="modal-header">

                    <h5 class="modal-title" id="tituloModalFrete">
                        Cadastrar novo frete
                    </h5>

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                    ></button>

                </div>


                <div class="modal-body">

                    <div class="row">


                        <!-- ORIGEM -->

                        <div class="col-md-6 mb-3">

                            <label class="form-label">
                                Endereço de origem *
                            </label>

                            <select
                                name="origem_id"
                                id="origem_id"
                                class="form-select"
                                required
                            >

                                <option value="">
                                    Selecione a origem
                                </option>

                                <?php foreach ($enderecos as $endereco): ?>

                                    <option value="<?php echo $endereco['id']; ?>">
                                        <?php echo htmlspecialchars($endereco['nome']); ?>
                                    </option>

                                <?php endforeach; ?>

                            </select>

                        </div>


                        <!-- DESTINO -->

                        <div class="col-md-6 mb-3">

                            <label class="form-label">
                                Endereço de destino *
                            </label>

                            <select
                                name="destino_id"
                                id="destino_id"
                                class="form-select"
                                required
                            >

                                <option value="">
                                    Selecione o destino
                                </option>

                                <?php foreach ($enderecos as $endereco): ?>

                                    <option value="<?php echo $endereco['id']; ?>">
                                        <?php echo htmlspecialchars($endereco['nome']); ?>
                                    </option>

                                <?php endforeach; ?>

                            </select>

                        </div>


                        <!-- TRANSPORTADOR -->

                        <div class="col-md-12 mb-3">

                            <label class="form-label">
                                Transportador *
                            </label>

                            <select
                                name="transportador_id"
                                id="transportador_id"
                                class="form-select"
                                required
                            >

                                <option value="">
                                    Selecione o transportador
                                </option>

                                <?php foreach ($transportadores as $transportador): ?>

                                    <option value="<?php echo $transportador['id']; ?>">
                                        <?php echo htmlspecialchars($transportador['nome']); ?>
                                    </option>

                                <?php endforeach; ?>

                            </select>

                        </div>


                        <!-- PRODUTOS -->

                        <div class="col-md-12 mb-3">

                            <label class="form-label">
                                Produtos *
                            </label>

                            <?php if (count($produtos) > 0): ?>

                                <div class="border rounded p-3">

                                    <?php foreach ($produtos as $produto): ?>

                                        <div class="row align-items-center mb-2">

                                            <div class="col-md-8">

                                                <div class="form-check">

                                                    <input
                                                        class="form-check-input checkbox-produto"
                                                        type="checkbox"
                                                        name="produtos[]"
                                                        value="<?php echo $produto['id']; ?>"
                                                        id="produto<?php echo $produto['id']; ?>"
                                                    >

                                                    <label
                                                        class="form-check-label"
                                                        for="produto<?php echo $produto['id']; ?>"
                                                    >
                                                        <?php echo htmlspecialchars($produto['nome']); ?>
                                                    </label>

                                                </div>

                                            </div>

                                            <div class="col-md-4">

                                                <input
                                                    type="number"
                                                    name="quantidades[<?php echo $produto['id']; ?>]"
                                                    class="form-control form-control-sm quantidade-produto"
                                                    min="1"
                                                    value="1"
                                                    placeholder="Quantidade"
                                                    disabled
                                                    data-produto="<?php echo $produto['id']; ?>"
                                                >

                                            </div>

                                        </div>

                                    <?php endforeach; ?>

                                </div>

                            <?php else: ?>

                                <div class="alert alert-warning">
                                    Cadastre pelo menos um produto antes de criar um frete.
                                </div>

                            <?php endif; ?>

                        </div>


                        <!-- DATA -->

                        <div class="col-md-6 mb-3">

                            <label class="form-label">
                                Data do frete *
                            </label>

                            <input
                                type="date"
                                name="data_frete"
                                id="data_frete"
                                class="form-control"
                                required
                            >

                        </div>


                        <!-- VALOR -->

                        <div class="col-md-6 mb-3">

                            <label class="form-label">
                                Valor do frete (R$) *
                            </label>

                            <input
                                type="number"
                                name="valor"
                                id="valor"
                                class="form-control"
                                step="0.01"
                                min="0"
                                placeholder="Ex: 250.00"
                                required
                            >

                        </div>


                        <!-- STATUS -->

                        <div class="col-md-12 mb-3">

                            <label class="form-label">
                                Status
                            </label>

                            <select
                                name="status"
                                id="status"
                                class="form-select"
                            >

                                <option value="Pendente">
                                    Pendente
                                </option>

                                <option value="Em andamento">
                                    Em andamento
                                </option>

                                <option value="Concluído">
                                    Concluído
                                </option>

                            </select>

                        </div>

                    </div>

                    <small class="text-muted">
                        * Campos obrigatórios.
                    </small>

                </div>


                <div class="modal-footer">

                    <button
                        type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal"
                    >
                        Cancelar
                    </button>

                    <button
                        type="submit"
                        class="btn btn-dark"
                    >
                        Salvar Frete
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>


<!-- ======================================
     JAVASCRIPT
======================================= -->

<script>

// Habilitar ou desabilitar quantidade dos produtos

document.querySelectorAll('.checkbox-produto').forEach(function(checkbox) {

    checkbox.addEventListener('change', function() {

        const id = this.value;

        const campo = document.querySelector(
            '.quantidade-produto[data-produto="' + id + '"]'
        );

        campo.disabled = !this.checked;

        if (!this.checked) {
            campo.value = 1;
        }

    });

});


// Preparar modal para novo cadastro

document.getElementById('btnNovoFrete').addEventListener('click', function() {

    document.getElementById('formFrete').reset();

    document.getElementById('frete_id').value = '';

    document.getElementById('tituloModalFrete').textContent =
        'Cadastrar novo frete';

    document.querySelectorAll('.quantidade-produto').forEach(function(campo) {

        campo.disabled = true;
        campo.value = 1;

    });

});


// Preparar modal para edição

document.querySelectorAll('.btn-editar').forEach(function(botao) {

    botao.addEventListener('click', function() {

        const id = this.dataset.id;

        // Preencher informações do frete

        document.getElementById('frete_id').value = id;

        document.getElementById('origem_id').value =
            this.dataset.origem;

        document.getElementById('destino_id').value =
            this.dataset.destino;

        document.getElementById('transportador_id').value =
            this.dataset.transportador;

        document.getElementById('data_frete').value =
            this.dataset.data;

        document.getElementById('valor').value =
            this.dataset.valor;

        document.getElementById('status').value =
            this.dataset.status;

        document.getElementById('tituloModalFrete').textContent =
            'Editar frete #' + id;


        // Desmarcar todos os produtos

        document.querySelectorAll('.checkbox-produto').forEach(function(checkbox) {

            checkbox.checked = false;

        });

        document.querySelectorAll('.quantidade-produto').forEach(function(campo) {

            campo.disabled = true;
            campo.value = 1;

        });


        // Recuperar produtos vinculados ao frete

        const produtos = this.dataset.produtos;

        if (produtos) {

            const listaProdutos = produtos.split(',');

            listaProdutos.forEach(function(item) {

                const dados = item.split(':');

                const produtoId = dados[0];
                const quantidade = dados[1];

                const checkbox = document.getElementById(
                    'produto' + produtoId
                );

                const campo = document.querySelector(
                    '.quantidade-produto[data-produto="' + produtoId + '"]'
                );

                if (checkbox && campo) {

                    checkbox.checked = true;

                    campo.disabled = false;

                    campo.value = quantidade;

                }

            });

        }

    });

});

</script>
```