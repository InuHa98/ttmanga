<?php

class BBcode
{

	public static function tags($var = '')
	{
		$bbcode = array(
			"/\[b\](.*?)\[\/b\]/is" => "<b>$1</b>",
			"/\[i\](.*?)\[\/i\]/is" => "<i>$1</i>",
			"/\[u\](.*?)\[\/u\]/is" => "<u>$1</u>",
			"/\[s\](.*?)\[\/s\]/is" => "<s>$1</s>",
			"/\[font\=(.*)\](.*)\[\/font\]/is" => '<font face="$1">$2</font>',
			"/\[size\=(.*?)\](.*?)\[\/size\]/is" => '<font size="$1">$2</font>',
			"/\[small\](.*?)\[\/small\]/is" => "<font size=\"x-small\">$1</font>",
			"/\[color\=(.*?)\](.*?)\[\/color\]/is" => "<font color=\"$1\">$2</font>",
			"/\[smiley\](.*?)\[\/smiley\]/is" => "<img class=\"comment-smiley-icon\" src=\"$1\" />",
			"/\[img\](.*?)\[\/img\]/is" => "<img src=\"$1\" alt=\"Image\" />",
			"/\[img\=(.*)\]/is" => "<img src=\"$1\" alt=\"Image\" />",
			"/\[left\](.*?)\[\/left\]/is" => "<span align=\"left\">$1</span>",
			"/\[right\](.*?)\[\/right\]/is" => "<span align=\"right\">$1</span>",
			"/\[center\](.*?)\[\/center\]/is" => "<center>$1</center>",
			"/\[align\=(left|center|right)\](.*?)\[\/align\]/is" => "<div style=\"text-align: $1;\">$2</div>",
			"/\[url=([\"']|&quot;|)(.*?)(?:\\1)(.*?)(?:[\"']|&quot;)?\](.*?)\[\/url\]/is" => "<a rel=\"nofollow\" href=\"$2$3\" target=\"_blank\" title=\"$2$3\">$4</a>",
			"/\[url\](.*?)\[\/url\]/is" => "<a rel=\"nofollow\" href=\"$1\" target=\"_blank\" title=\"$1\">$1</a>"
		);
		$var = preg_replace(array_keys($bbcode), array_values($bbcode), $var);
		$var = preg_replace_callback(
			'/\[tag=([0-9]+)\]([^\[]+)\[\/tag\]/is',
			function ($m) {
				$id = $m[1];
				$name = $m[2];
				$url = RouteMap::get('profile', ['id' => $id]);
				return '<a class="mce-tag-username" data-id="' . $id . '" target="_blank" href="' . $url . '">' . $name . '</a>';
			},
			$var
		);
		return $var;
	}

	public static function notags($var = '')
	{
		$bbcode = array(
			"/\[b\](.*?)\[\/b\]/is" => "$1",
			"/\[i\](.*?)\[\/i\]/is" => "$1",
			"/\[u\](.*?)\[\/u\]/is" => "$1",
			"/\[s\](.*?)\[\/s\]/is" => "$1",
			"/\[font\=(.*)\](.*)\[\/font\]/is" => "$2",
			"/\[size\=(.*?)\](.*?)\[\/size\]/is" => "$2",
			"/\[small\](.*?)\[\/small\]/is" => "$1",
			"/\[download\](.*?)\[\/download\]/is" => "$1",
			"/\[color\=(.*?)\](.*?)\[\/color\]/is" => "$2",
			"/\[smiley\](.*?)\[\/smiley\]/is" => "",
			"/\[tag=([0-9]+)\]([^\[]+)\[\/tag\]/is" => "$2",
			"/\[img\](.*?)\[\/img\]/is" => "",
			"/\[img\=(.*)\]/is" => "",
			"/\[left\](.*?)\[\/left\]/is" => "$1",
			"/\[right\](.*?)\[\/right\]/is" => "$1",
			"/\[center\](.*?)\[\/center\]/is" => "$1",
			"/\[align\=(left|center|right)\](.*?)\[\/align\]/is" => "$2",
			"/\[url=([\"']|&quot;|)(.*?)(?:\\1)(.*?)(?:[\"']|&quot;)?\](.*?)\[\/url\]/is" => "$3$2",
			"/\[url\](.*?)\[\/url\]/is" => "$1"
		);
		$var = preg_replace(array_keys($bbcode), array_values($bbcode), $var);
		return $var;
	}

	public static function hide($var = '')
	{
		$bbcode = array(
			"/\[img\](.*?)\[\/img\]/is" => " [image] ",
			"/\[smiley\](.*?)\[\/smiley\]/is" => " [icon] ",
			"/\[tag=([0-9]+)\]([^\[]+)\[\/tag\]/is" => "$2",
			"/\[img\=(.*)\]/is" => " [image] "
		);
		$var = preg_replace(array_keys($bbcode), array_values($bbcode), $var);
		return self::notags($var);
	}
}

?>