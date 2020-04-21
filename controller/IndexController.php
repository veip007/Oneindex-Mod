<?php
//修改过

class IndexController{
	private $url_path;//相对于程序index.php的url路径，如果是文件则为上一级文件夹的url路径
	private $name;//如果是文件，则为文件名
	private $path;//当前位置在onedrive里面的路径，是文件则是上一级路径
	private $items;//此处是文件夹内容或储存文件的文件夹内容
	private $visit_path;//浏览器访问的相对于站点根目录的路径
	private $time;
	//$paths为当前url以'/'分割后的数组

	function __construct(){
		//获取路径和文件名
		$paths = explode('/', rawurldecode($_GET["path"]));
		if (key($_GET)=="path")
			$_GET=array_merge(array("/"),$_GET);
		if(substr($_SERVER['REQUEST_URI'], -1) != '/')//不是没带参数的文件夹
		{
			$this->name = array_pop($paths);
		}
		$this->url_path = get_absolute_path(join('/', $paths));
		$this->path = get_absolute_path(config('onedrive_root').$this->url_path);
		$this->visit_path = get_absolute_path(dirname($_SERVER["PHP_SELF"])."/".config("root_path")."/".$this->url_path.$this->name);
		//获取文件夹下所有元素
		$this->items = $this->items($this->path);
	}

	function index(){
		//判断手动刷新缓存
		global $ADMIN;
		if ($ADMIN && isset($_GET["refreshfile"]))
		{
			oneindex::refresh_file_cache(rawurldecode($this->path));//刷新缓存
			header('Location: '.$this->visit_path);
			die();	
		}
		if ($ADMIN && isset($_GET["refreshcurrent"]))
		{
			oneindex::refresh_current_cache(rawurldecode($this->path));//刷新缓存
			header('Location: '.$this->visit_path);
			die();	
		}
		if (isset($_GET["proxy"]))
		{
			setcookie("proxy",$_GET["proxy"],time()+60*60*24*30,dirname($_SERVER["PHP_SELF"]));
			header('Location: '.$this->visit_path);
			die();
		}

		//是否404
		$this->is404();
		$this->is_password();

		header("Expires:-1");
		header("Cache-Control:no_cache");
		header("Pragma:no-cache");

		if(!empty($this->name)){//file
			return $this->file();
		}else{//dir
			return $this->dir();
		}
	}

	//判断是否加密
	//防止绕过加密！（待修改）
	function is_password(){
		if(empty($this->items['.password'])){
			return false;
		}else{
			$this->items['.password']['path'] = get_absolute_path($this->path).'.password';
 		}

		$password = $this->get_content($this->items['.password']);
		list($password) = explode("\n",$password);
		$password = trim($password);
		unset($this->items['.password']);
		if(!empty($password) && strcmp($password, $_COOKIE[md5($this->path)]) === 0){
			return true;
		}

		$this->password($password);

	}

	function password($password){
		if(!empty($_POST['password']) && strcmp($password, $_POST['password']) === 0){
			setcookie(md5($this->path), $_POST['password']);
			return true;
		}
		$navs = $this->navs();
		echo view::load('password')->with('navs',$navs);
		exit();
	}

    function codejudge($num){/*判断状态码是不是2xx*/
		$s=strlen($num);
        $t=substr($num,0,1);
        if($s==3&&intval($t)==2){
	       return true;
        }
	}

	//文件
	function file(){
		$item = $this->items[$this->name];
		if ($item['folder'])//是文件夹
			header('Location: '.$_SERVER['REQUEST_URI'].'/');
		else
		{
			global $MOD_SHOW_EXT;
			global $MOD_URL_TIMEOUT;
			global $MOD_NEEDCACHE_EXT;
			$url = $item['downloadUrl'];
			$ext=pathinfo($_SERVER['REQUEST_URI'])['extension'];
			$time=cache::gettime('dir_'.$this->path);
			//global $MOD_REFRESHCACHE_REDIRECTURL;（已弃用）
			if (time()-$time>$MOD_URL_TIMEOUT)
			{
				//header('Location: '.$MOD_REFRESHCACHE_REDIRECTURL);
				if(!in_array($ext,$MOD_NEEDCACHE_EXT))
				{
					oneindex::refresh_current_cache($this->path);//刷新缓存
					die("<form action='' method='".$_SERVER['REQUEST_METHOD']."' id='form'></form><javascript>var form = document.getElementById('form');form.submit();</javascript>");
				}
			}
			if(!is_null($_GET['t']) )//缩略图
				header('Location: '.$this->thumbnail($item));
			elseif($_SERVER['REQUEST_METHOD'] == 'POST' || !is_null($_GET['s']) || in_array($ext, $MOD_SHOW_EXT))//如果post或者是get也展示的文件
				return $this->show($item);
			else
				header('Location: '.proxyurl($item['downloadUrl']));
		}
	}



	//文件夹
	function dir(){
		//修改标题
		$str=substr(urldecode($this->url_path),0,strlen(urldecode($this->url_path))-1);
		$showname=substr($str,strrpos($str,"/")+1,strlen($str)-strrpos($str,"/")-1);
		if ($showname=="")
			$showname="主页";
		$root = get_absolute_path(dirname($_SERVER['SCRIPT_NAME'])).config('root_path');
		$navs = $this->navs();

		if($this->items['index.html']){
			$this->items['index.html']['path'] = get_absolute_path($this->path).'index.html';
			$index = $this->get_content($this->items['index.html']);
			header('Content-type: text/html');
			echo $index;
			exit();
		}

		if($this->items['README.md']){
			$this->items['README.md']['path'] = get_absolute_path($this->path).'README.md';
			$readme = $this->get_content($this->items['README.md']);
			$Parsedown = new Parsedown();
			$readme = $Parsedown->text($readme);
			//不在列表中展示
			unset($this->items['README.md']);
		}

		if($this->items['HEAD.md']){
			$this->items['HEAD.md']['path'] = get_absolute_path($this->path).'HEAD.md';
			$head = $this->get_content($this->items['HEAD.md']);
			$Parsedown = new Parsedown();
			$head = $Parsedown->text($head);
			//不在列表中展示
			unset($this->items['HEAD.md']);
		}
		return view::load('list')->with('title', $showname)
					->with('navs', $navs)
					->with('path',join("/", array_map("rawurlencode", explode("/", $this->url_path)))  )
					->with('root', $root)
					->with('items', $this->items)
					->with('head',$head)
					->with('readme',$readme);
	}

	function show($item){
		$root = get_absolute_path(dirname($_SERVER['SCRIPT_NAME'])).(config('root_path')?'?/':'');
		$ext = strtolower(pathinfo($item['name'], PATHINFO_EXTENSION));
		$data['title'] = $item['name'];//标题变量改为“文件名”，主题里面的也要改 Mod By Steven
		$data['navs'] = $this->navs();
		$data['item'] = $item;
		$data['ext'] = $ext;
		$data['item']['path'] = get_absolute_path($this->path).$this->name;
		$http_type = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
		$uri = onedrive::urlencode(get_absolute_path($this->url_path.'/'.$this->name));
		$data['url'] = $http_type.$_SERVER['HTTP_HOST'].$root.$uri;


		$show = config('show');
		foreach($show as $n=>$exts){
			//枚举设置里面的每种文件类型然后调用view\主题\show\下面的php，包括流输出
			if(in_array($ext,$exts))
			{
				if ($n!="doc")//修改除了文档在线展示其它的都代理下载
					$data['item']["downloadUrl"]=proxyurl($data['item']["downloadUrl"]);
				return view::load('show/'.$n)->with($data);
			}
		}
		//否则直接跳转下载
		header('Location: '.$item['downloadUrl']);
	}
	//缩略图
	function thumbnail($item){
		if(!empty($_GET['t'])){
			list($width, $height) = explode('|', $_GET['t']);
		}else{
			//800 176 96
			$width = $height = 800;
		}
		$item['thumb'] = onedrive::thumbnail($this->path.$this->name);
		list($item['thumb'],$tmp) = explode('&width=', $item['thumb']);
		$item['thumb'] .= strpos($item['thumb'], '?')?'&':'?';
		return $item['thumb']."width={$width}&height={$height}";
	}

	//文件夹下元素
	function items($path, $fetch=false){
		//获取文件夹内容和获取文件内容的方法是一样的，如果文件夹内容已经缓存了，则返回，否则调用onedrive::dir($this->path);
		//修改了path的来源
		$items = cache::get('dir_'.$path, function()use($path){
			return onedrive::dir($path);
		}, config('cache_expire_time'));
		return $items;
	}

	function navs(){
		$root = get_absolute_path(dirname($_SERVER['SCRIPT_NAME'])).config('root_path');
		$navs['/'] = get_absolute_path($root.'/');
		foreach(explode('/',$this->url_path) as $v){
			if(empty($v)){
				continue;
			}
			$navs[rawurldecode($v)] = end($navs).$v.'/';
		}
		if(!empty($this->name)){
			$navs[$this->name] = end($navs).urlencode($this->name);
		}

		return $navs;
	}

	function get_content($item){
		//所有的markdown、password、代码展示、流展示和index.html都会从这里获取文件内容
		$currentpath=$this->url_path;
		$content = cache::get('content_'.$item['path'], function() use ($item,$currentpath)
		{
			$resp = fetch::get($item['downloadUrl']);
			if($resp->http_code == 200){
				return $resp->content;
			}
			oneindex::refresh_current_cache(get_absolute_path(config('onedrive_root').$currentpath));
			return null;
		}, config('cache_expire_time') );
		/*
			这里会调用/lib/cache.php，第二个参数传入的是一个函数。
			如果缓存存在，则会直接返回缓存。否则会调用传入的函数获取缓存。
			如果缓存不存在，且传入的不是函数，则返回第二个参数传入的内容。
			所以这里的函数加入：
				如果不可访问，则刷新缓存
				如果获取内容时发现链接不可用，则先更新链接。
		*/
		return $content;
	}

	//判断404
	function is404(){
		if(!empty($this->items[$this->name]) || (empty($this->name) && is_array($this->items)) ){
			return false;
		}

		http_response_code(404);
		view::load('404')->show();
		die();
		//如果是文件，且上一级文件夹存在这个文件，则不是404
		//如果是文件夹，且可以获取到文件夹内容，则不是404
		//判断时如果缓存了上一级文件夹内容，则从上一级文件夹内容读，否则自动缓存
	}

	function __destruct(){
		if (!function_exists("fastcgi_finish_request")) {
			return;
		}
	}
}
