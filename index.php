<?php
//引入类文件
require './wechat.inc.php';
//实例化类
$wechat = new Wechat();
//调用方法
//判断是来验证的，还是来请求消息的
if($_GET["echostr"]){
  $wechat->valid();
}else{
  $wechat->responseMsg();
}
