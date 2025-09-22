<?php
class Softwares
{
    protected $db;
    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    public function getPerSala($salas)
    {
        $sql = "SELECT sw.id, sw.nome, sw.tipo, sw.versao_instalada, sw.fabricante, sw.so_compativel, sw.licencas_tipo, sw.em_uso, sw.licencas_validade,
            CONCAT(ef.predio, '-', ef.bloco, '-', ef.sala) AS sala, 
            sws.qtde, sws.obs  
            FROM software_sala sws
            JOIN software sw ON sws.software = sw.id
            JOIN espaco_fisico ef ON sws.sala = ef.cod_espaco_fisico
            WHERE CONCAT(ef.predio, '-', LOWER(ef.bloco), '-', REPLACE(ef.sala::text, '.', '-')) = :sala ORDER BY sw.nome";
        return $this->db->runSQL($sql, [$salas])->fetchAll();
    }
    public function getContagemElementos($salas)
    {
        $sql = "SELECT COUNT(*) 
            FROM software_sala sws
            JOIN software sw ON sws.software = sw.id
            JOIN espaco_fisico ef ON sws.sala = ef.cod_espaco_fisico
            WHERE CONCAT(ef.predio, '-', LOWER(ef.bloco), '-', REPLACE(ef.sala::text, '.', '-')) = :sala";
        return $this->db->runSQL($sql, [$salas])->fetchColumn();
    }
}
