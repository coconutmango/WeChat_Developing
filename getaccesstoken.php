<?php
//引入wechat类文件
require './wechat.inc.php';
//实例化类
$wechat = new Wechat();
//调用类内属性方法
$wechat->getAccessToken();
