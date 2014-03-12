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
		public function GetQuery($SQL, $option='') {

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
	// Header 에러 출력
		public function SetErrorHeader($Number) {
		
			/*
			400 ㅡ 잘못된 요청
			403 ㅡ 사용자권한없음
			404 ㅡ 페이지 없음
			405 ㅡ 방식 허용 안함
			406 ㅡ 승인금지
			408 ㅡ 서버작업중
			500 ㅡ 내부서버 오류
			501 ㅡ 사용금지
			505 ㅡ 사용금지
			*/
			// 기본 에러 넘버 지정
			if(!$Number) $Number = '403';

			@header('HTTP/1.0 '.$Number);
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
	
}
?>