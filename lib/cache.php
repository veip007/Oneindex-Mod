<?php
//修改过，加了批注
	!defined('CACHE_PATH') && define('CACHE_PATH', sys_get_temp_dir().'/');
	class cache{
		// 驱动方式（支持filecache/memcache/secache）
		static $type = 'secache';

		// 返回缓存实例
    	protected static function c(){
	    	static $instance = null;
	    	if(!is_null($instance)){
		    	return $instance;
	    	}
	    	
			list($type, $config) = explode(':', self::$type, 2);

			$type .= '_';
	    	if( in_array($type, array('filecache_', 'memcache_', 'secache_', 'redis_')) ){
				//调用cache驱动代码
		    	$file = str_replace("\\", "/", dirname(__FILE__)) . '/cache/'.$type.'.php';
			    include_once( $file );
		    	$instance = new $type($config);
		    	return $instance;
	    	}
    	}

		// 获取缓存
		static function get($key, $default=null, $expire=99999999){
			/*
			如果缓存存在，则会直接返回缓存。否则会调用传入的函数获取缓存。
			如果缓存不存在，且传入的不是函数，则返回第二个参数传入的内容。
			expire在缓存驱动文件里面判断。
			*/
			$value = self::c()->get($key);//获取该文件缓存的内容
			if(!is_null($value)&&$value!=false){//Mod by Steven 修改了有时缓存错误为false也重新检测
				return $value;
			}elseif(is_callable($default)){
				$value = $default();
				self::set($key, $value, $expire);
				return $value;
			}elseif(!is_null($default)){
				self::set($key, $default, $expire);
				return $default;
			}
		}

		//获取缓存设置时间
		//Mod by Steven
		static function gettime($key)
		{
			return self::c()->gettime($key);
		}

		// 设置缓存
		static function set($key, $value, $expire=99999999){
			return self::c()->set($key, $value, $expire);
		}

		// 清空缓存
		static function clear(){
			return self::c()->clear();
		}

		// 删除缓存
		static function del($key){
			return self::set($key, null);
		}

		// 判断缓存是否设置
		static function has($key){
			if(is_null(self::get($key))){
				return false;
			}else{
				return true;
			}
		}
		// 读取并删除缓存
		static function pull($key){
			$value = self::get($key);
			self::del($key);
			return $value;
		}
	}
