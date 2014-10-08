<?php
define('EARTH_RADIUS', 6378.137);//地球半径，假设地球是规则的球体
define('PI', 3.1415926);
define("TOKEN", "mandi_dajike_kuniao");
define("TEXT","<xml>
                <ToUserName><![CDATA[%s]]></ToUserName>
                <FromUserName><![CDATA[%s]]></FromUserName>
                <CreateTime>%s</CreateTime>
                <MsgType><![CDATA[%s]]></MsgType>
                <Content><![CDATA[%s]]></Content>
                <FuncFlag>0</FuncFlag>
                </xml>");
define("IMAGE","<xml>
                    <ToUserName><![CDATA[%s]]></ToUserName>
                    <FromUserName><![CDATA[%s]]></FromUserName>
                    <CreateTime>%s</CreateTime>
                    <MsgType><![CDATA[%s]]></MsgType>
                    <Image>
                        <MediaId><![CDATA[%s]]></MediaId>
                    </Image>
                  </xml>");
define("MUSIC","<xml>
                    <ToUserName><![CDATA[%s]]></ToUserName>
                    <FromUserName><![CDATA[%s]]></FromUserName>
                    <CreateTime>%s</CreateTime>
                    <MsgType><![CDATA[%s]]></MsgType>
                    <Music>
                        <Title><![CDATA[%s]]></Title>
                        <Description><![CDATA[%s]]></Description>
                        <MusicUrl><![CDATA[%s]]></MusicUrl>
                        <HQMusicUrl><![CDATA[%s]]></HQMusicUrl>
                    </Music>
                 </xml>");
define("VOICE","<xml>
                    <ToUserName><![CDATA[%s]]></ToUserName>
                    <FromUserName><![CDATA[%s]]></FromUserName>
                    <CreateTime>%s</CreateTime>
                    <MsgType><![CDATA[%s]]></MsgType>
                    <Voice>
                        <MediaId><![CDATA[%s]]></MediaId>
                    </Voice>
                  </xml>");
define("IMAGE","<xml>
                        <ToUserName><![CDATA[%s]]></ToUserName>
                        <FromUserName><![CDATA[%s]]></FromUserName>
                        <CreateTime>%s</CreateTime>
                        <MsgType><![CDATA[%s]]></MsgType>
                        <Image>
                            <MediaId><![CDATA[%s]]></MediaId>
                        </Image>
                   </xml>");
define("NEWS_HEAD","<xml>
                    <ToUserName><![CDATA[%s]]></ToUserName>
                    <FromUserName><![CDATA[%s]]></FromUserName>
                    <CreateTime>%s</CreateTime>
                    <MsgType><![CDATA[%s]]></MsgType>
                    <ArticleCount>%s</ArticleCount>
                          <Articles>");
define("NEWS_ITEM",         "<item>
                                    <Title><![CDATA[%s]]></Title>
                                    <Description><![CDATA[%s]]></Description>
                                    <PicUrl><![CDATA[%s]]></PicUrl>
                                    <Url><![CDATA[%s]]></Url>
                               </item>");
define("NEWS_FOOTER","</Articles>
                      </xml>");


class     WechatCallbackapiTest extends MallbaseApp
{

    public function valid()
    {
        $echoStr = $_GET["echostr"];
        if($this->checkSignature()){
            echo $echoStr;
            exit;
        }
    }

    private function checkSignature()
    {
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];

        $token = TOKEN;
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );

        if( $tmpStr == $signature ){
            return true;
        }else{
            return false;
        }
    }

    public function responseMsg()
    {
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];

        if (!empty($postStr)){
            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            $fromUsername = $postObj->FromUserName;
            $toUsername = $postObj->ToUserName;
            $keyword = trim($postObj->Content);
            $msgType = $postObj->MsgType;
            $event = $postObj->Event;
            $time = time();
            //事件推送
            if($msgType == 'event'){
                //关注事件
                if($event == 'subscribe'){
                    $this -> enter($fromUsername,$toUsername,$time);
                }
                //自定义菜单点击事件
                else if($event == 'CLICK'){
                    $eventKey = $postObj->EventKey;
                    $this -> menuReply($fromUsername,$toUsername,$time,$eventKey);
                }else if($event == 'LOCATION'){
                    $lat = $postObj->Latitude;
                    $lon = $postObj->Longitude;
                    $cache = & cache_server();
                    $userLocaltion = $fromUsername."-".$lat."-".$lon;
                    $cache->set("weixin_".$fromUsername,$userLocaltion,3600);
                }
                //文本消息
            }else if($msgType == 'text'){
                //接收文本信息并回复
                $this -> receiveText($keyword,$fromUsername,$toUsername,$time);
            }else if($msgType == 'image'){
                $contentStr = 'You\'ve just send a picture to me...';
                echo $this->replyText($contentStr,$fromUsername,$toUsername,$time);
            }else if( $msgType == 'location'){
//                $contentStr = "接收地理位置成功！";
//                echo $this->replyText($contentStr,$fromUsername,$toUsername,$time);
//                接收用户发送过来的地理位置




                $latitude = $postObj->Location_X;
                $longitude = $postObj->Location_Y;
                $this->replyNews($fromUsername,$toUsername,$time,$latitude,$longitude);

            }

            //...其余类型
            else{}
        }else{
            echo "";
            exit;
        }
    }

    private function enter($fromUsername,$toUsername,$time){
        $contentStr = "您好！欢迎关注大集客官网微信公众平台，我是小集。我将为您提供最全面、最丰富的手机商城购物服务。累计购物满500元即可获100次抽奖机会！三个奖池让你抽疯掉！成为会员还会有您想不到的惊喜哦！\n\n<a href='http://www.dajike.com/weixin/index.php?app=member&act=register_form'>点击这里 立即注册</a>\n\n想知道更多？猛戳下面菜单……";
        echo $this->replyText($contentStr,$fromUsername,$toUsername,$time);
    }

    //接收用户发送过来的文本信息，并回复
    private function receiveText($keyword,$fromUsername,$toUsername,$time){
        if($keyword == '1'){
            $contentStr = 'hello'."/微笑";
            echo $this->replyText($contentStr,$fromUsername,$toUsername,$time);

        }else if($keyword == '2'){
            $contentStr = 'Hello world'."<a href='www.dajike.com'>进入大集客首页</a>";
            echo $this->replyText($contentStr,$fromUsername,$toUsername,$time);

        }else if($keyword == '3'){
            $xml = "<xml>
                    <ToUserName><![CDATA[%s]]></ToUserName>
                    <FromUserName><![CDATA[%s]]></FromUserName>
                    <CreateTime>%s</CreateTime>
                    <MsgType><![CDATA[%s]]></MsgType>
                    <ArticleCount>2</ArticleCount>
                          <Articles>
                                 <item>
                                    <Title><![CDATA[%s]]></Title>
                                    <Description><![CDATA[%s]]></Description>
                                    <PicUrl><![CDATA[%s]]></PicUrl>
                                    <Url><![CDATA[%s]]></Url>
                                 </item>
                                <item>
                                <Title><![CDATA[%s]]></Title>
                                <Description><![CDATA[%s]]></Description>
                                <PicUrl><![CDATA[%s]]></PicUrl>
                                <Url><![CDATA[%s]]></Url>
                                </item>
                         </Articles>
                    </xml>";
            $picUrl1 = "http://www.dajike.com/data/files/2013/0831/_201308311426331368_100X100.png";
            $picUrl2 = "http://map.wap.soso.com/x/?type=bus&cond=1&traffic=open&welcomeChange=1&welcomeClose=1&startLat=31.324806&startLng=121.448372&endLat=31.310814&endLng=121.448367&key=1028+128+Memorial+Road%2C+Baoshan%2C+Shanghai%2C+China%7C%7C%E4%B8%8A%E6%B5%B7%E5%B8%82%E5%85%B1%E5%92%8C%E6%96%B0%E8%B7%AF4330%E5%8F%B7";
            $url = "www.dajike.com";
            $resultStr = sprintf($xml, $fromUsername, $toUsername, $time, "news","商品1","商品1",$picUrl1,$url,"商品2","商品2",$picUrl2,$url);
            echo $resultStr;

        }else if($keyword == '4' || $keyword == 'music' || $keyword == '音乐'){
            $title = "最炫名族风";
            $desc = "最炫名族风,high起来！！";
            $musicUrl = "http://zj189.cn/zj/download/music/zxmzf.mp3";
            $HQMusicUrl = "http://zj189.cn/zj/download/music/zxmzf.mp3";
            echo $this->replyMusic($fromUsername, $toUsername,$time,$title,$desc,$musicUrl,$HQMusicUrl);

        }else if($keyword == '5'){
            $contentStr = "你好，欢迎您关注大集客!\n回复：\n【1】<a href='www.dajike.com'>首页</a>\n【2】用户中心\n【3】购物车\n【4】微网站\n【5】帮助中心";
            echo $this->replyText($contentStr,$fromUsername,$toUsername,$time);
        }

    }

    //回复自定义按钮
    function menuReply($fromUsername,$toUsername,$time,$eventKey){
        //判断菜单的key值
        //点击附近加盟商家，判断用户是否同意公众平台使用地理位置
        if($eventKey == 'V1001_01'){
            $cache = & cache_server();
            $userLocation = $cache->get("weixin_".$fromUsername);
            if($userLocation != ""){
                $contentStr = "abcdefg_"."$userLocation";
                echo $this->replyText($contentStr,$fromUsername,$toUsername,$time);
            }else{
                $contentStr = "分享位置即可查看附近商家及优惠信息。吃喝玩乐，一网打尽！
                            \n你，准备好了吗？现在跟着小集来查询吧!
                            \n【1】点击屏幕左下方的键盘
                            \n【2】点选右侧‘+’按钮，选择‘位置’
                            \n【3】若定位有偏差，请拖动地图选择你的位置
                            \n【4】点击右上角‘发送’即可"."$userLocation";
                echo $this->replyText($contentStr,$fromUsername,$toUsername,$time);
            }
        }else if($eventKey == "V1001_03_02"){
            $contentStr = "客服热线：400-728-1117".
                "\n1.服务中心加盟合作".
                "\n联系人：周先生".
                "\nQQ：2880323937".
                "\nE-mail:\nshichang@dajike.com" .
                "\n2.产品托管/工厂直供加盟".
                "\n联系人：唐先生\nQQ：2880323939\nE-mail:\ntuoguan@dajike.com".
                "\n3.网上店铺入驻\n联系人：施先生\nQQ：2880323947\nE-mail:\nchanpin@dajike.com".
                "\n4.本地商家加盟\n联系人：俞小姐\nQQ：2880323940\nE-mail:\n bendi@dajike.com";
            echo $this->replyText($contentStr,$fromUsername,$toUsername,$time);
        }else if($eventKey == "V1001_03_03"){
            $contentStr = "客服热线：400-728-1117\n1.技术中心\n联系人：彭先生\nQQ:2880323923\nE-mail:\n jishu@dajike.com".
                          "\n2.移动商城\n联系人：林小姐\nQQ：2880323925".
                "\n3.产品托管/工厂直供\n联系人：唐先生\nQQ：2880323939".
                "\n4.产品入驻\n联系人：施先生\nQQ：2880323947".
                "\n5.服务中心合作\n联系人：周先生\nQQ：2880323937\nE-mail:\nshichang@dajike.com".
                "\n6.本地商家入驻\n联系人：俞小姐\nQQ：2880323940\nE-mail:\n bendi@dajike.com";
            echo $this->replyText($contentStr,$fromUsername,$toUsername,$time);
        }else if($eventKey == "V1001_03_04"){
            $contentStr = '亲爱的用户，大集客为您提供了关于购物指南、支付方式、售后服务等各方面的指导文章，'."<a href='www.dajike.com/weixin/index.php?app=help'>点击这里</a>".'获取更多帮助文章。';
            echo $this->replyText($contentStr,$fromUsername,$toUsername,$time);
        }else if($eventKey == "V1001_01_02"){
            $newsHead = NEWS_HEAD;
            $newsFooter = NEWS_FOOTER;
            $newsItem = NEWS_ITEM;
            $msgType = "news";
            $newsTpl = "";
            $newsHead = sprintf($newsHead,$fromUsername,$toUsername,$time,$msgType,1);
            $title = "免费申请POS机";
            $des = "安装POS机能提升企业形象，体现商家的档次，帮助商家在激烈的市场竞争中树立优势。安装POS机可以方便消费者的购物消费结算……";
            $picUrl = "http://www.dajike.com/weixin/templates/images/pos.jpg";
            $url = "http://www.dajike.com/weixin/pos/apply";
            $newsItem = sprintf($newsItem,$title,$des,$picUrl,$url);
            $newsTpl = $newsHead.$newsItem.$newsFooter;
            echo $newsTpl;
        }else if($eventKey == "V1002_01_01"){
            $member_mod  = &m("member");
            $weixin_user = $member_mod->get("weixin_id=".$fromUsername);
            if(!$weixin_user || $weixin_user['user_id'] <= 0){
                $contentStr = "您还未登录大集客：<a href='http://www.dajike.com/weixin/index.php?app=member&act=register_form'>登录大集客</a>";
            }else{
                $contentStr = "您的专属链接：<a href='".$SITE_URL . "/reg/".base64_encode($weixin_user['user_id'])."'>www.dajike.com/reg/".base64_encode($weixin_user['user_id'])."</a>";
            }
            echo $this->replyText($contentStr,$fromUsername,$toUsername,$time);
        }
    }

    //回复文本消息（同时回复多条文本消息，参见微信客服接口）
    public  function replyText($msg,$fromUsername,$toUsername,$time){
        $textTpl = TEXT;
        $msgType = "text";
        return sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $msg);
    }
    //回复音乐消息
    public function replyMusic($fromUsername, $toUsername, $time,$title,$desc,$musicUrl,$HQMusicUrl){
        $musicTpl = MUSIC;
        $msgType = "music";
        return sprintf($musicTpl,$fromUsername, $toUsername, $time,$msgType,$title,$desc,$musicUrl,$HQMusicUrl);
    }
    //回复语音消息
    public function replyVoice($mediaId,$fromUsername,$toUsername,$time){
        $voiceTpl = VOICE;
        $msgType= "voice";
        return sprintf($voiceTpl, $fromUsername, $toUsername, $time, $msgType, $mediaId);
    }
    //回复图片消息
    public function replyImage($mediaId,$fromUsername,$toUsername,$time){
        $imageTpl = IMAGE;
        $msgType= "image";
        return sprintf($imageTpl, $fromUsername, $toUsername, $time, $msgType, $mediaId);
    }

    /**
     * 回复图文消息
     * 根据用户发过来的地理位置信息（经纬度），可查找附近商家
     * @param $fromUsername  发送者
     * @param $toUsername    接收者
     * @param $time          发送时间
     * @param $latitude
     * @param $longitude
     * @param array $result
     * @return string
     */
    public function replyNews($fromUsername,$toUsername,$time,$latitude = null,$longitude = null){
        $newsHead = NEWS_HEAD;
        $newsFooter= NEWS_FOOTER;
        $msgType = "news";
        $newsTpl="";
        //业务：查找附近商家
        if($latitude != null && $longitude != null ){

            //找到附近10公里的加盟商家
            $results = $this->getNearbyStore($latitude,$longitude,10,$fromUsername,$toUsername,$time);
            if( $results[0] <= 0){
                $contentStr = '附近没有加盟商家！';
                echo $this->replyText($contentStr,$fromUsername,$toUsername,$time);
            }else{
                $newsHead = sprintf($newsHead,$fromUsername,$toUsername,$time,$msgType,$results[0]);
                $newsTpl = $newsHead.$results[1].$newsFooter;

                $fp=fopen("/tmp/log.txt","w+");
                fwrite($fp, $newsTpl);
                fclose($fp);


                echo $newsTpl;
            }
        }
    }

    /**
     * 根据当前位置(经纬度)，查找附近加盟商家
     * @param $latitude  当前纬度
     * @param $longitude 当前经度
     * @param $distance  查找范围（单位：km）
     * @return mixed     符合查询条件的商店的
     *                     格式：array( $count,$stores )
     * @author liz
     */
    public function getNearbyStore($latitude,$longitude,$distance){
        $latitude = floatval($latitude);
        $longitude = floatval($longitude);
        $sql = "select store.store_id,store.store_name,address,store.latitude,store.longitude,
                            3956*2*asin( sqrt(  power( sin( (%s-abs(store.latitude))*pi()/180/2),2)+ cos(%s*pi()/180) * cos(abs(store.latitude)*pi()/180) * power(sin((%s-store.longitude)*pi()/180/2),2) )) as distance
                        from ".DB_PREFIX."store store
                        where store.state=1 and  store.dropState=1 and store.latitude is not null and store.longitude is not null
                        having distance < %s
                        order by distance limit 10";
        $sql = sprintf($sql,$latitude,$latitude,$longitude,$distance);
        $store_mod = &m('store');
        $stores = $store_mod->db->getAll($sql);

        //构造成xml格式
        $address = $this->getAddressByLatLng($latitude,$longitude);
        $newsItems = '';
        $count = count($stores);
        $newsItemTpl = NEWS_ITEM;
        $company_latitude = "31.3244888462";//公司纬度
        $company_longitude = "121.4467039704"; //公司经度

        if( $count > 0 ){
            //当前位置与公司的距离
            //$company_distance =  3956*2*asin( sqrt(  power( sin( ($latitude-abs($company_latitude))*pi()/180/2),2)+ cos($latitude*pi()/180) * cos(abs(store.latitude)*pi()/180) * power(sin(($longitude-$company_longitude)*pi()/180/2),2) ));
            $url = "http://map.wap.soso.com/x/?type=bus&cond=1&traffic=open&welcomeChange=1&welcomeClose=1&startLat=%s&startLng=%s&endLat=%s&endLng=%s&key=%s||%s";
            $url = sprintf($url,$latitude,$longitude,$company_latitude,$company_longitude,$address,"上海宝山区一二八纪念路928号十楼");
            $newsItem = sprintf($newsItemTpl,"上海酷鸟网络科技有限公司","上海酷鸟网络科技有限公司",
                "http://www.dajike.com/weixin/templates/images/logo02.png",$url);
            $newsItems .= strval($newsItem);
            $cc = 1;
            foreach($stores as $value){
                $cc ++; if ($cc > 5) break;
                $store_latitude = $value["latitude"];
                $store_longitude = $value["longitude"];
                $store_address = $value["address"];
                $store_distance = $value["distance"];
                $store_distance = round($store_distance*1000);//转换距离单位为：米
                $url = "http://map.wap.soso.com/x/?type=bus&cond=1&traffic=open&welcomeChange=1&welcomeClose=1&startLat=%s&startLng=%s&endLat=%s&endLng=%s&key=%s||%s";
                $url = sprintf($url,$latitude,$longitude,$store_latitude,$store_longitude,$address,$store_address);
                $picUrl = $this->getStoreLogo($value['store_id']);
                $store_name = $value['store_name'];
                //$newsItem = sprintf($newsItemTpl,$store_name."  $store_distance"."米\n", $store_name,$picUrl,$url);
                $newsItem = sprintf($newsItemTpl, $store_name ."  $store_distance" . "米\n" . $store_address, $store_name, $picUrl, $url);
                $newsItems .= strval($newsItem);
            }
        }
        return array($cc - 1,$newsItems);
    }

    /**根据店铺id获取店铺logo
     * @param $store_id
     * @return string
     * @author liz
     */
    function getStoreLogo($store_id){
        $picUrl = "";
        if( !$store_id || $store_id ==null || $store_id <= 0 ){
            $picUrl ="http://www.dajike.com/data/system/default_store_logo.gif";
            return $picUrl;
        }
        $store_uploadfile_mod = &m('storeuploadedfile');
        $store_images = $store_uploadfile_mod->find(array(
            'conditions'=> 'store_id = '.$store_id.' and dropState =1',
            'order' => 'if_default desc'
        ));
        if( !$store_images || count($store_images) == 0 ){
            $picUrl ="http://www.dajike.com/data/system/default_store_logo.gif";
            return $picUrl;
        }
        $store_images = current($store_images);//取出第一个
        if( !$store_images['file_path'] || $store_images['file_path'] == "" ){
            $picUrl ="http://www.dajike.com/data/system/default_store_logo.gif";
            return $picUrl;
        }
        $imageArray = explode(".",$store_images['file_path']);
        $picUrl = "http://www.dajike.com/". $imageArray[0]."_60X60.".$imageArray[1];
        return $picUrl;
    }


    //根据经纬度获取详细地址
    public function getAddressByLatLng($latitude,$longitude){
        $url = "http://api.map.baidu.com/geocoder/v2/?ak=lcCMY1xxIFG2mUh5TOG8iwTa&callback=renderReverse&location=%s,%s&output=xml&pois=0";
        $url = sprintf($url,$latitude,$longitude);
        $xml = $this->post($url);
        $resultXML = simplexml_load_string($xml);
//            $status = $resultXML->status;
        $result = $resultXML->result;
        $address = $result->formatted_address;
        return $address;
    }

    /**
     *  计算两组经纬度坐标 之间的距离
     *   params ：lat1 纬度1； lng1 经度1； lat2 纬度2； lng2 经度2；
     *   return : m
     */
    function getDistance($lat1, $lng1, $lat2, $lng2){
        $lat1 = floatval($lat1);
        $lng1 = floatval($lng1);
        $lat2 = floatval($lat2);
        $lng2 = floatval($lng2);
        $EARTH_RADIUS = 6378.137;
        $radLat1 = $this->rad($lat1);
        //echo $radLat1;
        $radLat2 = $this->rad($lat2);
        $a = $radLat1 - $radLat2;
        $b = $this->rad($lng1) - $this->rad($lng2);
        $s = 2 * asin(sqrt(pow(sin($a/2),2) +
            cos($radLat1)*cos($radLat2)*pow(sin($b/2),2)));
        $s = $s *$EARTH_RADIUS;
        //$s结果形式：
        //1.4544330558519千米(精确坐标)
        //1.4544101877862千米（标准坐标）
        //精确坐标与标准坐标误差2cm
        return round(floatval($s)*1000);
    }
    function rad($d){
        return $d * 3.1415926535898 / 180.0;
    }

//发送http请求
    function post($url, $jsonData = ''){
        $ch = curl_init($url) ;
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS,$jsonData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        $result = curl_exec($ch) ;
        curl_close($ch) ;
        return $result;
    }




}
?>


