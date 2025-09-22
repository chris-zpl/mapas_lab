<?php
class Validate
{
    public static function is_sala_valida($info, $salas_validas)
    {
        return array_key_exists($info, $salas_validas);
    }
    public static function is_status_valido($info)
    {
        $array = ['funcionando', 'manutencao', 'defeito', 'reserva'];
        foreach ($array as $valor) {
            if ($info === $valor) {
                return true;
            }
        }
        return false;
    }
    public static function is_texto($texto, $min = 0, $max = 1000)
    {
        $length = mb_strlen($texto);
        return ($length >= $min and $length <= $max);
    }
    public static function is_disco_valido($info)
    {
        $array = ['nenhum', 'ssd250', 'ssd480', 'ssd500', 'ssd1000', 'hd250', 'hd320', 'hd500', 'hd1000'];
        foreach ($array as $valor) {
            if ($info === $valor) {
                return true;
            }
        }
        return false;
    }
    public static function is_numero($number, $min = 0, $max = 50)
    {
        return ($number >= $min and $number <= $max);
    }
}
