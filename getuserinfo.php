<?php
//引入类文件
require './wechat.inc.php';
//实例化类
$wechat = new Wechat();
//get接收openid值
$openid = $_GET['openid'];
//调用方法
$wechat->getUserInfo($openid);