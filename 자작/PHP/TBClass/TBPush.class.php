<?php
class TBPush {

	var $config = array();

	# 설정
	public function set($type='', $key='') {

		$this->config[$type] = $key;
	}

	# 설정 출력
	public function config() {

		return $this->config;
	}

	# 메시지 전송
	public function send($device=array(), $subject='', $message='', $option=array()) {

		if(is_array($device['gcm'])) $this->GCM($device['gcm'], $subject, $message, $option);
		if(is_array($device['apns'])) $this->APNS($device['apns'], $subject, $message, $option);
	}

	# GCM 발송
	private function GCM($device, $subject='', $message='', $option=array()) {

		// 초기 값
		$data = array();

		// 통신 URL
		$url = 'http://android.googleapis.com/gcm/send';

		// 클래스 설정값 변수화
		$config = $this->config;

		// 조건확인
		if(!trim($config['gcm'])) $this->error('[gcm] 설정 값 없음');
		if(!trim($message)) $this->error('[gcm] 전달할 메시지가 없습니다.');
		if(!is_array($device) || count($device) == 0) $this->error('[gcm] DEVICE 설정 값 오류');

		// 전달내용 조합
		$data['registration_ids'] = $device;
		if(trim($subject)) $data['data']['subject'] = $subject;
		$data['data']['message'] = $message;

		// 통신 해더 생성
		$headers = array(
			'Authorization: key=' . $config['gcm'],
			'Content-Type: application/json'
		);

		// 통신 처리
		$ch = curl_init();
		curl_setopt( $ch, CURLOPT_URL, $url);
		curl_setopt( $ch, CURLOPT_POST, true);
		curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode($data));
		$result = curl_exec($ch);
		curl_close($ch);

		// 결과 반환
		return '[gcm] '.$result;
	}

	# APNS 발송 (http://qnibus.com/blog/how-to-develop-service-provider/#comment-512 참조)
	private function APNS($device, $subject='', $message='', $option=array()) {

		// 옵션값 변수화
		/*
		// https://developer.apple.com/library/ios/documentation/NetworkingInternet/Conceptual/RemoteNotificationsPG/Chapters/ApplePushService.html#//apple_ref/doc/uid/TP40008194-CH100-SW9
		*/
		$mode  = $option['mode'];
		$badge = $option['badge']?(int)$option['badge']:1; // 지정뱃지
		$sound = $option['sound']; // 지정 사운드
		$acme1 = $option['acme1']; // 커스텀 변수1
		$acme2 = $option['acme2']; //커스텀 변수2

		// 통신 URL
		if($mode == 'test') $url = 'ssl://gateway.sandbox.push.apple.com:2195';
		else $url = 'ssl://gateway.push.apple.com:2195';

		// 클래스 설정값 변수화
		$config = $this->config;

		// 조건확인
		if(!is_file($config['apns'])) $this->error('[apns] 인증서 파일이 존재하지 않습니다.');
		if(!is_array($device) || count($device) == 0) $this->error('[apns] DEVICE 설정 값 오류');
		if(!trim($message)) $this->error('[apns] 전달할 메시지가 없습니다.');

		// 설정값
		$result = '';
		$data = array();
		$data['aps'] = array();
		$data['aps']['arert'] = array();
		$data['aps']['arert']['body'] = $message;
		$data['aps']['badge'] = $badge;
		if($sound) $data['aps']['sound'] = $sound;
		if($acme1) $data['acme1'] = $acme1;
		if($acme2) $data['acme2'] = $acme2;

		// 디바이스(토큰) 수 만큼 전송
		for($i=0; $i<count($device); $i++) {

			$msg = '';
			$ctx = stream_context_create();
			stream_context_set_option($ctx, 'ssl', 'local_cert', $config['apns']);
			$fp = stream_socket_client($url, $err, $errstr, 60, STREAM_CLIENT_CONNECT, $ctx);
			if(!$fp) $this->error("[apns] Failed to connect $err $errstr\n");
			$payload = json_encode($data); 
			$msg = chr(0) . pack("n",32) . pack('H*', str_replace(' ', '', $device[$i])) . pack("n",strlen($payload)) . $payload;
			$result .= "[apsn] Sending message :" . $payload . "\n";
			fwrite($fp, $msg);
			fclose($fp);
		}

		return $result;
	}

	public function error($msg){

		echo "ERROR:";
		echo "\t" . $msg;
	}
}