<?php
include '../../src/bootstrap.php';

// Remove a sessão de autenticação
Sessao::deletar('_secureAuthMapas');
redirect('/painel');
exit;