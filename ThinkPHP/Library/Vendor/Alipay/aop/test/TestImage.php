<?php
/**
 * Alipay.com Inc.
 * Copyright (c) 2004-2014 All Rights Reserved.
 */



include('../AlipayMobilePublicMultiMediaClient.php');


header("Content-type: text/html; charset=gbk");

/**
 *
 * @author wangYuanWai
 * @version $Id: Test.hp, v 0.1 Aug 6, 2014 4:20:17 PM yikai.hu Exp $
 */
class TestImage{


	public $partner_public_key  = "MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCcRgtqfn4hkiuRCpoReYGsGwKysDWqJT6JIUOHt4bSV15BBgvvwyYTZRq+p3Oh31AXZPACs+w6SmwlqMyxnz/xwmWB1TYb9DxLbrC+xCx8e43cB3YZ2hidakknGl0ry04c4g25ritHVIo73mPBjx6KfU7rxxWWnWNNjipdoNOfswIDAQAB";
	public $alipay_public_key   = "MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDDI6d306Q8fIfCOaTXyiUeJHkrIvYISRcc73s3vF1ZT7XN8RNPwJxo8pWaJMmvyTn9N4HQ632qJBVHf8sxHi/fEsraprwCtzvzQETrNRwVxLO5jVmRGi60j8Ue1efIlzPXV9je9mkjzOmdssymZkh2QhUrCmZYI/FCEa3/cNMW0QIDAQAB";
		//公用变量
	public $serverUrl = 'http://publicexprod.d5336aqcn.alipay.net/chat/multimedia.do';//'http://publicexprod.d5336aqcn.alipay.net/chat/multimedia.do';//'http://i.com/works/photo-sdk/_data/1.jpg';//"http://i.com/works/photo-sdk/_data/publicexprod.php";//"http://publicexprod.d5336aqcn.alipay.net/chat/multimedia.do";
	public $appId = "2017080107982299";

//	public $partner_private_key = 'MIICdwIBADANBgkqhkiG9w0BAQEFAASCAmEwggJdAgEAAoGBAKK0PXoLKnBkgtOl0kvyc9X2tUUdh/lRZr9RE1frjr2ZtAulZ+Moz9VJZFew1UZIzeK0478obY/DjHmD3GMfqJoTguVqJ2MEg+mJ8hJKWelvKLgfFBNliAw+/9O6Jah9Q3mRzCD8pABDEHY7BM54W7aLcuGpIIOa/qShO8dbXn+FAgMBAAECgYA8+nQ380taiDEIBZPFZv7G6AmT97doV3u8pDQttVjv8lUqMDm5RyhtdW4n91xXVR3ko4rfr9UwFkflmufUNp9HU9bHIVQS+HWLsPv9GypdTSNNp+nDn4JExUtAakJxZmGhCu/WjHIUzCoBCn6viernVC2L37NL1N4zrR73lSCk2QJBAPb/UOmtSx+PnA/mimqnFMMP3SX6cQmnynz9+63JlLjXD8rowRD2Z03U41Qfy+RED3yANZXCrE1V6vghYVmASYsCQQCoomZpeNxAKuUJZp+VaWi4WQeMW1KCK3aljaKLMZ57yb5Bsu+P3odyBk1AvYIPvdajAJiiikRdIDmi58dqfN0vAkEAjFX8LwjbCg+aaB5gvsA3t6ynxhBJcWb4UZQtD0zdRzhKLMuaBn05rKssjnuSaRuSgPaHe5OkOjx6yIiOuz98iQJAXIDpSMYhm5lsFiITPDScWzOLLnUR55HL/biaB1zqoODj2so7G2JoTiYiznamF9h9GuFC2TablbINq80U2NcxxQJBAMhw06Ha/U7qTjtAmr2qAuWSWvHU4ANu2h0RxYlKTpmWgO0f47jCOQhdC3T/RK7f38c7q8uPyi35eZ7S1e/PznY=';
	public $partner_private_key = 'MIICXQIBAAKBgQCcRgtqfn4hkiuRCpoReYGsGwKysDWqJT6JIUOHt4bSV15BBgvvwyYTZRq+p3Oh31AXZPACs+w6SmwlqMyxnz/xwmWB1TYb9DxLbrC+xCx8e43cB3YZ2hidakknGl0ry04c4g25ritHVIo73mPBjx6KfU7rxxWWnWNNjipdoNOfswIDAQABAoGBAI+dP3iK7tdjQW5108kjZSwJVp1omqjWuXXEgA8Fdn1vlUskh2u8aA2C6OU1dmrYkv4s4PVa7ElVg7XIPvUtaNEQ9GZ83rhB8qijxsaTBO5e0UxL3d6qG2NXCuJiHNeo/mvSJ0Vatm21nHjNSRB/5f8m9iGwBVqqg/RTf5KnWEDBAkEAz8OJUDEMIKpXLnaa57ejSNZqasgI6B87idi2+2SGnzV3PQHNLIHY+SlBdXtmEcvyqIICmoleSYGIJbnbKm6ouQJBAMCOLsjnY3CSXj+VYVDjfe6f7wgBNmad1kvmV1hBJsVpeKv8vjxHAR5ZYo6ThsopTJN5GtykYrKFcBgN3xx9/csCQGRvMPTcEPHFhomGelGjm0J9rEncUznqxzxWz/Xs3YsfLHoIYeevCXVBNUyWj3vw7Gf7GUkdOMAt5uPd2Y3EmrkCQH9h0AtyH3OKMLVJgg574IRq4ztdafqqseiWIfQtbZOtOXo1gjfoFRJZuXxulf3JInJw7FdInE5TPht7mbyEkM8CQQCh4naJZAcjUEk11Ax7HlVtozFqZBagEF7rA2iykoVGuvyhyK4z3bJz2ciNcWbDJdSxebQwk3dywswdjqBYlkUE';
	public $format = "json";
	public $charset = "GBK";



	function __construct(){

	}

	public function load() {
		$alipayClient = new AlipayMobilePublicMultiMediaClient(
			$this -> serverUrl,
			$this -> appId,
			$this -> partner_private_key,
			$this -> format,
			$this -> charset
		);
		$response = null;
		$outputStream = null;
		$request = $alipayClient -> getContents() ;

		//200
		//echo( '状态码：'. $request -> getCode() .', ');
		//echo '<hr /><br /><br /><br />';

		$fileType = $request -> getType();
		//echo( '类型：'. $fileType .', ');
		if( $fileType == 'text/plain'){
			//出错，返回 json
			echo $request -> getBody();

		}else{

			$type = $request -> getFileSuffix( $fileType );

			//echo $this -> getParams();
			//exit();

			//返回 文件流
			header("Content-type: ". $fileType ); //类型


			header("Accept-Ranges: bytes");//告诉客户端浏览器返回的文件大小是按照字节进行计算的
			header("Accept-Length: ". $request -> getContentLength() );//文件大小
			header("Content-Length: ". $request -> getContentLength() );//文件大小
			header('Content-Disposition: attachment; filename="'. time() .'.'. $type .'"'); //文件名
			echo $request -> getBody() ;
			exit ( ) ;
		}

		//echo( '内容： , '. $request -> getContentLength()  );

		//echo '<hr /><br /><br /><br />';
		//echo  '参数：<pre>';

		//echo ($request -> getParams());

		//echo '</pre>' ;
	}
}





//  测试
$test1 = new TestImage();
$test1 -> load();
