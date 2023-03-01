<?
# 수정일 : 2021-04-30
DEFINE(__LOG_DIR__,"./logs/");
DEFINE(__TMP_DIR__,"./tmp/");


class commonlib {
	function decrypt($srt, $encType,$key,$iv=""){
		$encType = strtoupper($encType);
		switch($encType){
			case "AES-128-ECB":
				$commonCrypt = new commonCrypt();
				$commonCrypt->encMode = strtolower("ECB");
				return $commonCrypt->decrypt(base64_decode($srt),$key,$iv);
				break;
			case "AES-128-CBC":
				$commonCrypt = new commonCrypt();
				return $commonCrypt->decrypt(base64_decode($srt),$key,$iv);
				break;
			case "SEED-CBC":
				break;
		}
	}
	function encrypt($srt, $encType,$key,$iv=""){
		$encType = strtoupper($encType);
		switch($encType){
			case "AES-128-ECB":
				$commonCrypt = new commonCrypt();
				$commonCrypt->encMode = strtolower("ECB");
				return base64_encode($commonCrypt->encrypt($srt,$key,$iv));
				break;
			case "AES-128-CBC":
				$commonCrypt = new commonCrypt();
				return base64_encode($commonCrypt->encrypt($srt,$key,$iv));
				break;
			case "SEED-CBC":
				break;
		}
	}
	# 추가 : 송덕화 20. 11. 25
	# 범주 : String(data) 관련툴	
	# 설명 : array 형식의 데이터 배열을 fileMakeExcelData 함수 사용하여 만든 파일 바로 다운로드 처리 후 파일삭제
	# 사용 : $rendStr = $this->gcscustomutil->getMakeStringRand(); / $rendStr = getMakeStringRand(10,"Aa0$");	 
	public function getMakeStringRand($len = 5, $type = 'Aa0')	// 랜덤 문자 생성 함수
	{
		$lowercase = 'abcdefghijklmnopqrstuvwxyz';				# 소문자
		$uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';				# 대문자
		$numeric = '0123456789';													# 숫자
		$special = '`~!@#$%^&*()-_=+\\|[{]};:\'",<.>/?';	# 특수문자
		if (strpos($type,'0') > -1 || strpos($type,'1') > -1 ) $key .= $numeric;
		if (strpos($type,'a') > -1) $key .= $lowercase;
		if (strpos($type,'A') > -1) $key .= $uppercase;
		if (strpos($type,'$') > -1 || strpos($type,'@') > -1 ) $key .= $special;
		for ($i = 0; $i < $len; $i++) $token .= $key[mt_rand(0, strlen($key) - 1)];
		return $token;
	}
	
	# 추가 : 송덕화 20. 11. 25
	# 범주 : Web 관련툴	
	# 설명 : 다양한 형태의 curl 옵션 가능한 함수 (기존 솔루션 함수보다 복잡한 기능 구현가능>> 파일첨부, 헤더구현, 메소드변경, 쿠키, 리퍼러, 에이전트 세팅 가능)
	# 사용 : $result = $this->gcscustomutil->getCallWebUrl("http://firstmall.kr/?action=test&value=call"); # get호출 / $result = getCallWebUrl("http://firstmall.kr/",array("action"=>"test","value"=>"call") ); # post
	public function getCallWebUrl($url, $data="", $option=array()) {
		$ch = curl_init();
		if(!empty($option['header'])) $header = $option['header'];
		if(!empty($option['file'])) $file = $option['file'];
		if(!empty($option['method'])) $method = $option['method'];		
		
		if(is_file($file)) $data['file'] = new CurlFile($file);		# 파일을 첨부하는 경우

		curl_setopt($ch, CURLOPT_URL, $url);
		if(!empty($option['header'])) curl_setopt($ch, CURLOPT_HTTPHEADER,  $option['header']);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, ($option['timeout'])?$option['timeout']:5);
		curl_setopt($ch, CURLOPT_VERBOSE, true);
		if(!empty($option['cookie'])) curl_setopt ($ch, CURLOPT_COOKIE, $option['cookie']);
		if(!empty($option['referer'])) curl_setopt ($ch, CURLOPT_COOKIE, $option['referer']);
		if(!empty($option['useragent'])) curl_setopt ($ch, CURLOPT_USERAGENT, $option['useragent']);
		#curl_setopt($ch, CURLOPT_HEADER, 1); //헤더값을 가져오기위해 사용합니다. 쿠키를 가져오려고요.

		# 데이터 확인 및 메소드 설정
		if (!empty($data)) {
			if(!$method) $method = "POST";
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);			
		}
		else if(!$method) $method = "GET";				
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
				
		if (substr($url, 0, 5) == "https") {
			if(!empty($option['SSLVERSION'])) curl_setopt($ch, CURLOPT_SSLVERSION, $option['sslversion']); // SSL 버젼 (https 접속시에 필요)
			if(!empty($option['sslVERIFYHOST'])) curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, true);
			if(!empty($option['sslVERIFYPEER'])) curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true); // 인증서 체크같은데 true 시 안되는 경우가 많다.		
		} else {
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		}
		$result = curl_exec($ch);				# curl 실행
		$resultCode = curl_errno($ch);	# curl 오류코드 확인
		$resultMsg = curl_error($ch);		# curl 오류 확인
		curl_close($ch);
		
		if ($result === FALSE) return array("resultCode"=>$resultCode,"resultMsg"=>$resultMsg);
		else return $result;
	}
}
class commonCrypt
{
	var $algorithm = "";

	function __construct($algorithm=""){
		#$this->algorithm = "rijndael-128";
		$this->algorithm = "AES-128-CBC";
		$this->encMode = "cbc";
		$this->binEnc = "base64";
		if($algorithm) $this->algorithm = $algorithm;
		
	}
	function PKCS5Pad($text, $blocksize = 16)
	{
		$pad = $blocksize - (strlen($text) % $blocksize);
		return $text . str_repeat(chr($pad), $pad);
	}
	function PKCS5Unpad($text)
	{
		$pad = ord($text{strlen($text)-1});
		if ($pad > strlen($text)) return $text;
		if (!strspn($text, chr($pad), strlen($text) - $pad)) return $text;
		return substr($text, 0, -1 * $pad);
	}
	function encrypt($str,$key,$iv='')
	{
		#echo "binEnc=><xmp>{$this->binEnc}</xmp>";
		if($this->binEnc == "base64") return @openssl_encrypt($str, $this->algorithm, $key, 0, $iv);
		else if($this->binEnc == "hex") return bin2hex(base64_decode(@openssl_encrypt($str, $this->algorithm, $key, 0, $iv)));
		
		$ciphertext = @mcrypt_encrypt($this->algorithm, $key, $this->PKCS5Pad($str), $this->encMode, $iv);
		return $ciphertext;

		/*$td = mcrypt_module_open($this->algorithm, '', $this->encMode, '');
		@mcrypt_generic_init($td, $key, $iv);
		$encrypted = @mcrypt_generic($td, $this->PKCS5Pad(($str)));
		
		mcrypt_generic_deinit($td);
		mcrypt_module_close($td);
		return ($encrypted);*/
	}
 
	function decrypt($str,$key,$iv='')
	{
		#echo "decrypt=><xmp>$ciphertext</xmp>";
		return openssl_decrypt($str, $this->algorithm, $key, 0, $iv);

		$decrypted = mcrypt_decrypt($this->algorithm, $key, $str , $this->encMode, $iv);
		return ($this->PKCS5Unpad($decrypted));

		/*$td = mcrypt_module_open($this->algorithm, '', $this->encMode, '');
		@mcrypt_generic_init($td, $key, $iv);
		$decrypted = @mdecrypt_generic($td, $code);

		mcrypt_generic_deinit($td);
		mcrypt_module_close($td);
		return ($this->PKCS5Unpad($decrypted));*/
	}
	
	function AESCBCPKCS5($source_data, $key, $iv, $mode = "enc", $base64 = "yes")
	{
		if($mode=="dec")
		{
			if($base64=="yes") return $this->decrypt($iv,$key,base64_decode($source_data));
			else return $this->decrypt($iv,$key,$source_data);
		}
		else
		{
			if($base64=="yes") return base64_encode($this->encrypt($iv,$key,$source_data));
			else $this->encrypt($iv,$key,$source_data);
		}
	}
}

class cmsLicence
{
	public function MakeKey() {
		$res = openssl_pkey_new([
				"digest_alg" => "sha256",
				"private_key_bits" => 2048,
				"private_key_type" => OPENSSL_KEYTYPE_RSA
				]);
		openssl_pkey_export($res, $privKey);
		$pubKey = openssl_pkey_get_details($res);
		return array('publicKey'=>$pubKey['key'], 'privateKey'=>$privKey);
	}
}


function logFiler($filename,$date=""){
	$file = file_get_contents(__LOG_DIR__.$filename);
	$fileArray = explode("\n\n",$file);
	foreach($fileArray as $fileObj){
		list($dateKey)=explode("] =>Array",$fileObj);		
		$dateKey = substr($dateKey,1);
		if(!$dateKey) continue;
		$dateKey = str_replace("[","",$dateKey);
		
		$fileObjArr=explode("] => ",$fileObj);
		$contents = substr(array_pop($fileObjArr),0,-1);
		foreach($fileObjArr as $idx => $arrData){
			$key[$idx+1] = array_pop(explode("[",$arrData));
			if($idx) list($val[$idx]) = explode("[",$arrData);
		}
		#exit("<xmp id='logArray'>".print_r($key,1)."</xmp>");

		if(!empty($key)) foreach($key as $idx => $k) $objData[$dateKey][$k]=trim($val[$idx]);
		$objData[$dateKey][$k] = trim($contents);
		#$objData[$dateKey]['postData'] = $contents;
		#exit("<xmp id='logArray'>".print_r($contents,1)."</xmp>");
		#echo("<xmp id='logArray'>key=>".print_r($key,1)."</xmp>");
		#echo("<xmp id='logArray'>val=>".print_r($val,1)."</xmp>");
		#exit("<xmp id='logArray'>".print_r($objData,1)."</xmp>");
	}
	#exit("<div id='logArray'>".print_r($objData,1)."</div>");
	#exit("<xmp id='logArray'>objData=>".print_r($objData,1)."</xmp>");
	if(!empty($date) && $objData[$date]) return $objData[$date];
	return $objData;
}
function logWriter($filename,$data,$type=''){
	if(!is_dir(__LOG_DIR__)) {
		@mkdir(__LOG_DIR__, 0777);
		@chmod(__LOG_DIR__, 0777);
	}
	if($type=='json') return file_put_contents(__LOG_DIR__.$filename, json_encode(['date'=>date('Y-m-d H:i:s'),'data'=>$data],JSON_UNESCAPED_UNICODE)."\n\n",FILE_APPEND);
	else return file_put_contents(__LOG_DIR__.$filename, date('[Y-m-d H:i:s] =>').print_r($data,1)."\n\n",FILE_APPEND);
}

/*
# 검색해서 카피하는 소스 퍼옴 https://doolyit.tistory.com/203
function recursive_copy($src,$dst) {
	$funcName = __FUNCTION__;
	$dir = opendir($src);
	@mkdir($dst,0777,true);		# dir생성+권한변경
	while(false !== ( $file = readdir($dir)) ) {
		if (( $file != '.' ) && ( $file != '..' )) {
			if ( is_dir($src . '/' . $file) ) {
				$this->{$funcName}($src . '/' . $file,$dst . '/' . $file);
			}
			else {
				#echo "recursive_copy $src . '/' . $file => $dst . '/' . $file <br>";
				copy($src . '/' . $file,$dst . '/' . $file);
				chmod($dst . '/' . $file, 0777);		# 권한변경
			}				
		}
	}
	closedir($dir);
}

# sftp용 수정
function ssh2_sftp_copy($src,$dst,$exception=['.svn','skin/aaa']) {
	global $sshConn,$sftp;
	$funcName = __FUNCTION__;
	$dir = opendir($src);

	echo("<xmp>".print_r($src,1)."</xmp>");

	@ssh2_sftp_mkdir($sftp,$dst,0777,true);		# dir생성+권한변경
	while(false !== ( $file = readdir($dir)) ) {
		if(in_array($file,$exception)) continue;	# 제외파일 설정의 경우
		if(in_array($dir.'/'.$file,$exception)) continue;	# 제외파일 설정의 경우

		if (( $file != '.' ) && ( $file != '..' )) {
			if(is_dir($src.'/'.$file)) $funcName($src . '/' . $file,$dst . '/' . $file , $exception);
			else if(!ssh2_scp_send($sshConn, $src . '/' . $file ,$dst . '/' . $file,0644))	echo $src . '/' . $file." 업로드 오류!";	# 업로드
			#else exit($src . '/' . $file." 업로드 성공!\n<br>");	# 업로드
			$succ[]="$src/$file => $dst/$file 업로드 성공!\n<br>";	# 업로드
			
		}
		if(!empty($succ)) exit("<xmp>".print_r($succ,1)."</xmp>");

	}
	closedir($dir);
}

# 디렉토리를 통째로 zip 파일로 압축하는 소스입니다. (https://www.habonyphp.com/2020/07/php-zip_27.html)
# $zip = new ZipArchive;	$zip->open($zipfile, ZipArchive::CREATE);	dirZip($zip,$dir);
function dirZip($zipResource,$dir) {
	$funcName = __FUNCTION__;
	set_time_limit(0);	# 초과시간 무제한
	if(filetype($dir) === 'dir') {
		clearstatcache();
		if($fp = @opendir($dir)) { 
			while(false !== ($ftmp = readdir($fp))){ 
				if(($ftmp !== ".") && ($ftmp !== "..") && ($ftmp !== "")){ 
					if(filetype($dir.'/'.$ftmp) === 'dir') { 
						clearstatcache();						
						$zipResource->addEmptyDir($dir.'/'.$ftmp); // 디렉토리이면 생성하기 
						$funcName($zipResource,$dir.'/'.$ftmp);	# 재귀함수 호출
					}
					else {
						// 파일이면 파일 압축하기 
						$zipResource->addFile($dir.'/'.$ftmp); 
					} 
				} 
			} 
		} 
		if(is_resource($fp)) closedir($fp);
	}
	else $zipResource->addFile($dir); // 파일이면 파일 압축하기
} // end func 
*/

# zip 압축풀기
/*$zip = new ZipArchive();
$filename = __MOD_FILE_PATH__."$SETMODULE.zip"; // 압축 파일명
if (!$zip->open($filename)) exit("cannot open <$filename>\n"); # 압축 파일 열기 실패
$list = [];
for ($i=0; $i<$zip->numFiles;$i++) {
    // 파일 리스트 담기
    array_push($list,$zip->statIndex($i));
}
$zip->close();
echo "<xmp>".print_r($list,1)."</xmp>";
exit;*/


?>