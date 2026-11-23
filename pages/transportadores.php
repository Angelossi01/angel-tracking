<?php

require_once __DIR__ . '/../config/conexao.php';

$mensagem = "";
$tipoMensagem = "";

// PROCESSAR AÇÕES
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $acao = $_POST["acao"] ?? "";

    // CADASTRAR OU EDITAR
    if ($acao === "cadastrar" || $acao === "editar") {

        $nome = trim($_POST["nome"] ?? "");
        $documento = trim($_POST["documento"] ?? "");
        $telefone = trim($_POST["telefone"] ?? "");
        $email = trim($_POST["email"] ?? "");

        if ($nome === "" || $documento === "" || $telefone === "" || $email === "") {

            $mensagem = "Preencha todos os campos obrigatórios.";
            $tipoMensagem = "danger";

        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

            $mensagem = "Informe um endereço de e-mail válido.";
            $tipoMensagem = "danger";

        } else {

            try {

                if ($acao === "cadastrar") {

                    $sql = "INSERT INTO transportadores
                    (nome, documento, telefone, email)
                    VALUES
                    (:nome, :documento, :telefone, :email)";

                    $mensagemSucesso = "Transportador cadastrado com sucesso!";

                } else {

                    $id = $_POST["id"] ?? "";

                    if (!filter_var($id, FILTER_VALIDATE_INT) || $id <= 0) {
                        throw new Exception("Transportador inválido.");
                    }

                    $sql = "UPDATE transportadores SET
                    nome = :nome,
                    documento = :documento,
                    telefone = :telefone,
                    email = :email
                    WHERE id = :id";

                    $mensagemSucesso = "Transportador atualizado com sucesso!";
                }

                $stmt = $pdo->prepare($sql);

                $dados = [
                    ":nome" => $nome,
                    ":documento" => $documento,
                    ":telefone" => $telefone,
                    ":email" => $email
                ];

                if ($acao === "editar") {
                    $dados[":id"] = $id;
                }

                $stmt->execute($dados);

                $mensagem = $mensagemSucesso;
                $tipoMensagem = "success";

            } catch (Exception $erro) {

                $mensagem = $erro instanceof PDOException
                    ? "Erro ao salvar o transportador. Verifique se o documento já está cadastrado."
                    : $erro->getMessage();

                $tipoMensagem = "danger";

            }
        }
    }

    // EXCLUIR TRANSPORTADOR
    elseif ($acao === "excluir") {

        $id = $_POST["id"] ?? "";

        if (!filter_var($id, FILTER_VALIDATE_INT) || $id <= 0) {

            $mensagem = "Transportador inválido.";
            $tipoMensagem = "danger";

        } else {

            try {

                $sql = "DELETE FROM transportadores WHERE id = :id";

                $stmt = $pdo->prepare($sql);
                $stmt->execute([":id" => $id]);

                if ($stmt->rowCount() > 0) {

                    $mensagem = "Transportador excluído com sucesso!";
                    $tipoMensagem = "success";

                } else {

                    $mensagem = "Transportador não encontrado.";
                    $tipoMensagem = "warning";

                }

            } catch (PDOException $erro) {

                $mensagem = "Não foi possível excluir. Este transportador pode estar associado a um frete.";

                $tipoMensagem = "danger";

            }
        }
    }
}

// BUSCAR TRANSPORTADORES
$sql = $pdo->query("SELECT * FROM transportadores ORDER BY id DESC");
$transportadores = $sql->fetchAll(PDO::FETCH_ASSOC);

?>

<div class="page-header">

    <div>
        <h2>Transportadores</h2>
        <p>Gerencie os transportadores cadastrados no sistema.</p>
    </div>

    <button class="btn btn-primary"
            data-bs-toggle="modal"
            data-bs-target="#modalTransportador">

        <i class="bi bi-plus-lg"></i> Novo Transportador

    </button>

</div>


<?php if ($mensagem !== ""): ?>

    <div class="alert alert-<?= $tipoMensagem ?> alert-dismissible fade show">

        <?= htmlspecialchars($mensagem) ?>

        <button type="button" class="btn-close"
                data-bs-dismiss="alert"></button>

    </div>

<?php endif; ?>


<div class="dashboard-card">

    <h5 class="mb-4">Transportadores cadastrados</h5>

    <div class="table-responsive">

        <table class="table table-hover align-middle">

            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Documento</th>
                    <th>Telefone</th>
                    <th>E-mail</th>
                    <th>Ações</th>
                </tr>
            </thead>

            <tbody>

                <?php if (count($transportadores) > 0): ?>

                    <?php foreach ($transportadores as $transportador): ?>

                        <tr>

                            <td><?= $transportador["id"] ?></td>

                            <td><?= htmlspecialchars($transportador["nome"]) ?></td>

                            <td><?= htmlspecialchars($transportador["documento"]) ?></td>

                            <td><?= htmlspecialchars($transportador["telefone"]) ?></td>

                            <td><?= htmlspecialchars($transportador["email"]) ?></td>

                            <td>

                                <!-- EDITAR -->
                                <button
                                    class="btn btn-sm btn-outline-primary"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalEditar<?= $transportador["id"] ?>">

                                    <i class="bi bi-pencil"></i>

                                </button>


                                <!-- EXCLUIR -->
                                <form method="POST" class="d-inline"
                                      onsubmit="return confirm('Deseja realmente excluir este transportador?');">

                                    <input type="hidden" name="acao" value="excluir">

                                    <input type="hidden" name="id"
                                           value="<?= $transportador["id"] ?>">

                                    <button type="submit"
                                            class="btn btn-sm btn-outline-danger">

                                        <i class="bi bi-trash"></i>

                                    </button>

                                </form>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                <?php else: ?>

                    <tr>
                        <td colspan="6" class="text-center py-4">
                            Nenhum transportador cadastrado ainda.
                        </td>
                    </tr>

                <?php endif; ?>

            </tbody>

        </table>

    </div>

</div>


<!-- MODAL DE CADASTRO -->
<div class="modal fade" id="modalTransportador" tabindex="-1">

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content">

            <div class="modal-header">

                <h5 class="modal-title">Cadastrar transportador</h5>

                <button type="button" class="btn-close"
                        data-bs-dismiss="modal"></button>

            </div>

            <form method="POST">

                <input type="hidden" name="acao" value="cadastrar">

                <div class="modal-body">

                    <div class="mb-3">

                        <label class="form-label">Nome *</label>

                        <input type="text" name="nome"
                               class="form-control" required>

                    </div>

                    <div class="mb-3">

                        <label class="form-label">Documento (CPF/CNPJ) *</label>

                        <input type="text" name="documento"
                               class="form-control" required>

                    </div>

                    <div class="mb-3">

                        <label class="form-label">Telefone *</label>

                        <input type="text" name="telefone"
                               class="form-control" required>

                    </div>

                    <div class="mb-3">

                        <label class="form-label">E-mail *</label>

                        <input type="email" name="email"
                               class="form-control" required>

                    </div>

                    <small class="text-muted">* Campos obrigatórios</small>

                </div>

                <div class="modal-footer">

                    <button type="button" class="btn btn-secondary"
                            data-bs-dismiss="modal">
                        Cancelar
                    </button>

                    <button type="submit" class="btn btn-primary">
                        Salvar transportador
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>


<!-- MODAIS DE EDIÇÃO -->
<?php foreach ($transportadores as $transportador): ?>

<div class="modal fade" id="modalEditar<?= $transportador["id"] ?>" tabindex="-1">

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content">

            <div class="modal-header">

                <h5 class="modal-title">Editar transportador</h5>

                <button type="button" class="btn-close"
                        data-bs-dismiss="modal"></button>

            </div>

            <form method="POST">

                <input type="hidden" name="acao" value="editar">

                <input type="hidden" name="id"
                       value="<?= $transportador["id"] ?>">

                <div class="modal-body">

                    <div class="mb-3">

                        <label class="form-label">Nome *</label>

                        <input type="text" name="nome"
                               class="form-control"
                               value="<?= htmlspecialchars($transportador["nome"]) ?>"
                               required>

                    </div>

                    <div class="mb-3">

                        <label class="form-label">Documento (CPF/CNPJ) *</label>

                        <input type="text" name="documento"
                               class="form-control"
                               value="<?= htmlspecialchars($transportador["documento"]) ?>"
                               required>

                    </div>

                    <div class="mb-3">

                        <label class="form-label">Telefone *</label>

                        <input type="text" name="telefone"
                               class="form-control"
                               value="<?= htmlspecialchars($transportador["telefone"]) ?>"
                               required>

                    </div>

                    <div class="mb-3">

                        <label class="form-label">E-mail *</label>

                        <input type="email" name="email"
                               class="form-control"
                               value="<?= htmlspecialchars($transportador["email"]) ?>"
                               required>

                    </div>

                </div>

                <div class="modal-footer">

                    <button type="button" class="btn btn-secondary"
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