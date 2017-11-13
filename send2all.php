<?php
//引入类文件
require './wechat.inc.php';
//实例化
$wechat = new Wechat();
//调用类属性方法
$wechat->send2All();