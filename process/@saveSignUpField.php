<?php
/**
 * 이 파일은 iModule 회원모듈의 일부입니다. (https://www.imodules.io)
 * 
 * 회원가입폼의 필드를 저장한다.
 *
 * @file /modules/member/process/@saveSignUpField.php
 * @author Arzz (arzz@arzz.com)
 * @license MIT License
 * @version 3.1.0
 * @modified 2018. 4. 9.
 */
if (defined('__IM__') == false) exit;

$reservation = array('idx','domain','type','email','password','code','name','nickname','homepage','telephone','cellphone','gender','birthday','zipcode','address','exp','point','last_login','leave_date','status','etc','level','photo');

$errors = array();
$label = Request('label');
$oName = Request('oName');
$name = Request('name');
$name_etc = Request('name_etc');

if ($oName) {
	$is_system = in_array($oName,$reservation);
	$type = $is_system == true ? $oName : 'etc';
	$input = $is_system == true ? 'system' : Request('input');
	$name = $oName;
} else {
	$is_system = $name !== 'etc';
	$type = $is_system == true ? $name : 'etc';
	$input = $is_system == true ? 'system' : Request('input');
	$name = $name == 'etc' ? $name_etc : $name;
	
	if (preg_match('/^[a-z]+[a-z_0-9]*$/',$name) == false) {
		$errors['name_etc'] = $this->getErrorText('INVALID_FIELD_NAME');
	}
	
	if ($this->db()->select($this->table->signup)->where('name',$name)->where('label',$label)->has() == true) {
		if ($is_system == true) $errors['name'] = $this->getErrorText('DUPLICATED');
		else $errors['name_etc'] = $this->getErrorText('DUPLICATED');
	}
}

$title = Request('title') ? Request('title') : $errors['title'] = $this->getErrorText('REQUIRED');
$codes = Request('title_codes');
$languages = Request('title_languages');
$title_languages = new stdClass();
for ($i=0, $loop=count($codes);$i<$loop;$i++) {
	if (preg_match('/^[a-z]{2}$/',trim($codes[$i])) == true && strlen(trim($languages[$i])) > 0) {
		$title_languages->{trim($codes[$i])} = trim($languages[$i]);
	}
}
$help = Request('help');
$codes = Request('help_codes');
$languages = Request('help_languages');
$help_languages = new stdClass();
for ($i=0, $loop=count($codes);$i<$loop;$i++) {
	if (preg_match('/^[a-z]{2}$/',trim($codes[$i])) == true && strlen(trim($languages[$i])) > 0) {
		$help_languages->{trim($codes[$i])} = trim($languages[$i]);
	}
}
$is_required = Request('is_required') == 'on' ? 'TRUE' : 'FALSE';
if (in_array($name,array('agreement','privacy','email','password','name','nickname', 'cpnumber', 'cpname')) == true) $is_required = true;

if ($is_system === false && in_array($name,$reservation) == true) {
	$errors['name_etc'] = $this->getErrorText('RESERVED_NAME');
}

$configs = new stdClass();
if (in_array($input,array('select','radio','checkbox')) == true) {
	$options = Request('options');
	for ($i=0, $loop=count($options);$i<$loop;$i++) {
		$options[$i] = json_decode($options[$i]);
	}
	$configs->options = $options;
	
	if ($input == 'checkbox') $configs->max = Request('max');
}
if (in_array($name,array('agreement','privacy')) == true) {
	$configs->content = Request('content');
}

if (count($errors) == 0) {
	$insert = array();
	$insert['type'] = $type;
	$insert['input'] = $input;
	$insert['title'] = $title;
	$insert['title_languages'] = json_encode($title_languages,JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
	$insert['help'] = $help;
	$insert['help_languages'] = json_encode($help_languages,JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
	$insert['configs'] = json_encode($configs,JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
	$insert['is_required'] = $is_required;
	
	if ($oName) {
		$this->db()->update($this->table->signup,$insert)->where('label',$label)->where('name',$oName)->execute();
	} else {
		$insert['label'] = $label;
		$insert['name'] = $name;
		
		if ($name == 'agreement') {
			$insert['sort'] = -2;
		} elseif ($name == 'privacy') {
			$insert['sort'] = -1;
		} else {
			$sort = $this->db()->select($this->table->signup,'MAX(sort) as sort')->where('label',$label)->getOne();
			$insert['sort'] = isset($sort->sort) == true ? $sort->sort + 1 : 0;
		}
		$this->db()->insert($this->table->signup,$insert)->execute();
	}
	
	$results->success = true;
} else {
	$results->success = false;
	$results->errors = $errors;
}
?>