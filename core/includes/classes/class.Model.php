<?php

abstract class Model {

	protected static $debugMode = false;

	public static $table = null;
	protected static $timestamps = false;
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

	protected static $default_selects = [];
	protected static $default_join = [];
	protected static $primary_key = 'id';
	protected static $order_by = null;
	protected static $where = [];

	private static $tmp_select = null;
	private static $tmp_join = null;
	private static $select = null;
	private static $join = null;

	public static $insert_id = null;

	abstract protected static function onBeforeInsert();
	abstract protected static function onSuccessInsert();
	abstract protected static function onErrorInsert();
	abstract protected static function onBeforeUpdate();
	abstract protected static function onSuccessUpdate();
	abstract protected static function onErrorUpdate();
	abstract protected static function onBeforeDelete();
	abstract protected static function onSuccessDelete();
	abstract protected static function onErrorDelete();

	private const STATEMENT_TABLE = '{table}';

	public static function debug()
	{
		self::$debugMode = true;
		return static::class;
	}

	private static function debug_mode()
	{
		if(self::$debugMode == true)
		{
			App::$database->debug();
			self::$debugMode = false;
		}
	}

	public static function select($selects = [], $overwrite = true)
	{

		if(is_array($selects))
		{
			static::$tmp_select = $overwrite !== false ? $selects : array_merge(static::$default_selects, $selects);
		}
		return static::class;
	}
	

	public static function join($join = [], $overwrite = true)
	{
		if (empty($join)) {
			return static::class;
		}
		
		if(is_array($join))
		{
			static::$tmp_join = $overwrite !== false ? $join : array_merge(static::$default_join, $join);
		}
		return static::class;
	}

	public static function where($where = [])
	{
		if(is_array($where))
		{
			static::$where = $where;
		}
		return static::class;
	}

	private static function build_join()
	{
		if(static::$tmp_join !== null)
		{
			static::$join = static::$tmp_join;
			static::$tmp_join = null;
		}
		else
		{
			static::$join = static::$default_join;
		}

		
		if(!is_array(static::$join))
		{
			static::$join = [static::$join];
		}

		static::$join = array_map([static::class, 'statement'], static::$join);
		static::$join = array_values(array_unique(static::$join));
	}

	private static function build_select()
	{
		if(static::$tmp_select !== null)
		{
			static::$select = static::$tmp_select;
			static::$tmp_select = null;
		}
		else
		{
			static::$select = static::$default_selects;
		}

		if(!is_array(static::$select))
		{
			static::$select = [static::$select];
		}

		self::build_join();

		static::$select = array_map([static::class, 'columnQuote'], static::$select);
		static::$select = array_values(array_unique(static::$select));

		return 'SELECT '.implode(', ', static::$select).' FROM '.static::get_table().' '.implode(' ', static::$join);
	}

	public static function build_where($where = [], $overwrite = false)
	{
		if (!$overwrite) {
			$where = $where ? array_merge(static::$where, $where) : static::$where;
		}
		
		if(!is_array($where))
		{
			$where = [];
		}

		if(!is_array(static::$join))
		{
			static::$join = [static::$join];
		}

		if(!isset($where['ORDER']))
		{
			$where['ORDER'] = static::$order_by;
		}

		return self::prefix($where);
	}


    private static function get_table()
    {
        if (preg_match('/^[\p{L}_][\p{L}\p{N}@$#\-_]*$/u', static::$table))
        {
            return '<'.static::$table.'>';
        }
        return static::$table;
    }

    private static function columnQuote($column = '')
    {
		$column = self::statement($column);
		
        if (preg_match('/^([\p{L}_][\p{L}\p{N}@$#\-_]*|\*)$/u', $column))
        {
            return '<'.static::$table.'.'.$column.'>';
        }

        return $column;
    }

	private static function statement($raw = null)
	{
		if(is_array($raw))
		{
			$result = [];
			array_walk($raw, function($value, $key) use(&$result) {
				$result[self::statement($key)] = is_array($value) ? self::statement($value) : $value;
			});
			return $result;
		}
		$raw = str_ireplace(self::STATEMENT_TABLE, static::$table, $raw);
		return $raw;
	}

	private static function prefix($where = null)
	{

		if(is_array($where))
		{
			$result = [];

			array_walk($where, function($value, $key) use(&$result) {
	
				if(is_int($key))
				{
					$value = self::statement($value);
					return $result[$key] = $value;
				}

				$key = self::statement($key);

				if(in_array($key, App::$database::CONDITIONS) || preg_match("/^(AND|OR)(\s+#.*)?$/", $key))
				{
					return $result[$key] = self::prefix($value);
				}
	
				if (preg_match('/^(\s*)\[RAW\]/u', $key) || preg_match('/(([\p{L}_][\p{L}\p{N}@$#\-_]*)(\.[\p{L}_][\p{L}\p{N}@$#\-_]*|\.\*))/u', $key))
				{
					return $result[(!$value || is_array($value)) ? $key : preg_replace('/^(\s*)\[RAW\](.*)/u', '$2', $key)] = $value;
				}

				return $result[static::$table.'.'.$key] = $value;
			});
			return $result;
		}

		return $where;
	}

	public static function count($where = [], $select = null)
	{
		self::debug_mode();
		$where = static::build_where($where);

		if(isset($where['ORDER'])) {
			unset($where['ORDER']);
		}

		$query = static::$table;
		if ($select) {
			$query = self::statement($select);
		} else {
			if (!empty($where)) {
				self::build_join();
				if (!empty(static::$join)) {
					$query = 'SELECT COUNT(*) FROM '.static::get_table().' '.implode(' ', static::$join);
				}
			}
		}

		return App::$database->count($query, $where);	
	}

	public static function has($where = [], $select = null)
	{
		self::debug_mode();
		$where = static::build_where($where);
		
		if(isset($where['ORDER'])) {
			unset($where['ORDER']);
		}

		$query = static::$table;
		if ($select) {
			$query = self::statement($select);
		} else {
			if (!empty($where)) {
				self::build_join();
				if (!empty(static::$join)) {
					$query = 'SELECT COUNT(*) FROM '.static::get_table().' '.implode(' ', static::$join);
				}
			}
		}

		return App::$database->has($query, $where);	
	}
	
	public static function list($where = [], $select = null)
	{
		self::debug_mode();
		return App::$database->select($select ? self::statement($select) : static::build_select(), static::build_where($where));	
	}

	public static function get($id = null, $select = null){

		if($id == '' || ($id && !static::$primary_key && !is_array($id)))
		{
			return null;
		}

		if(is_array($id) && !isset($id[0]))
		{
			$where = $id;
		}
		else
		{
			$where[static::$table.'.'.static::$primary_key] = $id;
		}

		self::debug_mode();
		return App::$database->get($select ? self::statement($select) : static::build_select(), static::build_where($where));
	}

	public static function insert($data = []){

		if(!$data || !is_array($data))
		{
			return false;
		}

		if(static::$timestamps === true)
		{
			if(!isset($data[static::CREATED_AT])) {
				$data[static::CREATED_AT] = time();
			}
			$data[static::UPDATED_AT] = 0;
		}

		static::$insert_id = null;

		if(method_exists(static::class, 'onBeforeInsert'))
		{
			static::onBeforeInsert($data);
		}

		self::debug_mode();
		if(App::$database->insert(static::$table, $data) > 0)
		{
			static::$insert_id = App::$database->lastInsertId();
			if(method_exists(static::class, 'onSuccessInsert'))
			{
				static::onSuccessInsert(static::$insert_id);
			}
			return true;
		}

		if(method_exists(static::class, 'onErrorInsert'))
		{
			static::onErrorInsert();
		}
		return false;
	}

	public static function update($id = null, $data = [])
	{
		if($id == '' || ($id && !static::$primary_key && !is_array($id)))
		{
			return false;
		}

		if(is_array($id) && !isset($id[0]))
		{
			$where = $id;
		}
		else
		{
			$where[static::$table.'.'.static::$primary_key] = $id;
		}

		if(static::$timestamps === true && !isset($data[static::UPDATED_AT]))
		{
			$data[static::UPDATED_AT] = time();
		}

		$where = static::build_where($where, true);
		if (isset($where['ORDER'])) {
			unset($where['ORDER']);
		}

		if(method_exists(static::class, 'onBeforeUpdate'))
		{
			static::onBeforeUpdate($data, $where);
		}

		self::debug_mode();
		$update = App::$database->update(static::$table, $data, $where);
		if($update !== false)
		{
			if(method_exists(static::class, 'onSuccessUpdate'))
			{
				static::onSuccessUpdate($update);
			}
			return $update;
		}

		if(method_exists(static::class, 'onErrorUpdate'))
		{
			static::onErrorUpdate();
		}
		return false;
	}

	public static function delete($id = null)
	{
		if($id == '' || ($id && !static::$primary_key && !is_array($id)))
		{
			return false;
		}

		if($id == '*')
		{
			$where = [];
		}
		else
		{
			if(is_array($id) && !isset($id[0]))
			{
				$where = $id;
			}
			else
			{
				$where[static::$table.'.'.static::$primary_key] = $id;
			}			
		}

		$where = static::build_where($where, true);
		if (isset($where['ORDER'])) {
			unset($where['ORDER']);
		}
		
		if(method_exists(static::class, 'onBeforeDelete'))
		{
			static::onBeforeDelete($where);
		}

		self::debug_mode();
		$delete = App::$database->delete(static::$table, $where);
		if($delete > 0)
		{
			if(method_exists(static::class, 'onSuccessDelete'))
			{
				static::onSuccessDelete($delete);
			}
			return $delete;
		}
		if(method_exists(static::class, 'onErrorDelete'))
		{
			static::onErrorDelete();
		}
		return false;
	}

	public static function getTableName() 
	{
		return static::$table;
	}
} 


?>