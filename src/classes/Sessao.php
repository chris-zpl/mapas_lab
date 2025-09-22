<?php

class Sessao
{
    public static function iniciar($nomeSessao, $duracaoDias)
    {
        session_set_cookie_params(3600 * 24 * $duracaoDias);
        self::setNome($nomeSessao);
    }
    public static function setNome($nomeSessao)
    {
        session_name($nomeSessao);
        session_start();
    }

    public static function regenerarId()
    {
        session_regenerate_id(true);
    }

    public static function set($key, $value)
    {
        $_SESSION[$key] = $value;
    }

    public static function get($key)
    {
        return isset($_SESSION[$key]) ? $_SESSION[$key] : null;
    }
    public static function logado($key, $sessao, $usuariosAutorizados)
    {
        if (isset($_COOKIE[$key]) && in_array($sessao, $usuariosAutorizados)) {
            redirect("/painel");
            exit();
        }
    }
    public static function naoLogado($key, $sessao, $usuariosAutorizados)
    {
        if (!isset($_COOKIE[$key]) || !in_array($sessao, $usuariosAutorizados)) {
            redirect("/nao-autorizado");
            exit();
        }
    }
    public static function deletar($valor)
    {
        //Sessao::setNome($valor);
        $_SESSION = [];
        $params = session_get_cookie_params();
        setcookie($valor, '', time() - 3600, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        session_destroy();
    }
}
