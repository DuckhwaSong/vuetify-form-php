<?
header("Content-Type: text/html; charset=UTF-8");

# 라이브러리 호출
include("common.lib.php");
$commonlib = new commonlib();

$mode = $_POST['mode'];


if($mode=="file"){
	$scandir = 	scandir(__LOG_DIR__);
	foreach($scandir as $file){
		if(substr($file,0,1)==".") continue;
		if(substr($file,-4)==".log") $select[] = $file;
	}
	$select = array_reverse($select);
	$selectOption ="<option value=''>===========</option>";
	foreach($select as $logfile) $selectOption .="<option>$logfile</option>";
	exit("<select name='logfile' onchange='logDateSelect($(this));'>$selectOption</select>"); 
}
else if($mode=="array"){
	$objData = logFiler($_POST['file']);
	$selectOption ="<option value=''>===========</option>";
	foreach($objData as $date => $data){
		$selectOption .="<option>$date</option>";
		$jsonDate = json_encode($data);
		#exit("<xmp id='logArray'>jsonDate=>".print_r($jsonDate,1)."</xmp>");
	}
	exit("<div id='logArray'><select name='logdate' onchange='logDateLoad($(this));'>$selectOption</select></div>"); 
}
else if($mode=="load"){
	$objData = logFiler($_POST['file'],$_POST['date']);
	exit(json_encode($objData));
	exit("<xmp id='logArray'>objData=>".print_r($objData,1)."</xmp>");

}
exit("<xmp>".print_r($select,1)."</xmp>");

?>