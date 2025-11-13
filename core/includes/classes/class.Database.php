<?php

namespace InuHa;

use PDO;
use Exception;
use PDOException;
use PDOStatement;

class Database {

    protected $pdo;
    protected $statement;
    public $error = null;

    protected $debugMode = false;

    private $options = [
        'host' => null,
        'username' => null,
        'password' => null,
        'dbname' => null,
        'port' => 3306,
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_general_ci',
        'prefix' => null
    ];

    protected $id = 0;

    public const CONDITIONS = ['GROUP', 'HAVING', 'ORDER', 'LIMIT', 'LIKE'];

    public function __construct($options = null)
    {
        if(is_array($options))
        {
            $this->options = array_merge($this->options, array_change_key_case($options));
        } else if(is_object($options) && $options instanceof PDO)
        {
            $this->pdo = $options;
        } else
        {
            //throw new PDOException('Incorrect connection options database.');
            return;
        }

        if($this->options['prefix'] == "")
        {
            $this->options['prefix'] = '';
        }
        $this->connection();
    }

    public function connection()
    {
        $dsn = [
            'mysql:host='.$this->options['host'],
            'dbname='.$this->options['dbname'],
            'port='.$this->options['port']
        ];

        $command = null;

        if($this->options['charset'])
        {
            $command = "SET NAMES '".$this->options['charset']."'".($this->options['collation'] ? " COLLATE '".$this->options['collation']."'" : '');
        }

        try {

            $this->pdo = new PDO(implode(';', $dsn), $this->options['username'], $this->options['password']);

            $this->pdo->setAttribute(
                PDO::ATTR_ERRMODE,
                isset($this->options['error']) && in_array($this->options['error'], [
                    PDO::ERRMODE_SILENT,
                    PDO::ERRMODE_WARNING,
                    PDO::ERRMODE_EXCEPTION
                ]) ? $this->options['error'] : PDO::ERRMODE_SILENT
            );

            $this->pdo->setAttribute(
                PDO::ATTR_DEFAULT_FETCH_MODE,
                isset($this->options['fetch_mode']) && in_array($this->options['fetch_mode'], [
                    PDO::FETCH_ASSOC,
                    PDO::FETCH_BOTH,
                    PDO::FETCH_BOUND,
                    PDO::FETCH_CLASS,
                    PDO::FETCH_INTO,
                    PDO::FETCH_LAZY,
                    PDO::FETCH_NUM,
                    PDO::FETCH_OBJ
                ]) ? $this->options['fetch_mode'] : PDO::FETCH_ASSOC
            );


            if($command)
            {
                $this->pdo->exec($command);
            }
        } catch (PDOException $e){
            throw new PDOException($e->getMessage());
        }
    }

    public function disconnect(){
        $this->pdo = null;
    }

    public function has($table = "", $where = null, $map = null)
    {

        if($map)
        {
            $this->buildMap($map);
        }

        if (preg_match('/^[\p{L}_][\p{L}\p{N}@$#\-_]*(\.?[\p{L}_][\p{L}\p{N}@$#\-_]*)?$/u', $table)) {

            $statement =  "SELECT COUNT(*) FROM {$this->tableQuote($table)}";

        } else {
            $statement = $this->buildRaw($table);
        }
        
        if($where != "")
        {
            $statement .= $this->buildWhere($where, $map);
        }

        $query = $this->exec($statement, $map);
        if (!$this->statement){
            return false;
        }


        $result = $query->fetchColumn();

        return $result > 0;
    }

    public function count($table = "", $where = null, $map = null)
    {
        if($map)
        {
            $this->buildMap($map);
        }

        if (preg_match('/^[\p{L}_][\p{L}\p{N}@$#\-_]*(\.?[\p{L}_][\p{L}\p{N}@$#\-_]*)?$/u', $table)) {

            $statement =  "SELECT COUNT(*) FROM {$this->tableQuote($table)}";

        } else {

            $statement = $this->buildRaw($table);
        }


        if($where != "")
        {
            $statement .= $this->buildWhere($where, $map);
        }


        $query = $this->exec($statement, $map);
        if (!$this->statement){
            return 0;
        }

        return $query->fetchColumn();
    }

    public function mapKey($group = null)
    {   
        $this->id++;
        return ":mapKey".$this->id."_".$group;
    }

    public function get($table = "", $columns = null, $where = null, $map = null)
    {
        if($map)
        {
            $this->buildMap($map);
        }
        if (preg_match('/^[\p{L}_][\p{L}\p{N}@$#\-_]*(\.?[\p{L}_][\p{L}\p{N}@$#\-_]*)?$/u', $table)) {
            $columns = $this->buildColumn($columns);

            $statement =  "SELECT ".$columns." FROM {$this->tableQuote($table)}";

        } else {
            $map = $where;
            $where = $columns;
            $statement = $this->buildRaw($table);  
        }

        if($where != "")
        {
            if(is_array($where)){
                $where['LIMIT'] = 1;
            }

            $statement .= $this->buildWhere($where, $map);
            
        }


        $query = $this->exec($statement, $map);
        if (!$this->statement){
            return false;
        }

        return $query->fetch(PDO::FETCH_ASSOC);
    }

    public function select($table = "", $columns = null, $where = null, $map = null)
    {
        if($map)
        {
            $this->buildMap($map);
        }

        if (preg_match('/^[\p{L}_][\p{L}\p{N}@$#\-_]*(\.?[\p{L}_][\p{L}\p{N}@$#\-_]*)?$/u', $table)) {
            $columns = $this->buildColumn($columns);

            $statement =  "SELECT ".$columns." FROM {$this->tableQuote($table)}";

        } else {
            $map = $where;
            $where = $columns;
            $statement = $this->buildRaw($table);
        }

        if($where != "")
        {
            $statement .= $this->buildWhere($where, $map);
        }


        $query = $this->exec($statement, $map);
        if (!$this->statement){
            return [];
        }

        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function union(...$query_data)
    {
        $query_data = array_map(fn($q) => $q + [
            'table' => '',
            'columns' => null,
            'where' => null,
            'map' => null
        ], $query_data);

        $statements = [];
        $map = [];
        foreach($query_data as $data) {
            if($data['map'])
            {
                $this->buildMap($data['map']);
            }

            if (preg_match('/^[\p{L}_][\p{L}\p{N}@$#\-_]*(\.?[\p{L}_][\p{L}\p{N}@$#\-_]*)?$/u', $data['table'])) {
                $columns = $this->buildColumn($data['columns']);
                $statement =  "SELECT ".$columns." FROM {$this->tableQuote($data['table'])}";
            } else {
                $data['map'] = $data['where'];
                $data['where'] = $data['columns'];
                $statement = $this->buildRaw($data['table']);
            }

            if($data['where'] != "")
            {
                $statement .= $this->buildWhere($data['where'], $data['map']);
            }
            $statements[] = "($statement)";
            $map = array_merge($map, $data['map']);
        }

        $union_statement = implode(' UNION ', $statements);
        $query = $this->exec($union_statement, $map);
        if (!$this->statement){
            return [];
        }

        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function delete($table = "", $where = null, $map = null)
    {
        $statement =  "DELETE FROM {$this->tableQuote($table)}";

        if($where != "")
        {
            $statement .= $this->buildWhere($where, $map);
        }

        $query = $this->exec($statement, $map);
        if (!$this->statement){
            return 0;
        }

        return $query->rowCount();
    }

    public function update($table = "", $data = [], $where = null, $map = [])
    {
        $stack = [];

        if($map)
        {
            $this->buildMap($map);
        }

        if (preg_match('/^[\p{L}_][\p{L}\p{N}@$#\-_]*(\.?[\p{L}_][\p{L}\p{N}@$#\-_]*)?$/u', $table)) {
            if(!$data){
                return false;
            }

            foreach($data as $key => $value) {

                $isIndex = is_int($key);
                if(preg_match("/^(?:\s+)?\[RAW\](?:\s+)?(.*)$/", $isIndex ? $value : $key, $raw))
                {
                    if($isIndex && !is_array($value))
                    {
                        $value = [];
                    }
    
                    $stack[] = "{$this->buildRaw($raw[1], $map, $value)}";
                    continue;
                }
                
                $column = $this->columnQuote(preg_replace("/(\s*\[(\+|\-|\*|\/)\]$)/", '', $key));
                $type = gettype($value);

                preg_match('/(?<column>[\p{L}_][\p{L}\p{N}@$#\-_]+)(\s*)(\[(?<operator>\+|\-|\*|\/)\])?/u', $key, $match);
                if (isset($match['operator'])) {
                    if (is_numeric($value)) {
                        $stack[] = "{$column} = {$column} {$match['operator']} {$value}";
                    }
                } else {

                    $mapKey = $this->mapKey();
                    
                    $stack[] = "{$column} = {$mapKey}";

                    if($type === 'array'){
                        $value = json_encode($value);
                        $type = 'string';
                    } else if($type === 'object'){
                        $value = serialize($value);
                        $type = 'string';
                    }

                    $map[$mapKey] = $this->typeMap($value, $type);
                }
            }

            $statement = "UPDATE {$this->tableQuote($table)} SET " . implode(', ', $stack);
        } else {
            $map = $where;
            $where = $data;
            $statement = $this->buildRaw($table);
        }

        if($where){
            $statement .= $this->buildWhere($where, $map);
        }


        $query = $this->exec($statement, $map);
        
        if (!$this->statement){
            return false;
        }

        return $query->rowCount();
    }

    public function insert($table = "", $values = [])
    {
        $stack = [];
        $columns = [];
        $map = [];

        if(!$values){
            return false;
        }

        if (!isset($values[0])) {
            $values = [$values];
        }

        foreach ($values as $data) {
            foreach ($data as $key => $value) {
                $columns[] = $key;
            }
        }

        $columns = array_unique($columns);

        foreach ($values as $data) {
            $values = [];
            foreach ($columns as $key) {
                $value = isset($data[$key]) ? $data[$key] : null;
                $type = gettype($value);

                $mapKey = $this->mapKey();
                $values[] = $mapKey;

                if($type === 'array'){
                    $value = json_encode($value);
                    $type = 'string';
                } else if($type === 'object'){
                    $value = serialize($value);
                    $type = 'string';
                }

                $map[$mapKey] = $this->typeMap($value, $type);
            }
            $stack[] = '(' . implode(', ', $values) . ')';
        }

        $statement = "INSERT INTO {$this->tableQuote($table)} (`".implode('`, `', $columns)."`) VALUES ".implode(', ', $stack);

        $query = $this->exec($statement, $map);
        
        if (!$this->statement){
            return false;
        }

        return $query->rowCount();
    }

    public function buildColumn($columns = null){
        if(is_null($columns)){
            return '*';
        }
        
        if(is_array($columns)){
            if(count($columns) > 1){
                $columns = implode(', ', array_map([$this, 'columnQuote'], $columns));
            } else {
                $columns = $this->columnQuote($columns[0]);
            }
        } else {
            $columns = $this->columnQuote($columns);
        }

        return $columns;
    }

    protected function dataImplode($data, &$map, $conjunctor)
    {
        $stack = [];

        foreach ($data as $key => $value) {

            $type = gettype($value);

            if ($type === 'array' && preg_match("/^(AND|OR)(\s+#.*)?$/", $key, $relationMatch))
            {
                $stack[] = '(' . $this->dataImplode($value, $map, ' ' . $relationMatch[1]) . ')';
                continue;
            }

            
            $isIndex = is_int($key);

            if(preg_match("/^(?:\s+)?\[RAW\](?:\s+)?(.*)$/", $isIndex ? $value : $key, $raw))
            {
                if($isIndex && !is_array($value))
                {
                    $value = [];
                }

                $stack[] = "{$this->buildRaw($raw[1], $map, $value)}";
                continue;
            }

            $mapKey = $this->mapKey();

            preg_match(
                '/([\p{N}\p{L}\-_\.]+)(\[(?<operator>\>\=?|\<\=?|\!|\<\>|\>\<|\!?~|REGEXP|NOTREGEXP)\])?([\p{N}\p{L}\-_\.]+)?/u',
                $isIndex ? $value : $key,
                $match
            );

            $column = $this->columnQuote($match[1]);
            $operator = isset($match['operator']) ? $match['operator'] : null;

            if ($isIndex && isset($match[4]) && in_array($operator, ['>', '>=', '<', '<=', '=', '!='])) {
                $stack[] = $column . ' ' . $operator . ' ' . $this->columnQuote($match[4]);
                continue;
            }

            if ($operator) {
                if (in_array($operator, ['>', '>=', '<', '<='])) {
                    $condition = "{$column} {$operator} ";

                    if (is_numeric($value)) {
                        $condition .= $mapKey;
                        $map[$mapKey] = [$value, is_float($value) ? PDO::PARAM_STR : PDO::PARAM_INT];
                    } else {
                        $condition .= $mapKey;
                        $map[$mapKey] = [$value, PDO::PARAM_STR];
                    }

                    $stack[] = $condition;
                } elseif ($operator === '!') {
                    switch ($type) {

                        case 'NULL':
                            $stack[] = $column . ' IS NOT NULL';
                            break;

                        case 'array':
                            $placeholders = [];

                            if(!$value) {
                                break;
                            }
                            
                            foreach ($value as $index => $item) {
                                $stackKey = $mapKey . $index . '_i';
                                $placeholders[] = $stackKey;
                                $map[$stackKey] = $this->typeMap($item, gettype($item));
                            }

                            $stack[] = $column . ' NOT IN (' . implode(', ', $placeholders) . ')';
                            break;

                        case 'integer':
                        case 'double':
                        case 'boolean':
                        case 'string':
                            $stack[] = "{$column} != {$mapKey}";
                            $map[$mapKey] = $this->typeMap($value, $type);
                            break;
                    }
                } elseif ($operator === '~' || $operator === '!~') {
                    if ($type !== 'array') {
                        $value = [$value];
                    }

                    $connector = ' OR ';
                    $data = array_values($value);

                    if (is_array($data[0])) {
                        if (isset($value['AND']) || isset($value['OR'])) {
                            $connector = ' ' . array_keys($value)[0] . ' ';
                            $value = $data[0];
                        }
                    }

                    $likeClauses = [];

                    foreach ($value as $index => $item) {
                        $item = strval($item);

                        /*
                        if (!preg_match('/((?<!\\\)\[.+(?<!\\\)\]|(?<!\\\)[\*\?\!\%\-#^_]|%.+|.+%)/', $item)) {
                            $item = '%' . $item . '%';
                        }
                        */

                        $likeClauses[] = $column.($operator === '!~' ? ' NOT' : '') . " LIKE {$mapKey}L{$index}";
                        $map["{$mapKey}L{$index}"] = [$item, PDO::PARAM_STR];
                    }

                    $stack[] = '(' . implode($connector, $likeClauses) . ')';
                } elseif ($operator === '<>' || $operator === '><') {
                    if ($type === 'array') {
                        if ($operator === '><') {
                            $column .= ' NOT';
                        }

                        $stack[] = "({$column} BETWEEN {$mapKey}a AND {$mapKey}b)";
                        $dataType = (is_numeric($value[0]) && is_numeric($value[1])) ? PDO::PARAM_INT : PDO::PARAM_STR;

                        $map[$mapKey . 'a'] = [$value[0], $dataType];
                        $map[$mapKey . 'b'] = [$value[1], $dataType];
                        
                    }
                } elseif ($operator === 'REGEXP') {
                    $stack[] = "{$column} REGEXP {$mapKey}";
                    $map[$mapKey] = [$value, PDO::PARAM_STR];
                } elseif ($operator === 'NOTREGEXP') {
                    $stack[] = "{$column} NOT REGEXP {$mapKey}";
                    $map[$mapKey] = [$value, PDO::PARAM_STR];
                }

                continue;
            }

            switch ($type) {

                case 'NULL':
                    $stack[] = $column . ' IS NULL';
                    break;

                case 'array':
                    $placeholders = [];
                    if(!$value) {
                        break;
                    }
                    foreach ($value as $index => $item) {
                        $stackKey = $mapKey . $index . '_i';

                        $placeholders[] = $stackKey;
                        $map[$stackKey] = $this->typeMap($item, gettype($item));
                    }

                    $stack[] = $column . ' IN (' . implode(', ', $placeholders) . ')';
                    break;

                case 'integer':
                case 'double':
                case 'boolean':
                case 'string':
                    $stack[] = "{$column} = {$mapKey}";
                    $map[$mapKey] = $this->typeMap($value, $type);
                    break;
            }

        }

        return implode($conjunctor . ' ', $stack);
    }

    public function build_raw_where($where, $map = []) {
        if($map)
        {
            $this->buildMap($map);
        }
        $statement = $this->buildWhere($where, $map);
        return $this->raw($statement, $map);
    }


    protected function buildWhere($where = "", &$map = [])
    {
        $clause = '';

        if (is_array($where)) {
            $conditions = array_diff_key($where, array_flip(
                self::CONDITIONS
            ));

            if ($conditions != "") {
                $dataImplode = trim($this->dataImplode($conditions, $map, ' AND'));
                if($dataImplode)
                {
                    $clause = ' WHERE ' . $dataImplode;
                }
            }

            if (isset($where['GROUP'])) {
                $group = $where['GROUP'];

                if (is_array($group)) {
                    $stack = [];

                    foreach ($group as $column => $value) {
                        $stack[] = $this->columnQuote($value);
                    }

                    $clause .= ' GROUP BY ' . implode(',', $stack);
                } else {
                    $clause .= ' GROUP BY ' . $this->columnQuote($group);
                }
            }

            if (isset($where['HAVING'])) {
                $having = $where['HAVING'];
                $clause .= ' HAVING ' . $this->dataImplode($having, $map, ' AND');
            }

            if (isset($where['ORDER']) && $where['ORDER']) {
                $order = $where['ORDER'];

                if (is_array($order)) {
                    $stack = [];

                    foreach ($order as $column => $value) {
                        if (is_array($value)) {
                            $valueStack = [];

                            foreach ($value as $item) {
                                $valueStack[] = is_int($item) ? $item : $this->quote($item);
                            }

                            $valueString = implode(',', $valueStack);
                            $stack[] = "FIELD({$this->columnQuote($column)}, {$valueString}) DESC";
                        } elseif ($value === 'ASC' || $value === 'DESC') {
                            $stack[] = $this->columnQuote($column) . ' ' . $value;
                        } elseif (is_int($column)) {
                            $stack[] = $this->columnQuote($value);
                        }
                    }

                    $clause .= ' ORDER BY ' . implode(',', $stack);
                } else {
                    $clause .= ' ORDER BY ' . $order;
                }
            }

            if (isset($where['LIMIT'])) {
                $limit = $where['LIMIT'];


                if (is_numeric($limit)) {
                    $clause .= ' LIMIT ' . $limit;
                } elseif (
                    is_array($limit) &&
                    is_numeric($limit[0]) &&
                    is_numeric($limit[1])
                ) {
                    $clause .= " LIMIT {$limit[1]} OFFSET {$limit[0]}";
                }

            }
        } else {
            $clause .= ' WHERE ' . $this->buildRaw($where);
        }

        return $clause;
    }

    public function lastInsertId()
    {
        return $this->pdo->lastInsertId();
    }

    public function prefix()
    {
        return $this->options['prefix'];
    }

    public function tableQuote($table = '')
    {
        if (preg_match('/^[\p{L}_][\p{L}\p{N}@$#\-_]*$/u', $table)) {
            return '`' . $this->options['prefix'] . $table . '`';
        }
    }

    public function columnQuote($column = '')
    {

        if (preg_match('/^[\p{L}_][\p{L}\p{N}@$#\-_]*(\.?[\p{L}_][\p{L}\p{N}@$#\-_]*)?$/u', $column)) {
            return strpos($column, '.') !== false ?
                '`' . $this->options['prefix'] . str_replace('.', '`.`', $column) . '`' :
                '`' . $column . '`';
        }

        if(preg_match('/^[\p{L}_][\p{L}\p{N}@$#\-_]*(\.\*)$/u', $column)){
            return '`' . $this->options['prefix'] . str_replace('.', '`.', $column);
        }
        return $this->buildRaw($column);
    }

    public function quote($string)
    {
        return "'" . preg_replace(['/([\'"])/', '/(\\\\\\\")/'], ["\\\\\${1}", '\\\${1}'], $string) . "'";
    }

    public function query($statement = null, $map = null)
    {
        $query = $this->exec($this->buildRaw($statement), $map);
        if (!$this->statement){
            return false;
        }

        return $query->rowCount();
    }

    public function buildRaw($raw = null, &$global_map = [], $map = [])
    {
        if (empty($raw)) {
            return null;
        }

        
        $query = preg_replace_callback(
            '/(([`\']).*?)?((FROM|TABLE|INTO|UPDATE|JOIN)\s*)?\<(([\p{L}_][\p{L}\p{N}@$#\-_]*)(\.[\p{L}_][\p{L}\p{N}@$#\-_]*|\.\*)?)\>(\s*(AS)\s*<([\p{L}_][\p{L}\p{N}@$#\-_]*)>)?([^,]*?\2)?/u',
            function ($matches) {
                
                if (!empty($matches[2]) && isset($matches[11])) {
                    return preg_replace_callback('/(?:\s+)<([\p{L}_][\p{L}\p{N}@$#\-_]*(\.?[\p{L}_][\p{L}\p{N}@$#\-_]*)?)>(?:\s+)/u', function($matches) {
                        return $this->columnQuote($matches[1]);
                    }, $matches[0]);
                }

                if (!empty($matches[4])) {

                    if(isset($matches[8]) && isset($matches[10])) {
                        return $matches[1] . $matches[4] . ' ' . $this->tableQuote($matches[5]).' '.$matches[9].' '.$this->tableQuote($matches[10]);
                    }

                    return $matches[1] . $matches[4] . ' ' . $this->tableQuote($matches[5]);
                }

                if(isset($matches[8]) && isset($matches[10])) {
                    return $matches[1] . $this->columnQuote($matches[5]).' '.$matches[9].' '.$this->columnQuote($matches[10]);
                }

                return $matches[1] . $this->columnQuote($matches[5]);
            },
            $raw
        );

        if($map)
        {
            $index = 0;
            $query = preg_replace_callback("/(\?|\:\w+)/", function($matches) use (&$index, &$global_map, $map) {
                $value = null;
                $mapKey = $this->mapKey();

                if($matches[1] == '?')
                {
                    if(isset($map[$index]))
                    {
                        $value = $map[$index];
                    }
                    
                    $index++;
                }
                else
                {
                    $replace = str_replace(':', '', $matches[1]);
                    if(isset($map[$matches[1]]))
                    {
                        $value = $map[$matches[1]];
                    }
                    else if(isset($map[$replace]))
                    {
                        $value = $map[$replace];
                    }
                }

                $global_map[$mapKey] = $this->typeMap($value, gettype($value));;
                return $mapKey;
            }, $query);         
        }

        return $query;
    }

    public function buildMap(&$map = [])
    {
        $rawMap = $map;
        if (!empty($rawMap)) {
            foreach ($rawMap as $key => $value) {
                $map[$key] = $this->typeMap($value, gettype($value));
            }
        }
    }

    public function exec($statement = "", $map = [], $callback = null)
    {
        if(!$this->pdo)
        {
            return null;
        }
        
        $this->statement = null;
        $this->error = null;

        if($this->debugMode === true)
        {
            echo $this->raw($statement, $map);
            $this->debugMode = false;
            //return null;
        }

        $statement = $this->pdo->prepare($statement);

        $errorInfo = $this->pdo->errorInfo();

        if ($errorInfo[0] !== '00000') {
            $this->error = $errorInfo[2];
            return null;
        }


        if(!is_null($map) && is_array($map)){
            $i = 1;
            foreach ($map as $key => $value) {
                if(is_array($value)){
                    $statement->bindValue($key, $value[0], $value[1]);
                } else {
                    $typeMap = $this->typeMap($value, gettype($value));
                    $statement->bindValue(is_int($key) ? $i : $key, $typeMap[0], $typeMap[1]);
                }
                $i++;
            }            
        }

        try {
            if (is_callable($callback)) {
                $this->pdo->beginTransaction();
                $callback($statement);
                $execute = $statement->execute();
                $this->pdo->commit();
            } else {
                $execute = $statement->execute();
            }
        } catch(PDOException $error){
            return null;
        }


        $errorInfo = $statement->errorInfo();

        if ($errorInfo[0] !== '00000') {
            $this->error = $errorInfo[2];
            return null;
        }

        if ($execute) {
            $this->statement = $statement;
        }

        return $statement;
    }

    public function transaction($actions = null)
    {
        if (is_callable($actions)) {
            $this->pdo->beginTransaction();

            try {
                $result = $actions($this);

                if ($result === false) {
                    $this->pdo->rollBack();
                } else {
                    $this->pdo->commit();
                }
            } catch (Exception $e) {
                $this->pdo->rollBack();
                throw $e;
            }
        }
    }

    protected function typeMap($value, $type = "")
    {
        $map = [
            'NULL' => PDO::PARAM_NULL,
            'integer' => PDO::PARAM_INT,
            'double' => PDO::PARAM_STR,
            'boolean' => PDO::PARAM_BOOL,
            'string' => PDO::PARAM_STR,
            'object' => PDO::PARAM_STR,
            'resource' => PDO::PARAM_LOB
        ];

        if ($type === 'boolean') {
            $value = ($value ? '1' : '0');
        } elseif ($type === 'NULL') {
            $value = null;
        }

        return [$value, $map[$type]];
    }

    public function PDO()
    {
        return $this->pdo;
    }

    public function getError()
    {
        return $this->error;
    }

    protected function raw($statement = '', $map = null)
    {

        $statement = preg_replace(
            '/(?!\'[^\s]+\s?)"([\p{L}_][\p{L}\p{N}@$#\-_]*)"(?!\s?[^\s]+\')/u',
            '`$1`',
            $statement
        );

        if(is_array($map)){
            foreach ($map as $key => $value) {
                if(is_array($value))
                {

                    if ($value[1] === PDO::PARAM_STR) {
                        $replace = $this->quote($value[0]);
                    } elseif ($value[1] === PDO::PARAM_NULL) {
                        $replace = 'NULL';
                    } elseif ($value[1] === PDO::PARAM_LOB) {
                        $replace = '{LOB_DATA}';
                    } else {
                        $replace = $value[0] . '';
                    }                
                } else {
                    $replace = $value . '';
                }
                $statement = str_replace($key, $replace, $statement);
            }            
        }


        return $statement;
    }
    public function debug()
    {
        $this->debugMode = true;
        return $this;
    }

    public function escape_string($str = "")
    {
        if(is_array($str))
        {
            return array_map(__METHOD__, $str);
        }

        if(!empty($str) && is_string($str))
        {
            return str_replace([
                '\\',
                "\0",
                "\n",
                "\r",
                "'",
                '"',
                "\x1a"
            ], [
                '\\\\',
                '\\0',
                '\\n',
                '\\r',
                "\\'",
                '\\"',
                '\\Z'
            ], $str);
        }

        return $str;
    }

}



?>