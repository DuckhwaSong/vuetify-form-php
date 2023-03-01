<?

# http://sandbox.localhost/newProjectTool/

header("Content-Type: text/html; charset=UTF-8");
#phpinfo();exit;
#$config = parse_ini_file("config.ini",true);
//echo "<xmp>".print_r($config,1)."</xmp>";


# 라이브러리 호출
include("common.lib.php");
$commonlib = new commonlib();

# 인풋 보조함수 호출
include("common.form.php");

# 세팅 값 불러오기
$setting = parse_ini_file("setting.ini");
#foreach($setting as $key => $val) $setting[$key] = openssl_decrypt($val, "AES-128-ECB","123456789ABCDEFG");
foreach($setting as $key => $val) $setting[$key] = $commonlib->decrypt($val, "AES-128-ECB","123456789ABCDEFG");

# 타이틀 설정
$title = "*신작업용*";
$titleComment ="신규로 뭔가 만들때 사용.";

# 폼-항목
foreach($setting as $idx => $value) if(strpos($value,",")!==false) $setting[$idx]=explode(",",$value);
#echo "<xmp>".print_r($setting,1)."</xmp>";

# 폼설정 
$actionFile = "process.action.php";
$echoForm .= form::input("HOSTIP","HOSTIP",array("size"=>"46"));
$echoForm .= form::input("HOSTDIR","HOSTDIR",array("size"=>"46","placeholder"=>"/www/devcustom_firstmall_kr"));
#$echoForm .= form::text("HOSTPASS","HOSTPASS",array("size"=>"15","placeholder"=>"HOSTPASS"));
$echoForm .= form::checkbox("세팅항목","settingItem1",array("style"=>"width:150px","arrOption"=>array("cms"=>"소스설치","db"=>"db설치","licence"=>"licence")));
$echoForm .= form::radio("세팅항목","settingItem2",array("style"=>"width:150px","arrOption"=>array("cms"=>"소스설치","db"=>"db설치","licence"=>"licence")));
$echoForm .= form::select("모듈 관리","SETTYPE",array("style"=>"width:150px","arrOption"=>array("status"=>"설치상태","addkey"=>"키추가","delkey"=>"키제거","install"=>"설치","delete"=>"삭제","expire"=>"만료")));
$echoForm .= "<v-divider></v-divider>";	# 줄바꿈
$echoForm .= form::file("파일","tmpfile",array("arrOption"=>array("값1"=>"타이틀1","값2"=>"타이틀2")));
$echoForm .= "<v-divider :thickness='2' color='info'></v-divider>";	# 줄바꿈
/*
$echoForm .= "<li class='form-line' style='border:1px solid gray; width: 100%; height: 1px; padding:0; margin:0;'></li>";	# 줄바꿈
$echoForm .= "<li class='form-line'>[이하 DB 세팅]</li>";	# 줄바꿈
$echoForm .= form::input("시리얼넘버","serialNo",array("size"=>"46","placeholder"=>"20220100XX"),$setting['serialNo']);
$echoForm .= form::input("DBHOST","DBHOST",array("size"=>"15","placeholder"=>"DBHOST"),$setting['DBHOST']);
$echoForm .= form::input("DBNAME","DBNAME",array("size"=>"15","placeholder"=>"DBNAME"),$setting['DBNAME']);
$echoForm .= form::input("DBUSER","DBUSER",array("size"=>"15","placeholder"=>"DBUSER"),$setting['DBUSER']);
$echoForm .= form::input("DBPASS","DBPASS",array("size"=>"15","placeholder"=>"DBPASS"),$setting['DBPASS']);

$echoForm .= form::checkbox("","settingItem",array("style"=>"width:150px","arrOption"=>array("newdb"=>"DB/DB USER생성")),$setting['FTPPW']);
$echoForm .= form::input("ROOTDBHOST","ROOTDBHOST",array("size"=>"15","placeholder"=>"ROOTDBHOST"),$setting['ROOTDBHOST']);
$echoForm .= form::input("ROOTDBUSER","ROOTDBUSER",array("size"=>"15","placeholder"=>"ROOTDBUSER"),$setting['ROOTDBUSER']);
$echoForm .= form::input("ROOTDBPASS","ROOTDBPASS",array("size"=>"15","placeholder"=>"ROOTDBPASS"),$setting['ROOTDBPASS']);

$addJScript .="
	// 체크박스마다 보이게/안보이게
	$('li[id^=\"id_ROOTDB\"]').hide();
	$('input[name=\"settingItem[]\"]').on('click',function(){
		if($(this).val()=='newdb'&& $('li[id=\"id_ROOTDBHOST\"]').is(':visible')==true){
			$('li[id^=\"id_ROOTDB\"]').hide();
		}
		else if($(this).val()=='newdb'&& $('li[id=\"id_ROOTDBHOST\"]').is(':visible')!=true){
			$('li[id^=\"id_ROOTDB\"]').show();
		}
	});	
";

$echoForm .= "<li class='form-line' style='border:1px solid gray; width: 100%; height: 1px; padding:0; margin:0;'></li>";	# 줄바꿈
$echoForm .= "<li class='form-line'>[이하 라이센스 세팅] > 라이센스 체크할 항목만 입력</li>";	# 줄바꿈
$echoForm .= form::input("라이센스-서버IP","licence_serverip",array("size"=>"15","placeholder"=>"127.0.0.1"),$setting['licence_serverip']);
$echoForm .= form::input("라이센스-호스트(도메인)","licence_hostname",array("size"=>"15","placeholder"=>"www.cms.kr"),$setting['licence_hostname']);
$echoForm .= form::input("라이센스-ROOT PATH","licence_rootpath",array("size"=>"15","placeholder"=>"/www/cms"),$setting['licence_rootpath']);
$echoForm .= form::input("라이센스-아웃바운드IP","licence_outip",array("size"=>"15","placeholder"=>"127.0.0.1"),$setting['licence_outip']);
*/
#$echoForm .= "<li class='form-line' style='border:0px solid red; width: 100%; height: 1px; padding:0; margin:0;'></li>";	# 줄바꿈
$echoForm .= form::button("버튼","submitBtn");


# 이하 건들지마셔
$jScript = "$(document).ready(function() {
	// outIP
	//setTimeout(function(){},1000);
	$.ajax({
		url : 'https://api.ip.pe.kr/',
		type : 'post',
		success : function(data) {
			$('#outIp').html(data);
		}
	});
	
	// 로그파일 SELECT로 보여줌
	$('#logBtn').on('click',function(){
		$.ajax({
			url : 'process.log.php',
			type : 'post',
			data : {mode:'file'},
			success : function(data) {
				//alert(data);
				$('#subHeader_log').html(data);
			}
		});

		//$('#subHeader_log').html('121212');
	});	

	// ajax로 데이터 전송 후 화면에 전달
	$('#input_submitBtn').on('click',function(){
		//alert('input_submitBtn');
		var rand = Math.floor(Math.random()*100);
		if(rand%5==0) var color='red';
		if(rand%5==1) var color='blue';
		if(rand%5==2) var color='green';
		if(rand%5==3) var color='brown';
		if(rand%5==4) var color='orange';
		$.ajax({
			url : '$actionFile',
			type : 'post',
			data : $('#apiTesterFrom').serialize(),
			success : function(data) {
				//alert(data);
				$('#outputText').html(data).css('border','1px solid '+ color);
				$('#outputForm').show();
			}				
		});

	});
});";
$jScript .= "
function logDateSelect(obj){
	var logfile = obj.val();
	//alert(subHeader_log);
	$('#logArray').remove();
	if(!logfile) return 0;
	$.ajax({
		url : 'process.log.php',
		type : 'post',
		data : {mode:'array',file:logfile},
		success : function(data) {
			obj.after(data);
		}
	});
}
function logDateLoad(obj){
	var logdate = obj.val();
	var logfile = $('select[name=logfile]').val();
	//return alert(logfile);
	if(!logdate) return 0;
	$.ajax({
		url : 'process.log.php',
		type : 'post',
		data : {mode:'load',file:logfile,date:logdate},
		success : function(data) {
			//alert(data);
			var jsonData = JSON.parse(data);			
			for(key in jsonData) valueLoader(key,jsonData[key]);
			$('#subHeader_log').html('');
		}
	});
}

// 폼정보수정 Jquery - 유용한함수될듯
function valueLoader(name,value){
	$('input[name='+name+'][type=text]').val(value);
	$('select[name='+name+']').val(value);
	$('textarea[name='+name+']').val(value);
	$('input[name='+name+'][type=radio]').each(function(){
		if( $(this).val() == value ) $(this).prop('checked',true);
	});
}
";

$Vue3CompositionAPI = "
	// Vue3 Composition API
	const { ref, createApp } = Vue;
	//const { createApp } = Vue;
	const { createVuetify } = Vuetify;
	const vuetify = createVuetify();
	let formVal=ref(".json_encode($setting,JSON_UNESCAPED_UNICODE).");
	let vueCustom={
		setup() {
			//alert('setup');
			const count = ref(0);
			const increment = () => {
				count.value++
			};
			const userName= ref(0);
			return {formVal};
		}
	};
	const app = createApp(vueCustom);
	app.use(vuetify).mount('#app');
";

$Vue3OptionsAPI= "
	// Vue3 Options API
	const { createApp } = Vue;
	const { createVuetify } = Vuetify;
	const vuetify = createVuetify();	
	let formVal=".json_encode($setting,JSON_UNESCAPED_UNICODE).";
	let vueCustom={
    data() {
      return {formVal
		,select: [{'title':'John','value':'A'},{'title':'jake','value':'B'}]
		,selected:''
		}
    },
    created:function(){
      //this.axiosGet()
    },
    mounted:function(){
      this.initd();     
    },
    methods: {
      initd(){
      },
      goodgood(event){
        alert('나도 좋아요');
        this.jsonData.count++;
      }
    }
  };
	const app = createApp(vueCustom);
	app.use(vuetify).mount('#app');
";
$vueJScript = $Vue3OptionsAPI;
$jScript .= $vueJScript;
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html class="supernova"><head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta property="og:title" content="Course Registration Form" >
<meta property="og:description" content="Please click the link to complete this form.">
<meta name="slack-app-id" content="AHNMASS8M">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=2.0, user-scalable=1" />
<meta name="HandheldFriendly" content="true" />
<title><?=$title?></title>

<!--script src="https://cdnjs.cloudflare.com/ajax/libs/punycode/1.4.1/punycode.min.js"></script-->
<script
  src="https://code.jquery.com/jquery-1.12.4.min.js"
  integrity="sha256-ZosEbRLbNQzLpnKIkEdrPv7lOy9C27hHQ+Xp8a4MxAQ="
  crossorigin="anonymous"></script>
<script
  src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"
  integrity="sha256-VazP97ZCwtekAsvgPBSUwPFKdrwD3unUfSGVYrahUqU="
  crossorigin="anonymous"></script>

	<!--	vue3 + vuetify  사용	-->
	<script src="./asset/js/vue.3.2.45.prod.js"></script>
	<link href="https://cdn.jsdelivr.net/npm/@mdi/font@4.x/css/materialdesignicons.min.css" rel="stylesheet"/>
	<link href="./asset/vuetify/vuetify.min.css" rel="stylesheet"/>
	<script src="./asset/vuetify/vuetify.min.js"></script>
	<!--	vue3 + vuetify  사용	-->

</head>
<body>
<div id='app'>
  <v-app id="inspire">
    <v-main class="bg-grey-lighten-1">
      <v-container>
        <v-row align="center" justify="center">
          <v-col align-self="center" cols="12" sm="8">
            <v-sheet min-height="70vh" rounded="lg">


			



			<v-list-item tag='li'>
			<ul class="form-section page-section" id="outputForm" style='display:none;'>
				<div class="form-header-group ">
				  <div class="header-text httal htvam">
					<div class="form-subHeader" id="outputText" style='border:1px solid red;'>
						
					</div>
				  </div>
				</div>
			</ul>
			</v-list-item>


			<v-list-item tag='li'>
				<v-alert title="<?=$title?>" text="<?=$titleComment?>" variant="outlined">(나가는 IP: <span id='outIp'></span>)
				<v-btn type='button' id='logBtn' block class='mt-4' color='success'>log</v-btn>
				<div id="subHeader_log" class="form-subHeader"></div>
				</v-alert>
			</v-list-item>

			<v-form validate-on="submit" @submit.prevent="submit" action="<?=$actionFile?>" id='apiTesterFrom'>



			<?=$echoForm?>

			</v-form>
            </v-sheet>
          </v-col>
        </v-row>
      </v-container>
    </v-main>
  </v-app>
</div>
</body>
<script>
<?=$jScript?>
</script>
</html>
