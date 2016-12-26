<?php 
	/****************************************************************************************
	
		Just a simple web proxy written in PHP that can allow you to bypass Imdb.com content
		T.toosi
	
	*****************************************************************************************/
	function strposa($haystack, $needles=array(), $offset=0) {
			$chr = array();
			foreach($needles as $needle) {
					$res = strpos($haystack, $needle, $offset);
					if ($res !== false) $chr[$needle] = $res;
			}
			if(empty($chr)) return false;
			return min($chr);
	}
	
	$method		= $_SERVER['REQUEST_METHOD'];
	$domainURL	= 'http://www.imdb.com';
	$url 		= isset($_GET['url']) ? $_GET['url'] : null;
    $headers	= getallheaders();

	if( strposa($url, ['css','jpg','png','js'])  ) $domainURL	= 'http://ia.media-imdb.com';
    foreach ( $headers as $key => $value){
      if($key == 'Host' || $key== 'Accept-Encoding')
        continue;
      $headers_str[]=$key.":".$value;
    }

    $ch = curl_init();
    curl_setopt($ch,CURLOPT_URL, $domainURL . '/' . $url);
    if( $method !== 'GET') {
      curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    }
    if($method == "PUT" || $method == "PATCH" || ($method == "POST" && empty($_FILES))) {
      $data_str = file_get_contents('php://input');
      curl_setopt($ch, CURLOPT_POSTFIELDS, $data_str);
      //error_log($method.': '.$data_str.serialize($_POST).'\n',3, 'err.log');
    }
    elseif($method == "POST") {
      $data_str = array();
      if(!empty($_FILES)) {
        foreach ($_FILES as $key => $value) {
          $full_path = realpath( $_FILES[$key]['tmp_name']);
          $data_str[$key] = '@'.$full_path;
        }
      }
      //error_log($method.': '.serialize($data_str+$_POST).'\n',3, 'err.log');
      curl_setopt($ch, CURLOPT_POSTFIELDS, $data_str+$_POST);
    }
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers_str );
    $result = curl_exec($ch);
	$contentType = curl_getinfo($ch,CURLINFO_CONTENT_TYPE);
    curl_close($ch);
    header('Content-Type: '.$contentType);
	
	$result = str_replace('http://ia.media-imdb.com', '', $result);
	$result = str_replace('https://ia.media-imdb.com', '', $result);
	$result = str_replace('http://www.imdb.com', '', $result);
	$result = str_replace('?pf', '/?pf', $result);
	$result = str_replace('<head>', '<head><style>#supertab {display: none;}</style>', $result);
    echo $result;
?>
