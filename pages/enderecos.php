<?php

require_once __DIR__ . '/../config/conexao.php';

$mensagem = "";
$tipoMensagem = "";

// Processar ações
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $acao = $_POST["acao"] ?? "";

    // CADASTRAR ENDEREÇO
    if ($acao === "cadastrar" || $acao === "editar") {

        $nome = trim($_POST["nome"] ?? "");
        $cep = trim($_POST["cep"] ?? "");
        $rua = trim($_POST["rua"] ?? "");
        $numero = trim($_POST["numero"] ?? "");
        $bairro = trim($_POST["bairro"] ?? "");
        $cidade = trim($_POST["cidade"] ?? "");
        $estado = strtoupper(trim($_POST["estado"] ?? ""));

        if (
            $nome === "" ||
            $cep === "" ||
            $rua === "" ||
            $numero === "" ||
            $bairro === "" ||
            $cidade === "" ||
            $estado === ""
        ) {

            $mensagem = "Preencha todos os campos.";
            $tipoMensagem = "danger";

        } elseif (!preg_match('/^[A-Z]{2}$/', $estado)) {

            $mensagem = "Informe uma sigla de estado válida, como PR ou SP.";
            $tipoMensagem = "danger";

        } else {

            try {

                if ($acao === "cadastrar") {

                    $sql = "INSERT INTO enderecos
                    (nome, cep, rua, numero, bairro, cidade, estado)
                    VALUES
                    (:nome, :cep, :rua, :numero, :bairro, :cidade, :estado)";

                    $mensagemSucesso = "Endereço cadastrado com sucesso!";

                } else {

                    $id = $_POST["id"] ?? "";

                    if (!filter_var($id, FILTER_VALIDATE_INT) || $id <= 0) {
                        throw new Exception("Endereço inválido.");
                    }

                    $sql = "UPDATE enderecos SET
                    nome = :nome,
                    cep = :cep,
                    rua = :rua,
                    numero = :numero,
                    bairro = :bairro,
                    cidade = :cidade,
                    estado = :estado
                    WHERE id = :id";

                    $mensagemSucesso = "Endereço atualizado com sucesso!";
                }

                $stmt = $pdo->prepare($sql);

                $dados = [
                    ":nome" => $nome,
                    ":cep" => $cep,
                    ":rua" => $rua,
                    ":numero" => $numero,
                    ":bairro" => $bairro,
                    ":cidade" => $cidade,
                    ":estado" => $estado
                ];

                if ($acao === "editar") {
                    $dados[":id"] = $id;
                }

                $stmt->execute($dados);

                $mensagem = $mensagemSucesso;
                $tipoMensagem = "success";

            } catch (Exception $erro) {

                $mensagem = $erro instanceof PDOException
                    ? "Erro ao salvar o endereço."
                    : $erro->getMessage();

                $tipoMensagem = "danger";

            }
        }
    }

    // EXCLUIR ENDEREÇO
    elseif ($acao === "excluir") {

        $id = $_POST["id"] ?? "";

        if (!filter_var($id, FILTER_VALIDATE_INT) || $id <= 0) {

            $mensagem = "Endereço inválido.";
            $tipoMensagem = "danger";

        } else {

            try {

                $sql = "DELETE FROM enderecos WHERE id = :id";

                $stmt = $pdo->prepare($sql);
                $stmt->execute([":id" => $id]);

                if ($stmt->rowCount() > 0) {
                    $mensagem = "Endereço excluído com sucesso!";
                    $tipoMensagem = "success";
                } else {
                    $mensagem = "Endereço não encontrado.";
                    $tipoMensagem = "warning";
                }

            } catch (PDOException $erro) {

                $mensagem = "Não foi possível excluir. Este endereço pode estar associado a um frete.";

                $tipoMensagem = "danger";

            }
        }
    }
}

// BUSCAR ENDEREÇOS
$sql = $pdo->query("SELECT * FROM enderecos ORDER BY id DESC");
$enderecos = $sql->fetchAll(PDO::FETCH_ASSOC);

?>

<div class="page-header">

    <div>
        <h2>Endereços</h2>
        <p>Gerencie os endereços utilizados nos fretes.</p>
    </div>

    <button class="btn btn-primary"
            data-bs-toggle="modal"
            data-bs-target="#modalEndereco">

        <i class="bi bi-plus-lg"></i> Novo Endereço

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

    <h5 class="mb-4">Endereços cadastrados</h5>

    <div class="table-responsive">

        <table class="table table-hover align-middle">

            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>CEP</th>
                    <th>Rua</th>
                    <th>Número</th>
                    <th>Bairro</th>
                    <th>Cidade</th>
                    <th>Estado</th>
                    <th>Ações</th>
                </tr>
            </thead>

            <tbody>

                <?php if (count($enderecos) > 0): ?>

                    <?php foreach ($enderecos as $endereco): ?>

                        <tr>

                            <td><?= $endereco["id"] ?></td>

                            <td><?= htmlspecialchars($endereco["nome"]) ?></td>

                            <td><?= htmlspecialchars($endereco["cep"]) ?></td>

                            <td><?= htmlspecialchars($endereco["rua"]) ?></td>

                            <td><?= htmlspecialchars($endereco["numero"]) ?></td>

                            <td><?= htmlspecialchars($endereco["bairro"]) ?></td>

                            <td><?= htmlspecialchars($endereco["cidade"]) ?></td>

                            <td><?= htmlspecialchars($endereco["estado"]) ?></td>

                            <td>

                                <button
                                    class="btn btn-sm btn-outline-primary"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalEditar<?= $endereco["id"] ?>">

                                    <i class="bi bi-pencil"></i>

                                </button>

                                <form method="POST" class="d-inline"
                                      onsubmit="return confirm('Deseja realmente excluir este endereço?');">

                                    <input type="hidden" name="acao" value="excluir">

                                    <input type="hidden" name="id"
                                           value="<?= $endereco["id"] ?>">

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
                        <td colspan="9" class="text-center py-4">
                            Nenhum endereço cadastrado ainda.
                        </td>
                    </tr>

                <?php endif; ?>

            </tbody>

        </table>

    </div>

</div>


<!-- MODAL DE CADASTRO -->
<div class="modal fade" id="modalEndereco" tabindex="-1">

    <div class="modal-dialog modal-dialog-centered modal-lg">

        <div class="modal-content">

            <div class="modal-header">

                <h5 class="modal-title">Cadastrar endereço</h5>

                <button type="button" class="btn-close"
                        data-bs-dismiss="modal"></button>

            </div>

            <form method="POST">

                <input type="hidden" name="acao" value="cadastrar">

                <div class="modal-body">

                    <div class="mb-3">
                        <label class="form-label">Nome do endereço *</label>
                        <input type="text" name="nome"
                               class="form-control" required>
                    </div>

                    <div class="row">

                        <div class="col-md-6 mb-3">
                            <label class="form-label">CEP *</label>
                            <input type="text" name="cep"
                                   class="form-control" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Rua *</label>
                            <input type="text" name="rua"
                                   class="form-control" required>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Número *</label>
                            <input type="text" name="numero"
                                   class="form-control" required>
                        </div>

                        <div class="col-md-8 mb-3">
                            <label class="form-label">Bairro *</label>
                            <input type="text" name="bairro"
                                   class="form-control" required>
                        </div>

                        <div class="col-md-8 mb-3">
                            <label class="form-label">Cidade *</label>
                            <input type="text" name="cidade"
                                   class="form-control" required>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Estado (UF) *</label>
                            <input type="text" name="estado"
                                   class="form-control"
                                   maxlength="2" placeholder="PR" required>
                        </div>

                    </div>

                    <small class="text-muted">* Campos obrigatórios</small>

                </div>

                <div class="modal-footer">

                    <button type="button" class="btn btn-secondary"
                            data-bs-dismiss="modal">
                        Cancelar
                    </button>

                    <button type="submit" class="btn btn-primary">
                        Salvar endereço
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>


<!-- MODAIS DE EDIÇÃO -->
<?php foreach ($enderecos as $endereco): ?>

<div class="modal fade" id="modalEditar<?= $endereco["id"] ?>" tabindex="-1">

    <div class="modal-dialog modal-dialog-centered modal-lg">

        <div class="modal-content">

            <div class="modal-header">

                <h5 class="modal-title">Editar endereço</h5>

                <button type="button" class="btn-close"
                        data-bs-dismiss="modal"></button>

            </div>

            <form method="POST">

                <input type="hidden" name="acao" value="editar">

                <input type="hidden" name="id"
                       value="<?= $endereco["id"] ?>">

                <div class="modal-body">

                    <div class="mb-3">

                        <label class="form-label">Nome do endereço *</label>

                        <input type="text" name="nome"
                               class="form-control"
                               value="<?= htmlspecialchars($endereco["nome"]) ?>"
                               required>

                    </div>

                    <div class="row">

                        <div class="col-md-6 mb-3">
                            <label class="form-label">CEP *</label>
                            <input type="text" name="cep"
                                   class="form-control"
                                   value="<?= htmlspecialchars($endereco["cep"]) ?>"
                                   required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Rua *</label>
                            <input type="text" name="rua"
                                   class="form-control"
                                   value="<?= htmlspecialchars($endereco["rua"]) ?>"
                                   required>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Número *</label>
                            <input type="text" name="numero"
                                   class="form-control"
                                   value="<?= htmlspecialchars($endereco["numero"]) ?>"
                                   required>
                        </div>

                        <div class="col-md-8 mb-3">
                            <label class="form-label">Bairro *</label>
                            <input type="text" name="bairro"
                                   class="form-control"
                                   value="<?= htmlspecialchars($endereco["bairro"]) ?>"
                                   required>
                        </div>

                        <div class="col-md-8 mb-3">
                            <label class="form-label">Cidade *</label>
                            <input type="text" name="cidade"
                                   class="form-control"
                                   value="<?= htmlspecialchars($endereco["cidade"]) ?>"
                                   required>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Estado (UF) *</label>
                            <input type="text" name="estado"
                                   class="form-control"
                                   maxlength="2"
                                   value="<?= htmlspecialchars($endereco["estado"]) ?>"
                                   required>
                        </div>

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