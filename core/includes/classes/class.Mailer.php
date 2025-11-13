<?php



class Mailer {

	public static $mailer_default;
	public static $template;

	private static $from = null;
	private static $name = null;

	protected static $verifyEmail;

	private const FOLDER_TEMPLATE = 'template_mailer';

	public const MODE_SMTP = 'SMTP';
	public const MODE_API = 'API';

	public const API_LIST = [
		'mailjet'
	];

	protected static $smtp = null;
	protected static $api = null;

	public const VAR_AUTHENTICATION = 'authentication';
	public const VAR_HOST = 'host';
	public const VAR_SECURE = 'secure';
	public const VAR_PORT = 'port';
	public const VAR_USERNAME = 'username';
	public const VAR_PASSWORD = 'password';
	public const VAR_SERVER = 'server';
	public const VAR_KEY = 'key';
	public const VAR_SECRET = 'secret';

	protected static $options = [
		"autoVerify" => false,
		"verifyEmailFrom" => "",
		"verifyConnectionTimeout" => 5,
		"verifyStreamTimeout" => 5,
		"limit" => 100 // gửi tối đa đến 100 mail cùng lúc
	];

	function __construct($options = [])
	{
		if($options)
		{
			if(isset($options['autoVerify']))
			{
				self::$options['autoVerify'] = $options['autoVerify'];
			}

			if(isset($options['verifyEmailFrom']))
			{
				self::$options['verifyEmailFrom'] = $options['verifyEmailFrom'];
			}

			if(isset($options['verifyConnectionTimeout']))
			{
				self::$options['verifyConnectionTimeout'] = $options['verifyConnectionTimeout'];
			}

			if(isset($options['verifyStreamTimeout']))
			{
				self::$options['verifyStreamTimeout'] = $options['verifyStreamTimeout'];
			}

			if(isset($options['limit']))
			{
				self::$options['limit'] = $options['limit'];
			}			
		}

		self::$mailer_default = env(DotEnv::MAILER_MODE, self::MODE_SMTP);
		self::$template = env(DotEnv::MAILER_TEMPLATE, null);
		self::$from = env(DotEnv::MAILER_FROM, 'no-reply@gmail.com');
		self::$name = env(DotEnv::MAILER_NAME, 'no-reply');

		$smtp = env(DotEnv::MAILER_SMTP, []);
		$api = env(DotEnv::MAILER_API, []);

		self::$smtp[self::VAR_AUTHENTICATION] = isset($smtp[self::VAR_AUTHENTICATION]) ? $smtp[self::VAR_AUTHENTICATION] : null;
		self::$smtp[self::VAR_HOST] = isset($smtp[self::VAR_HOST]) ? $smtp[self::VAR_HOST] : null;
		self::$smtp[self::VAR_SECURE] = isset($smtp[self::VAR_SECURE]) ? $smtp[self::VAR_SECURE] : null;
		self::$smtp[self::VAR_PORT] = isset($smtp[self::VAR_PORT]) ? $smtp[self::VAR_PORT] : null;
		self::$smtp[self::VAR_USERNAME] = isset($smtp[self::VAR_USERNAME]) ? $smtp[self::VAR_USERNAME] : null;
		self::$smtp[self::VAR_PASSWORD] = isset($smtp[self::VAR_PASSWORD]) ? $smtp[self::VAR_PASSWORD] : null;


		self::$api[self::VAR_SERVER] = isset($api[self::VAR_SERVER]) ? $api[self::VAR_SERVER] : null;
		self::$api[self::VAR_KEY] = isset($api[self::VAR_KEY]) ? $api[self::VAR_KEY] : null;
		self::$api[self::VAR_SECRET] = isset($api[self::VAR_SECRET]) ? $api[self::VAR_SECRET] : null;	
	

		if(self::$options['autoVerify'] == true)
		{		
			self::$verifyEmail = new verifyEmail();
		}

	}

	public static function setAccount($username = "", $password = "")
	{

		if($username)
		{
			self::$smtp[self::VAR_USERNAME] = $username;
		}

		if($password)
		{
			self::$smtp[self::VAR_PASSWORD] = $password;
		}
		return static::class;
	}

	public static function setTemplate($template = "")
	{
		self::$template = $template;
		return static::class;
	}

	public static function setFrom($from = "", $name = "")
	{

		if($from)
		{
			self::$from = $from;
		}

		if($name)
		{
			self::$name = $name;
		}
		return static::class;
	}

	public static function check($mail = "")
	{

		if(!filter_var($mail, FILTER_VALIDATE_EMAIL))
		{
			return false;
		}

		if(self::$options['verifyEmailFrom'])
		{
			self::$verifyEmail->setEmailFrom(self::$options['verifyEmailFrom']);
		}

		if(self::$options['verifyConnectionTimeout'])
		{
			self::$verifyEmail->setConnectionTimeout(self::$options['verifyConnectionTimeout']);
		}

		if(self::$options['verifyStreamTimeout'])
		{
			self::$verifyEmail->setStreamTimeout(self::$options['verifyStreamTimeout']);
		}


        if (self::$verifyEmail->check($mail))
        {
            return true;
        }

        return false;

	}

	public static function send($to = "", $subject = "", $body = "", $type = "text/html")
	{
		if(self::$mailer_default == self::MODE_API)
		{
			return self::sendApi($to, $subject, $body, $type);
		}

		if(empty($to) || empty(self::$from))
		{
			return false;
		}

		if(self::$smtp[self::VAR_AUTHENTICATION] == false)
		{

            $headers  = "From: ".self::$name." <".self::$from.">\r\nReply-To: ".self::$name." <".self::$from.">\n";
            $headers .= "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";

            if(mail(is_array($to) ? implode(",", $to) : $to, $subject, $body, $headers, '-f'.self::$from))
            {
                return true;
            }
            return false;
		}
		else
		{

			if(!class_exists('Swift_Mailer'))
			{
				require_once INCLUDE_PATH. '/vendor/SwiftMailer/swift_required.php';
			}
		
			if(empty(self::$smtp[self::VAR_HOST]) || empty(self::$smtp[self::VAR_USERNAME]) || empty(self::$smtp[self::VAR_PASSWORD]))
			{
				return false;
			}

			if(self::$options['autoVerify'] == true)
			{

				if(is_array($to))
				{
					$i = 0;
					foreach ($to as $mail){
						if(!self::check($mail))
						{
							unset($to[$i]);
						}
						$i++;		
					}
				}
				else
				{
					if(!self::check($to))
					{
						return false;
					}
				}
			}

			if(!is_array($to))
			{
				$to = [$to];
			}

			try {

				$transport = (new Swift_SmtpTransport(self::$smtp[self::VAR_HOST], self::$smtp[self::VAR_PORT], self::$smtp[self::VAR_SECURE]))
	  				->setUsername(self::$smtp[self::VAR_USERNAME])
	  				->setPassword(self::$smtp[self::VAR_PASSWORD]);

	  			$mailer = new Swift_Mailer($transport);
	  			$mailer->registerPlugin(new Swift_Plugins_AntiFloodPlugin(self::$options['limit']));

				$message = new Swift_Message($subject);

				$message->setFrom([self::$from => self::$name]);

				if(array_search(self::$from, $to))
				{
					$message->setTo(self::$from);
					unset($to[array_search(self::$from, $to)]);
				}

				if($to)
				{
					$message->setBcc($to);
				}

				$message->setBody($body, $type);

                if ($mailer->send($message))
                {
                    return true;
                }
                return false;

        	} catch(Swift_TransportException $e){
        		return false;
        	}

        	return false;
		}

	}


	public static function sendApi($to = "", $subject = "", $body = "", $type = "text/html")
	{
		if(empty($to) || empty(self::$api[self::VAR_SERVER]) || empty(self::$api[self::VAR_KEY]) || empty(self::$api[self::VAR_SECRET]))
		{
			return false;
		}

		if(!in_array(self::$api[self::VAR_SERVER], self::API_LIST)) {
			return false;
		}

		switch (self::$api[self::VAR_SERVER])
		{
			case 'mailjet':
				$body = [
					'Messages' => [
						[
							'From' => [
					    		'Email' => self::$from,
					    		'Name' => self::$name
					    	],
					    	'To' => [],
					    	'Subject' => $subject,
					    	'HTMLPart' => $body
						]
					]
				];

				if(!is_array($to))
				{
					$to = [$to];
				}

				foreach($to as $arr){
					$body['Messages'][0]['To'][] = [
					    'Email' => $arr
					];
				}

				$header = [
				  "Content-Type: application/json"
				];

				$ch = curl_init();
				curl_setopt_array($ch, [
					CURLOPT_CUSTOMREQUEST => 'POST',
					CURLOPT_URL => "https://api.mailjet.com/v3.1/send",
					CURLOPT_USERPWD => self::$api[self::VAR_KEY].":".self::$api[self::VAR_SECRET],
					CURLOPT_HTTPHEADER => $header,
					CURLOPT_POSTFIELDS => json_encode($body),
					CURLOPT_RETURNTRANSFER => 1,
					CURLOPT_TIMEOUT => 5,
					CURLOPT_FOLLOWLOCATION => 1,
					CURLOPT_FAILONERROR => 0,
					CURLOPT_SSL_VERIFYPEER => 0,
					CURLOPT_VERBOSE => 0
				]);

				$response = json_decode(curl_exec($ch), true);
				curl_close($ch);

				if(isset($response['ErrorMessage']))
				{
					return false;
				}
			break;
			
			default:
				return false;
			break;
		}
		return true;
	}

	public static function list_template() {
		$list = [];
		$files = glob(INCLUDE_PATH.'/'.self::FOLDER_TEMPLATE.'/*.html', GLOB_BRACE);
		if($files) {
			foreach($files as $file) {
				$list[] = basename($file);
			}
		}
		return $list;
	}

	public static function template($body = "", $template = null, $options = null)
	{
		if(!$options && is_array($template))
		{
			$options = $template;
			$template = null;
		}

		$default_options = [
			"pre-header" => "",
			"plugin-class" => "",
			"background" => "#ffffff",
			"contentbackground" => "#ffffff",
			"contentpaddingleft" => 0,
			"contentpaddingright" => 0,
			"headerborderbottom" => "1px solid #e5e5e5",
			"headertext" => "",
			"headerfont" => "Trebuchet, sans-serif",
			"headeralign" => "left",
			"headerfontsize" => 21,
			"headerbold" => "bold",
			"headeritalic" => 0,
			"headertexttransform" => "none",
			"headerbackground" => "#222222",
			"headercolor" => "#f09900",
			"headerpaddingtop" => 5,
			"headerpaddingright" => 20,
			"headerpaddingbottom" => 5,
			"headerpaddingleft" => 20,
			"header_spacer" => 10,
			"headerimg_placement" => "just_text",
			"headerimg" => "",
			"headerimg_width" => 600,
			"headerimg_height" => 1,
			"headerimg_alt" => "",
			"headerimg_align" => "",
			"headlinefont" => "Trebuchet, sans-serif",
			"headlinealign" => "left",
			"headlinefontsize" => 19,
			"headlinebold" => 0,
			"headlineitalic" => 0,
			"headlinetexttransform" => "none",
			"headlinecolor" => "#343434",
			"subheadlinefont" => "Trebuchet, sans-serif",
			"subheadlinealign" => "left",
			"subheadlinefontsize" => 18,
			"subheadlinebold" => 0,
			"subheadlineitalic" => 0,
			"subheadlinetexttransform" => "none",
			"subheadlinecolor" => "#343434",
			"textfont" => "Helvetica, Arial, sans-serif",
			"textalign" => "left",
			"textfontsize" => 14,
			"textbold" => 0,
			"textitalic" => 0,
			"textcolor" => "#525252",
			"linkcolor" => "#fbb72a",
			"linkbold" => 0,
			"linkitalic" => 0,
			"linktexttransform" => "none",
			"linkunderline" => 1,
			"footer" => "",
			"footerbackground" => "#ffffff",
			"footerbordertop" => "1px solid #f3f3f3",
			"footerpaddingtop" => 10,
			"footerpaddingright" => 0,
			"footerpaddingbottom" => 10,
			"footerpaddingleft" => 0,
			"headertexttranform" => "none",
			"headlinetexttranform" => "none",
			"subheadlinetexttranform" => "none",
			"linktexttranform" => "none"
		];

		if(is_array($options) && $options)
		{
			foreach ($options as $key => $value) {
				if(array_key_exists($key, $default_options))
				{
					$default_options[$key] = $value;
				}
			}
		}

		$template_name = $template != "" ? $template : self::$template;

		$template = file_exists(INCLUDE_PATH.'/'.self::FOLDER_TEMPLATE.'/'.$template_name) ? file_get_contents(INCLUDE_PATH.'/'.self::FOLDER_TEMPLATE.'/'.$template_name) : null;

		$template = preg_replace_callback("/###(.*?)###/si", function($var) use ($default_options){
			return isset($default_options[$var[1]]) ? $default_options[$var[1]] : '';
		}, $template);

		$template = str_replace("{#mailcontent#}", $body, $template);
		return is_null($template) ? $body : $template;

	}

}

?>