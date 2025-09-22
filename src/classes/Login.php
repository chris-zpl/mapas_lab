<?php

class Login
{
    private $htpasswd_file;

    public function __construct($htpasswd_file)
    {
        $this->htpasswd_file = $htpasswd_file;
    }

    public function autenticar($usuario, $senha)
    {
        if ($this->checkHtpasswd($usuario, $senha)) {
            Sessao::regenerarId();
            Sessao::set('usuario', $usuario);

            // Obtém a URI atual
            $current_uri = $_SERVER["REQUEST_URI"];

            // Define a URL base para evitar duplicação
            $base_url = '/labs/v4/mapas/public/';

            // Remove duplicatas da base URL
            $clean_uri = preg_replace('#(' . preg_quote($base_url) . ')+#', $base_url, $current_uri);

            // Redireciona para a URI limpa
            header('Location:' . $clean_uri);
            exit;
        } else {
            return false;
        }
    }
    private function checkHtpasswd($usuario, $senha)
    {
        $linhas = file($this->htpasswd_file);
        foreach ($linhas as $linha) {
            list($user, $hashedPassword) = explode(":", trim($linha), 2);
            if ($user === $usuario && crypt($senha, $hashedPassword) === $hashedPassword) {
                return true;
            }
        }
        return false;
    }
}
