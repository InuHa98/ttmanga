<?php

if(!function_exists('mb_strlen'))
{
    function mb_strlen($str = null)
    {
        return strlen($str);
    }
}

if(!function_exists('mb_strtolower')) {
    function mb_strtolower($str = null) {
        return strtolower($str);
    }
}

function env($name = null, $default = null)
{
	$result = getenv($name, true) ? trim(getenv($name).'') : (isset($_ENV[$name]) ? $_ENV[$name] : $default);
    if(is_string($result) && in_array(strtolower($result), ['false', 'true']))
    {
        $result = filter_var($result, FILTER_VALIDATE_BOOLEAN);
    }
    return $result;
}

function view($viewName = null, $data = null)
{
	return View::render($viewName, $data);
}

function lang($section = null, $key = null, $data = null)
{
    return Language::get($section, $key, $data);
}

function redirect($url) {
    return Router::redirect('*', $url);
}

function redirect_route($route_name, $data = []) {
    return Router::redirect('*', RouteMap::get($route_name, $data));
}

function _echo($var = "", $nl2br = false, $bbcode = null){
    if(is_array($var)){
        return array_map(__FUNCTION__, $var);
    }

    $var     = htmlentities(trim($var.''), ENT_QUOTES, 'UTF-8');
    $replace = array(
        chr(0) => '',
        chr(1) => '',
        chr(2) => '',
        chr(3) => '',
        chr(4) => '',
        chr(5) => '',
        chr(6) => '',
        chr(7) => '',
        chr(8) => '',
        chr(9) => '',
        chr(11) => '',
        chr(12) => '',
        chr(13) => '',
        chr(13) => '',
        chr(14) => '',
        chr(15) => '',
        chr(16) => '',
        chr(17) => '',
        chr(18) => '',
        chr(19) => '',
        chr(20) => '',
        chr(21) => '',
        chr(22) => '',
        chr(23) => '',
        chr(24) => '',
        chr(25) => '',
        chr(26) => '',
        chr(27) => '',
        chr(28) => '',
        chr(29) => '',
        chr(30) => '',
        chr(31) => ''
    );
    $var = strtr($var, $replace);
    
    if($nl2br === true)
    {
        $var = nl2br($var);
    }
    if ($bbcode === true) {
        $var = BBcode::tags($var);
    } elseif ($bbcode === false) {
        $var = BBcode::notags($var);
    }

    return $var;
}

function _time($time_ago)
{
    if(!$time_ago)
    {
        return null;
    }
    
    $current_time    = time();
    $time_difference = $current_time - intval($time_ago);
    $seconds         = $time_difference;

    $minutes = round($seconds / 60); // value 60 is seconds
    $hours   = round($seconds / 3600); //value 3600 is 60 minutes * 60 sec
    $days    = round($seconds / 86400); //86400 = 24 * 60 * 60;
    $weeks   = round($seconds / 604800); // 7*24*60*60;
    $months  = round($seconds / 2629440); //((365+365+365+365+366)/5/12)*24*60*60
    $years   = round($seconds / 31553280); //(365+365+365+365+366)/5 * 24 * 60 * 60

    if ($seconds <= 60) {
        return 'Vừa xong';
    } else if ($minutes <= 60) {
        return $minutes .' phút trước';
    } else if ($hours <= 24) {
        return $hours. ' giờ trước';
    } else if ($days <= 7) {
        return $days.' ngày trước';
    /*
    } else if ($weeks <= 4.3) {
        return $weeks.' tuần trước';
    } else if ($months <= 12){
        return $months.' tháng trước';
    */
    } else {
        return date('d/m/Y H:i', $time_ago);
    }
}

function _txtColor($text = "", $color = ""){
    return $color ? '<font color="'.$color.'">'._echo($text).'</font>': _echo($text);
}

function current_url()
{
    return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
}

function html_pagination($data = null, $url = null)
{
	if(!isset($data['current_page']))
	{
		$data = Pagination::get($url);
	}

    if($data['total_page'] <= 1)
    {
        return;
    }



    echo '<div class="pagination_wrap">';

    echo '<a class="page '.($data['previous'] == false ? 'disabled' : '').'" href="'.Pagination::build_url($data['previous']).'">‹ Trước</a>';


    foreach ($data['pages'] as $page){

        if($page == $data['current_page'])
        {
            echo '<span class="page active">'.$page.'</span>';
        }
        else
        {
            echo '<a class="page '.(!is_numeric($page) ? 'disabled' : '').'" href="'.Pagination::build_url($page).'">'.$page.'</a>';
        }
    }


    echo '<a class="page '.($data['next'] == false ? 'disabled' : '').'" href="'.Pagination::build_url($data['next']).'">Sau ›</a>';

    echo '</div>';

}

function get_imgur_client()
{
    $imgur_client = env(DotEnv::IMGUR_CLIENT_ID, []);

    if(!is_array($imgur_client))
    {
        return $imgur_client;
    }
    return $imgur_client[array_rand($imgur_client)];
}

function upload_imgur($data = null, $client_id = null)
{
    if(!$data || !$client_id)
    {
        return false;
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://api.imgur.com/3/image');
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Client-ID ' . $client_id
    ]);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, ["image" => $data]);
    $result = curl_exec($ch);
    curl_close($ch);
    $result = json_decode($result, true);

    return isset($result['data']['link']) ? $result['data']['link'] : false;
}

function render_avatar($user_id, $image = null, $show_name = false, $is_small = false, $class = [], $html = null)
{
    $user = is_array($user_id) ? $user_id : User::get($user_id);
	if (!$user) {
		return '';
	}

    $first_name = User::get_first_charname($user);
    $background = User::get_color_avatar($first_name);
    return '
    <span class="user-avatar'.($is_small ? ' avatar--small' : null).' '.implode(" ", $class).'" data-text="'._echo($first_name).'" style="--bg-avatar: '.$background.'">
        <img class="img-responsive" src="'.($image ? $image : User::get_avatar($user)).'" />
        '.$html.'
    </span>
    '.($show_name ? '<span class="user-display-name">'.User::get_username($user).'</span>' : null);
}

function getBrowser($u_agent = null){
    $u_agent = $u_agent ? $u_agent : $_SERVER['HTTP_USER_AGENT']; 
    $bname = 'Unknown';
    $ub = 'Unknown';
    $version = "";
    $platform = 'Unknown';

    $deviceType = 'Desktop';

    if(preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i',$u_agent)||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i',substr($u_agent,0,4))){

        $deviceType='Mobile';

    }

    if($_SERVER['HTTP_USER_AGENT'] == 'Mozilla/5.0(iPad; U; CPU iPhone OS 3_2 like Mac OS X; en-us) AppleWebKit/531.21.10 (KHTML, like Gecko) Version/4.0.4 Mobile/7B314 Safari/531.21.10') {
        $deviceType='Tablet';
    }

    if(stristr($_SERVER['HTTP_USER_AGENT'], 'Mozilla/5.0(iPad;'))
    {
        $deviceType='Tablet';
    }


    if (preg_match('/linux/i', $u_agent)) {
        $platform = 'linux';

    } elseif (preg_match('/macintosh|mac os x/i', $u_agent)) {
        $platform = 'mac';

    } elseif (preg_match('/windows|win32/i', $u_agent)) {
        $platform = 'windows';
    }

    if(preg_match('/MSIE/i',$u_agent) && !preg_match('/Opera/i',$u_agent)) 
    { 
        $bname = 'IE'; 
        $ub = "MSIE";

    } else if(preg_match('/Firefox/i',$u_agent))
    { 
        $bname = 'Mozilla Firefox'; 
        $ub = "Firefox"; 

    } else if(preg_match('/Chrome/i',$u_agent) && (!preg_match('/Opera/i',$u_agent) && !preg_match('/OPR/i',$u_agent))) 
    { 
        $bname = 'Chrome'; 
        $ub = "Chrome"; 

    } else if(preg_match('/Safari/i',$u_agent) && (!preg_match('/Opera/i',$u_agent) && !preg_match('/OPR/i',$u_agent))) 
    { 
        $bname = 'Safari'; 
        $ub = "Safari"; 

    } else if(preg_match('/Opera/i',$u_agent) || preg_match('/OPR/i',$u_agent)) 
    { 
        $bname = 'Opera'; 
        $ub = "Opera"; 

    } else if(preg_match('/Netscape/i',$u_agent)) 
    { 
        $bname = 'Netscape'; 
        $ub = "Netscape"; 

    } else if((isset($u_agent) && (strpos($u_agent, 'Trident') !== false || strpos($u_agent, 'MSIE') !== false)))
    {
        $bname = 'Internet Explorer'; 
        $ub = 'Internet Explorer'; 
    } 
    

    $known = [
        'Version',
        $ub,
        'other'
    ];
    $pattern = '#(?<browser>' . join('|', $known) . ')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';

    if (!preg_match_all($pattern, $u_agent, $matches)) 
    {
    }

    $i = count($matches['browser']);
    if ($i != 1) {
        if (strripos($u_agent,"Version") < strripos($u_agent,$ub))
        {
            $version= $matches['version'][0];

        }
        else
        {
            $version= @$matches['version'][1];
        }

    }
    else 
    {
        $version = $matches['version'][0];
    }

    if ($version == null || $version == "")
    {
        $version = "?";
    }

    return array(
        'user_agent' => $u_agent,
        'browser'      => $bname,
        'browser_version'   => $version,
        'os_platform'  => $platform,
        'pattern'   => $pattern,
        'device'    => $deviceType
    );
}

function json_api($code = 0, $message = null, $data = [], $flags = JSON_PRETTY_PRINT)
{
    header('Content-Type: application/json');
    return json_encode([
        'code' => $code,
        'message' => $message,
        'data' => $data
    ], $flags);
}

function assets($path) {
    return APP_URL.'/assets/'.ltrim($path, '/');
}

function appendUrlApi($url) {
    return str_replace(APP_URL, App::$url.Router::NAMESPACE_API, $url);
}

function hashLink($link = ''){
    if($link == '') {
        return $link;
    }

    $find    = array('https://','http://','.','-','/','_','?','=','%','+','a','d','o','g','j');
    $replace = array('TTmgs','TTmg','0d4O1T1','2D0a1S4h','S1l9A9s8H','L0i2nE','A12sk','Eq9ua8l','P3er8ce1t','pl98U0s','0cc175b','8277e09','d956797','b2f5ff4','363b122');
    $i = 0;
    foreach ($find as $arr) {
        $link = str_replace($arr, $replace[$i], $link);
        ++$i;
    }
    return $link;
}

function unHashLink($hash = '') {
    if($hash == '') {
        return $hash;
    }
    $find    = array('363b122','b2f5ff4','d956797','8277e09','0cc175b','pl98U0s','P3er8ce1t','Eq9ua8l','A12sk','L0i2nE','S1l9A9s8H','2D0a1S4h','0d4O1T1','TTmgs','TTmg');
    $replace = array('j','g','o','d','a','+','%','=','?','_','/','-','.','https://','http://');
    $i = 0;
    foreach ($find as $arr) {
        $hash = str_replace($arr, $replace[$i], $hash);
        ++$i;
    }
    return $hash;
}

function isUrlGoogleImage($link = '') {
    return preg_match("#https?://lh([0-9])\.googleusercontent#is", $link) || preg_match("#https?://lh([0-9])\.ggpht#is", $link) || preg_match("#https?://[0-9]+(\.bp\.blogspot)#is", $link);
}

function shortenNumber($num) {
    if ($num >= 1000000) {
        $main = floor($num / 1000000);
        $remain = floor(($num % 1000000) / 100000);
        return $remain > 0 ? "{$main}.{$remain}M" : "{$main}M";
    } elseif ($num >= 1000) {
        $main = floor($num / 1000);
        $remain = floor(($num % 1000) / 100);
        return $remain > 0 ? "{$main}.{$remain}K" : "{$main}K";
    }
    return number_format($num, 0, ',', '.');
}

?>