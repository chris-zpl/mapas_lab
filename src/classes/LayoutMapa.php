<?php

class LayoutMapa
{
    protected $db;
    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    public function get()
    {
        $sql = "SELECT * from mapas_lab_salas ORDER BY sala";
        return $this->db->runSQL($sql)->fetchAll();
    }
    public function getPorId($id)
    {
        $sql = "SELECT * from mapas_lab_salas WHERE id = :id";
        return $this->db->runSQL($sql, ['id' => $id])->fetch();
    }
    public function getPerSala($sala)
    {
        $sql = "SELECT * from mapas_lab_salas WHERE sala=:sala;";
        return $this->db->runSQL($sql, [':sala' => $sala])->fetch();
    }
    public function update($infos)
    {
        try {
            $sql = "UPDATE mapas_lab_salas 
                SET sala = :sala, 
                    titulo = :titulo, 
                    qtde_gerada = :qtde_gerada, 
                    mostrar_softwares = :mostrar_softwares, 
                    mostrar_sala = :mostrar_sala 
                WHERE id = :id";

            $this->db->runSQL($sql, $infos);
            return true;
        } catch (PDOException $e) {
            if ($e->errorInfo[1] === 1062) {
                return false;
            } else {
                throw $e;
            }
        }
    }
    public function updatePosicoes($infos)
    {
        try {
            $sql = "UPDATE mapas_lab_salas SET linhas = :linhas, colunas = :colunas, posicoes = :posicoes WHERE sala = :sala";
            $this->db->runSQL($sql, $infos);
            return true;
        } catch (PDOException $e) {
            if ($e->errorInfo[1] === 1062) {
                return false;
            } else {
                throw $e;
            }
        }
    }
    public function updateLog($infos)
    {
        try {
            $sql = "UPDATE mapas_lab_salas SET log = COALESCE(log, '') || CAST(:novoLog AS TEXT) WHERE id = :id";
            $this->db->runSQL($sql, $infos);
            return true;
        } catch (PDOException $e) {
            throw $e;
        }
    }

    public function insertInfoSala($infos)
    {
        try {
            unset($infos['posicoes']);
            $sql = "INSERT INTO mapas_lab_salas (linhas, colunas, titulo, qtde_gerada, sala, mostrar_softwares, mostrar_sala, log) VALUES (:linhas, :colunas, :titulo, :qtde_gerada, :sala, :mostrar_softwares, :mostrar_sala, :log)";
            $this->db->runSQL($sql, $infos);
            return true;
        } catch (PDOException $e) {
            if ($e->errorInfo[1] === 1062) {
                return false;
            } else {
                throw $e;
            }
        }
    }

    public function insert($infos)
    {
        try {
            $sql = "INSERT INTO mapas_lab_salas (linhas, colunas, posicoes, sala) VALUES (:linhas, :colunas, :posicoes, :sala)";
            $this->db->runSQL($sql, $infos);
            return true;
        } catch (PDOException $e) {
            if ($e->errorInfo[1] === 1062) {
                return false;
            } else {
                throw $e;
            }
        }
    }
}
