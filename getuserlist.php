<?php
//引入类文件
require './wechat.inc.php';
//实例化类
$wechat = new Wechat();
//调用方法
$wechat->getUserList();