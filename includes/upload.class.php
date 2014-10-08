<?php

/*
	[UCenter] (C)2001-2099 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: upload.class.php 1059 2011-03-01 07:25:09Z monkey $
*/

Class upload{

	var $dir;
	var $thumb_width;
	var $thumb_height;
	var $thumb_ext;
	var $watermark_file;
	var $watermark_pos;
	var $watermark_alpha;
	var $time;

	var $filetypedata = array();
	var $filetypeids = array();
	var $filetypes = array();

	function upload($time = 0) {
		$this->time = $time ? $time : time();
		$this->filetypedata = array(
			'av' => array('av', 'wmv', 'wav'),
			'real' => array('rm', 'rmvb'),
			'binary' => array('dat'),
			'flash' => array('swf'),
			'html' => array('html', 'htm'),
			'image' => array('gif', 'jpg', 'jpeg', 'png', 'bmp'),
			'office' => array('doc', 'xls', 'ppt'),
			'pdf' => array('pdf'),
			'rar' => array('rar', 'zip'),
			'text' => array('txt'),
			'bt' => array('bt'),
			'zip' => array('tar', 'rar', 'zip', 'gz'),
		);
		$this->filetypeids = array_keys($this->filetypedata);
		foreach($this->filetypedata as $data) {
			$this->filetypes = array_merge($this->filetypes, $data);
		}
	}

	function set_dir($dir) {
		$this->dir = $dir;
	}

	function dfsockopen($url, $limit = 0, $post = '', $cookie = '', $bysocket = FALSE, $ip = '', $timeout = 15, $block = TRUE) {
		return $this->_dfsockopen($url, $limit, $post, $cookie, $bysocket, $ip, $timeout, $block);
	}

	function fsocketopen($hostname, $port = 80, &$errno, &$errstr, $timeout = 15) {
		$fp = '';
		if(function_exists('fsockopen')) {
			$fp = fsockopen($hostname, $port, $errno, $errstr, $timeout);
		} elseif(function_exists('pfsockopen')) {
			$fp = pfsockopen($hostname, $port, $errno, $errstr, $timeout);
		} elseif(function_exists('stream_socket_client')) {
			$fp = stream_socket_client($hostname.':'.$port, $errno, $errstr, $timeout);
		}
		return $fp;
	}

    function generate_code($len = 5)
    {
        $chars = '23457acefhkmprtvwxy';
        for ($i = 0, $count = strlen($chars); $i < $count; $i++) {
            $arr[$i] = $chars[$i];
        }
        mt_srand((double) microtime() * 1000000);
        shuffle($arr);
        $code = substr(implode('', $arr), 5, $len);
        return $code;
    }

    function microtime_float()
    {
        list($usec, $sec) = explode(" ", microtime());
        $name=((float)$usec + (float)$sec);
        return str_replace('.','',$name);
    }

	function GrabImage($url,$filename="") {
	  if($url==""):return false;endif;
	  if($filename=="") {

	    	  $ext=strrchr($url,".");
			  static $imgext  = array('.jpg', '.jpeg', '.gif', '.png', '.bmp');
			  $ext=strtolower($ext);
			  if(in_array($ext, $imgext))
			  {

				  $filename=$this->microtime_float()."_".$this->generate_code(6).$ext;
				  ob_start();
				  readfile($url);
				  $img = ob_get_contents();
				  ob_end_clean();
				  $size = strlen($img);

                  $filepath="";
                  $up_dir=Conf::get('up_dir');
                  if(DIS_PATH!="" && DIS_PATH!="DIS_PATH")
                  {
                      $filepath=DIS_PATH."/".$up_dir."_down";
                  }else{
                      $filepath=$up_dir."_down";
                  }
                  if(!is_dir($filepath)){
                      //echo $filepath;
                      mkdir($filepath,0777, true);
                      //$this->mkpath($filepath,0777);
                  }

				  $fp2=@fopen($filepath."/".$filename, "a");

				  fwrite($fp2,$img);
				  fclose($fp2);
				  return $up_dir."_down/".$filename;
			  }else
			  {
			  	return "";
			  }

	  }
	}


    function  mkpath( $mkpath , $mode =0777){

        $path_arr = explode ( '/' , $mkpath );

        foreach  ( $path_arr   as   $value ){

            if (! empty ( $value )){

                if ( empty ( $path )) $path = $value ;

                  else   $path .= '/' . $value ;

                  is_dir ( $path )  or   mkdir ( $path , $mode );

             }

     }

        if ( is_dir ( $mkpath )) return  true;

        return  false;

    }

	function copy($sourcefile, $destfile) {
		move_uploaded_file($sourcefile, $destfile);
		@unlink($sourcefile);
	}

	function fileext($filename) {
		return substr(strrchr($filename, '.'), 1, 10);
	}

}

?>