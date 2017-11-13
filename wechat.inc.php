<?php
//引入配置文件
require './wechat.cfg.php';
//定义一个wechat类,存放各种微信调用接口的方法
Class Wechat{
  //私有化变量
  private $appid;
  private $appsecret;
  private $token;
  //构造方法，初始化属性
  public function __construct(){
    $this->appid = APPID;
    $this->appsecret = APPSECRET;
    $this->token = TOKEN;
    $this->textTpl = "<xml>
            <ToUserName><![CDATA[%s]]></ToUserName>
            <FromUserName><![CDATA[%s]]></FromUserName>
            <CreateTime>%s</CreateTime>
            <MsgType><![CDATA[%s]]></MsgType>
            <Content><![CDATA[%s]]></Content>
            <FuncFlag>0</FuncFlag>
            </xml>";
    $this->itemTpl = "<item>
            <Title><![CDATA[%s]]></Title>
            <Description><![CDATA[%s]]></Description>
            <PicUrl><![CDATA[%s]]></PicUrl>
            <Url><![CDATA[%s]]></Url>
            </item>";
    $this->newsTpl = "<xml>
            <ToUserName><![CDATA[%s]]></ToUserName>
            <FromUserName><![CDATA[%s]]></FromUserName>
            <CreateTime>%s</CreateTime>
            <MsgType><![CDATA[news]]></MsgType>
            <ArticleCount>%s</ArticleCount>
            <Articles>%s
            </Articles>
            </xml>";
    $this->musicTpl = "<xml>
            <ToUserName><![CDATA[%s]]></ToUserName>
            <FromUserName><![CDATA[%s]]></FromUserName>
            <CreateTime>%s</CreateTime>
            <MsgType><![CDATA[music]]></MsgType>
            <Music>
            <Title><![CDATA[%s]]></Title>
            <Description><![CDATA[%s]]></Description>
            <MusicUrl><![CDATA[%s]]></MusicUrl>
            <HQMusicUrl><![CDATA[%s]]></HQMusicUrl>
            <ThumbMediaId><![CDATA[%s]]></ThumbMediaId>
            </Music>
            </xml>";
  }
  //win测试方法
    public function wintest(){
		echo 'this is win test page';
	  }
  //mac测试方法
    public function mactest(){
    echo 'this is mac test page';
    }
  //调用的微信服务器验证的方法
    public function valid()
      {
          $echoStr = $_GET["echostr"];

          //valid signature , option
          if($this->checkSignature()){
            echo $echoStr;
            exit;
          }
      }
      //发送和接收消息主体方法
      public function responseMsg()
      {
      // file_put_contents('./test.txt','11111');
      //get post data, May be due to the different environments
      //xml模板数据
      $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
          //extract post data
      if (!empty($postStr)){
                  /* libxml_disable_entity_loader is to prevent XML eXternal Entity Injection,
                     the best way is to check the validity of xml by yourself */
                  //微信建议我们开启的一种安全方式
                  libxml_disable_entity_loader(true);
                  $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
                  //进行一个消息类型的分类
                  switch ($postObj->MsgType) {
                    case 'text':
                      $this->_doText($postObj);  //做文本回复操作
                      break;
                    case 'image':
                      $this->_doImage($postObj); //图片消息的简单处理
                      break;
                    case 'location':
                      $this->_doLocation($postObj); //位置的处理
                      break;
                    case 'event':
                      $this->_doEvent($postObj); //事件的处理
                      break;
                    default:
                      # code...
                      break;
                  }
      }
    }
   
   // 根据openID列表群发消息
    
    function send2All(){
      //获取openListStr
      // $openIDStr = $this->getUserList('1');
      //1.url地址
      $url = 'https://api.weixin.qq.com/cgi-bin/message/mass/send?access_token='.$this->getAccessToken();
      //2.组合post数据
      $data = '{
             "touser":[
              "openid1
              ","openid2
              ","openid3
              ","openid4
              ","openid5
              "
              ],
              "msgtype": "text",
              "text": { "content": "群发消息"}
            }';
     //3.发送请求
     $content = $this->request($url,true,'post',$data);
     //4.处理返回值
     var_dump($content);
    }
   
    // 回复音乐信息
    private function _sendMusic($postObj){
      //1.收集需要传输的信息
      $Title = '音乐名';
      $Description = '描述';
      $MusicUrl = '音乐地址';
      $HQMusicUrl = 'HQ音乐地址';
      $ThumbMediaId = 'ThumbMediaId';
      //2.组合Music音乐信息的模板
        $resultStr = sprintf($this->musicTpl, $postObj->FromUserName, $postObj->ToUserName, time(), $Title, $Description, $MusicUrl, $HQMusicUrl, $ThumbMediaId);
      //3.把xml模板输出出来
        echo $resultStr;
    }

    //事件处理
    private function _doEvent($postObj){
      //根据事件属性，封装对应的操作方法
      switch ($postObj->Event) {
        case 'subscribe':
          $this->_doSubscribe($postObj);
          break;
        case 'unsubscribe':
          $this->_doUnsubscribe($postObj);
          # code...
          break;
        case 'CLICK':
          $this->_doClick($postObj);
          break;
        default:
          # code...
          break;
      }
    }
    //自定义菜单点击事件
    private function _doClick($postObj){
      //根据点击传输过来的事件key的值，进行相对性的操作
      switch ($postObj->EventKey) {
        case 'news':
          $this->_sendTuwen($postObj);  //news发送图文信息的处理
          break;
        default:
          # code...
          break;
      }
    }
    //发送图文信息
    private function _sendTuwen($postObj){
      //发送图文的一些相关操作
      //定义一个新闻信息的数组
      $itemArray = array(
      array(
            'Title' => '',
            'Description' => '',
            'PicUrl' => '',
            'Url' => ''
        ),
      array(
            'Title' => '',
            'Description' => '',
            'PicUrl' => '',
            'Url' => ''
        )
      );
      $itemList = '';
      //遍历组合xml item新闻列表
      foreach ($itemArray as $key => $value){
      //.= 连接之前的字符串
        $itemList .= sprintf($this->itemTpl, $value['Title'], $value['Description'], $value['PicUrl'], $value['Url']);
      }
      //组合news图文信息的模板
        $resultStr = sprintf($this->newsTpl, $postObj->FromUserName, $postObj->ToUserName, time(), count($itemArray), $itemList);
      //把xml模板输出出来
        echo $resultStr;
    }
    //关注事件处理
    private function _doSubscribe($postObj){
      //如果用户关注了的话，我们给他返回一句
      //组合经纬度字符串
      $contentStr = '欢迎关注';
      //组合xml文档
      $resultStr = sprintf($this->textTpl, $postObj->FromUserName, $postObj->ToUserName, time(), "text", $contentStr);
      //输出xml文档
      echo $resultStr;

    }
    //取消关注事件处理
    private function _doUnsubscribe(){
      // 记录取消关注的时间和用户ipenid
      $openID = $postObj->FromUserName;
      $time = $postObj->CreateTime;
      // 追加写
      file_put_contents('./unsubscribe.txt',date('Y/m/d H:i:s').'---'.$openID.'\r\n',FILE_APPEND);
    }
    //文本信息的处理
    private function _doText($postObj){
      $keyword = trim($postObj->Content);
      if(!empty( $keyword ))
              {
                $msgType = "text";
                //接入机器人api
                //1.url地址
                // $url = 'http://api.qingyunke.com/api.php?key=free&appid=0&msg='.$keyword;
                //2.发送请求
                // $content = $this->request($url,false);
                //3.处理返回值
                //json解码
                // $content = json_decode($content);
                //获取返回的数据’
                // $contentStr = $content->content;
                //判断如果发送的是音乐两个字，那么就调取想对应的方法
                if($keyword == '音乐'){
                  $this->_sendMusic($postObj);   //发送音乐信息
                  exit;
                }
                $contentStr = "";
                if($keyword == ''){
                  $contentStr = '';
                }
                $resultStr = sprintf($this->textTpl, $postObj->FromUserName, $postObj->ToUserName, time(), $msgType, $contentStr);
                echo $resultStr;
              }
      }
    //图片信息的简单处理
    private function _doImage($postObj){
      //获取发送过来的图片地址
      $picUrl = $postObj->PicUrl;
      //保存图片到本地
      //组合xml文档
      $resultStr = sprintf($this->textTpl, $postObj->FromUserName, $postObj->ToUserName, time(), "text", $picUrl);
      //输出xml文档
      echo $resultStr;
    }
    //位置信息的简单处理
    private function _doLocation($postObj){
      //组合经纬度字符串
      $contentStr = '您当前位置：x为'.$postObj->Location_X.' Y为:'.$postObj->Location_Y;
      //组合xml文档
      $resultStr = sprintf($this->textTpl, $postObj->FromUserName, $postObj->ToUserName, time(), "text", $contentStr);
      //输出xml文档
      echo $resultStr;
    }
    //检查签名方法
    private function checkSignature()
    {
          // you must define TOKEN by yourself
          if (!defined("TOKEN")) {
              throw new Exception('TOKEN is not defined!');
          }

          $signature = $_GET["signature"];
          $timestamp = $_GET["timestamp"];
          $nonce = $_GET["nonce"];

      $token = $this->token;
      //begin
      $tmpArr = array($token, $timestamp, $nonce);
          // use SORT_STRING rule
      sort($tmpArr, SORT_STRING);
      $tmpStr = implode( $tmpArr );
      $tmpStr = sha1( $tmpStr );
      //end
      //判断验证是否成功
      if( $tmpStr == $signature ){
        return true;
      }else{
        return false;
      }
    }
  //封装请求方法
  function request($url,$https=true,$method='get',$data=null){
    //1.初始化url
    $ch = curl_init($url);
    //2.设置相关的参数
    //字符串不直接输出,进行一个变量的存储
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    //判断是否为https请求
    if($https === true){
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
      curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    }
    //判断是否为post请求
    if($method == 'post'){
      curl_setopt($ch, CURLOPT_POST, true);
      curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    }
    //3.发送请求
    $str = curl_exec($ch);
    //4.关闭连接,避免无效消耗资源
    curl_close($ch);
    //返回请求到的结果
    return $str;
  }
  //获取access_token
  function getAccessToken(){
    //1.url地址
    $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$this->appid.'&secret='.$this->appsecret;
    //2.发送请求
    $content = $this->request($url);
    //3.处理返回值
    //json字符串解码为一个对象
    $content = json_decode($content);
    //以对象方法去调用返回值的属性
    $access_token = $content->access_token;
    //做一个定时脚本的任务，每2个小时去执行一次，获取access_token的缓存
    // echo $access_token.'<br />';
    //保存为本地缓存
    // $data = file_put_contents('./accesstoken', $access_token);
    //输出获取到的access_token
    // echo $data;
    // 直接返回access_token给调用这个方法的方法
    return $access_token;
  }
  //直接调取accesss_token缓存的方法
  function getAccessTokenCache(){
  //   //直接读取文件
    $access_token = file_get_contents('./accesstoken');
  //   //返回给调用此方法的方法
    return $access_token;
  }
  //获取ticket(票)值
  function getTicket($tmp=true){
    //1.url地址
    $url = 'https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token='.$this->getAccessToken();
    //2.组合post发送的数据
    //判断是临时的还是永久的二维码
    if($tmp == true){
      $data = '{"expire_seconds": 604800, "action_name": "QR_SCENE", "action_info": {"scene": {"scene_id": 123}}}';
    }else{
      $data = '{"action_name": "QR_LIMIT_SCENE", "action_info": {"scene": {"scene_id": 123}}}';
    }
    //3.发送请求
    $content = $this->request($url,true,'post',$data);
    //4.处理返回值
    //对返回json数据，进行解码处理
    $content = json_decode($content);
    //通过对象调取属性方式，获取ticket
    $ticket = $content->ticket;
    //输出查看$ticket
    echo  $ticket;
  }
  //通过ticket获取二维码
  function getQRCode($ticket){
    //1.url地址
    $url = 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket='.$ticket;
    //2.get方式直接发送请求
    $content = $this->request($url);
    //3.处理返回值
    file_put_contents('./qrcode.jpg',$content);
  }
  //删除菜单操作
  function delMenu(){
    //1.url地址
    $url = 'https://api.weixin.qq.com/cgi-bin/menu/delete?access_token='.$this->getAccessToken();
    //2.发送请求
    $content = $this->request($url);
    //3.处理返回值
    //json字符串转码为对象
    $content = json_decode($content);
    //判断是否删除成功
    if($content->errmsg == 'ok'){
      echo '删除菜单成功！';
    }else{
      echo '删除失败！'.'<br />';
      echo '错误代码为:'.$content->errcode;
    }
  }
  //创建菜单操作
  function createMenu(){
    //1.url地址
    $url = 'https://api.weixin.qq.com/cgi-bin/menu/create?access_token='.$this->getAccessToken();
    //2.组合post参数
    $data = '{
     "button":[
     {
          "type":"click",
          "name":"",
          "key":"news"
      },
      {
           "name":"",
           "sub_button":[
           {
               "type":"view",
               "name":"thinkphp",
               "url":"http://www.thinkphp.cn/"
            },
            {
               "type":"view",
               "name":"百度",
               "url":"http://www.baidu.com/"
            },
            {
               "name": "发送位置",
               "type": "location_select",
               "key": "rselfmenu_2_0"
            }]
       }]
 }';
    //3.携带post菜单数据,发送请求
    $content = $this->request($url,true,'post',$data);
    //4.处理返回值
    //json字符串转化为一个对象
    $content = json_decode($content);
    //判断是否创建成功
    if($content->errmsg == 'ok'){
      echo '创建菜单成功！';
    }else{
      echo '创建失败！'.'<br />';
      echo '错误代码为:'.$content->errcode;
    }
  }
  //查询菜单
  function showMenu(){
    //1.url地址
    $url = 'https://api.weixin.qq.com/cgi-bin/menu/get?access_token='.$this->getAccessToken();
    //2.发送请求
    $content = $this->request($url);
    //3.处理返回值,没有具体业务逻辑，我们直接看看就行了
    var_dump($content);
  }
  //获取用户openID列表
  //type 0表示输出信息，不等于表示返回openID列表
  function getUserList($type='0'){
    //1.url地址
    $url = 'https://api.weixin.qq.com/cgi-bin/user/get?access_token='.$this->getAccessToken();
    //2.发送请求
    $content = $this->request($url);
    //3.处理返回值
    //json字符串解码为一个对象
    $content = json_decode($content);
    //获取openID列表属性，它是一个数组
    $openIDList = $content->data->openid;
    //判断需求信息类型
    $openIDStr = '';
    if($type !== '0'){
      // var_dump($openIDList);
      //循环拼接字符串
      foreach ($openIDList as $key => $value) {
        $openIDStr .= '"'.$value.'",';
      }
      return $openIDStr;
    }else{
      // var_dump($content);die();
      echo '关注用户数：'.$content->total.'<br />';
      echo '用户列表为：'.'<br />';
      //遍历输出每一个关注用户的openID
      foreach ($openIDList as $key => $value) {
         echo '<a href="http://localhost/getuserinfo.php?openid='.$value.'">'.$value.'</a><br />';
      }
    }
  }
  //通过openID换取用户基本的信息
  function getUserInfo($openid){
    //1.url地址
    $url = 'https://api.weixin.qq.com/cgi-bin/user/info?access_token='.$this->getAccessToken().'&openid='.$openid.'&lang=zh_CN';
    //2.发送请求
    $content = $this->request($url);
    //3.返回值处理
    //json字符串解码为一个对象
    $content = json_decode($content);
    //输出用户基本信息
    echo '昵称:'.$content->nickname.'<br />';
    echo '性别:'.$content->sex.'<br />';
    echo '省份:'.$content->province.'<br />';
    echo '头像:<img src="'.$content->headimgurl.'" />';
  }
}