<?
header("Content-Type: text/html; charset=UTF-8");

# 라이브러리 호출
include("common.lib.php");
$commonlib = new commonlib();

# 세팅 값 저장
foreach($_POST as $key => $val) {
	if(is_array($val)) $val = implode(",",$val);
	$configData .= "$key=\"".$commonlib->encrypt($val, "AES-128-ECB","123456789ABCDEFG")."\"\r\n";
}
file_put_contents("setting.ini", $configData);

# 로그 저장
$date = date('Y-m-d');
#logWriter("{$date}.log",$_POST,'json');
logWriter("{$date}.log",$_POST);

# 출력테스트
#$home = "http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
#echo "HOME : $home<br><br>";

# 오토로드
spl_autoload_register(function ($class) {    
    include "$class.php";
});

if(!empty($_POST)) exit("<xmp>" . print_r($_POST, 1) . "</xmp>");
exit("<xmp>" . print_r($_SERVER, 1) . "</xmp>");

# 초과시간 변경
set_time_limit(0);

# 변수정리
$HOSTUSER = trim($_POST['HOSTUSER']);
$HOSTPASS = trim($_POST['HOSTPASS']);
$HOSTDIR = trim($_POST['HOSTDIR']);

# 0. 접속 #ssh 접속 
list($HOSTIP,$HOSTPORT) = array_map("trim",explode(":",trim($_POST['HOSTIP'])));
if(empty($HOSTPORT)) $HOSTPORT=22;
$sshConn = ssh2_connect($HOSTIP,$HOSTPORT);
ssh2_auth_password($sshConn, $HOSTUSER, $HOSTPASS);
if(!empty($sshConn)) sleep(1);	# 접속된경우 1초간 지연

# 0. 접속 #sftp 접속 (scp 명령어가 막힌경우 sftp로 전송해야함)
use phpseclib\Net\SFTP;
$sftp = new SFTP($HOSTIP,$HOSTPORT); # sftp 접속
if (!$sftp->login($HOSTUSER, $HOSTPASS)) exit('sftp Login Failed');	# sftp 로그인
$sftp->chdir("{$HOSTDIR}/");	# sftp 기본경로 이동

# 1. 소스세팅
if(in_array("cms",$_POST['settingItem'])){	# tar.gz 파일 업로드 & 설치
	include("module.cms.php"); # 모듈 cms 설치
}
# 2. DB/DBUSER생성
if(in_array("newdbdb",$_POST['settingItem'])){
	include("module.newdbdb.php"); # DB/DBUSER생성
}
# 2. DB세팅
if(in_array("db",$_POST['settingItem'])){
	include("module.db.php"); # 모듈 db 설치
}
# 3. 라이센스세팅
if(in_array("licence",$_POST['settingItem'])){
	include("module.licence.php"); # 모듈 라이센스 설치
}


# 접속종료
if(!empty($sshConn)) sleep(1);	# 접속된경우 1초간 지연
ssh2_exec($sshConn,'echo "EXITING" && exit;');
unset($sshConn);
exit;

?>
