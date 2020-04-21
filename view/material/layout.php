<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0,maximum-scale=1.0, user-scalable=no"/>
	<title><?php e($title.' - '.config('site_name'));?></title>
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/mdui@0.4.1/dist/css/mdui.min.css" integrity="sha256-lCFxSSYsY5OMx6y8gp8/j6NVngvBh3ulMtrf4SX5Z5A=" crossorigin="anonymous">
	<script src="https://cdn.jsdelivr.net/npm/mdui@0.4.1/dist/js/mdui.min.js" integrity="sha256-dZxrLDxoyEQADIAGrWhPtWqjDFvZZBigzArprSzkKgI=" crossorigin="anonymous"></script>
	<style>
		.mdui-appbar .mdui-toolbar{
			height:56px;
			font-size: 16px;
		}
		.mdui-toolbar>*{
			padding: 0 6px;
			margin: 0 2px;
			opacity:0.5;
		}
		.mdui-toolbar>.mdui-typo-headline{
			padding: 0 16px 0 0;
		}
		.mdui-toolbar>i{
			padding: 0;
		}
		.mdui-toolbar>a:hover,a.mdui-typo-headline,a.active{
			opacity:1;
		}
		.mdui-container{
			max-width:980px;
		}
		.mdui-list-item{
			-webkit-transition:none;
			transition:none;
		}
		.mdui-list>.th{
			background-color:initial;
		}
		.mdui-list-item>a{
			width:100%;
			line-height: 48px
		}
		.mdui-list-item{
			margin: 2px 0px;
			padding:0;
		}
		.mdui-toolbar>a:last-child{
			opacity:1;
		}
		@media screen and (max-width:980px){
			.mdui-list-item .mdui-text-right{
				display: none;
			}
			.mdui-container{
				width:100% !important;
				margin:0px;
			}
			.mdui-toolbar>*{
				display: none;
			}
			.mdui-toolbar>a:last-child,.mdui-toolbar>.mdui-typo-headline,.mdui-toolbar>i:first-child{
				display: block;
			}
		}
	</style>
	<style>
		.bar
		{
			position:absolute;
			top:40px;
			right:10px;
			text-align: center;
		}
		.bar div
		{
			margin:5px 0;
		}
		.bar a
		{
			color:red;
			text-decoration:none;
		}
	</style>
</head>

<?php
	global $ADMIN;
	$url=$_SERVER['REQUEST_URI'].(config("root_path")=="?"?"&":"?");
?>
	
<div class="bar">
	<?php if ($ADMIN):?>
	<div style="margin-top:20px">刷新缓存</div>
	<div><a href="<?php echo $url."refreshcurrent" ?>">刷新目录</a></div>
	<div><a href="<?php echo $url."refreshfile" ?>">刷新文件</a></div>
	<?php endif;?>
	<div style="margin-top:20px">代理开关</div>
<?php
	global $PROXY_NAME;
	foreach ($PROXY_NAME as $id=>$name)
		echo "<div><a href='".$url."proxy=$id'>$name</a></div>";
?>
</div>

<body class="mdui-theme-primary-blue-grey mdui-theme-accent-blue">
	<header class="mdui-appbar mdui-color-theme">
		<div class="mdui-toolbar mdui-container">
			<a href="/" class="mdui-typo-headline"><?php e(config('site_name'));?></a>
			<?php foreach((array)$navs as $n=>$l):?>
			<i class="mdui-icon material-icons mdui-icon-dark" style="margin:0;">chevron_right</i>
			<a href="<?php e($l);?>"><?php e($n);?></a>
			<?php endforeach;?>
			<!--<a href="javascript:;" class="mdui-btn mdui-btn-icon"><i class="mdui-icon material-icons">refresh</i></a>-->
		</div>
	</header>
	
	<div class="mdui-container">
    	<?php view::section('content');?>
	</div>
	<div style="text-align:center;color:gray;margin:10px 0">
		<?php
			global $PROXY_NAME;
			$id=$_COOKIE["proxy"];
			if (!isset($id)||$id==0)
				echo "未使用代理";
			else
				echo "使用".$PROXY_NAME[$_COOKIE["proxy"]]."代理";
		?>
	</div>
</body>
</html>