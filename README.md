# PHP PDO - DataLayer

Gerenciador de requisições php em banco de dados mysql utilizando PDO

## Instalação

Para instalar esta dependência basta executar o comando abaixo:
```shell
composer require victormmeloc/datalayer
```

## Iniciar

Para usar essa classe, é nessário informar o nome da tabela, chamando a variável stática, conforme abaixo:
```php
use Victormmelo\DataLayer\DataLayer;  
DataLayer::$table = 'users';  
```
## Utilização

Para usar este gerenciador basta seguir o exemplo abaixo:

#### pagination()
```php
//DEFINE AS CONFIGURAÇÕES DO BANCO DE DADOS
DataLayer\Datalayer::config(
    'localhost',
    'datalayer',
    'root',
    'pass',
    '3306'
);
```

#### pagination()
Método responsável por montar a query, fazer a consulta no banco de dados e retornar um array com os dados.  
Para invoca-lo é possível informar a quantidade de dados que serão retornados e as colunas, caso seja default, será utilizado a quantidade de 50 retornos e todas as colunas.
```php
use Victormmelo\DataLayer\DataLayer;  
DataLayer::$quantity = 5;  
DataLayer::$columns = 'first_name,last_name';  

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

#### Consulta geral no banco (GET)
```php
use Victormmelo\DataLayer\DataLayer;
DataLayer::$table = 'users';
DataLayer::$columns = 'first_name,last_name';
$teste = DataLayer::get();
$teste = DataLayer::run();
return $teste;
```

#### Consulta com filtros (GET) (WHERE)
```php
use Victormmelo\DataLayer\DataLayer;
$filter = [
    'first_name' => [
        'operator' => '=',
        'value' => 'Victor'
    ],
    'last_name' => [
        'operator' => 'LIKE',
        'value' => 'M%'
    ]
];
DataLayer::$filter = $filter;
DataLayer::$table = 'users';
DataLayer::$columns = 'first_name,last_name';
$teste = DataLayer::get();
$teste = DataLayer::run();
return $teste;
```

#### Inserir dados no banco (INSERT)
```php
use Victormmelo\DataLayer\DataLayer;
DataLayer::$table = 'users';
$request = [
    'first_name' => 'Rafael',
    'last_name' => 'Moraes Cruvinel',
    'genre' => 'M'
];
$teste = DataLayer::create($request);
```

#### Atualizar dados no banco (UPDATE)
```php
use Victormmelo\DataLayer\DataLayer;
DataLayer::$table = 'users';
$request = [
    'first_name' => 'Rafael',
    'last_name' => 'Moraes Cruvinel',
    'genre' => 'M'
];
$teste = DataLayer::update($request, $id);
```

#### Deletar dados no banco (DELETE)
```php
use Victormmelo\DataLayer\DataLayer;
DataLayer::$table = 'users';
$teste = DataLayer::delete($id);
```

#### Ver colunas da tabela (DESCRIBE)
```php
use Victormmelo\DataLayer\DataLayer;
DataLayer::$table = 'users';
$teste = DataLayer::describe();
```