<?php


class Pagination {
	public static $pages = [];
	public static $first = 0;
	public static $end   = 1;
	public static $previous = 1;
	public static $next     = 1;

	public static $limit = 10;
	public static $start = 0;
	public static $total_item = 0;
	public static $total_page = 1;
	public static $current_page = 1;

	private static $query_string = null;

	private static $_instance = null;

	private const PARAM_PAGE = 'page';
	private const SHOW_ITEM_PAGE = 2;

	public const SEPARATOR = '...';

	function __construct($total_item = null, $limit = null, $current_page = null)
	{
		self::$_instance = $this;
		self::set($total_item, $limit, $current_page);
	}

	public static function set($total_item = 0, $limit = null, $page = null, $start = null)
	{
		self::set_total_item($total_item);
		self::set_limit($limit);

		$page = $page ? $page : (isset($_GET[self::PARAM_PAGE]) ? intval($_GET[self::PARAM_PAGE]) : null);
		$start = $start ? $start: (isset($_GET['start']) ? abs(intval($_GET['start'])) : 0);
		$limit = $limit > 0 ? $limit : (isset($_GET['limit']) ? intval($_GET['limit']) : null);

	    self::$limit =  $limit > 0 ? $limit : self::$limit;
	    self::$current_page  = $page > 0 ? $page : 1;
	    self::$start  = $page ? (self::$current_page * self::$limit - self::$limit) : $start;

	    self::$total_page = ceil(self::$total_item / self::$limit);

	    self::$end = self::$total_page;
	    if(self::$end <= 0)
	    {
	    	self::$end = 1;
	    }

        if(self::$total_item <= 0)
        {
            self::$start = 0;
            self::$current_page = 1;
        }
        else
        {
            if(self::$current_page > self::$total_page || self::$start > self::$total_item)
            {
                self::$current_page = self::$total_page;
                self::$start = self::$current_page * self::$limit - self::$limit;
            }
        }

        self::build_query();

        $isValid = self::$total_item > self::$limit;
    
    	self::$previous = (self::$current_page > 1) && $isValid  ? self::$current_page - 1 : false;
    	self::$next     = (self::$current_page < self::$end) && $isValid ? self::$current_page + 1 : false;
    	self::$first    = (self::$current_page != 1) && $isValid ? 1 : false;
        self::$end      = (self::$current_page != self::$end) && $isValid ? self::$end : false;


		$split_page = 1;

	    $begin = (self::$current_page - $split_page) < 1 ? 1 : (self::$current_page - $split_page);
	    $end   = (self::$current_page + $split_page) > self::$end ? self::$total_page : (self::$current_page + $split_page);

	    self::$pages = [];

	    if(self::$first)
		{
			self::$pages[] = self::$first;
			if((self::$first + 1) < (self::$current_page - $split_page))
			{
				self::$pages[] = self::$first + 1;
				if($begin - end(self::$pages) > 1)
				{
					self::$pages[] = self::SEPARATOR;
				}
			}
		}


	    for ($i = $begin; $i <= $end; $i++)
		{
			if(!in_array($i, self::$pages))
			{
	    		self::$pages[] = $i;
			}
	    }

	    if(self::$end)
		{
			if((self::$end - 1) > (self::$current_page + $split_page))
			{
				if((self::$end - 1) - $end > 1)
				{
					self::$pages[] = self::SEPARATOR;
				}
				self::$pages[] = self::$end - 1;
			}
			
			if(!in_array(self::$end, self::$pages))
			{
				self::$pages[] = self::$end;
			}
		}

		return self::$_instance;
	}

	public static function get($url = null)
	{
		if(!self::$query_string)
		{
			self::set();
		}

		if($url != "")
		{
			self::build_query($url);
		}

	    return [
			'pages' => self::$pages,
			'first' => self::$first,
			'end' => self::$end,
			'previous' => self::$previous,
			'next' => self::$next,
			'limit' => self::$limit,
			'start' => self::$start,
			'total_item' => self::$total_item,
			'total_page' => self::$total_page,
			'current_page' => self::$current_page,
			'query_string' => self::$query_string
	    ];
	}

	private static function build_query($uri = null)
	{

		if($uri == "")
		{
			$uri = isset($_SERVER['REQUEST_URI']) ? trim($_SERVER['REQUEST_URI']) : null;
		}

		$url = preg_replace("#^(.*?)\?(.*?)$#si", "$1", $uri);
	    
	    if(preg_match("#^(.*?)\?(.*?)$#si", $uri))
	    {
	        $query_string = preg_replace("#^(.*?)\?(.*?)$#si", "$2", $uri);
	        parse_str($query_string, $query_array);
	        if(isset($query_array[self::PARAM_PAGE]))
	        {
	            unset($query_array[self::PARAM_PAGE]);
	        }
	        self::$query_string = $url.'?'.http_build_query($query_array).(count($query_array) > 0 ? '&' : '').self::PARAM_PAGE.'=';
	    }
	    else
	    {
	        self::$query_string = $url.'?'.self::PARAM_PAGE.'=';
	    }
	    return self::$_instance;
	}

	public static function build_url($page = null)
	{
		if(!self::$query_string)
		{
			self::build_query();
		}

		return self::$query_string.$page;
	}

	public static function set_total_item($total_item = 0)
	{
		$total_item = abs(intval($total_item));
		if($total_item > 0)
		{
			self::$total_item = $total_item;
		}
		return self::$_instance;
	}

	public static function set_limit($limit = 1)
	{
		$limit = abs(intval($limit));
		if($limit > 0)
		{
			self::$limit = $limit;
		}
		return self::$_instance;
	}
}




?>