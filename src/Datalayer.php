<?php

namespace DataLayer;

use PDO;
use PDOException;

class Datalayer{
    private static $connection;
    private static $host;
    private static $name;
    private static $user;
    private static $pass;
    private static $port;
    private static string $table;
    private static string $sql;

    public function __construct($table = 'users'){
        self::$table = $table;
        $this->setConnection();
    }

    public static function config($host,$name,$user,$pass,$port = 3306){
        self::$host = $host;
        self::$name = $name;
        self::$user = $user;
        self::$pass = $pass;
        self::$port = $port;
    }

    private function setConnection(){
        try{
            self::$connection = new PDO('mysql:host='.self::$host.';dbname='.self::$name.';port='.self::$port,self::$user,self::$pass);
            self::$connection->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
        }catch(PDOException $e){
            die('ERROR: '.$e->getMessage());
        }
    }

    public static function run(){
        $sql = self::$connection->query(self::$sql);
        $stmt = $sql->fetchAll();
        return $stmt;
    }

    public static function pagination(int $page = 1, int $quantity = 50, int $start = 0){
        //MONTA A QUERY INICIAL
        self::get();

        //CALCULA O TOTAL DE LINHAS SEM O LIMIT
        $quantityRows = self::$connection->query(self::$sql)->rowCount();

        //CALCULA O LIMIT
        $start = ($quantity * $page) - $quantity;

        //INSERE A QUERY DE 'LIMIT' NO SQL
        self::limit($quantity, $start);

        //BUSCA OS DADOS
        $data = self::run();

        //CALCULA A QUANTIDADE DE PAGINAS
        $quantityPages = ceil($quantityRows / $quantity);

        //CALCULA A PÁGINA ANTERIOR
        $previus = $page <= 1 ? null : $page - 1;

        //CALCULA A PRÓXIMA PÁGINA
        $next = $page >= $quantityPages ? null : $page + 1;

        //CALCULA O FIM
        $end = $start + $quantity < $quantityRows ? $start + $quantity : $quantityRows;

        //MONTA O OBJETO DE RETORNO
        $arr = [
            'previus' => $previus,
            'next' => $next,
            'current' => $page,
            'quantityRows' => $quantityRows,
            'quantityPages' => $quantityPages,
            'start' => $start + 1,
            'end' => $end,
            'data' => $data,
        ];
            
        //QUANTAS PAGINAS TEM A PESQUISA
        return $arr;
    }

    public static function limit(int $quantity = 50, int $start = 0){
        self::$sql = self::$sql . " LIMIT " . $start . "," . $quantity;
        return;
    }
    
    public static function get(array $filter = [], string $columns = '*'){
        $sql = "SELECT $columns FROM " .self::$table;
        if($filter){
            $sql .= " WHERE ";
            foreach ($filter as $key => $value) {
                $value['operator'] = strtoupper($value['operator']) == 'LIKE' ? ' LIKE ' : $value['operator'];
                if(gettype($value['value']) == 'integer' || gettype($value['value']) == 'double'){
                    $sql .= $key . $value['operator'] . $value['value'] . " AND ";
                } else {
                    $sql .= $key . $value['operator'] . "'" . $value['value'] . "' AND ";
                }
                
            }
            $sql = rtrim($sql, ' AND ');
        }
        self::$sql = $sql;
        return;
    }

    public static function getId($id){
        $sql = self::$connection->query("SELECT * FROM ".self::$table." WHERE id=$id");
        $stmt = $sql->fetch();
        return $stmt;
    }

    public static function create($data){
        $keys = '';
        $values = '';
        foreach ($data as $key => $value) {
            $keys .= $key . ',';
            $values .= '"' . $value . '",';
        }
        $keys = rtrim($keys, ',');
        $values = rtrim($values, ',');
        
        $sql = "INSERT INTO ".self::$table." ($keys) VALUES ($values)";
        self::$connection->exec($sql);
        return self::$connection->lastInsertId();
    }

    public static function update($data, $id){
        $dataUpdated = '';
        foreach ($data as $key => $value) {
            $dataUpdated .= $key . '="' . $value . '",';
        }
        $dataUpdated = rtrim($dataUpdated, ',');
        
        $sql = "UPDATE ".self::$table." SET $dataUpdated WHERE id=$id";
        self::$connection->exec($sql);
        return $id;
    }

    public static function delete($id){
        $resposta = [];
        $verificaId = self::getId($id);
        if(!$verificaId){
            $resposta = [
                'status' => 404,
                'mensagem' => 'Não foi possível encontrar esse id no banco de dados'
            ];

            throw new \Exception(json_encode($resposta, JSON_UNESCAPED_UNICODE), 404);
            return;
        }
        
        $sql = "DELETE FROM ".self::$table." WHERE id=$id";
        self::$connection->exec($sql);
        $resposta = [
            'status' => 200,
            'mensagem' => 'O item foi excluído com sucesso'
        ];
        return json_encode($resposta, JSON_UNESCAPED_UNICODE);
    }

    public static function describe(){
        $table = self::$table;
        $connection = self::$connection;
        $sql = $connection->query("DESCRIBE $table");
        $stmt = $sql->fetchAll();
        return $stmt;
    }
}