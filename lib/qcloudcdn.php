<?php
    function randstr($length)
    {
        $str='abcdefghijklmnopqrstuvwxyz0123456789';
        $len=strlen($str)-1;
        $randstr='';
        for($i=0;$i<$length;$i++)
        {
            $num=mt_rand(0,$len);
            $randstr .= $str[$num];
        }
        return $randstr;
    }
    function qcloudcdn($url,$key)
    {
        //typea
        $urlinfo=parse_url($url);
        $rand=randstr(16);
        $time=time()-60;
        $md5=md5($urlinfo["path"]."-".$time."-".$rand."-0-".$key);
        return $urlinfo["path"]."?".$urlinfo["query"]."&sign=".$time."-$rand-0-$md5";
    }
?>