<?php
class CMS
{
    protected $db_cadastro = null;
    protected $db_ementas = null;
    protected $salas = null;
    protected $login = null;
    protected $softwares = null;
    protected $modelos = null;
    protected $colunas = null;
    protected $htpasswd_file;

    public function __construct($dsn_cadastro, $dsn_ementas, $username, $password, $htpasswd_file)
    {
        $this->db_cadastro = new Database($dsn_cadastro, $username, $password);
        $this->db_ementas = new Database($dsn_ementas, $username, $password);
        $this->htpasswd_file = $htpasswd_file;
    }
    public function getSalas()
    {
        if ($this->salas === null) {
            $this->salas = new Salas($this->db_cadastro);
        }
        return $this->salas;
    }
    public function getLogin()
    {
        if ($this->login === null) {
            $this->login = new Login($this->htpasswd_file);
        }
        return $this->login;
    }
    public function getSoftwares()
    {
        if ($this->softwares === null) {
            $this->softwares = new Softwares($this->db_ementas);
        }
        return $this->softwares;
    }
    public function getModelos()
    {
        if ($this->modelos === null) {
            $this->modelos = new Modelos($this->db_cadastro);
        }
        return $this->modelos;
    }
    public function getLayoutMapa()
    {
        if ($this->colunas === null) {
            $this->colunas = new LayoutMapa($this->db_cadastro);
        }
        return $this->colunas;
    }
}
