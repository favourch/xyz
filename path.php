<?php
require_once('Connections/tams.php');
require_once('param/param.php');
require_once('PHPMailer/PHPMailerAutoload.php') ;

$path = dirname(__FILE__);

$scheme = 'http';
if(isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on')
    $scheme = 'https';

$suffix = $site_root == '..'? '': "/{$site_root}";

define('BASEURL', $scheme."://".$_SERVER['HTTP_HOST']);
define('SITEURL', BASEURL."{$suffix}");
define('SITEIMGURL', SITEURL."/img");
define('BASEPATH', str_replace("\\", "/", $path));
define('IMGPATH', BASEPATH.'/img');
define('INCPATH', BASEPATH.'/include');
define('CERTPATH', BASEPATH.'/certs');

$base_path = BASEPATH;
$img_path = IMGPATH;

require_once('functions/function.php');
