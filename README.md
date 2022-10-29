# PHP PDO - Datalayer

Gerenciador de requisições php em banco de dados mysql utilizando PDO

## Instalação

Para instalar esta dependência basta executar o comando abaixo:
```shell
composer require victormmeloc/datalayer
```

## Iniciar

Para usar essa classe, é nessário informar o nome da tabela, chamando a variável stática, conforme abaixo:
```php
// INSERE O AUTOLOAD
require __DIR__.'/vendor/autoload.php';

use DataLayer\Datalayer;

// DEFINE A TABELA
Datalayer::setTable('users');  
```
## Utilização

Para usar este gerenciador basta seguir o exemplo abaixo:

#### configurar dados do banco
```php
//DEFINE AS CONFIGURAÇÕES DO BANCO DE DADOS
Datalayer::config(
    'localhost',
    'datalayer',
    'root',
    'pass',
    '3306'
);
new Datalayer;
```

#### pagination()
Método responsável por montar a query, fazer a consulta no banco de dados e retornar um array com os dados.  
Para invoca-lo é possível informar a quantidade de dados que serão retornados e as colunas, caso seja default, será utilizado a quantidade de 50 retornos e todas as colunas.
```php
Datalayer::pagination($page,$quantity,$filter,$columns);

// RETORNO
$arr = [
    'previus' => página anterior, caso não tenha retornará null,
    'next' => próxima página, caso não tenha retornará null,
    'current' => página atual,
    'quantityRows' => quantidade de linhas gerais da consulta,
    'quantityPages' => quantidade de páginas,
    'start' => linha inicial,
    'end' => linha final,
    'data' => objeto com os dados,
];
```
```php
// PARAMETROS

/**
 * PAGE
 * a página que deseja fazer a pesquisa
 * @param int $page
 * valor default é '1'
 */
$page = 1;

/**
 * QUANTITY
 * a quantidade de retornos por página
 * @param int $quantity
 * valor default é '50'
 */
$quantity = 50;

/**
 * FILTER
 * os filtros da consulta. Esse filtro representa a cláusula WHERE da consultar
 * @param array $filter
 * valor default é '[]'
 */
$filter = [
    'name' => [
        'operator' => '=',
        'value' => 'Victor'
    ],
    'email' => [
        'operator' => 'LIKE',
        'value' => 'M%'
    ]
];

/**
 * COLUMNS (opcional)
 * as colunas que deseja retornar
 * @param string $columns
 * valor default é '*'
 */
$columns = 'name,email';
```

#### GET
```php
Datalayer::get(array $filter = [], string $columns = '*');
$data = Datalayer::run();
return $data;
```
```php
// PARAMETROS
/**
 * FILTER
 * os filtros da consulta. Esse filtro representa a cláusula WHERE da consultar
 * @param array $filter
 * valor default é '[]'
 */
$filter = [
    'name' => [
        'operator' => '=',
        'value' => 'Victor'
    ],
    'email' => [
        'operator' => 'LIKE',
        'value' => 'M%'
    ]
];

/**
 * COLUMNS (opcional)
 * as colunas que deseja retornar
 * @param string $columns
 * valor default é '*'
 */
$columns = 'name,email';
```

#### CREATE
```php
$request = [
    'nome' => 'Primeiro',
    'email' => 'Segundo Sobrenome',
];

$id = Datalayer::create($request);
```

#### UPDATE
```php
$request = [
    'first_name' => 'Raul',
    'last_name' => 'da Silva Sauro',
    'genre' => 'M'
];
Datalayer::update($request, $id);
```

#### DELETE
```php
Datalayer::delete($id);
```

#### DESCRIBE  
Retornar as colunas da tabela
```php
$columns = Datalayer::describe();
```