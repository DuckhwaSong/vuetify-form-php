<?
# 수정일 : 2023-02-24
# vue용 변경
CLASS form{
	static function input($title,$key,$option,$value=""){
		if(!$option['size']) $option['size'] = "30";
		if(0);
		else return "
		<v-list-item tag='li' :key='id_$key'>
			<v-text-field
			id='input_$key' name='$key' placeholder='{$option['placeholder']}'
			v-model='formVal.$key'
			label='$key'
			></v-text-field>
		</v-list-item>
		";
	}
	static function text($title,$key,$option=array(),$value=""){
		if(!$option['cols']) $option['cols'] = "40";
		if(!$option['rows']) $option['rows'] = "6";
		if(0);
		else return "
		<v-list-item tag='li' :key='id_$key'>
			<v-textarea id='input_$key'  name='$key' v-model='formVal.$key' cols='{$option['cols']}' rows='{$option['rows']}' label='$title'></v-textarea>
		</v-list-item>
		";
	}

	static function select($title,$key,$option,$value=""){
		#foreach($option['arrOption'] as $verK => $verT) $arrOption .= "<option value='$verK' ".(($verK==$value)?"selected":"").">$verT</option>";
		foreach($option['arrOption'] as $verK => $verT) $arrOptionx[] = ['title'=>$verT,'value'=>$verK];
		$arrOption = json_encode($arrOptionx,JSON_UNESCAPED_UNICODE);
		#$arrOption = addslashes($arrOption);
		#echo "<xmp>".print_r($arrOption,1)."</xmp>";
		if(0);
		else return "
		<v-list-item tag='li' :key='id_$key'>
			<v-select
			label='$title'
			name='$key'
			:items='$arrOption'
			v-model='formVal.$key'
			variant='solo'
			></v-select>
		</v-list-item>
		";
	}
	static function checkbox($title,$key,$option,$value=""){
		#foreach($option['arrOption'] as $verK => $verT) $arrOption .= "<label><input type='checkbox' name='{$key}[]' value='$verK' ".(($value==$verK)?"checked":"").">$verT</label>";
		foreach($option['arrOption'] as $verK => $verT) $arrOption .= "<v-checkbox label='$verT' v-model='formVal.$key' value='{$verK}' name='{$key}[]'></v-checkbox>";
		if(0);
		else return "
		<v-list-item tag='li' :key='id_$key'>
			<p>$title</p>
			<input type='hidden' name='{$key}[]'>
			<v-row class='ma-1' align='start'>
			$arrOption		
			</v-row>
		</v-list-item>

		";
	}
	static function radio($title,$key,$option,$value=""){
		#foreach($option['arrOption'] as $verK => $verT) $arrOption .= "<label><input type='radio' name='{$key}' value='$verK' ".(($value==$verK)?"checked":"").">$verT</label>";
		foreach($option['arrOption'] as $verK => $verT) $arrOption .= "<v-radio label='$verT' value='{$verK}' name='{$key}'></v-radio>";
		if(0);
		else return "
		<v-list-item tag='li' :key='id_$key'>
			<v-radio-group id='label_$key' inline label='$title' v-model='formVal.$key'>
				$arrOption
			</v-radio-group>
		</v-list-item>
		";
	}
	static function file($title,$key,$option,$value=""){
		if(0);
		else return "
		<v-list-item tag='li' :key='id_$key'>
			<v-file-input label='$title' id='input_$key' name='$key'></v-file-input>
		</v-list-item>
		";
	}
	static function button($title,$key,$type="button"){
		if(0);
		else return "
		<v-list-item tag='li' :key='id_$key'>
			<v-btn type='$type' id='input_$key' name='$key' block class='mt-4' color='warning'>$title</v-btn>
		</v-list-item>
		";
	}
	static function submit($title,$key=""){
		if(0);
		else return "
		<v-list-item tag='li' :key='id_$key'>
			<v-btn type='submit' id='input_$key' name='$key' block class='mt-4' color='warning'>$title</v-btn>
		</v-list-item>
		";
	}



}
?>