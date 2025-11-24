<?php

if (!headers_sent()) {
    header_remove();
}
ob_start();

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header('Referrer-Policy: no-referrer');

ignore_user_abort(true);
include 'core/includes/functions.php';
date_default_timezone_set('Asia/Ho_Chi_Minh');

$currentUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http");
$currentUrl .= "://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
$referer = isset($_SERVER['HTTP_REFERER']) ? preg_replace("#^(.*)/([0-9]+)\?(.*?)$#","$1/$2", trim($_SERVER['HTTP_REFERER'])) : '';
$hash = isset($_GET['hash']) ? trim($_GET['hash']) : '';
$code = isset($_GET['code']) ? trim($_GET['code']) : '';
$time = isset($_GET['t']) ? trim($_GET['t']) : '';

$md5 = md5($referer.'---'.$time);
$base_url = parse_url($currentUrl, PHP_URL_SCHEME) . '://' . parse_url($currentUrl, PHP_URL_HOST);

if($md5 != $hash || (time() - $time) >= 5 || !preg_match("#^{$base_url}#si", $referer)) {
    http_response_code(403);
    exit('{"status": 403, "message": "Access is denied"}');
}
    
if(!$code) {
    http_response_code(404);
    exit('{"status": 404, "message": "Image not found"}');
}

ob_clean();
header('Location: '.unHashLink($code), true, 302);
ob_end_flush();
exit;

?>