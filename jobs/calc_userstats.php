<?PHP
function calc_userstats($ts3,$mysqlcon,$dbname,$slowmode,$timezone,$logpath,$select_arr) {
	$sqlexec = '';
	$max_timestamp = key($select_arr['max_timestamp_user_snapshot']);
	$min_timestamp_week = key($select_arr['usersnap_min_week']);
	$min_timestamp_month = key($select_arr['usersnap_min_month']);

	$job_begin = $select_arr['job_check']['calc_user_limit']['timestamp'];
	$job_end = ceil(count($select_arr['all_user']) / 10) * 10;
	if ($job_begin >= $job_end) {
		$job_begin = 0;
		$job_end = 10;
	} else {
		$job_end = $job_begin + 10;
	}

	$sqlhis = array_slice($select_arr['all_user'],$job_begin ,10);
	
	$uuids = '';
	foreach ($sqlhis as $uuid => $userstats) {
		$uuids .= "'".$uuid."',";
	}
	$uuids = substr($uuids, 0, -1);

	if(isset($sqlhis) && $max_timestamp != NULL && $min_timestamp_week != NULL && $min_timestamp_month != NULL) {
		//enter_logfile($logpath,$timezone,6,"Update User Stats between ".$job_begin." and ".$job_end.":");
		if(($userdataweekbegin = $mysqlcon->query("SELECT uuid,count,idle FROM $dbname.user_snapshot WHERE timestamp=$min_timestamp_week AND uuid IN ($uuids)")->fetchAll(PDO::FETCH_ASSOC|PDO::FETCH_UNIQUE)) === false) {
			enter_logfile($logpath,$timezone,2,"calc_userstats 6:".print_r($mysqlcon->errorInfo(), true));
		}
		if(($userdatamonthbegin = $mysqlcon->query("SELECT uuid,count,idle FROM $dbname.user_snapshot WHERE timestamp=$min_timestamp_month AND uuid IN ($uuids)")->fetchAll(PDO::FETCH_ASSOC|PDO::FETCH_UNIQUE)) === false) {
			enter_logfile($logpath,$timezone,2,"calc_userstats 8:".print_r($mysqlcon->errorInfo(), true));
		}
		if(($userdataend = $mysqlcon->query("SELECT uuid,count,idle FROM $dbname.user_snapshot WHERE timestamp=$max_timestamp AND uuid IN ($uuids)")->fetchAll(PDO::FETCH_ASSOC|PDO::FETCH_UNIQUE)) === false) {
			enter_logfile($logpath,$timezone,2,"calc_userstats 7:".print_r($mysqlcon->errorInfo(), true));
		}

		$allupdateuuid = $allupdaterank = $allupdatecountw = $allupdatecountm = $allupdateidlew = $allupdateidlem = $allupdateactw = $allupdateactm = $allupdatetotac = $allupdatebase64 = $allupdatecldtup = $allupdatecldtdo = $allupdateclddes = $allinsertuserstats = '';
		
		foreach ($sqlhis as $uuid => $userstats) {
			check_shutdown($timezone,$logpath); usleep($slowmode);
			try {
				$clientinfo = $ts3->clientInfoDb($userstats['cldbid']);

				if(isset($userdataend[$uuid]) && isset($userdataweekbegin[$uuid])) {
					$count_week = $userdataend[$uuid]['count'] - $userdataweekbegin[$uuid]['count'];
					$idle_week = $userdataend[$uuid]['idle'] - $userdataweekbegin[$uuid]['idle'];
					$active_week = $count_week - $idle_week;
				} else {
					$count_week = 0;
					$idle_week = 0;
					$active_week = 0;
				}
				if(isset($userdataend[$uuid]) && isset($userdatamonthbegin[$uuid])) {
					$count_month = $userdataend[$uuid]['count'] - $userdatamonthbegin[$uuid]['count'];
					$idle_month = $userdataend[$uuid]['idle'] - $userdatamonthbegin[$uuid]['idle'];
					$active_month = $count_month - $idle_month;
				} else {
					$count_month = 0;
					$idle_month = 0;
					$active_month = 0;
				}
				$clientdesc = $mysqlcon->quote($clientinfo['client_description'], ENT_QUOTES);;
				if(isset($select_arr['uuid_stats_user'][$uuid])) {
					$allupdateuuid = $allupdateuuid . "'" . $uuid . "',";
					$allupdaterank = $allupdaterank . "WHEN '" . $uuid . "' THEN '" . $userstats['rank'] . "' ";
					$allupdatecountw = $allupdatecountw . "WHEN '" . $uuid . "' THEN '" . $count_week . "' ";
					$allupdatecountm = $allupdatecountm . "WHEN '" . $uuid . "' THEN '" . $count_month . "' ";
					$allupdateidlew = $allupdateidlew . "WHEN '" . $uuid . "' THEN '" . $idle_week . "' ";
					$allupdateidlem = $allupdateidlem . "WHEN '" . $uuid . "' THEN '" . $idle_month . "' ";
					$allupdateactw = $allupdateactw . "WHEN '" . $uuid . "' THEN '" . $active_week . "' ";
					$allupdateactm = $allupdateactm . "WHEN '" . $uuid . "' THEN '" . $active_month . "' ";
					$allupdatetotac = $allupdatetotac . "WHEN '" . $uuid . "' THEN '" . $clientinfo['client_totalconnections'] . "' ";
					$allupdatebase64 = $allupdatebase64 . "WHEN '" . $uuid . "' THEN '" . $clientinfo['client_base64HashClientUID'] . "' ";
					$allupdatecldtup = $allupdatecldtup . "WHEN '" . $uuid . "' THEN '" . $clientinfo['client_total_bytes_uploaded'] . "' ";
					$allupdatecldtdo = $allupdatecldtdo . "WHEN '" . $uuid . "' THEN '" . $clientinfo['client_total_bytes_downloaded'] . "' ";
					$allupdateclddes = $allupdateclddes . "WHEN '" . $uuid . "' THEN " . $clientdesc . " ";
				} else {
					$allinsertuserstats = $allinsertuserstats . "('" . $uuid . "', '" .$userstats['rank'] . "', '" . $count_week . "', '" . $count_month . "', '" . $idle_week . "', '" . $idle_month . "', '" . $active_week . "', '" . $active_month . "', '" . $clientinfo['client_totalconnections'] . "', '" . $clientinfo['client_base64HashClientUID'] . "', '" . $clientinfo['client_total_bytes_uploaded'] . "', '" . $clientinfo['client_total_bytes_downloaded'] . "', " . $clientdesc . "),";
				}
			} catch (Exception $e) {
				//enter_logfile($logpath,$timezone,6,$e->getCode() . ': ' . $e->getMessage()."; Client (uuid: ".$uuid." cldbid: ".$userstats['cldbid'].") was missing in TS database, perhaps its already deleted".);
			}
		}
		unset($sqlhis, $userdataweekbegin, $userdataend, $userdatamonthbegin);
		
		if ($allupdateuuid != '') {
			$allupdateuuid = substr($allupdateuuid, 0, -1);
			$sqlexec .= "UPDATE $dbname.job_check SET timestamp=$job_end WHERE job_name='calc_user_limit'; UPDATE $dbname.stats_user set rank = CASE uuid $allupdaterank END, count_week = CASE uuid $allupdatecountw END, count_month = CASE uuid $allupdatecountm END, idle_week = CASE uuid $allupdateidlew END, idle_month = CASE uuid $allupdateidlem END, active_week = CASE uuid $allupdateactw END, active_month = CASE uuid $allupdateactm END, total_connections = CASE uuid $allupdatetotac END, base64hash = CASE uuid $allupdatebase64 END, client_total_up = CASE uuid $allupdatecldtup END, client_total_down = CASE uuid $allupdatecldtdo END, client_description = CASE uuid $allupdateclddes END WHERE uuid IN ($allupdateuuid); ";
			unset($allupdateuuid, $allupdaterank, $allupdatecountw, $allupdatecountm, $allupdateidlew, $allupdateidlem, $allupdateactw, $allupdateactm, $allupdatetotac, $allupdatebase64, $allupdatecldtup, $allupdatecldtdo, $allupdateclddes);
		}

		if($allinsertuserstats != '') {
			$allinsertuserstats = substr($allinsertuserstats, 0, -1);
			$sqlexec .= "UPDATE $dbname.job_check SET timestamp=$job_end WHERE job_name='calc_user_limit'; INSERT INTO $dbname.stats_user (uuid,rank,count_week,count_month,idle_week,idle_month,active_week,active_month,total_connections,base64hash,client_total_up,client_total_down,client_description) VALUES $allinsertuserstats; ";
			unset($allinsertuserstats);
		}
	}
	return($sqlexec);
}
?>