<?php
function mcraftQuery_SE( $host, $port = 25565, $timeout = 2 ) {

    $fp = @fsockopen($host, $port, $errno, $errstr, $timeout);  			
	if(!$fp) return false;

	stream_set_timeout($fp, $timeout);
	
	fwrite($fp, "\xFE\x01");
	$data = fread($fp, 1024);
	fclose($fp);

	if(!$data or substr($data, 0, 1) != "\xFF") return false;
		
	$data = substr( $data, 3 );
	$data = iconv( 'UTF-16BE', 'UTF-8', $data );

	// ver 1.4 >
	if( $data[1] === "\xA7" && $data[2] === "\x31" ) {
			
		$data = explode( "\x00", $data );

		return Array(
			'hostname'   => $data[3 ],
			'numpl'    => (int)$data[4],
			'maxplayers' => (int)$data[5],
			'protocol'   => (int)$data[1],
			'version'    => $data[2]
		);
	}
	
	$data = explode("\xA7", $data );
		
	return Array(
		'hostname'   => substr( $data[0], 0, -1 ),
		'numpl'      => isset( $data[1] ) ? (int)$data[1] : 0,
		'maxplayers' => isset( $data[2] ) ? (int)$data[2] : 0
	);
}

function mcraftQuery($host, $port = 25565, $timeout = 1) {

	$fp = @fsockopen("udp://" .$host, $port, $errno, $errstr, $timeout);
	if(!$fp) return false;

	stream_set_timeout($fp, $timeout);	
	
	$str1 = "\xFE\xFD\x09\x01\x02\x03\x04";	// Arbitrary session id at the end (4 bytes)
	fwrite($fp, $str1);
	$resp1 = fread($fp, 256);
	
	if(empty($resp1) or $resp1[0] != "\x09")	// Check for a valid response
		return false;

	// Parse the challenge token from string to integer
	$token = 0;
	for($i = 5; $i < (strlen($resp1) - 1); $i++)
	{
		$token *= 10;
		$token += $resp1[$i];
	}

	// Divide the int32 into 4 bytes
	$token_arr = array(	0 => ($token / (256*256*256)) % 256,
				1 => ($token / (256*256)) % 256,
				2 => ($token / 256) % 256,
				3 => ($token % 256)
			);

	// Get the full version of server status. ID and challenge tokens appended to command 0x00, payload padded to 8 bytes.
	$str = "\xFE\xFD\x00\x01\x02\x03\x04"
		. chr($token_arr[0]) . chr($token_arr[1]) . chr($token_arr[2]) . chr($token_arr[3])
		. "\x00\x00\x00\x00";
	fwrite($fp, $str);
	$data2 = fread($fp, 4096);
	$full_stat = substr($data2, 11);	// Strip the crap from the start

	$tmp = explode("\x00\x01player_\x00\x00", $full_stat);	// First, split the payload in two parts
	
	if (sizeof($tmp) < 2) return array('too_many' => true); // if ask server to much it return empty data
	
	$t = explode("\x00", $tmp[0]);		// Divide the first part from every NULL-terminated string end into key1 val1 key2 val2...
	unset($t[count($t) - 1]);		// Unset the last entry, because the are two 0x00 bytes at the end
	$t2 = explode("\x00", $tmp[1]);		// Explode the player information from the second part

	$info = array();
	for($i = 0; $i < count($t); $i += 2)
	{
		if($t[$i] == "")
			break;

		$info[$t[$i]] = $t[$i + 1];
	}

	$players = array();
	foreach($t2 as $one)
	{
		if($one == "")
			break;

		$players[] = $one;
	}

	$full_stat = $info;
	$full_stat['players'] = $players;

	return $full_stat;
}

?>