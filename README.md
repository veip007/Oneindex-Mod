# OneIndex-MOD  
Onedrive Directory Index  
改动自downwa提供的Oneindex，此为自用改动版本。  

### 期望增加的功能  
防止加密文件夹子文件夹访问绕过。  

## 安装运行
**请参考downwa的安装教程**  
<img width="658" alt="image" src="https://raw.githubusercontent.com/veip007/oneindex/files/images/install.gif">  

## 必要配置
由于只改了filecache，缓存方式务必选择filecache。  
由于缓存自动更新，请修改缓存过期时间为无穷大，比如233333333。  
还有其它配置写在index.php前面。请用代码编辑器打开并修改。  

### 安装说明与必要配置
安装与原版安装并无区别，绑定世纪互联账户:
####·第一步：应用注册
![image](https://tva3.sinaimg.cn/large/006rXXh5gy1gepkyv8n6gj30gv09yq3m.jpg)
打开世纪互联Azure管理面板```portal.azure.cn```并登录
依次打开“Azure Active Directory”->“应用注册”
添加一个新应用，信息如图所示，名称随意。
注册完就可以看到应用ID（客户端ID）了
####·第二步：修改Oneindex代码
打开Oneindex的”index.php”文件。可以看到写着“世纪互联”后面的两行已经注释掉了。删掉注释即可。
####·第三步：安装
在安装页面把刚才申请的”ClientID”和”ClientSecret”填入即可。其它与原版安装并无差别，不再多加描述。
问题:不知道为什么，Oneindex安装世纪互联会出现“Service unavailable”的问题。第一次成功打开文件，再打开就提示这个了。过了一天自己好了。


**因为自动更新缓存，所以不用crontab，并且缓存过期时间设置为无限大，比如233333。
由于只改了filecache缓存模式，务必选择filecache缓存模式 **

###cdn/反代配置
首先要配置好一个cdn/反代：
选择加速方式
然后打开```index.php```，编辑```$PROXY_NAME```数组。
在数组里面添加代理名称，关闭代理编号为0，比如：
```
$PROXY_NAME=array("关闭","cf workers","普通cdn","反代","腾讯云鉴权cdn")
```
然后编辑```proxyurl```函数。下面给出几个示例，其它的自己写：
```
//CF Workers 代理
if ($id==1)
	return "https://cfworkers.domain.com/?url=".urlencode($url);

//普通cdn，把onedrive域名替换为cdn域名
if ($id==2)
	return str_replace("xxx-my.sharepoint.com","cdn.domain.com",$url);

//nat vps反代CF Workers
if ($id==3)
	return "http://rp.domain.com:12321?url=".urlencode($url);

//腾讯云鉴权cdn，配置的时候鉴权方式选择type-a
if ($id==4)
	return "https://lxy.domain.com".qcloudcdn($url,"鉴权密钥");
```

###其它配置
get方式访问的时候展示：在```$MOD_SHOW_EXT```里面加上扩展名。（不会跳转到下载链接而是走oneindex的展示页面）

把文件缓存到vps：在```$MOD_NEEDCACHE_EXT```里面加扩展名。

重新绑定账户：访问```?reauth```



### cdn/反代配置说明

首先要配置好一个cdn/反代：
选择加速方式
·用vps做反代
速度：氪金越多越快
·用cloudflare做代理
速度：一般
价格：免费，提供用不完的请求数
·Fast.io的储存加速服务
速度：较快
价格：免费
限制：单文件限制500mb，需要放到指定的文件夹
·腾讯云CDN
速度：飞快
价格：收费较贵，可用代金卷撸，新用户前六个月免费每月10G流量
限制：域名需要备案
·NodeCache
速度：一般
价格：新用户1T免费流量

####查询Onedrive域名
登录OneDrive，记下网址前面形如```xxx-my.sharepoint.com```的部分。
这个域名即为回源域名/源站地址/回源Host/反代目标域名/发送域名
![image](https://tva2.sinaimg.cn/large/006rXXh5gy1gepl4yttj7j30u001574h.jpg)

### vps反代
把Onedrive域名反代成自己的域名
宝塔：参考“利用宝塔反向代理”
普通vps：参考“apache配置反代”
最后访问```https://自己的域名/personal/xxxx/_layouts/15/download.aspx...```

PS：当然你也可以反代```xxx-my.sharepoint.com /personal/xxxx/_layouts/15/download.aspx ```，然后访问的时候就不用加上那么一大条东西了。

### Cloudflare workers
参考:```https://raw.githubusercontent.com/veip007/Oneindex-Mod/master/proxy.js```
最后访问```https://cf域名?url=经过urlencode的OneDrive直链```

### Fast.io部署
官网：```Fast.io```，傻瓜式操作

注册登录
绑定账户
把文件放到指定的文件夹
在fast.io刷新同步
访问```https://自定义的域名/Onedrive文件路径```
CDN部署方式
所有cdn部署方式都相似，以腾讯云为例。
然后，新建cdn，添加域名，参数如下：
![image](https://tvax4.sinaimg.cn/large/006rXXh5gy1geplbddt4oj30q90zk40y.jpg)
提交完点进去，可以看到cname，把域名解析到cname上即可。
![image](https://tva4.sinaimg.cn/large/006rXXh5gy1geplbw1x4kj30mk0eu3zl.jpg)
访问方式同vps反代。





