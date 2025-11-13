<?php

class Language
{

    private static $path;

    public static $lang;
    public static $default;
    private static $url;
    private static $cookie;
    private static $browser;
    private static $auto_language;
    private static $loaded = [];
    private static $data = [];

    private static $accept_list = [
        'vi' => 'Vietnamese'
    ];

    private const FILE_EXT = '.lng';

    private const ERROR_NOT_FOUND = '{{LANGUAGE_KEY_NOT_FOUND}}';
    public const COOKIE_NAME = 'lang';
    public const GET_NAME = 'hl';

    public function __construct($config = [])
    {
        self::init($config);
    }

    public static function init($config = null)
    {
        self::$url = Request::get(self::GET_NAME, null);
        self::$cookie = Request::cookie(self::COOKIE_NAME, null);
        self::$browser = substr(Request::server('HTTP_ACCEPT_LANGUAGE', ''), 0, 2);

        if($config)
        {
            if(isset($config['default']))
            {
                self::$default = trim($config['default'].'');
            } 

            if(isset($config['path']))
            {
                self::$path = rtrim($config['path'], '/');
            }

            if(isset($config['auto_language']))
            {
                self::$auto_language = $config['auto_language'];
            }
    
        }

        if(self::$auto_language == true && !self::getDefault())
        {
            self::fromBrowser();
        }
        
        self::fromCookie();
        self::fromUrl();

        if (!self::$lang)
        {
            self::$lang = self::getDefault();
        }

        if(isset($config['loaded']))
        {
            self::load($config['loaded']);
        }

        return static::class;
    }

    public static function change($lang = null, $set_cookie = false)
    {
        if($lang == "")
        {
            return static::class;
        }

        $lang = strtolower($lang);

        $path = self::$path.'/'.$lang;
        if(!file_exists($path))
        {
            return static::class;
        }
        self::setLanguage($lang);

        if($set_cookie == true)
        {
            self::set_cookie($lang);
        }
        return static::class;
    }

    public static function set_cookie()
    {
        setcookie(self::COOKIE_NAME, self::getLanguage(), time()+ 3600 * 24 * 365, "/");
        return static::class;
    }

    public static function makePath($path = null)
    {
        return str_replace('.', '/', $path);
    }

    public static function load_file($directory = null)
    {

        if($directory == "")
        {
            return static::class;
        }
        if(!is_file($directory))
        {
            $directory = rtrim(self::makePath($directory), '/*').'/*';
        }


        $glob = glob($directory);

        if($glob === false)
        {
            return static::class;
        }

        foreach ($glob as $file) {
            if(is_dir($file))
            {
                self::load_file($file);
            }
            else
            {
                if(!in_array($file, self::$loaded) && preg_match("/^(.*)".self::FILE_EXT."$/si", $file))
                {
                    if(is_file($file))
                    {
                        $data = parse_ini_file($file, true);
                        foreach ($data as $key => $value) {
                            if(is_array($value))
                            {
                                if(!isset(self::$data[$key]))
                                {
                                    self::$data[$key] = [];
                                }
                                self::$data[$key] = array_merge(self::$data[$key], $value);                        
                            }
                            else
                            {
                                self::$data[$key] = $value; 
                            }
                        }
                        self::$loaded[] = $file;
                    }
                }
            }
        }
        return static::class;
    }


    public static function load($file_name = null)
    {
        $lang = self::getLanguage();

        if($lang == "")
        {
            throw new Exception('Current language not found: '.$lang);
            return static::class;
        }

        if($file_name == "" || !file_exists(self::$path))
        {
            return static::class;
        }


        $path = self::$path.'/'.$lang;
        if(!file_exists($path))
        {
            throw new Exception('Path lang not found: '.$path);
            return static::class;
        }

        if(!is_array($file_name))
        {
            $file_name = [$file_name];
        }



        if(in_array("*", $file_name))
        {
            self::load_file($path);
        }
        else
        {
            foreach($file_name as $file)
            {
                self::load_file($path.'/'.$file);
            }
        }
        return static::class;
    }

    public static function get($section = null, $key = null, $data = [])
    {
        if(!$data && is_array($key))
        {
            $data = $key;
            $key = $section;
            $section = null;
        }

        if($key == "")
        {
            $key = $section;
            $section = null;
        }

        if($key == "")
        {
            return null;
        }

        $result = null;

        if($section)
        {
            $result =  isset(self::$data[$section][$key]) ? self::$data[$section][$key] : self::ERROR_NOT_FOUND;
        }
        else
        {
            $result = isset(self::$data[$key]) && !is_array(self::$data[$key]) ? self::$data[$key] : self::ERROR_NOT_FOUND;
        }

        if($result != self::ERROR_NOT_FOUND && $data)
        {
            preg_match_all("/(\{(?:\w+)\})/U", $result, $args);
            $key_replace = isset($args[1]) ? $args[1] : [];

            $data_replace = [];
            $i = 0;
            foreach ($key_replace as $key => $value) {
                $name_var = str_replace(['{', '}'], '', $value);
                $data_replace[$value] = isset($data[$name_var]) ? $data[$name_var] : $value;
                $i++;
            }

            $result = strtr($result, $data_replace);
        }

        return $result;
    }

    public static function getDefault()
    {
        return self::$default;
    }

    private static function setLanguage($language = null)
    {
        $language = strtolower($language);
        if($language != "" && array_key_exists($language, self::$accept_list))
        {
            self::$lang = $language;
        }
        return static::class;
    }

    private static function getLanguage()
    {
        return strtolower(self::$lang);
    }

    private static function fromUrl()
    {
        self::change(self::$url);
        return static::class;
    }

    private static function fromCookie()
    {
        self::change(self::$cookie);
        return static::class;
    }

    private static function fromBrowser()
    {
        self::change(self::$browser);
        return static::class;
    }

    public static function list()
    {
        return self::$accept_list;
    }
}

?>