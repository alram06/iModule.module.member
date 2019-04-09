<?php
/**
 * 이 파일은 iModule 회원모듈의 일부입니다. (https://www.imodules.io)
 * 
 * 회원라벨의 표시순서를 저장한다.
 *
 * @file /modules/member/process/@saveLabelSort.php
 * @author Arzz (arzz@arzz.com)
 * @license MIT License
 * @version 3.1.0
 * @modified 2018. 4. 9.
 */
if (defined('__IM__') == false) exit;

$updated = json_decode(Request('updated'));
for ($i=0, $loop=count($updated);$i<$loop;$i++) {
	if ($updated[$i]->idx > 0) $this->db()->update($this->table->label,array('sort'=>$updated[$i]->sort))->where('idx',$updated[$i]->idx)->execute();
}

$results->success = true;
?>