<?php


class DotEnv
{
    public const APP_NAME = 'APP_NAME';
    public const APP_TITLE = 'APP_TITLE';
    public const APP_DESCRIPTION = 'APP_DESCRIPTION';
    public const APP_EMAIL = 'APP_EMAIL';
    public const APP_LIMIT_ITEM_PAGE = 'APP_LIMIT_ITEM_PAGE';
    public const PROFILE_UPLOAD_MODE = 'PROFILE_UPLOAD_MODE';
    public const VIEW_HOT = 'VIEW_HOT';
    public const LIMIT_LOGIN = 'APP_LIMIT_LOGIN';
    public const DEFAULT_THEME = 'DEFAULT_THEME';
    public const DEFAULT_LANGUAGE = 'DEFAULT_LANGUAGE';
    public const IMGUR_CLIENT_ID = 'IMGUR_CLIENT_ID';
    public const APP_REQUIRED_LOGIN = 'APP_REQUIRED_LOGIN';
    public const ENCODE_URL_IMAGE = 'ENCODE_URL_IMAGE';

    public const MAILER_MODE = 'MAILER_MODE';
    public const MAILER_TEMPLATE = 'MAILER_TEMPLATE';
    public const MAILER_NAME = 'MAILER_NAME';
    public const MAILER_FROM = 'MAILER_FROM';
    public const MAILER_SMTP = 'MAILER_SMTP';
    public const MAILER_API = 'MAILER_API';

    protected $path;

    public function __construct($path = null)
    {
        if(!is_file($path))
        {
            return;
        }
        $this->path = $path;
        $this->load();
    }

    public function load()
    {
        if (!is_readable($this->path))
        {
            return;
        }

        $lines = file($this->path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {

            if (strpos(trim($line), '#') === 0) {
                continue;
            }

            list($name, $value) = explode('=', $line, 2);
            $name = trim($name.'');
            $value = trim($value.'');

            if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
                putenv(sprintf('%s=%s', $name, $value));
            }
        }
    }

    public static function json($path = null)
    {
        if(!is_file($path))
        {
            return;
        }
        

        $json = json_decode(file_get_contents($path), true);

        if(json_last_error() !== JSON_ERROR_NONE)
        {
            return;
        }

        foreach($json as $key => $value)
        {
            if (!array_key_exists($key, $_ENV)) {
                $_ENV[$key] = $value;
            }
        }
        
    }
}

?>