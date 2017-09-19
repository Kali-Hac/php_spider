<?php
    ini_set('max_execution_time', 3000);
    session_start();
    $id=session_id();
    $_SESSION['id']=$id;
    $cookie = tempnam('./cookie', 'cookie'); //用一个随机的不重复的文件名保存cookie,$cookie即为cookie文件名  
    $verify_code_url = "http://xsweb.scuteo.com/(4t02zy451w5ikqjsubt1qe3e)/CheckCode.aspx"; //验证码地址
    $curl = curl_init();//初始化一个curl
    curl_setopt($curl, CURLOPT_URL, $verify_code_url);//设置URL
    curl_setopt($curl, CURLOPT_COOKIEJAR, $cookie);  //保存cookie
    curl_setopt($curl, CURLOPT_HEADER, 0);//设置浏览器头
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);//设置返回方式，不自动输出，只能通过echo输出
    $img = curl_exec($curl);  //执行curl，获得验证码图片
    curl_close($curl);//关闭curl
    $fp = fopen("verifyCode.jpg","w");  //打开一个jpg格式文件保存验证码
    fwrite($fp,$img);   
    fclose($fp);
    header("Content-type: text/html; charset=gb2312"); 
    //设置charset,这个在原网页html头部可以看到,避免输出编码错误 

    sleep(10);//给你10s输入验证码
    $_POST['code']= file_get_contents('code.txt');
    $_POST['xh']='你的学号';
    $_POST['pw']='你的密码';
    $_SESSION['xh']=$_POST['xh'];
    $xh=$_POST['xh'];
    $pw=$_POST['pw'];
    $code= $_POST['code'];
    $url="http://xsweb.scuteo.com/(4t02zy451w5ikqjsubt1qe3e)/default2.aspx";  //教务处地址
    $con1=login_post($url,$cookie,'');
    preg_match_all('/<input type="hidden" name="__VIEWSTATE" value="([^<>]+)" \/>/', $con1, $view); //获取__VIEWSTATE字段并存到$view数组中
    $post=array(
        '__VIEWSTATE'=>$view[1][0],
        'txtUserName'=>$xh,
        'TextBox2'=>$pw,
        'txtSecretCode'=>$code,
        'RadioButtonList1'=>iconv('utf-8', 'gb2312', '学生'),
        'Button1'=>iconv('utf-8', 'gb2312', '登录'),
        'lbLanguage'=>'',
        'hidPdrs'=>'',
        'hidsc'=>''
    );
    $con2=login_post($url,$cookie,http_build_query($post));
    // echo $con2;

//自定义一个发起curl链接的函数，传入url,cookie和post数据
    function login_post($url,$cookie,$post){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);  //不自动输出数据，要echo才行
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);  //重要，抓取跳转后数据
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie); 
        curl_setopt($ch, CURLOPT_REFERER, 'http://xsweb.scuteo.com/(4t02zy451w5ikqjsubt1qe3e)/default2.aspx');  //重要，302跳转需要referer，可以在Request Headers找到 
        curl_setopt($ch, CURLOPT_POSTFIELDS,$post);  //post提交数据
        $result=curl_exec($ch);
        curl_close($ch);
        return $result;
    }
  preg_match_all('/<span id="xhxm">([^<>]+)<\/span>/', $con2, $xm);
  $xm[1][0]=substr($xm[1][0],0,-4);  //字符串截取，获得姓名
  //http://xsweb.scuteo.com/(4t02zy451w5ikqjsubt1qe3e)/xf_xsqxxxk.aspx?xh=
  $url2="http://xsweb.scuteo.com/(4t02zy451w5ikqjsubt1qe3e)/xf_xsqxxxk.aspx?xh=".$xh."&xm=".$xm[1][0];
  $viewstate=login_post($url2,$cookie,'');
  preg_match_all('/<input type="hidden" name="__VIEWSTATE" value="([^<>]+)" \/>/', $viewstate, $vs);
  $state=$vs[1][0];  //$state存放一会post的__VIEWSTATE
  for($i=0;$i<5;$i++){ //设置连续抢课次数
  $post=array(
    '__VIEWSTATE'=>$state,
    'TextBox1'=>iconv('utf-8', 'gb2312', '学术英语写作'),
    'Button2'=> '%C8%B7%B6%A8',
    'kcmcGrid:_ctl2:xk'=>'on',
    'Button1'=>iconv('utf-8', 'gb2312', '  提交  '),
    );
  $lesson_page=login_post($url2,$cookie,  http_build_query($post));
  echo $lesson_page;
  //sleep(3);//可以设置每3s选课一次，这样不会被教务系统劝阻
}
?>