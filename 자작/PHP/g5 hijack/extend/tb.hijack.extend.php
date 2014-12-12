<?php
header("Content-Type: text/html; charset=UTF-8");

# 기본환경 설정
define('TB_ROOT_HIJACK', '0'); // 루트 하이젝 을 할 경우 1
$getcwd = str_replace('\\', '/', getcwd()); // 윈도우의 경우 \를 /로 변경
define('TB_HIJACK_PATH', G5_PATH.'/tb/core'); // 납치 후 처리 할 코어 폴더
define('TB_WORK_DIR', str_replace(G5_PATH, '', $getcwd)); // 작업중인 DIR에서 고유 dir만 남김
$TBWorkFile = basename($_SERVER['PHP_SELF']); // 현재 작업중인 파일 명
$TBWorkingDir = array('bbs'); // 납치할 폴더명

# 해당 문서를 가장 마지막으로 호출(GPF 참조)
foreach(glob(dirname(__FILE__).'/*.php') as $IncludeFile) {

	if(basename($IncludeFile) == basename(__FILE__)) continue;
	@include_once($IncludeFile);
}

# 루트 경로 파일 납치 조건
if(TB_ROOT_HIJACK == 1) {

	foreach(glob($getcwd.'/*.php') as $RootHiJack) {

		$RootFiles = str_replace(G5_PATH.'/', '', $RootHiJack);
		if($TBWorkFile == $RootFiles) define('ROOT_HIJACK', 1);
	}
}

# 납치하기~ 
if (strpos2($getcwd, $TBWorkingDir) > 0 || ROOT_HIJACK == 1) {

	if(is_dir(TB_HIJACK_PATH.TB_WORK_DIR) && is_file(TB_HIJACK_PATH.TB_WORK_DIR.'/'.$TBWorkFile)) {

		// 원본 구조
		define('TB_REAL_PATH', $getcwd);

		// 변경된 파일을 호출
		@include_once(TB_HIJACK_PATH.TB_WORK_DIR.'/'.$TBWorkFile);

		// 하이젝 모드 off명령을 받았다면..
		if(TB_HIJACK_MODE == 'off') return;
		exit;
	} else { return; }
}


# array형태의 핸들을 strpos처럼 비교 (http://php.net/manual/en/function.ob-start.php)
function strpos2($haystack, $needles=array(), $offset=0) {

	$chr = array();
	foreach($needles as $needle) {
		$res = strpos($haystack, $needle, $offset);
		if ($res !== false) $chr[$needle] = $res;
	}
	if(empty($chr)) return false;
	return min($chr);
}

unset($TBWorkFile, $TBWorkingDir, $getcwd);