<?php
class Salas
{
    protected $db;
    public function __construct(Database $db)
    {
        $this->db = $db;
    }
    public function getPerSala($sala)
    {
        $sql = "SELECT * FROM mapas_lab_patrimonios WHERE sala=:sala ORDER BY num;";
        return $this->db->runSQL($sql, [':sala' => $sala])->fetchAll();
    }
    public function get($info)
    {
        $sql = "SELECT * FROM mapas_lab_patrimonios WHERE id=:id;";
        return $this->db->runSQL($sql, [':id' => $info])->fetch();
    }
    public function getAtivosPorSala($sala)
    {
        $sql = "SELECT id FROM mapas_lab_patrimonios 
            WHERE sala = :sala AND mostrar = true 
            ORDER BY num ASC;";
        return $this->db->runSQL($sql, [':sala' => $sala])->fetchAll();
    }
    public function getPrimeiroOculto($sala)
    {
        $sql = "SELECT * FROM mapas_lab_patrimonios 
            WHERE sala = :sala AND mostrar = '0' 
            ORDER BY id ASC LIMIT 1;";
        return $this->db->runSQL($sql, [':sala' => $sala])->fetchAll();
    }
    public function getTodasSalas()
    {
        $sql = "SELECT * FROM mapas_lab_patrimonios;";
        return $this->db->runSQL($sql)->fetchAll();
    }
    public function getModificado($sala)
    {
        $sql = "SELECT modificado FROM mapas_lab_patrimonios WHERE sala=:sala ORDER BY modificado DESC LIMIT 1;";
        return $this->db->runSQL($sql, [':sala' => $sala])->fetch();
    }
    public function getContagemElementos($sala)
    {
        $sql = "SELECT COUNT(*) FROM mapas_lab_patrimonios WHERE sala=:sala AND num NOT LIKE '-%';";
        return $this->db->runSQL($sql, [':sala' => $sala])->fetchColumn();
    }
    public function getValorMax($sala)
{
    $sql = "SELECT MAX(CAST(num AS INTEGER)) AS max_num 
            FROM mapas_lab_patrimonios 
            WHERE sala = :sala AND mostrar = true;";
    return $this->db->runSQL($sql, [':sala' => $sala])->fetch();
}
    // SETOR ADMIN 
    public function getLogs($sala)
    {
        try {
            $sql = "SELECT * FROM mapas_lab_patrimonios WHERE sala = :sala ORDER BY num ASC";
            return $this->db->runSQL($sql, [':sala' => $sala])->fetchAll();
        } catch (PDOException $e) {
            throw $e;
        }
    }
    public function insert($infos)
    {
        try {
            $sql = "INSERT INTO mapas_lab_patrimonios (num,modelo_maquina,maquina,modelo_monitor,monitor,p_rede,status_pc,status_monitor,reserva_pc,reserva_monitor,reserva_modelo_pc,reserva_modelo_monitor,obs_pc,obs_monitor,disco,sala,log,modificado,mostrar) VALUES (:num,:modelo_maquina,:maquina,:modelo_monitor,:monitor,:p_rede,:status_pc,:status_monitor,:reserva_pc,:reserva_monitor,:reserva_modelo_pc,:reserva_modelo_monitor,:obs_pc,:obs_monitor,:disco,:sala,:log,:modificado,:mostrar);";
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
    public function update($infos)
    {
        try {
            //unset($infos['num'], $infos['sala'], $infos['log']);
            $colunas = array_keys($infos);
            $setCampos = [];
            foreach ($colunas as $col) {
                if ($col !== 'id') {
                    $setCampos[] = "$col = :$col";
                }
            }
            $sql = "UPDATE mapas_lab_patrimonios SET " . implode(', ', $setCampos) . " WHERE id = :id";
            //$sql = "UPDATE mapas_lab_patrimonios SET modelo_maquina = :modelo_maquina, maquina = :maquina, modelo_monitor = :modelo_monitor, monitor = :monitor, p_rede = :p_rede, status_pc = :status_pc, status_monitor = :status_monitor, reserva_pc = :reserva_pc, reserva_monitor = :reserva_monitor, reserva_modelo_pc = :reserva_modelo_pc, reserva_modelo_monitor = :reserva_modelo_monitor, obs_pc = :obs_pc, obs_monitor = :obs_monitor, disco = :disco, modificado = :modificado WHERE id = :id";
            //var_dump($setCampos);
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
    public function updateTrocaPatrim($infos)
    {
        try {
            unset($infos['log']);

            $colunas = array_keys($infos);
            $setCampos = [];
            foreach ($colunas as $col) {
                if ($col !== 'id') {
                    $setCampos[] = "$col = :$col";
                }
            }
            $sql = "UPDATE mapas_lab_patrimonios SET " . implode(', ', $setCampos) . " WHERE id = :id";

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
            $sql = "UPDATE mapas_lab_patrimonios SET log = log || :novoLog WHERE id = :id";
            $this->db->runSQL($sql, $infos);
            return true;
        } catch (PDOException $e) {
            throw $e;
        }
    }
    public function searchCount($term)
    {
        $arguments = [
            'term1' => '%' . $term . '%',
            'term2' => '%' . $term . '%',
            'term3' => '%' . $term . '%',
            'term4' => '%' . $term . '%',
            'term5' => '%' . $term . '%'
        ];

        $sql = "SELECT COUNT(p.id)
            FROM mapas_lab_patrimonios p
            JOIN mapas_lab_salas s ON s.sala = p.sala
            WHERE s.mostrar_sala = true AND p.mostrar = true
              AND (
                  p.maquina ILIKE :term1 OR 
                  p.monitor ILIKE :term2 OR 
                  p.p_rede ILIKE :term3 OR 
                  p.reserva_pc ILIKE :term4 OR 
                  p.reserva_monitor ILIKE :term5
              );";

        return $this->db->runSQL($sql, $arguments)->fetchColumn();
    }

    public function search($term)
    {
        $arguments = [
            'term1' => '%' . $term . '%',
            'term2' => '%' . $term . '%',
            'term3' => '%' . $term . '%',
            'term4' => '%' . $term . '%',
            'term5' => '%' . $term . '%'
        ];

        $sql = "SELECT 
                p.id, p.num, p.modelo_maquina, p.maquina, p.modelo_monitor, p.monitor, 
                p.p_rede, p.disco, p.sala, p.status_pc, p.status_monitor, 
                p.reserva_pc, p.reserva_monitor, p.reserva_modelo_pc, 
                p.reserva_modelo_monitor, p.obs_pc, p.obs_monitor, p.mostrar
            FROM mapas_lab_patrimonios p
            JOIN mapas_lab_salas s ON s.sala = p.sala
            WHERE s.mostrar_sala = true AND p.mostrar = true
              AND (
                  p.maquina ILIKE :term1 OR 
                  p.monitor ILIKE :term2 OR 
                  p.p_rede ILIKE :term3 OR 
                  p.reserva_pc ILIKE :term4 OR 
                  p.reserva_monitor ILIKE :term5
              )
            ORDER BY p.num ASC;";

        return $this->db->runSQL($sql, $arguments)->fetchAll();
    }
}
