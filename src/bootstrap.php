<?php
define('APP_ROOT', dirname(dirname(__FILE__)));
require 'functions.php';
require APP_ROOT . '/config/config.php';

/* Usado como uma referência a uma classe que ainda não foi definida. 
Ele usa as funções registradas com "spl_autoload_register" 
para tentar carregar essa classe automaticamente. */
spl_autoload_register(function ($classe) {
    $caminho = APP_ROOT . '/src/classes/' . $classe . '.php';
    if (file_exists($caminho)) {
        require $caminho;
    }
});

/* Para inicializar um objeto CMS com as informações de conexão ao banco de dados. 
Em seguida, as variáveis $dsn, $username, $password e $htpasswd_file são removidas da memória. */
$cms = new CMS($dsn_cadastro, $dsn_ementas, $username, $password, $htpasswd_file);
unset($dsn_cadastro, $dsn_ementas, $username, $password, $htpasswd_file);
