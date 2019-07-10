**This package is abandoned. Consider upgrading to [bentools/where](https://github.com/bpolaszek/where), a very easy way to generate conditions and full queries on the fly.**

MySkewHell - Tuple
==================

An intuitive way to work with MySql prepared statements. 

Use the PHP array syntax to dynamically add conditions on a SQL query.

Each value will be transfomed to a placeholder ; you can then bind each placeholder to its value.

## Building a query : working with placeholders and values

#### positional placeholders (default)

```php
$sql        =   "SELECT * FROM users WHERE 1" . PHP_EOL;
$tuples     =   new TupleANDWrapper();
$tuples[]   =   ['nickname' => 'johndoe'];
$tuples[]   =   ['firstname', '=', 'John'];
$tuples[]   =   ['lastname', 'LIKE', 'Doe'];
$tuples[]   =   new TupleORWrapper([
                                    ['active' => true], 
                                    ['date_registered', 'BETWEEN', ['2015-01-01', '2015-01-30']]
                                    ]);
$tuples[]   =   ['id_group', 'NOT IN', [5, 8, 15]];
$tuples[]   =   ['nb_logins', '>=', 2];
$tuples[]   =   "lastloggedin <= CURRENT_DATE - INTERVAL 1 MONTH";

$sql        .=  'AND ' . $tuples;

var_dump($sql);
```

Output :

```sql
SELECT *
    FROM users
WHERE 1
    AND (
          `nickname` = ?
          AND `firstname` = ?
          AND `lastname` LIKE ?
          AND (
                (`active` = ?  
                  OR `date_registered` BETWEEN ? AND ?)
              )
          AND `id_group` NOT IN (?, ?, ?)
          AND `nb_logins` >= ?
          AND lastloggedin <= CURRENT_DATE - INTERVAL 1 MONTH
        )
```

```php
var_dump($tuples->getPlaceHolders());
```

Output : 
```
array (size=10)
  0 => string '?' (length=1)
  1 => string '?' (length=1)
  2 => string '?' (length=1)
  3 => string '?' (length=1)
  4 => string '?' (length=1)
  5 => string '?' (length=1)
  6 => string '?' (length=1)
  7 => string '?' (length=1)
  8 => string '?' (length=1)
  9 => string '?' (length=1)
```

```php
var_dump($tuples->getValues());
```

Output :
```
array (size=10)
  0 => string 'johndoe' (length=7)
  1 => string 'John' (length=4)
  2 => string 'Doe' (length=3)
  3 => boolean true
  4 => string '2015-01-01' (length=10)
  5 => string '2015-01-30' (length=10)
  6 => int 5
  7 => int 8
  8 => int 15
  9 => int 2
```

Query preview :

```sql
SELECT *
  FROM users
WHERE 1
    AND (
          `nickname` = 'johndoe'
          AND `firstname` = 'John'
          AND `lastname` LIKE 'Doe'
          AND (
                (`active` = 1
                  OR `date_registered` BETWEEN '2015-01-01' AND '2015-01-30')
                )
          AND `id_group` NOT IN (5, 8, 15)
          AND `nb_logins` >= 2
          AND lastloggedin <= CURRENT_DATE - INTERVAL 1 MONTH
      )

```

#### Named placeholders

```php
$sql        =   "SELECT * FROM users WHERE 1" . PHP_EOL;
$tuples     =   new TupleANDWrapper();
$tuples[]   =   ['nickname' => 'johndoe'];
$tuples[]   =   ['firstname', '=', 'John'];
$tuples[]   =   ['lastname', 'LIKE', 'Doe'];
$tuples[]   =   new TupleORWrapper([
                                    ['active' => true], 
                                    ['date_registered', 'BETWEEN', ['2015-01-01', '2015-01-30']]]
                );
$tuples[]   =   ['id_group', 'NOT IN', [5, 8, 15]];
$tuples[]   =   ['nb_logins', '>=', 2];
$tuples[]   =   "lastloggedin <= CURRENT_DATE - INTERVAL 1 MONTH";

// Same code here : just specify we want to use named placeholders
$tuples     ->  useNamedPlaceholders(true);

$sql        .=  'AND ' . $tuples;

var_dump($sql);
```

Output :

```sql
SELECT *
  FROM users
WHERE 1
  AND (
        `nickname` = :nickname
        AND `firstname` = :firstname
        AND `lastname` LIKE :lastname
        AND (
              (`active` = :active
                OR `date_registered` BETWEEN :date_registered1 AND :date_registered2)
        )
        AND `id_group` NOT IN (:id_group1, :id_group2, :id_group3)
        AND `nb_logins` >= :nb_logins
        AND lastloggedin <= CURRENT_DATE - INTERVAL 1 MONTH
      )
```


```php
var_dump($tuples->getPlaceHolders());
```

Output :

```
array (size=10)
  0 => string ':nickname' (length=9)
  1 => string ':firstname' (length=10)
  2 => string ':lastname' (length=9)
  3 => string ':active' (length=7)
  4 => string ':date_registered1' (length=17)
  5 => string ':date_registered2' (length=17)
  6 => string ':id_group1' (length=10)
  7 => string ':id_group2' (length=10)
  8 => string ':id_group3' (length=10)
  9 => string ':nb_logins' (length=10)
```


```php
var_dump($tuples->getValues());
```

Output :

```
array (size=10)
  'nickname' => string 'johndoe' (length=7)
  'firstname' => string 'John' (length=4)
  'lastname' => string 'Doe' (length=3)
  'active' => boolean true
  'date_registered1' => string '2015-01-01' (length=10)
  'date_registered2' => string '2015-01-30' (length=10)
  'id_group1' => int 5
  'id_group2' => int 8
  'id_group3' => int 15
  'nb_logins' => int 2
```

Query preview :

```sql
SELECT *
  FROM users
WHERE 1
    AND (
          `nickname` = 'johndoe'
          AND `firstname` = 'John'
          AND `lastname` LIKE 'Doe'
          AND (
                (`active` = 1
                  OR `date_registered` BETWEEN '2015-01-01' AND '2015-01-30')
                )
          AND `id_group` NOT IN (5, 8, 15)
          AND `nb_logins` >= 2
          AND lastloggedin <= CURRENT_DATE - INTERVAL 1 MONTH
      )
```


## Examples with PDO

#### Using positional placeholders
```php
$pdo        =   new PDO("mysql:host=localhost;dbname=mydb", "username", "password");
$stmt       =   $pdo->prepare($sql);

foreach ($tuples AS $key => $value)
    $stmt->bindValue(++$key, $value);

$stmt->execute();
var_dump($stmt->fetchAll(PDO::FETCH_ASSOC));
```

#### Using named placeholders
```php
$pdo        =   new PDO("mysql:host=localhost;dbname=mydb", "username", "password");
$stmt       =   $pdo->prepare($sql);

foreach ($tuples AS $key => $value)
    $stmt->bindValue(sprintf(':%s', $key), $value);

$stmt->execute();
var_dump($stmt->fetchAll(PDO::FETCH_ASSOC));
```

## Examples with [PDOExtended][1]

#### Using positional placeholders
```php
$pdo        =   new PDOExtended("mysql:host=localhost;dbname=mydb", "username", "password");
$stmt       =   $pdo->prepare($sql);
var_dump($stmt->sqlArray($tuples->getValues()));
```

#### Using named placeholders
```php
$pdo        =   new PDOExtended("mysql:host=localhost;dbname=mydb", "username", "password");
$stmt       =   $pdo->prepare($sql);
var_dump($stmt->sqlArray($tuples->getValues()));
```

## Operators
 
####  Comparison operators
    
```php
['my_column', '=', 'my_string']; // will output "`my_column` = ?"
['my_column', 'LIKE', 'my_string%']; // will output "`my_column` LIKE ?"
['my_column', '>=', 2]; // will output "`my_column` >= ?"
['my_column', '>', 2]; // will output "`my_column` > ?"
['my_column', '<=', 2]; // will output "`my_column` <= ?"
['my_column', '<', 2]; // will output "`my_column` < ?"
['my_column', '<>', 2]; // will output "`my_column` <> ?"
['my_column', '!=', 'my_string]; // will output "`my_column` != ?"
```
 
####  Simple range operator
    
```php
['my_column', 'BETWEEN', ['2015-01-01', '2015-01-30']]; // will output "`my_column` BETWEEN ? AND ?"
```
 
####  Multiple range operators
    
```php
['my_column', 'IN', [5, 3]]; // will output "`my_column` IN (?, ?)"
['my_column', 'NOT IN', [8, 6, 9, 12]]; // will output "`my_column` NOT IN (?, ?, ?, ?)"
```
 
####  Shortcut
    
```php
['my_column' => 'my_string']; // will output "`my_column` = ?"
```

## Wrappers

#### "AND" wrapper

    
```php
$tuples     =   new TupleANDWrapper();
$tuples[]   =   ['my_column', 'IN', [5, 3]]; // will output "`my_column` IN (?, ?)"
$tuples[]   =   ['my_column', 'NOT IN', [8, 6, 9, 12]]; // will output "`my_column` NOT IN (?, ?, ?, ?)"
var_dump((string) $tuples)); // will output "(`my_column` IN (?, ?) AND `my_column` NOT IN (?, ?, ?, ?))"
```

#### "OR" wrapper

    
```php
$tuples     =   new TupleORWrapper();
$tuples[]   =   ['my_column', 'IN', [5, 3]]; // will output "`my_column` IN (?, ?)"
$tuples[]   =   ['my_column', 'NOT IN', [8, 6, 9, 12]]; // will output "`my_column` NOT IN (?, ?, ?, ?)"
var_dump((string) $tuples)); // will output "(`my_column` IN (?, ?) OR `my_column` NOT IN (?, ?, ?, ?))"
```

#### "String" wrapper

```php
$tuples     =   new TupleORWrapper();
$tuples[]   =   ['my_column', 'BETWEEN', ['2015-01-01', '2015-01-30']]
$tuples[]   =   "`another_column` >= CURRENT_DATE - INTERVAL 3 DAY"
var_dump((string) $tuples)); // will output "(`my_column` BETWEEN ? AND ? OR `another_column` >= CURRENT_DATE - INTERVAL 3 DAY"
```

Installation
============

Add the following line into your composer.json :

    {
        "require": {
            "myskewhell/tuple": "dev-master"
        }
    }  
    
Enjoy.

[1]: https://github.com/bpolaszek/bentools-pdoextended
