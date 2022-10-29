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

    public function __construct(){
        $this->setConnection();
    }

    public static function config($host,$name,$user,$pass,$port = 3306){
        self::$host = $host;
        self::$name = $name;
        self::$user = $user;
        self::$pass = $pass;
        self::$port = $port;
    }

    public static function setTable($table){
        self::$table = $table;
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

    public static function pagination(int $page = 1, int $quantity = 50, array $filter = [], string $columns = '*'){
        //MONTA A QUERY INICIAL
        self::get($filter,$columns);

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

    /**
     * Insere na query a parte do LIMIT
     * @param int $quantity - a quantidade de linhas que serão retornadas
     * @param int $start - a linha de início do limit
     */
    public static function limit(int $quantity = 50, int $start = 0){
        self::$sql = self::$sql . " LIMIT " . $start . "," . $quantity;
        return;
    }
    
    /**
     * Monta a query
     * @param array $filter - as informações das cláusulas WHERES que quer adicionar
     * @param string $columns - as colunas que deseja retornar
     */
    public static function get(array $filter = [], string $columns = '*'){
        // INICIAR A QUERY
        $sql = "SELECT $columns FROM " .self::$table;
        
        // VERIFICA SE TEM FILTRO PARA INSERIR NA QUERY
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

            // REMOVE DO FIM DA QUERY A STRING ' AND '
            $sql = rtrim($sql, ' AND ');
        }

        // ATUALZIA A VARIAVEL
        self::$sql = $sql;
        return;
    }

    /**
     * Busca o primeiro registro no banco de dados de acordo com a coluna e o valor requerido
     * @param string $filter
     * @param string $column
     * @return array Dados do banco
     */
    public static function find(string $filter,string $column = 'id'){
        //VERIFICA SE O FILTER FOI INFORMADO, POIS ELE É OBRIGATÓRIO
        if(!$filter){ return; }

        //VERIFICA SE O FILTER É UM NUMERO, CASO CONTRÁRIO A QUERY TERA ASPAS SIMPLES ENVOLVENDO A VARIAVEL
        if(gettype($filter) == 'integer' or gettype($filter) == 'double'){
            $sql = self::$connection->query("SELECT * FROM ".self::$table." WHERE $column=$filter");
        } else {
            $sql = self::$connection->query("SELECT * FROM ".self::$table." WHERE $column='$filter'");
        }
        return $sql->fetch();
    }

    /**
     * Insere um dado no banco
     * @param array $request - um array com as chaves e valores a serem inseridos no banco
     * @return int $id
     */
    public static function create($request){
        // INICIA AS VARIAVEIS
        $keys = '';
        $values = '';

        // PREENCHE AS VARIVEIS DE CHAVE E VALOR
        foreach ($request as $key => $value) {
            $keys .= $key . ',';
            $values .= '"' . $value . '",';
        }

        // REMOVE AS STRINGS DO FIM DAS VARIAVEIS
        $keys = rtrim($keys, ',');
        $values = rtrim($values, ',');
        
        // MONTA A QUERY
        $sql = "INSERT INTO ".self::$table." ($keys) VALUES ($values)";
        
        // EXECUTA A QUERY
        self::$connection->exec($sql);

        //RETORNA O ULTIMO A ID INSERIDO
        return self::$connection->lastInsertId();
    }

    /**
     * Atualiza um dado no banco
     * @param array $request - um array com as chaves e valores a serem inseridos no banco
     * @param int $id
     * @return int $id
     */
    public static function update($request, $id){
        // INICIA A VARIAVEL
        $resposta = [];

        // CONSULTA O ID NO BANCO
        $verificaId = self::find($id);

        // VERIFICA SE O ID EXISTE NO BANCO
        if(!$verificaId){
            $resposta = [
                'status' => 404,
                'mensagem' => 'Não foi possível encontrar esse id no banco de dados'
            ];

            throw new \Exception(json_encode($resposta, JSON_UNESCAPED_UNICODE), 404);
            return;
        }

        // INICIA A VARIAVEL
        $dataUpdated = '';

        // PREENCHE A VARIAVEL DE ACORDO COM O REQUEST
        foreach ($request as $key => $value) {
            $dataUpdated .= $key . '="' . $value . '",';
        }

        // REMOVE DO FIM DA STRING A VIRGULA
        $dataUpdated = rtrim($dataUpdated, ',');

        // MONTA A QUERY
        $sql = "UPDATE ".self::$table." SET $dataUpdated WHERE id=$id";

        // EXECUTA A QUERY
        self::$connection->exec($sql);

        // MONTA O ARRAY COM A RESPOSTA DE SUCESSO
        $resposta = [
            'status' => 200,
            'mensagem' => 'O item foi atualizado com sucesso'
        ];

        // RETORNA A RESPOSTA
        return $resposta;
    }

    public static function delete($id){
        // INICIA A VARIAVEL
        $resposta = [];

        // CONSULTA O ID NO BANCO
        $verificaId = self::find($id);

        // VERIFICA SE O ID EXISTE NO BANCO
        if(!$verificaId){
            $resposta = [
                'status' => 404,
                'mensagem' => 'Não foi possível encontrar esse id no banco de dados'
            ];

            throw new \Exception(json_encode($resposta, JSON_UNESCAPED_UNICODE), 404);
            return;
        }
        
        // MONTA A QUERY
        $sql = "DELETE FROM ".self::$table." WHERE id=$id";
        
        // EXECUTA A QUERY
        self::$connection->exec($sql);

        // MONTA O ARRAY COM A RESPOSTA DE SUCESSO
        $resposta = [
            'status' => 200,
            'mensagem' => 'O item foi excluído com sucesso'
        ];

        // RETORNA A RESPOSTA
        return $resposta;
    }

    public static function describe(){
        // FAZ A CONSULTA NO BANCO DE DADOS
        $sql = self::$connection->query("DESCRIBE ".self::$table);
        
        // RETORNA A CONSULTA
        return $sql->fetchAll();
    }
}