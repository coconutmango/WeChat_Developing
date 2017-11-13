<?php
//引入wechat类文件
require './wechat.inc.php';
//实例化类
$wechat = new Wechat();
//调用类内属性方法
//获取ticket值
$ticket = $wechat->getTicket();
//拿ticket获取二维码
$wechat->getQRCode($ticket);

