<?php
include "lib/qcloudcdn.php";
require  __DIR__.'/init.php';

//--------------------程序配置--------------------
//默认文件只有post才展示，这里设置无论get/post等都展示的后缀名
$MOD_SHOW_EXT=array("pdf","img","jpg","png","html","css");
//管理员
$ADMIN=false;
//需要除了流输出和代码模式之外缓存的文件扩展名和文件名，目前在改动的手动和自动刷新文件缓存时使用
$MOD_NEEDCACHE_EXT=array();//默认会加上页面配置的流输出模式和代码模式的文件
$MOD_NEEDCACHE_NAME=array("index.html",".password","HEAD.md","README.md");
//下载url超时时间（秒）
$MOD_URL_TIMEOUT=3600-10;
//代理名称
$PROXY_NAME=array("关闭");
function proxyurl($url)
{
	$id=$_COOKIE["proxy"];
	if (!isset($id)||$id==0)
		return $url;
	return $url;
}
//------------------------------------------------

if ($_COOKIE['admin'] == md5(config('password').config('refresh_token')))
	$ADMIN=true;

$show = config('show');
if ($show)
	foreach (array("stream","code") as $key)//从config获取设置的所有需要缓存的文件扩展名
		$MOD_NEEDCACHE_EXT=array_merge($MOD_NEEDCACHE_EXT,$show[$key]);
if ($ADMIN && isset($_GET["reauth"]))
{
	config("refresh_token","");
	header('Location: '.dirname($_SERVER["PHP_SELF"]));
	die();
}
error_reporting(0); 

//世纪互联
//onedrive::$api_url = "https://microsoftgraph.chinacloudapi.cn/v1.0";
//onedrive::$oauth_url = "https://login.partner.microsoftonline.cn/common/oauth2/v2.0";


/**
 *    程序安装
 */
if( empty( config('refresh_token') ) ){
	route::any('/','AdminController@install');
}

/**
 *    系统后台
 */
route::group(function(){
	return ($_COOKIE['admin'] == md5(config('password').config('refresh_token')) );
},function(){
	route::get('/logout','AdminController@logout');
	route::any('/admin/','AdminController@settings');
	route::any('/admin/cache','AdminController@cache');
	route::any('/admin/show','AdminController@show');
	route::any('/admin/setpass','AdminController@setpass');
	route::any('/admin/images','AdminController@images');

	route::any('/admin/upload','UploadController@index');
	//守护进程
	route::any('/admin/upload/run','UploadController@run');
	//上传进程
	route::post('/admin/upload/task','UploadController@task');
});
//登陆
route::any('/login','AdminController@login');

//跳转到登陆
route::any('/admin/',function(){
	return view::direct(get_absolute_path(dirname($_SERVER['SCRIPT_NAME'])).'?/login');
});



define('VIEW_PATH', ROOT.'view/'.(config('style')?config('style'):'material').'/');
/**
 *    OneImg
 */
$images = config('images@base');
if( ($_COOKIE['admin'] == md5(config('password').config('refresh_token')) || $images['public']) ){
	route::any('/images','ImagesController@index');
	if($images['home']){
		route::any('/','ImagesController@index');
	}
}


/**
 *    列目录
 */
route::group(function () {
	$hotlink = config('onedrive_hotlink');

	// 未启用防盗链
	if (!$hotlink) {
		return true;
	}
	// referer 不存在
	if (!isset($_SERVER['HTTP_REFERER'])) {
		return true;
	}

	$referer_domain = get_domain($_SERVER['HTTP_REFERER']);
	// 当前域本身
	if (str_is(get_domain(), $referer_domain)) {
		return true;
	}

	// 白名单
	$hotlinks = explode(';', $hotlink);
	$referer = false;
	
	foreach ($hotlinks as $_hotlink) {
		if (str_is(trim($_hotlink), $referer_domain)) {
			$referer = true;
		}
	}
	if (!$referer) {
		header('HTTP/1.1 403 Forbidden');
	}

	return $referer;
}, function() {
    route::any('{path:#all}','IndexController@index');
});
?>
