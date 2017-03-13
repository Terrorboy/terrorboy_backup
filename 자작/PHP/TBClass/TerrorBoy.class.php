<?php
/* ==================================================== */
/* 정보텍                                               */
/* ---------------------------------------------------- */
/* - 초기작업 2013-06-04 화요일                       - */
/* - Terrorboy                                        - */
/* -[
                 http://api.terrorboy.net
                                                     ]- */
/* ==================================================== */
// mysql 커넥트 필요시 설정 사용
/*
define('DB_LOCALHOST', 'localhost');
define('DB_DATABASE', 'database_name');
define('DB_ID', 'db_id');
define('DB_PASSWORD', 'db_pw');
*/

class Terrorboy {

	public function  __construct() { }


	protected function systemLoad() { }

	/* --------------------------------------------------------------------------- */
	// XML을 php array로 변환 (http://www.bin-co.com/php/scripts/xml2array/)
		public function xml2array($contents, $get_attributes=1, $priority = 'tag') { 

			if(!$contents) return array(); 

			if(!function_exists('xml_parser_create')) return array();

			$parser = xml_parser_create(''); 
			xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, "UTF-8");
			xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0); 
			xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1); 
			xml_parse_into_struct($parser, trim($contents), $xml_values); 
			xml_parser_free($parser);

			if(!$xml_values) return;

			$xml_array = array();
			$parents = array();
			$opened_tags = array();
			$arr = array();
			$current = &$xml_array; 
			$repeated_tag_index = array();

			foreach($xml_values as $data) {

				unset($attributes,$value);
				extract($data);
				$result = array(); 
				$attributes_data = array();

				if(isset($value)) { 

					if($priority == 'tag') $result = $value; 
					else $result['value'] = $value;
				}

				if(isset($attributes) and $get_attributes) {
					
					foreach($attributes as $attr => $val) { 

						if($priority == 'tag') $attributes_data[$attr] = $val; 
						else $result['attr'][$attr] = $val;
					} 
				} 

				if($type == "open") {

					$parent[$level-1] = &$current; 

					if(!is_array($current) or (!in_array($tag, array_keys($current)))) {

						$current[$tag] = $result; 
						if($attributes_data) $current[$tag. '_attr'] = $attributes_data; 
						$repeated_tag_index[$tag.'_'.$level] = 1;
						$current = &$current[$tag];
					}
					else {

						if(isset($current[$tag][0])) {

							$current[$tag][$repeated_tag_index[$tag.'_'.$level]] = $result; 
							$repeated_tag_index[$tag.'_'.$level]++; 
						}
						else {

							$current[$tag] = array($current[$tag],$result);
							$repeated_tag_index[$tag.'_'.$level] = 2;

							if(isset($current[$tag.'_attr'])) {

								$current[$tag]['0_attr'] = $current[$tag.'_attr']; 
								unset($current[$tag.'_attr']); 
							}
						} 

						$last_item_index = $repeated_tag_index[$tag.'_'.$level]-1; 
						$current = &$current[$tag][$last_item_index]; 
					} 

				}
				else if($type == "complete") {

					if(!isset($current[$tag])) {

						$current[$tag] = $result; 
						$repeated_tag_index[$tag.'_'.$level] = 1; 
						if($priority == 'tag' and $attributes_data) $current[$tag. '_attr'] = $attributes_data; 
					}
					else {

						if(isset($current[$tag][0]) and is_array($current[$tag])) { 

							$current[$tag][$repeated_tag_index[$tag.'_'.$level]] = $result;

							if($priority == 'tag' and $get_attributes and $attributes_data) { 

								$current[$tag][$repeated_tag_index[$tag.'_'.$level] . '_attr'] = $attributes_data; 
							} 

							$repeated_tag_index[$tag.'_'.$level]++;
						}
						else {

							$current[$tag] = array($current[$tag],$result);
							$repeated_tag_index[$tag.'_'.$level] = 1; 

							if($priority == 'tag' and $get_attributes) { 

								if(isset($current[$tag.'_attr'])) {

									$current[$tag]['0_attr'] = $current[$tag.'_attr']; 
									unset($current[$tag.'_attr']); 
								}

								if($attributes_data) { 

									$current[$tag][$repeated_tag_index[$tag.'_'.$level] . '_attr'] = $attributes_data; 
								} 
							} 

							$repeated_tag_index[$tag.'_'.$level]++; 
						} 
					}
				}
				elseif($type == 'close') {

					$current = &$parent[$level-1]; 
				} 
			}

			return($xml_array); 
		}


	/* --------------------------------------------------------------------------- */
	// Mysql 커넥트
		public function Connect() {

			$connect = mysql_connect(DB_LOCALHOST, DB_ID, DB_PASSWORD) or die(mysql_error());
			mysql_select_db(DB_DATABASE, $connect);
			mysql_query(" SET NAMES 'utf8' ");

			return $connect;
		}


	/* --------------------------------------------------------------------------- */
	// DB 정보를 손쉽게 출력 하기 위하여 클래스로 함수 작업 // 출력형식 array()
		public function GetQuery($SQL, $option=array()) {

			$Result = mysql_query($SQL);

			if($Result === FALSE) {

				die(mysql_error());
			}

			if($option['output'] == "array") {

				while($row = mysql_fetch_array($Result)) {

					$return[] = $row;
					$i++;
				}
			}
			else if($option['output'] == "array2") {

				while($row[] = mysql_fetch_assoc($Result)) {

					$return = $row;
					$i++;
				}
			}
			else {

				$return = mysql_fetch_array($Result);
			}

			return $return;
		}


	/* --------------------------------------------------------------------------- */
	// Mysql에 테이블이 있는지 확인 (테이블이 있다면 1반환)
		public function IsTable($Table) {

			$sql = " desc " . $Table;
			$result = mysql_query($sql);

			if(mysql_num_rows($result)) return true;
			else return false;
		}

	/* --------------------------------------------------------------------------- */
	// Mysql에 테이블에 필드가 있는지 확인 (필드가 있다면 1반환)
		public function IsField($Table, $Field) {

			$sql = ' show columns from ' . $Table . ' like \''.$Field.'\' ';
			$result = mysql_query($sql);

			if(mysql_num_rows($result)) return true;
			else return false;
		}


	/* --------------------------------------------------------------------------- */
	// Mysql 테이블의 정보 출력 (인덱스, 컬럼 리스트, 컬럼 데이터 반환)
		public function IsTableData($Table) {

			// 초기값
			$ColnumNum = 0;
			$IndexNum = 0;

			// 테이블 인덱스 정보
			$IndexResult = mysql_query(' show index from ' . $Table);
			while($IndexData = mysql_fetch_assoc($IndexResult)){

				$Index[$IndexNum] = $IndexData;
				$IndexNum++;
			}


			// 테이블 컬럼 상세 정보
			$ColumnResult = mysql_query(' show columns from ' . $Table);
			while($ColumnData = mysql_fetch_assoc($ColumnResult)){

				$Column['list'][$ColnumNum] = $ColumnData['Field'];
				$Column['data'][$ColumnData['Field']] = $ColumnData;
				$Column['data'][$ColumnData['Field']]['number'] = $ColnumNum;

				$ColnumNum++;
			}
			

			// 정보를 모두 변수에 담음
			$list['index'] = $Index; // 인덱스 정보
			$list['columns'] = $Column; // 컬럼 정보


			return $list;
		}


	/* --------------------------------------------------------------------------- */
	// Mysql 테이블의 정보 출력 (필드 데이터)
		public function GetTableData($Table) {

			// 초기값
			$ColnumNum = 0;

			// 테이블 컬럼 상세 정보
			$ColumnResult = mysql_query(' show full columns from ' . $Table);
			$CommentData = array();
			while($ColumnData = mysql_fetch_assoc($ColumnResult)){

				$Column[$ColnumNum]['field'] = $ColumnData['Field'];
				$Column[$ColnumNum]['type'] = $ColumnData['Type'];
				$Column[$ColnumNum]['null'] = $ColumnData['Null'];
				$Column[$ColnumNum]['default'] = $ColumnData['Default'];
				$Column[$ColnumNum]['comment'] = $ColumnData['Comment'];
				$ColnumNum++;
			}

			return $Column;
		}


	/* --------------------------------------------------------------------------- */
	// 날짜를 요일로 변경
		public function DayOfTheWeek($date) {

			$DayOfTheWeek_data = date("w", strtotime($date));

			switch($DayOfTheWeek_data) {

				case 0: $DayOfTheWeek = "일"; break;
				case 1: $DayOfTheWeek = "월"; break;
				case 2: $DayOfTheWeek = "화"; break;
				case 3: $DayOfTheWeek = "수"; break;
				case 4: $DayOfTheWeek = "목"; break;
				case 5: $DayOfTheWeek = "금"; break;
				case 6: $DayOfTheWeek = "토"; break;
			}

			return $DayOfTheWeek;
		}


	/* --------------------------------------------------------------- */
	// 시간단위를 am, pm으로 나눔
		public function AmPm($Data) {

			$Return = array();

			if(preg_match('`:`', $Data)) {

				$dTemp = explode(':', $Data);
				$Data = $dTemp[0];
			}

			if($Data > 12) {

				$Return['eng'] = 'PM';
				$Return['kor'] = '오후';
				$Return['date'] = str_pad(((int)$Data-12), 2,0, STR_PAD_LEFT);
			}
			else {

				$Return['eng'] = 'AM';
				$Return['kor'] = '오전';
				$Return['date'] = str_pad((int)$Data, 2,0, STR_PAD_LEFT);
			}

			if($dTemp[1]) {

				$Return['date'] = $Return['date'].':'.$dTemp[1];
				unset($dTemp);
			}

			return $Return;
		}


	/* --------------------------------------------------------------------------- */
	// SMS:: 전화번호 포맷변경
		public function tel_type($tel_number, $delimiter1="-", $delimiter2="-") {
			
			if(!preg_match("/-/i", $tel_number)) {

				$temp1 = strlen($tel_number);
				if ($temp1 == 8) {

					$ex_num = substr($tel_number, 0, 4);
					$telNum = substr($tel_number, -4);

					return $ex_num.$delimiter2.$telNum;
				}
				else if($temp1 < 9) {
					
					return $tel_number;
				}

				// check l-code for split
				$l2 = substr($tel_number, 0, 2);
				$l3 = substr($tel_number, 0, 3);

				$exacLen = 3;

				if ($l2 == "02") $exacLen = 2;
				if ($l3 == "050") $exacLen = 4;

				$l_code = substr($tel_number, 0, $exacLen);
				$ex_num = substr($tel_number, $exacLen, ($temp1-$exacLen-4));
				$telNum = substr($tel_number, -4);


				return $l_code.$delimiter1.$ex_num.$delimiter2.$telNum;
			}
			else {

				return $tel_number;
			}
		}


	/* --------------------------------------------------------------------------- */
	// alert 생성
		public function SetAlert($type, $msg){
			// Type: info, success, warning, error
			set_session("alert", array("ip"=>$_SERVER['REMOTE_ADDR'], "type"=>$type, "msg"=>$msg));
		}
	// alert 호출
		public function AlertPrint($skin){
			// 스킨경로 설정
			$skin_path = G5_SKIN_PATH."/alert/".$skin;
			$skin_url = G5_SKIN_URL."/alert/".$skin;
			
			// 세션의 정보를 변수화
			$ip = $_SESSION['alert'][ip];
			$type = $_SESSION['alert'][type];
			$msg = $_SESSION['alert'][msg];
			
			// 사용자 아이피와 세션의 아이피가 같은지 확인
			if($_SERVER['REMOTE_ADDR'] == $ip){
				// alert 가 초기화 되지 않았다면 호출
				if($_SESSION['alert']){
					// 스킨 호출
					include_once($skin_path."/alert.skin.php");
					// alert 최기화
					set_session("alert", "");
				}
			}
		}


	/* --------------------------------------------------------------------------- */
	// Header 에러 출력 -> 코드이그나이터 에러페이지를 본따 재수정
		public function SetErrorHeader($Number, $Umsg='') {
		
			/*
			400 ㅡ 잘못된 요청
			403 ㅡ 사용자권한없음
			404 ㅡ 페이지 없음
			405 ㅡ 방식 허용 안함
			406 ㅡ 승인금지
			408 ㅡ 서버작업중
			500 ㅡ 내부서버 오류
			505 ㅡ 사용금지
			-> http://en.wikipedia.org/wiki/List_of_HTTP_status_codes
			*/
			// 기본 에러 넘버 지정
			if(!$Number) $Number = '406';

			$msg = array();

			# 1XX Informational
			$msg[100] = '100 Continue';
			$msg[101] = '101 Switching Protocols';
			$msg[102] = '102 Processing (WebDAV; RFC 2518)';

			# 2XX Success
			$msg[200] = '200 OK';
			$msg[201] = '201 Created';
			$msg[202] = '202 Accepted';
			$msg[203] = '203 Non-Authoritative Information (since HTTP/1.1)';
			$msg[204] = '204 No Content';
			$msg[205] = '205 Reset Content';
			$msg[206] = '206 Partial Content';
			$msg[207] = '207 Multi-Status (WebDAV; RFC 4918)';
			$msg[208] = '208 Already Reported (WebDAV; RFC 5842)';
			$msg[226] = '226 IM Used (RFC 3229)';

			# 3XX Redirection
			$msg[300] = '300 Multiple Choices';
			$msg[301] = '301 Moved Permanently';
			$msg[302] = '302 Found';
			$msg[303] = '303 See Other';
			$msg[304] = '304 Not Modified';
			$msg[305] = '305 Use Proxy (since HTTP/1.1)';
			$msg[306] = '306 Switch Proxy';
			$msg[307] = '307 Temporary Redirect (since HTTP/1.1)';
			$msg[308] = '308 Permanent Redirect (Experimental RFC; RFC 7238)';

			# 4xx Client Error
			$msg[400] = '400 Bad Request';
			$msg[401] = '401 Unauthorized';
			$msg[402] = '402 Payment Required';
			$msg[403] = '403 Forbidden';
			$msg[404] = '404 Not Found';
			$msg[405] = '405 Method Not Allowed';
			$msg[406] = '406 Not Acceptable';
			$msg[407] = '407 Proxy Authentication Required';
			$msg[408] = '408 Request Timeout';
			$msg[409] = '409 Conflict';
			$msg[410] = '410 Gone';
			$msg[411] = '411 Length Required';
			$msg[412] = '412 Precondition Failed';
			$msg[413] = '413 Request Entity Too Large';
			$msg[414] = '414 Request-URI Too Long';
			$msg[415] = '415 Unsupported Media Type';
			$msg[416] = '416 Requested Range Not Satisfiable';
			$msg[417] = '417 Expectation Failed';
			$msg[418] = '418 I\'m a teapot (RFC 2324)';
			$msg[419] = '419 Authentication Timeout (not in RFC 2616)';
			$msg[422] = '422 Unprocessable Entity (WebDAV; RFC 4918)';
			$msg[423] = '423 Locked (WebDAV; RFC 4918)';
			$msg[424] = '424 Failed Dependency (WebDAV; RFC 4918)';
			$msg[426] = '426 Upgrade Required';
			$msg[428] = '428 Precondition Required (RFC 6585)';
			$msg[429] = '429 Too Many Requests (RFC 6585)';
			$msg[431] = '431 Request Header Fields Too Large (RFC 6585)';
			$msg[440] = '440 Login Timeout';
			$msg[444] = '444 No Response';
			$msg[449] = '449 Retry With';

			# 5xx Server Error
			$msg[500] = '500 Internal Server Error';
			$msg[501] = '501 Not Implemented';
			$msg[502] = '502 Bad Gateway';
			$msg[503] = '503 Service Unavailable';
			$msg[504] = '504 Gateway Timeout';
			$msg[505] = '505 HTTP Version Not Supported';
			$msg[506] = '506 Variant Also Negotiates (RFC 2295)';
			$msg[507] = '507 Insufficient Storage (WebDAV; RFC 4918)';
			$msg[508] = '508 Loop Detected (WebDAV; RFC 5842)';
			$msg[510] = '510 Not Extended (RFC 2774)';
			$msg[511] = '511 Network Authentication Required (RFC 6585)';
			$msg[598] = '598 Network read timeout error (Unknown)';
			$msg[599] = '599 Network connect timeout error (Unknown)';

			if($Umsg) $msg[$Number] = $Umsg;

			die('<!doctype html>
				<html lang="ko">
					<head>
						<title>'.$Number.' Error</title>
						<meta charset="utf-8">
						<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
						<style type="text/css">

						::selection{ background-color: #E13300; color: white; }
						::moz-selection{ background-color: #E13300; color: white; }
						::webkit-selection{ background-color: #E13300; color: white; }

						body {
							background-color: #fff;
							margin: 40px;
							font: 13px/20px normal Helvetica, Arial, sans-serif;
							color: #4F5155;
						}

						a {
							color: #003399;
							background-color: transparent;
							font-weight: normal;
						}

						h1 {
							color: #444;
							background-color: transparent;
							border-bottom: 1px solid #D0D0D0;
							font-size: 19px;
							font-weight: normal;
							margin: 0 0 14px 0;
							padding: 14px 15px 10px 15px;
						}

						code {
							font-family: Consolas, Monaco, Courier New, Courier, monospace;
							font-size: 12px;
							background-color: #f9f9f9;
							border: 1px solid #D0D0D0;
							color: #002166;
							display: block;
							margin: 14px 0 14px 0;
							padding: 12px 10px 12px 10px;
						}

						#container {
							margin: 10px;
							border: 1px solid #D0D0D0;
							-webkit-box-shadow: 0 0 8px #D0D0D0;
						}

						p {
							margin: 12px 15px 12px 15px;
						}
						</style>
					</head>
					<body>
						<div id="container">
							<h1>'.$Number.' Error!!</h1>
							<p>'.$msg[$Number].'</p>
						</div>
					</body>
				</html>');
			exit;
		}


	/* --------------------------------------------------------------------------- */
	// Header 정보 배열 출력
		public function HeaderInfo() {
			
			$headers = getallheaders();
			while (list ($header, $value) = each ($headers)) {

				$info[$header] = $value;
			}

			return $info;
		}


	/* --------------------------------------------------------------------------- */
	// array출력 형식을 XML화 (xml 해더는 따로 선언하세요.)
		public function array2xml($Arrays, $Element='item') {

			for($i=0; $i<count($Arrays); $i++) {

				$auto[$i] = $Arrays[$i];

				foreach ($auto[$i] as $key=>$value) { 

					$ReturnAuto[$i]['name'][] = $key;
					$ReturnAuto[$i]['value'][] = $value;
				}
			}

			for($i=0; $i<count($ReturnAuto); $i++) {

				$xml .= '<'.$Element.'>'.PHP_EOL;

				for($s=0; $s<count($ReturnAuto[$i]['name']); $s++) {

					$xml .= "<{$ReturnAuto[$i]['name'][$s]}><![CDATA[{$ReturnAuto[$i]['value'][$s]}]]></{$ReturnAuto[$i]['name'][$s]}>".PHP_EOL;
				}

				$xml .= '</'.$Element.'>'.PHP_EOL;
			}

			return $xml;
		}


	/* --------------------------------------------------------------- */
	// 콘솔 (TB서버로 로그 날림)
		public function TBConsole($Data='', $HeaderInfo='') {

			$server = 'z9n.net'; // 콘솔서버 주소
			$port = 80; // 콘솔 포트

			if(is_array($Data)) $Data = http_build_query($Data);
			if($HeaderInfo == 1) {

				$RData = $Data.'&RemoteAgent='.urlencode($_SERVER['HTTP_USER_AGENT']).'&RemoteAddr='.urlencode($_SERVER['REMOTE_ADDR']).'&RemoteDomain='.urlencode($_SERVER['SERVER_NAME']).'&RemotePort='.urlencode($_SERVER['SERVER_PORT']).'&RemoteUri='.urlencode($_SERVER['REQUEST_URI']).'&RemoteReferer'.urlencode($_SERVER['HTTP_REFERER']);
			}
			else {

				$RData = $Data;
			}

			$handle = @fopen('http://'.$server.':'.$port.'?'.$RData, 'r');
			@fclose($handle);
		}


	/* --------------------------------------------------------------------------- */
	// array를 자바스크립트 콘솔로 출력 한다. (http://kr1.php.net/print_r#96859)
		public function console($Data) {

			echo "<script>\r\n//<![CDATA[\r\nif(!console){var console={log:function(){}}}".PHP_EOL;

			$output = explode("\r\n", $Data);
			if(is_array($Data)) {

				$lines = json_encode($Data);
				echo "console.table({$lines});".PHP_EOL;
			}
			else if(trim($output)) {

				foreach($output as $line) {
					$line = addslashes($line);
					echo "console.log(\"".str_replace(array("\r\n","\r","\n"), '', $line)."\");".PHP_EOL;
				}
			}
			echo "\r\n//]]>\r\n</script>".PHP_EOL;
		}


	/* --------------------------------------------------------------- */
	// 콘솔모드에 데이터를 출력한다. (https://github.com/adamschwartz/log)
	/*
	$tb->console2('BOX', 'box');
	$tb->console2('CODE', 'code');
	$tb->console2('RED', 'red');
	$tb->console2('BLUE', 'blue');
	$tb->console2('text');
	$tb->console2('[c="color:#d200ff; font-weight:bold"]user style[c]');
	$tb->console2('user style2', 'color:#d200ff; font-weight:bold; font-size:20px;');
	
	$tb->console2('user s
	tyle333', 'color:#ff0000; font-weight:bold; font-size:20px;');
	*/
		function console2($Data, $style='') {

			static $tb_console2 = true;

			$uniqid = uniqid();

			if($tb_console2) {

				echo "<script>\r\n//<![CDATA[\r\nif(!console){var console={log:function(){}}}</script>".PHP_EOL;
				echo "<script src='/include/js/log.min.js'></script>".PHP_EOL;
				echo "<script>
					var ConsoleLogBox = 'font-family: \'Helvetica Neue\', Helvetica, Arial, sans-serif; color: #fff; font-size: 20px; padding: 15px 20px; background: #444; border-radius: 4px; line-height: 100px; text-shadow: 0 1px #000';
					var ConsoleLogCode = 'background: rgb(255, 255, 219); padding: 1px 5px; border: 1px solid rgba(0, 0, 0, 0.1)';
					var ConsoleLogFontRed = 'color:#ff0000';
					var ConsoleLogFontBlue = 'color:#2400ff';
					</script>".PHP_EOL;
			}

			if($style == 'box') { $StyleCode = 'ConsoleLogBox'; }
			else if($style == 'code') { $StyleCode = 'ConsoleLogCode'; }
			else if($style == 'red') { $StyleCode = 'ConsoleLogFontRed'; }
			else if($style == 'blue') { $StyleCode = 'ConsoleLogFontBlue'; }
			else if($style) { $StyleCode = 'ConsoleLogUserStyle_'.$uniqid; }

			echo "<script>";
			if($StyleCode == 'ConsoleLogUserStyle_'.$uniqid) echo 'var ConsoleLogUserStyle_'.$uniqid.' = "'.$style.'"; '.PHP_EOL;
			if($style) {

				$LogHeader = "%c";
				$LogTail = ", $StyleCode";
			}

			$output = explode("\r\n", $Data);
			if(is_array($Data)) {

				$lines = json_encode($Data);
				echo "console.table({$lines});".PHP_EOL;
			}
			else if(trim($output)) {

				foreach($output as $line) {
					$line = addslashes($line);
					echo "log('{$LogHeader}{$line}'{$LogTail}); ".PHP_EOL;
				}
			}
			echo "</script>".PHP_EOL;

			$tb_console2 = false;
		}


	/* --------------------------------------------------------------- */
	// 바이러니타입 이미지를 실 이미지로 변환
		public function binary2images($TempSavePath, $SavePath, $SaveName, $BinaryData, $output='') {

			if(is_file($SavePath)) {

				$file = $SavePath;
				$return = @getimagesize($file);
				$return['path'] = $SavePath;
				$return['url'] = str_replace(G5_PATH, G5_URL, $return['path']);
				return $return;
			}
			else {

				$base = $BinaryData;
				$base = str_replace("\\n", "\n", $base);
				$TempName =  md5(time()).time();
				$Images = $TempSavePath.'/'.$TempName;
				$binary=base64_decode($base);
				Header('Content-type:image/jpeg');
				$file = fopen($Images, 'wb');
				fwrite($file, $binary);
				fclose($file);
				unset($base);
				unset($binary);
				unset($file);
				header("Content-Type: text/html; charset=UTF-8");
				$SaveImages = $SavePath;

				@rename($Images, $SaveImages.'/'.$SaveName);

				$return = @getimagesize($SaveImages);
				$return['path'] = $SavePath.$imageTypeArray[$return[2]];
				$return['url'] = str_replace(G5_PATH, G5_URL, $return['path']);
				$return['name'] = $SaveName;
				return $return ;
			}
		}


	/* --------------------------------------------------------------- */
	// 주소->좌표, 좌표->주소 (https://developers.google.com/maps/documentation/geocoding/?hl=ko)
	/*
	$tb->Geo('서울 송파', 'address', 'xml');
	$tb->Geo('37.5145437,127.1065971', 'geo', 'xml');
	
	$tb->Geo('서울 송파', 'address', 'json');
	$tb->Geo('37.5145437,127.1065971', 'geo', 'json');
	*/
		public function Geo($Data, $Type='address', $Output='xml') {

			$num = 1;
			$return = array();
			$FullAddress = array();
			$FullAddressCount = array();
			$BaseUrl = 'http://maps.googleapis.com/maps/api/geocode/'.$Output.'?language=ko&sensor=false';


			if($Type == 'address') $response = file_get_contents($BaseUrl.'&address='.urlencode($Data));
			else $response = file_get_contents($BaseUrl.'&latlng='.$Data);


			if($Output == 'xml') {

				$obj = $this->xml2array($response);

				if($obj['GeocodeResponse']['result']['0']) $obj = $obj['GeocodeResponse']['result']['0'];
				else $obj = $obj['GeocodeResponse']['result'];

				$return = array();
				$return['address'] = $obj['formatted_address'];

				$FullAddress = explode(' ', $return['address']);
				$FullAddressCount = count($FullAddress);
				for($s=0; $s<$FullAddressCount; $s++) {

					$num = $s+1;
					$return['addr_'.$num]   = $FullAddress[$s];
				}
				/*
				문제되는 경우가 많음
				$return['addr1']   = $obj['address_component'][0]['long_name'];
				$return['addr2']   = $obj['address_component'][1]['long_name'];
				$return['addr3']   = $obj['address_component'][2]['long_name'];
				*/
				$return['lat']   = $obj['geometry']['location']['lat'];
				$return['lng']   = $obj['geometry']['location']['lng'];
			}
			else {

				$obj = json_decode($response);
				$obj = $obj->{'results'}[0];

				$return = array();
				$return['address'] = $obj->{'formatted_address'};

				$FullAddress = explode(' ', $return['address']);
				$FullAddressCount = count($FullAddress);
				for($s=0; $s<$FullAddressCount; $s++) {

					$num = $s+1;
					$return['addr_'.$num]   = $FullAddress[$s];
				}
				/*
				문제되는 경우가 많음
				$return['addr1']   = $obj->{'address_components'}[0]->{'long_name'};
				$return['addr2']   = $obj->{'address_components'}[1]->{'long_name'};
				$return['addr3']   = $obj->{'address_components'}[2]->{'long_name'};
				*/
				$return['lat']   = $obj->{'geometry'}->{'location'}->{'lat'};
				$return['lng']   = $obj->{'geometry'}->{'location'}->{'lng'};
			}

			return $return;
		}


	/* --------------------------------------------------------------- */
	// 구글 서버를 이용한 주소 리스트 받기
	/*
	$tb->Address('동/읍/면 입력');
	*/
		public function Address($Data='') {

			$i = 0;
			$num = 1;
			$return = array();
			$FullAddress = array();
			$FullAddressCount = array();

			$BaseUrl = 'http://maps.googleapis.com/maps/api/geocode/xml?language=ko&sensor=false';

			$response = file_get_contents($BaseUrl.'&address='.urlencode($Data));
			$obj = $this->xml2array($response);

			if(!$obj['GeocodeResponse']['result'][0]) $obj = array($obj['GeocodeResponse']['result']);
			else $obj = $obj['GeocodeResponse']['result'];

			foreach($obj as $val) {

				$return[$i]['address'] = $obj[$i]['formatted_address'];

				$FullAddress[$i] = explode(' ', $return[$i]['address']);
				$FullAddressCount[$i] = count($FullAddress[$i]);
				for($s=0; $s<$FullAddressCount[$i]; $s++) {

					$num = $s+1;
					$return[$i]['addr_'.$num]   = $FullAddress[$i][$s];
				}

				/*
				문제가 되는 경우가 많음
				$return[$i]['addr1']   = $obj[$i]['address_component'][0]['long_name'];
				$return[$i]['addr2']   = $obj[$i]['address_component'][1]['long_name'];
				$return[$i]['addr3']   = $obj[$i]['address_component'][2]['long_name'];
				*/
				$return[$i]['lat']   = $obj[$i]['geometry']['location']['lat'];
				$return[$i]['lng']   = $obj[$i]['geometry']['location']['lng'];

				$i++;
			}

			return $return;
		}


	/* --------------------------------------------------------------- */
	// 랜덤 html 색상 코드 (http://stackoverflow.com/a/20218712)
		public function RandColor() {

			$str = '';

			for ($i = 0; $i < 6; $i++) {

				$randNum = rand(0, 15);

				switch ($randNum) {

					case 10: $randNum = 'A';
						break;
					case 11: $randNum = 'B';
						break;
					case 12: $randNum = 'C';
						break;
					case 13: $randNum = 'D';
						break;
					case 14: $randNum = 'E';
						break;
					case 15: $randNum = 'F';
						break;
				}

				$str .= $randNum;
			}
			return $str;
		}


	/* --------------------------------------------------------------- */
	// 2차원 배열 키값 정렬(http://php.net/manual/en/function.sort.php#94663)
		public function array_key_sort($array, $key, $order='asc') { 

			$tmp = array();
			$tmp2 = array();

			foreach($array as $akey => $array2) { $tmp[$akey] = $array2[$key]; }

			if($order == "desc") arsort($tmp , SORT_NUMERIC);
			else asort($tmp , SORT_NUMERIC);

			foreach($tmp as $key => $value) { $tmp2[$key] = $array[$key]; }

			return $tmp2;
		}


	/* --------------------------------------------------------------- */
	// 디바이스 정보 (스텍오버플로우)
		public function device_info() {

			$Info = array();
			$agent = $_SERVER['HTTP_USER_AGENT'];

			$Info['os'] = 'Unknown';
			$Info['browser'] = array();
			$Info['browser']['base_name'] = 'Unknown';
			$Info['browser']['name'] = 'Unknown';

			// 플랫 폼 확인
			if(preg_match('`Android|Apache-HttpClient/UNAVAILABLE \(java`i', $agent)) $Info['os'] = 'android';

			else if(preg_match('`iPod|iPhone|iso`i', $agent)) $Info['os'] = 'ios';
			else if(preg_match('`BlackBerry`i', $agent)) $Info['os'] = 'blackberry';
			else if(preg_match('`SymbianOS`i', $agent)) $Info['os'] = 'symbianos';
			else if(preg_match('`Windows CE`i', $agent)) $Info['os'] = 'windows ce';
			else if(preg_match('`webOS`i', $agent)) $Info['os'] = 'webos';
			else if(preg_match('`PalmOS`i', $agent)) $Info['os'] = 'palmos';


			else if(preg_match('`windows|win32`i', $agent)) $Info['os'] = 'window';
			else if(preg_match('`macintosh|mac os x`i', $agent)) $Info['os'] = 'mac';
			else if(preg_match('`linux`i', $agent)) $Info['os'] = 'linux';

			// 브라우져 확인
			if(preg_match('`MSIE`i',$agent) && !preg_match('`Opera`i',$agent)) {

				$Info['browser']['base_name'] = 'Internet Explorer';
				$Info['browser']['name'] = "MSIE";
			} 
			else if(preg_match('`Firefox`i',$agent)) {

				$Info['browser']['base_name'] = 'Mozilla Firefox';
				$Info['browser']['name'] = "Firefox";
			}
			else if(preg_match('`Chrome`i',$agent)) {
				
				$Info['browser']['base_name'] = 'Google Chrome';
				$Info['browser']['name'] = "Chrome";
			}
			else if(preg_match('`Safari`i',$agent)) {
				
				$Info['browser']['base_name'] = 'Apple Safari';
				$Info['browser']['name'] = "Safari";
			}
			else if(preg_match('`Opera`i',$agent)) {
				
				$Info['browser']['base_name'] = 'Opera';
				$Info['browser']['name'] = "Opera";
			}
			else if(preg_match('`Netscape`i',$agent)) {

				$Info['browser']['base_name'] = 'Netscape';
				$Info['browser']['name'] = "Netscape";
			}

			return $Info;
		}


	/* --------------------------------------------------------------- */
	// 이름의 초성을 출력 한다. 
	// http://jmnote.com/wiki/UTF-8_%ED%95%9C%EA%B8%80_%EC%B4%88%EC%84%B1,_%EC%A4%91%EC%84%B1,_%EC%A2%85%EC%84%B1_%EB%B6%84%EB%A6%AC_(PHP)
	private function utf8_strlen($str) { return mb_strlen($str, 'UTF-8'); }
	private function utf8_charAt($str, $num) { return mb_substr($str, $num, 1, 'UTF-8'); }
	private function utf8_ord($ch) {

		$len = strlen($ch);
		if($len <= 0) return false;
		$h = ord($ch{0});
		if($h <= 0x7F) return $h;
		if($h < 0xC2) return false;
		if($h <= 0xDF && $len>1) return ($h & 0x1F) <<  6 | (ord($ch{1}) & 0x3F);
		if($h <= 0xEF && $len>2) return ($h & 0x0F) << 12 | (ord($ch{1}) & 0x3F) << 6 | (ord($ch{2}) & 0x3F);          
		if ($h <= 0xF4 && $len>3) return ($h & 0x0F) << 18 | (ord($ch{1}) & 0x3F) << 12 | (ord($ch{2}) & 0x3F) << 6 | (ord($ch{3}) & 0x3F);

		return false;
	}

	public function name_linear($str) {

		$cho = array('ㄱ','ㄲ','ㄴ','ㄷ','ㄸ','ㄹ','ㅁ','ㅂ','ㅃ','ㅅ','ㅆ','ㅇ','ㅈ','ㅉ','ㅊ','ㅋ','ㅌ','ㅍ','ㅎ');
		$jung = array('ㅏ','ㅐ','ㅑ','ㅒ','ㅓ','ㅔ','ㅕ','ㅖ','ㅗ','ㅘ','ㅙ','ㅚ','ㅛ','ㅜ','ㅝ','ㅞ','ㅟ','ㅠ','ㅡ','ㅢ','ㅣ');
		$jong = array('','ㄱ','ㄲ','ㄳ','ㄴ','ㄵ','ㄶ','ㄷ','ㄹ','ㄺ','ㄻ','ㄼ','ㄽ','ㄾ','ㄿ','ㅀ','ㅁ','ㅂ','ㅄ','ㅅ','ㅆ','ㅇ','ㅈ','ㅊ','ㅋ',' ㅌ','ㅍ','ㅎ');
		$result = '';

		for ($i=0; $i<$this->utf8_strlen($str); $i++) {

			$code = $this->utf8_ord($this->utf8_charAt($str, $i)) - 44032;
			if ($code > -1 && $code < 11172) {
				
				$cho_idx = $code / 588;      
				$jung_idx = $code % 588 / 28;  
				$jong_idx = $code % 28;
				$result .= $cho[$cho_idx];
			}
		}

		return $result;
	}


	/* --------------------------------------------------------------- */
	// 양력->음력 변환(http://cricri4289.blogspot.kr/2013/11/php.html)
	/**
	 * 음->양
	 * $sola_date = LunarToSola('2015-01-01');
	 * date("Y-m-d", $sola_date['time']);
	 *
	 * 
	 * 양->음
	 * $lunar_date = SolaToLunar(date('Ymd'));
	 * date("Y-m-d", $lunar_date['time']);
	**/
	public function sunlunar_data() {
		return "1212122322121-1212121221220-1121121222120-2112132122122-2112112121220-2121211212120-2212321121212-2122121121210-2122121212120-1232122121212-1212121221220-1121123221222-1121121212220-1212112121220-2121231212121-2221211212120-1221212121210-2123221212121-2121212212120-1211212232212-1211212122210-2121121212220-1212132112212-2212112112210-2212211212120-1221412121212-1212122121210-2112212122120-1231212122212-1211212122210-2121123122122-2121121122120-2212112112120-2212231212112-2122121212120-1212122121210-2132122122121-2112121222120-1211212322122-1211211221220-2121121121220-2122132112122-1221212121120-2121221212110-2122321221212-1121212212210-2112121221220-1231211221222-1211211212220-1221123121221-2221121121210-2221212112120-1221241212112-1212212212120-1121212212210-2114121212221-2112112122210-2211211412212-2211211212120-2212121121210-2212214112121-2122122121120-1212122122120-1121412122122-1121121222120-2112112122120-2231211212122-2121211212120-2212121321212-2122121121210-2122121212120-1212142121212-1211221221220-1121121221220-2114112121222-1212112121220-2121211232122-1221211212120-1221212121210-2121223212121-2121212212120-1211212212210-2121321212221-2121121212220-1212112112210-2223211211221-2212211212120-1221212321212-1212122121210-2112212122120-1211232122212-1211212122210-2121121122210-2212312112212-2212112112120-2212121232112-2122121212110-2212122121210-2112124122121-2112121221220-1211211221220-2121321122122-2121121121220-2122112112322-1221212112120-1221221212110-2122123221212-1121212212210-2112121221220-1211231212222-1211211212220-1221121121220-1223212112121-2221212112120-1221221232112-1212212122120-1121212212210-2112132212221-2112112122210-2211211212210-2221321121212-2212121121210-2212212112120-1232212122112-1212122122120-1121212322122-1121121222120-2112112122120-2211231212122-2121211212120-2122121121210-2124212112121-2122121212120-1212121223212-1211212221220-1121121221220-2112132121222-1212112121220-2121211212120-2122321121212-1221212121210-2121221212120-1232121221212-1211212212210-2121123212221-2121121212220-1212112112220-1221231211221-2212211211220-1212212121210-2123212212121-2112122122120-1211212322212-1211212122210-2121121122120-2212114112122-2212112112120-2212121211210-2212232121211-2122122121210-2112122122120-1231212122212-1211211221220-2121121321222-2121121121220-2122112112120-2122141211212-1221221212110-2121221221210-2114121221221";
	}
	function LunarToSola($date) {

		$getYEAR = date('Y', strtotime($date));
		$getMONTH = date('m', strtotime($date));
		$getDAY = date('d', strtotime($date));

		$arrayDATASTR = sunlunar_data();
		$arrayDATA = explode("-",$arrayDATASTR);

		$arrayLDAYSTR="31-0-31-30-31-30-31-31-30-31-30-31";
		$arrayLDAY = explode("-",$arrayLDAYSTR);

		$arrayYUKSTR="갑-을-병-정-무-기-경-신-임-계";
		$arrayYUK = explode("-",$arrayYUKSTR);

		$arrayGAPSTR="자-축-인-묘-진-사-오-미-신-유-술-해";
		$arrayGAP = explode("-",$arrayGAPSTR);

		$arrayDDISTR="쥐-소-호랑이-토끼-용-뱀-말-양-원숭이-닭-개-돼지";
		$arrayDDI = explode("-",$arrayDDISTR);

		$arrayWEEKSTR="일-월-화-수-목-금-토";
		$arrayWEEK = explode("-",$arrayWEEKSTR);

		if ($getYEAR <= 1881 || $getYEAR >= 2050) { //년수가 해당일자를 넘는 경우
			$YunMonthFlag = 0;
			return false; //년도 범위가 벗어남..
		}
		if ($getMONTH > 12) { // 달수가 13이 넘는 경우
			$YunMonthFlag = 0;
			return false; //달수 범위가 벗어남..
		}

		$m1 = $getYEAR - 1881;

		if (substr($arrayDATA[$m1],12,1) == 0) { // 윤달이 없는 해임
		$YunMonthFlag = 0;
		} else {
			if (substr($arrayDATA[$m1],$getMONTH, 1) > 2) {
				$YunMonthFlag = 1;
			} else {
				$YunMonthFlag = 0;
			}
		}

		$m1 = -1;
		$td = 0;

		if ($getYEAR > 1881 && $getYEAR < 2050) {
			$m1 = $getYEAR - 1882;
			for ($i=0;$i<=$m1;$i++) {
				for ($j=0;$j<=12;$j++) {
					$td = $td + (substr($arrayDATA[$i],$j,1));
				}

				if (substr($arrayDATA[$i],12,1) == 0) {
					$td = $td + 336;
				} else {
					$td = $td + 362;
				}
			}
		} else {
			$gf_lun2sol = 0;
		}

		$m1++;
		$n2 = $getMONTH - 1;
		$m2 = -1;

		while(1) {
			$m2++;
			if (substr($arrayDATA[$m1], $m2, 1) > 2) {
				$td = $td + 26 + (substr($arrayDATA[$m1], $m2, 1));
				$n2++;
			} else {
				if ($m2 == $n2) {
					if ($gf_yun) {
						$td = $td + 28 + (substr($arrayDATA[$m1], $m2, 1));
					}
				break;

				} else {
					$td = $td + 28 + (substr($arrayDATA[$m1], $m2, 1));
				}
			}
		}

		$td = $td + $getDAY + 29;
		$m1 = 1880;

		while(1) {
			$m1++;
			if ($m1 % 400 == 0 || $m1 % 100 != 0 && $m1 % 4 == 0) {
				$leap = 1;
			} else {
				$leap = 0;
			}

			if ($leap == 1) {
				$m2 = 366;
			} else {
				$m2 = 365;
			}

			if ($td < $m2) break;

			$td = $td - $m2;
		}

		$syear = $m1;
		$arrayLDAY[1] = $m2 - 337;

		$m1 = 0;

		while(1) {
			$m1++;
			if ($td <= $arrayLDAY[$m1-1]) {
				break;
			}
			$td = $td - $arrayLDAY[$m1-1];
		}
		$smonth = $m1;
		$sday = $td;
		$y = $syear - 1;
		$td = intval($y*365) + intval($y/4) - intval($y/100) + intval($y/400);

		if ($syear % 400 == 0 || $syear % 100 != 0 && $syear % 4 == 0) {
			$leap = 1;
		} else {
			$leap = 0;
		}

		if ($leap == 1) {
			$arrayLDAY[1] = 29;
		} else {
			$arrayLDAY[1] = 28;
		}

		for ($i=0;$i<=$smonth-2;$i++) {
			$td = $td + $arrayLDAY[$i];
		}

		$td = $td + $sday;
		$w = $td % 7;

		$sweek = $arrayWEEK[$w];
		$gf_lun2sol = 1;

		if($smonth<10) $smonth="0".$smonth;
		if($sday<10) $sday="0".$sday;

		$Ary[year]=$syear;
		$Ary[month]=$smonth;
		$Ary[day]=$sday;
		$Ary[time]=mktime(0,0,0,$Ary[month],$Ary[day],$Ary[year]);
		return $Ary;
	}
	public function SolaToLunar($date) {

		$getYEAR = date('Y', strtotime($date));
		$getMONTH = date('m', strtotime($date));
		$getDAY = date('d', strtotime($date));

		$arrayDATASTR = $this->sunlunar_data();
		$arrayDATA = explode("-",$arrayDATASTR);
		$arrayLDAYSTR="31-0-31-30-31-30-31-31-30-31-30-31";
		$arrayLDAY = explode("-",$arrayLDAYSTR);
		$dt = $arrayDATA;



		for ($i=0;$i<=168;$i++) {

			$dt[$i] = 0;
			for ($j=0;$j<12;$j++) {

				switch (substr($arrayDATA[$i],$j,1)) {
					case 1:
						$dt[$i] += 29;
					break;
					case 3:
						$dt[$i] += 29;
					break;
					case 2:
						$dt[$i] += 30;
					break;
					case 4:
						$dt[$i] += 30;
					break;
				}
			}

			switch (substr($arrayDATA[$i],12,1)) {
				case 0:
				break;
				case 1:
					$dt[$i] += 29;
				break;
				case 3:
					$dt[$i] += 29;
				break;
				case 2:
				$dt[$i] += 30;
					break;
				case 4:
					$dt[$i] += 30;
				break;
			}
		}

		$td1 = 1880 * 365 + (int)(1880/4) - (int)(1880/100) + (int)(1880/400) + 30;
		$k11 = $getYEAR - 1;
		$td2 = $k11 * 365 + (int)($k11/4) - (int)($k11/100) + (int)($k11/400);

		if($getYEAR % 400 == 0 || $getYEAR % 100 != 0 && $getYEAR % 4 == 0) $arrayLDAY[1] = 29;
		else $arrayLDAY[1] = 28;

		if($getMONTH > 13)  $gf_sol2lun = 0;
		if($getDAY > $arrayLDAY[$getMONTH-1]) $gf_sol2lun = 0;

		for($i=0;$i<=$getMONTH-2;$i++) { $td2 += $arrayLDAY[$i]; }

		$td2 += $getDAY;
		$td = $td2 - $td1 + 1;
		$td0 = $dt[0];

		for($i=0;$i<=168;$i++) {
			if($td <= $td0) break;
			$td0 += $dt[$i+1];
		}

		$ryear = $i + 1881;
		$td0 -= $dt[$i];
		$td -= $td0;

		if(substr($arrayDATA[$i], 12, 1) == 0) $jcount = 11;
		else $jcount = 12;

		$m2 = 0;

		// 달수 check, 윤달 > 2 (by harcoon)
		for($j=0;$j<=$jcount;$j++) {

			if(substr($arrayDATA[$i],$j,1) <= 2) {
				$m2++;
				$m1 = substr($arrayDATA[$i],$j,1) + 28;
				$gf_yun = 0;
			}
			else {

				$m1 = substr($arrayDATA[$i],$j,1) + 26;
				$gf_yun = 1;
			}
			if($td <= $m1) break;
			$td = $td - $m1;
		}

		$k1=($ryear+6) % 10;
		$syuk = $arrayYUK[$k1];
		$k2=($ryear+8) % 12;
		$sgap = $arrayGAP[$k2];
		$sddi = $arrayDDI[$k2];
		$gf_sol2lun = 1;

		if($m2<10) $m2="0".$m2;
		if($sday<10) $td="0".$td;

		$Ary['year']=$ryear;
		$Ary['month']=$m2;
		$Ary['day']=$td;
		$Ary['time']=mktime(0,0,0,$Ary['month'],$Ary['day'],$Ary['year']);

		return $Ary;
	}
}
?>