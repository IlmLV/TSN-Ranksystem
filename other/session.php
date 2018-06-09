<?PHP
if (isset($_POST['refresh'])) {
	$rspathhex = 'rs_'.dechex(crc32(__DIR__)).'_';
	rem_session_ts3($rspathhex);
}
function set_session_ts3($voiceport, $mysqlcon, $dbname, $language, $adminuuid) {
	if (!empty($_SERVER['HTTP_CLIENT_IP']))
		$hpclientip = $_SERVER['HTTP_CLIENT_IP'];
	elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
		$hpclientip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	elseif(!empty($_SERVER['HTTP_X_FORWARDED']))
		$hpclientip = $_SERVER['HTTP_X_FORWARDED'];
	elseif(!empty($_SERVER['HTTP_FORWARDED_FOR']))
		$hpclientip = $_SERVER['HTTP_FORWARDED_FOR'];
	elseif(!empty($_SERVER['HTTP_FORWARDED']))
		$hpclientip = $_SERVER['HTTP_FORWARDED'];
	elseif(!empty($_SERVER['REMOTE_ADDR']))
		$hpclientip = $_SERVER['REMOTE_ADDR'];
	else
		$hpclientip = 0;
	
	$hpclientip = inet_pton($hpclientip);
	$rspathhex = 'rs_'.dechex(crc32(__DIR__)).'_';
	
    $allclients = $mysqlcon->query("SELECT u.uuid,u.cldbid,u.name,u.ip,u.firstcon,s.total_connections FROM $dbname.user as u LEFT JOIN $dbname.stats_user as s ON u.uuid=s.uuid WHERE online='1';")->fetchAll();
    $_SESSION[$rspathhex.'connected'] = 0;
	$_SESSION[$rspathhex.'tsname'] = "verification needed!";
    $_SESSION[$rspathhex.'serverport'] = $voiceport;
    foreach ($allclients as $client) {
        if ($hpclientip == $client['ip']) {
			if(isset($_SESSION[$rspathhex.'uuid_verified']) && $_SESSION[$rspathhex.'uuid_verified'] != $client['uuid']) {
				continue;
			}
			$_SESSION[$rspathhex.'tsname'] = htmlspecialchars($client['name']);
			if(isset($_SESSION[$rspathhex.'tsuid']) && $_SESSION[$rspathhex.'tsuid'] != $client['uuid']) {
				$_SESSION[$rspathhex.'multiple'] .= htmlspecialchars($client['uuid']).'=>'.htmlspecialchars($client['name']).',';
				$_SESSION[$rspathhex.'tsname'] = "verification needed!";
				unset($_SESSION[$rspathhex.'admin']);
			} elseif (!isset($_SESSION[$rspathhex.'tsuid'])) {
				$_SESSION[$rspathhex.'multiple'] = htmlspecialchars($client['uuid']).'=>'.htmlspecialchars($client['name']).',';
			}
            $_SESSION[$rspathhex.'tsuid'] = $client['uuid'];
			foreach ($adminuuid as $auuid) {
				if ($_SESSION[$rspathhex.'tsuid'] == $auuid) {
					$_SESSION[$rspathhex.'admin'] = TRUE;
				}
			}
            $_SESSION[$rspathhex.'tscldbid'] = $client['cldbid'];
            if ($client['firstcon'] == 0) {
                $_SESSION[$rspathhex.'tscreated'] = "unkown";
            } else {
                $_SESSION[$rspathhex.'tscreated'] = date('d-m-Y', $client['firstcon']);
            }
            if ($client['total_connections'] != NULL) {
                $_SESSION[$rspathhex.'tsconnections'] = $client['total_connections'];
            } else {
                $_SESSION[$rspathhex.'tsconnections'] = 0;
            }
            $convert = array('a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p');
            $uuidasbase16 = '';
            for ($i = 0; $i < 20; $i++) {
                $char = ord(substr(base64_decode($_SESSION[$rspathhex.'tsuid']), $i, 1));
                $uuidasbase16 .= $convert[($char & 0xF0) >> 4];
                $uuidasbase16 .= $convert[$char & 0x0F];
            }
            if (is_file('../avatars/' . $uuidasbase16 . '.png')) {
                $_SESSION[$rspathhex.'tsavatar'] = $uuidasbase16 . '.png';
            } else {
                $_SESSION[$rspathhex.'tsavatar'] = "none";
            }
            $_SESSION[$rspathhex.'connected'] = 1;
            $_SESSION[$rspathhex.'language'] = $language;
        }
    }
}
?>