<?php

$host = "localhost";
$banco = "angel_tracking";
$usuario = "root";
$senha = "";

try {

    $pdo = new PDO(
        "mysql:host=$host;dbname=$banco;charset=utf8mb4",
        $usuario,
        $senha
    );

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $erro) {

    die("Erro na conexão com o banco de dados: " . $erro->getMessage());

}

?>
