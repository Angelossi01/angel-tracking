# Angel Tracking 🚚

Sistema web de gerenciamento logístico desenvolvido em PHP, como parte de uma atividade acadêmica da disciplina de Fundamentos de Programação para Internet.

O Angel Tracking tem como objetivo facilitar o controle de operações logísticas, permitindo gerenciar produtos, endereços, transportadores e fretes em um único ambiente.

## 📌 Funcionalidades

- **Dashboard:** visualização geral dos indicadores do sistema.
- **Produtos:** cadastro, consulta, edição e exclusão de produtos.
- **Endereços:** gerenciamento de endereços de origem e destino.
- **Transportadores:** cadastro e gerenciamento de transportadores.
- **Fretes:** criação, consulta, edição e exclusão de fretes, com associação de produtos, endereços e transportadores.
- **Relatórios:** acompanhamento dos fretes, com filtros por período e indicadores de desempenho.

## 🛠️ Tecnologias utilizadas

- PHP
- HTML5
- CSS3
- JavaScript
- Bootstrap 5
- MySQL
- Git e GitHub

## 🎨 Identidade visual

O sistema utiliza uma interface minimalista, com tons de cinza arroxeado, creme e marrom, buscando proporcionar uma experiência visual simples e organizada.

## ⚙️ Como executar o projeto

### 1. Instalar as ferramentas necessárias

- XAMPP
- Visual Studio Code
- Navegador de internet

### 2. Clonar o repositório

No terminal, execute:

```bash
git clone https://github.com/Angelossi01/angel-tracking.git
```

### 3. Colocar o projeto no servidor local

Mova a pasta `angel-tracking` para o diretório:

```text
C:\xampp\htdocs\
```

### 4. Iniciar o XAMPP

Abra o XAMPP Control Panel e inicie os serviços:

- Apache
- MySQL

### 5. Criar o banco de dados

Acesse o phpMyAdmin:

```text
http://localhost/phpmyadmin
```

Crie um banco de dados chamado:

```text
angel_tracking
```

### 6. Importar o banco de dados

1. Selecione o banco `angel_tracking`.
2. Clique na opção **Importar**.
3. Selecione o arquivo `database/angel_tracking.sql`.
4. Clique em **Importar** ou **Executar**.

### 7. Configurar a conexão

Verifique o arquivo:

```text
config/conexao.php
```

Configure as informações de conexão de acordo com seu ambiente MySQL.

Na configuração padrão do XAMPP, o usuário geralmente é `root` e a senha fica vazia.

### 8. Acessar o sistema

Abra o navegador e acesse:

```text
http://localhost/angel-tracking/
```

Pronto! O sistema estará disponível no ambiente local.

## 📁 Estrutura do projeto

```text
angel-tracking/
│
├── assets/
│   ├── css/
│   ├── img/
│   └── js/
│
├── config/
│   └── conexao.php
│
├── database/
│   └── angel_tracking.sql
│
├── includes/
│   ├── header.php
│   └── sidebar.php
│
├── pages/
│   ├── dashboard.php
│   ├── produtos.php
│   ├── enderecos.php
│   ├── transportadores.php
│   ├── fretes.php
│   └── relatorios.php
│
├── .gitignore
├── index.php
└── README.md
```

## 🎓 Projeto acadêmico

Projeto desenvolvido para a disciplina de Fundamentos de Programação para Internet, com o objetivo de aplicar conhecimentos de desenvolvimento web, integração com banco de dados e gerenciamento de informações.

---

**Angel Tracking © 2026**