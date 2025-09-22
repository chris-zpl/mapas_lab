<?php
require_once __DIR__ . '/env.php';

define("DOC_ROOT", '/labs/v4/mapas/public');
define("ROOT_FOLDER", 'public');

// As configurações de acesso ao banco de dados, bem como o arquivo onde está as senhas armazenadas no sistema
$type = 'pgsql';
$dbname_cadastro = "feng_cadastro";
$dbname_ementas = "feng_ementas";
$username = getenv('DB_USER');
$password = getenv('DB_PASS');
$htpasswd_file = '/home/politecnica/engenharia/.htpasswd';

$dsn_cadastro = "$type:dbname=$dbname_cadastro";

// Configuração para o banco secundário
$dsn_ementas = "$type:dbname=$dbname_ementas";
