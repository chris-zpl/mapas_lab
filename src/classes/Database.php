<?php
class Database extends PDO
{
    protected $connected = false;
    public function __construct($dsn, $username, $password, $options = [])
    {
        $default_options = [
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ];
        $options = array_replace($default_options, (array)$options);
        try {
            parent::__construct($dsn, $username, $password, $options);
            $this->connected = true;
        } catch (Exception  $e) {
            $this->connected = false;
        }
    }
    public function runSQL($sql, $arguments = null)
    {
        // Caso não haja conexão, lança o erro na tela
        if(!$this->connected){
            throw new Exception("Conexão não estabelecida. Operação abortada.");
        }

        if (!$arguments) {
            return $this->query($sql);
        }

        $stmt = $this->prepare($sql);

        $is_named = false;
        foreach ($arguments as $key => $_) {
            if (is_string($key)) {
                $is_named = true;
                break;
            }
        }

        if ($is_named) {
            // Parâmetros nomeados: :campo
            foreach ($arguments as $key => $value) {
                switch (true) {
                    case is_bool($value):
                        $paramType = PDO::PARAM_BOOL;
                        break;
                    case is_int($value):
                        $paramType = PDO::PARAM_INT;
                        break;
                    case is_null($value):
                        $paramType = PDO::PARAM_NULL;
                        break;
                    default:
                        $paramType = PDO::PARAM_STR;
                        break;
                }
                $stmt->bindValue($key, $value, $paramType);
            }
        } else {
            // Parâmetros posicionais: ?
            foreach (array_values($arguments) as $index => $value) {
                switch (true) {
                    case is_bool($value):
                        $paramType = PDO::PARAM_BOOL;
                        break;
                    case is_int($value):
                        $paramType = PDO::PARAM_INT;
                        break;
                    case is_null($value):
                        $paramType = PDO::PARAM_NULL;
                        break;
                    default:
                        $paramType = PDO::PARAM_STR;
                        break;
                }
                $stmt->bindValue($index + 1, $value, $paramType); // +1 é essencial aqui!
            }
        }

        $stmt->execute();
        return $stmt;
    }
}
