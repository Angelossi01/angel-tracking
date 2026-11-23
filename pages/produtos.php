<?php

require_once __DIR__ . '/../config/conexao.php';

$mensagem = "";
$tipoMensagem = "";

// Processar ações do formulário
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $acao = $_POST["acao"] ?? "";

    // CADASTRAR PRODUTO
    if ($acao === "cadastrar") {

        $nome = trim($_POST["nome"] ?? "");
        $descricao = trim($_POST["descricao"] ?? "");
        $peso = $_POST["peso"] ?? "";
        $preco = $_POST["preco"] ?? "";

        if ($nome === "" || $peso === "" || $preco === "") {

            $mensagem = "Preencha todos os campos obrigatórios.";
            $tipoMensagem = "danger";

        } elseif (!is_numeric($peso) || !is_numeric($preco) || $peso < 0 || $preco < 0) {

            $mensagem = "Informe valores válidos para peso e preço.";
            $tipoMensagem = "danger";

        } else {

            try {

                $sql = "INSERT INTO produtos (nome, descricao, peso, preco)
                        VALUES (:nome, :descricao, :peso, :preco)";

                $stmt = $pdo->prepare($sql);

                $stmt->execute([
                    ":nome" => $nome,
                    ":descricao" => $descricao,
                    ":peso" => $peso,
                    ":preco" => $preco
                ]);

                $mensagem = "Produto cadastrado com sucesso!";
                $tipoMensagem = "success";

            } catch (PDOException $erro) {

                $mensagem = "Erro ao cadastrar o produto.";
                $tipoMensagem = "danger";

            }
        }
    }

    // EDITAR PRODUTO
    elseif ($acao === "editar") {

        $id = $_POST["id"] ?? "";
        $nome = trim($_POST["nome"] ?? "");
        $descricao = trim($_POST["descricao"] ?? "");
        $peso = $_POST["peso"] ?? "";
        $preco = $_POST["preco"] ?? "";

        if (!filter_var($id, FILTER_VALIDATE_INT) || $id <= 0) {

            $mensagem = "Produto inválido.";
            $tipoMensagem = "danger";

        } elseif ($nome === "" || $peso === "" || $preco === "") {

            $mensagem = "Preencha todos os campos obrigatórios.";
            $tipoMensagem = "danger";

        } elseif (!is_numeric($peso) || !is_numeric($preco) || $peso < 0 || $preco < 0) {

            $mensagem = "Informe valores válidos para peso e preço.";
            $tipoMensagem = "danger";

        } else {

            try {

                $sql = "UPDATE produtos
                        SET nome = :nome,
                            descricao = :descricao,
                            peso = :peso,
                            preco = :preco
                        WHERE id = :id";

                $stmt = $pdo->prepare($sql);

                $stmt->execute([
                    ":nome" => $nome,
                    ":descricao" => $descricao,
                    ":peso" => $peso,
                    ":preco" => $preco,
                    ":id" => $id
                ]);

                $mensagem = "Produto atualizado com sucesso!";
                $tipoMensagem = "success";

            } catch (PDOException $erro) {

                $mensagem = "Erro ao atualizar o produto.";
                $tipoMensagem = "danger";

            }
        }
    }

    // EXCLUIR PRODUTO
    elseif ($acao === "excluir") {

        $id = $_POST["id"] ?? "";

        if (!filter_var($id, FILTER_VALIDATE_INT) || $id <= 0) {

            $mensagem = "Produto inválido.";
            $tipoMensagem = "danger";

        } else {

            try {

                $sql = "DELETE FROM produtos WHERE id = :id";

                $stmt = $pdo->prepare($sql);
                $stmt->execute([":id" => $id]);

                if ($stmt->rowCount() > 0) {
                    $mensagem = "Produto excluído com sucesso!";
                    $tipoMensagem = "success";
                } else {
                    $mensagem = "Produto não encontrado.";
                    $tipoMensagem = "warning";
                }

            } catch (PDOException $erro) {

                $mensagem = "Não foi possível excluir. Este produto pode estar associado a um frete.";
                $tipoMensagem = "danger";

            }
        }
    }
}

// Buscar produtos cadastrados
$sql = $pdo->query("SELECT * FROM produtos ORDER BY id DESC");
$produtos = $sql->fetchAll(PDO::FETCH_ASSOC);

?>

<div class="page-header">

    <div>
        <h2>Produtos</h2>
        <p>Gerencie os produtos cadastrados no sistema.</p>
    </div>

    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalProduto">
        <i class="bi bi-plus-lg"></i> Novo Produto
    </button>

</div>


<?php if ($mensagem !== ""): ?>

    <div class="alert alert-<?= $tipoMensagem ?> alert-dismissible fade show">
        <?= htmlspecialchars($mensagem) ?>

        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>

<?php endif; ?>


<div class="dashboard-card">

    <h5 class="mb-4">Produtos cadastrados</h5>

    <div class="table-responsive">

        <table class="table table-hover align-middle">

            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Descrição</th>
                    <th>Peso (kg)</th>
                    <th>Preço (R$)</th>
                    <th>Ações</th>
                </tr>
            </thead>

            <tbody>

                <?php if (count($produtos) > 0): ?>

                    <?php foreach ($produtos as $produto): ?>

                        <tr>

                            <td><?= $produto["id"] ?></td>

                            <td><?= htmlspecialchars($produto["nome"]) ?></td>

                            <td><?= htmlspecialchars($produto["descricao"] ?? "") ?></td>

                            <td>
                                <?= number_format($produto["peso"], 2, ",", ".") ?>
                            </td>

                            <td>
                                R$ <?= number_format($produto["preco"], 2, ",", ".") ?>
                            </td>

                            <td>

                                <!-- Botão editar -->
                                <button
                                    class="btn btn-sm btn-outline-primary"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalEditar<?= $produto["id"] ?>"
                                    title="Editar produto">

                                    <i class="bi bi-pencil"></i>

                                </button>


                                <!-- Botão excluir -->
                                <form method="POST" class="d-inline"
                                      onsubmit="return confirm('Tem certeza que deseja excluir este produto?');">

                                    <input type="hidden" name="acao" value="excluir">

                                    <input type="hidden" name="id"
                                           value="<?= $produto["id"] ?>">

                                    <button type="submit"
                                            class="btn btn-sm btn-outline-danger"
                                            title="Excluir produto">

                                        <i class="bi bi-trash"></i>

                                    </button>

                                </form>

                            </td>

                        </tr>


                        <!-- Modal de edição -->
                        <div class="modal fade" id="modalEditar<?= $produto["id"] ?>" tabindex="-1">

                            <div class="modal-dialog modal-dialog-centered">

                                <div class="modal-content">

                                    <div class="modal-header">

                                        <h5 class="modal-title">Editar produto</h5>

                                        <button type="button" class="btn-close"
                                                data-bs-dismiss="modal"></button>

                                    </div>

                                    <form method="POST">

                                        <input type="hidden" name="acao" value="editar">

                                        <input type="hidden" name="id"
                                               value="<?= $produto["id"] ?>">

                                        <div class="modal-body">

                                            <div class="mb-3">

                                                <label class="form-label">Nome do produto *</label>

                                                <input type="text" name="nome"
                                                       class="form-control"
                                                       value="<?= htmlspecialchars($produto["nome"]) ?>"
                                                       required>

                                            </div>

                                            <div class="mb-3">

                                                <label class="form-label">Descrição</label>

                                                <textarea name="descricao"
                                                          class="form-control"
                                                          rows="3"><?= htmlspecialchars($produto["descricao"] ?? "") ?></textarea>

                                            </div>

                                            <div class="mb-3">

                                                <label class="form-label">Peso (kg) *</label>

                                                <input type="number" name="peso"
                                                       class="form-control"
                                                       step="0.01" min="0"
                                                       value="<?= $produto["peso"] ?>"
                                                       required>

                                            </div>

                                            <div class="mb-3">

                                                <label class="form-label">Preço (R$) *</label>

                                                <input type="number" name="preco"
                                                       class="form-control"
                                                       step="0.01" min="0"
                                                       value="<?= $produto["preco"] ?>"
                                                       required>

                                            </div>

                                            <small class="text-muted">* Campos obrigatórios</small>

                                        </div>

                                        <div class="modal-footer">

                                            <button type="button"
                                                    class="btn btn-secondary"
                                                    data-bs-dismiss="modal">
                                                Cancelar
                                            </button>

                                            <button type="submit" class="btn btn-primary">
                                                Salvar alterações
                                            </button>

                                        </div>

                                    </form>

                                </div>

                            </div>

                        </div>

                    <?php endforeach; ?>

                <?php else: ?>

                    <tr>
                        <td colspan="6" class="text-center py-4">
                            Nenhum produto cadastrado ainda.
                        </td>
                    </tr>

                <?php endif; ?>

            </tbody>

        </table>

    </div>

</div>


<!-- Modal de cadastro -->
<div class="modal fade" id="modalProduto" tabindex="-1">

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content">

            <div class="modal-header">

                <h5 class="modal-title">Cadastrar novo produto</h5>

                <button type="button" class="btn-close"
                        data-bs-dismiss="modal"></button>

            </div>

            <form method="POST">

                <input type="hidden" name="acao" value="cadastrar">

                <div class="modal-body">

                    <div class="mb-3">

                        <label class="form-label">Nome do produto *</label>

                        <input type="text" name="nome"
                               class="form-control" required>

                    </div>

                    <div class="mb-3">

                        <label class="form-label">Descrição</label>

                        <textarea name="descricao"
                                  class="form-control" rows="3"></textarea>

                    </div>

                    <div class="mb-3">

                        <label class="form-label">Peso (kg) *</label>

                        <input type="number" name="peso"
                               class="form-control"
                               step="0.01" min="0" required>

                    </div>

                    <div class="mb-3">

                        <label class="form-label">Preço (R$) *</label>

                        <input type="number" name="preco"
                               class="form-control"
                               step="0.01" min="0" required>

                    </div>

                    <small class="text-muted">* Campos obrigatórios</small>

                </div>

                <div class="modal-footer">

                    <button type="button" class="btn btn-secondary"
                            data-bs-dismiss="modal">
                        Cancelar
                    </button>

                    <button type="submit" class="btn btn-primary">
                        Salvar produto
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>