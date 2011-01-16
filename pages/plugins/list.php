<?php

	$template_settings=array();
	$params = array_slice($hr_URI, 1);
	
	$additional = '';
	if (User::$role == User::ROLE_ADMIN) {
		$additional = '-3, -2, -1, ';
	}
	$orderBy = 'pdv.vdate DESC';
	$limit = '10';
	$pageNum = '0';
	$wherr = array();
	$friendlyFields = array('lastupdate' => 'pdv.vdate', 'pluginname' => 'pl.pname', 'downloads' => 'pl.pdownloads', 'addeddate' => 'pl.padded_date', 'rating' => 'pl.prating', 'authorname' => 'XENFOROpl.authorid');
	$using = array();
	foreach ($params as $param) {
		// parameter structure:
		// order,field,asc/desc - non stackable
		// limit,number - non stackable
		// where,field,oper,value - stackable
		// page,number - non stackable
		$param = explode(',', $param);
		if ($param[0] != 'order' && $param[0] != 'limit' && $param[0] != 'where' && $param[0] != 'page') {
			continue; // ignore it
		}
		if ($param[0] == 'order' && count($param) != 3) continue;
		if ($param[0] == 'limit' && count($param) != 2) continue;
		if ($param[0] == 'page' && count($param) != 2) continue;
		if ($param[0] == 'where' && count($param) != 4) continue;
		if ($param[0] == 'where' || $param[0] == 'order') { // validate 2nd argument as field:
			if (!isset($friendlyFields[$param[1]])) continue;
			if ($param[0] == 'order' && $friendlyFields[$param[1]] == 'XENFORO') continue;
		}
		if ($param[0] == 'limit' || $param[0] == 'page') { // validate 2nd argument as number:
			if (!is_numeric($param[1])) continue;
			$param[1] = (int)$param[1];
		}
		if ($param[0] == 'order') { // validate 3rd argument as asc/desc
			if ($param[2] != 'asc' && $param[2] != 'desc') continue;
		}
		if ($param[0] == 'where') { // validate 3rd argument as operator
			if ($param[2] != '=' && $param[2] != '!=' &&
				$param[2] != '>' && $param[2] != '<' &&
				$param[2] != '>=' && $param[2] != '<=') {
				continue;
			}
		}
		
		if ($param[0] == 'where') {
			if ($param[1] != 'authorname') {
				$fieldName = $friendlyFields[$param[1]];
				$operator = $param[2];
				$value = Database::getHandle()->quote($param[3]);
				$wherr[] = $fieldName . ' ' . $operator . ' ' . $value;
			} else {
				$u = XenForo_Model::create('XenForo_Model_User');
				$userID = (int)$u->getUserIdFromUser($u->getUserByName($param[3]));
				$value = Database::getHandle()->quote($param[3]);
				if ($param[2] == '=') {
					$wherr[] = '(pl.pauthor_ID = ' . $userID . ' OR pl.pauthorname = '.$value.')';
				} else {
					$wherr[] = '(pl.pauthor_ID != ' . $userID . ')';
					$wherr[] = '(pl.pauthorname != '.$value . ' OR pl.pauthorname IS NULL)';
				}
			}
		} else if ($param[0] == 'order') {
			$fieldName = $friendlyFields[$param[1]];
			$direction = $param[2];
			$orderBy = $fieldName . ' ' . $direction;
		} else if ($param[0] == 'limit') {
			$limitTo = $param[1];
			if ($limitTo > 20) continue;
			$limit = $limitTo;
		} else if ($param[0] == 'page') {
			$pageNum = $param[1] - 1;
		}
		$using[] = $params;
	}
	
	$template_settings['HR_DEBUG_INFO'] = print_r($using, true).PHP_EOL.PHP_EOL.PHP_EOL.print_r($wherr,true);
	
	$where = '(1 = 1)';
	foreach ($wherr as $what) {
		$where .= ' AND (' . $what . ')';
	}
	if (strlen($where) > strlen('(1 = 1) AND ')) {
		$where = substr($where, strlen('(1 = 1) AND '));
	}
	$limit = ($pageNum * $limit) . ', ' . $limit;
	
	$pdo = Database::getHandle();
	$sql = "SELECT SQL_CALC_FOUND_ROWS pl.* FROM plugins AS pl LEFT JOIN plugin_downloads AS pd ON pl.pid = pd.pid LEFT JOIN plugin_downloads_version AS pdv ON pd.did = pdv.did WHERE ({$where}) AND pstatus IN ($additional 0, 1, 2)  GROUP BY pl.pid ORDER BY {$orderBy} LIMIT {$limit}";
	$template_settings['HR_DEBUG_INFO'] .= PHP_EOL.PHP_EOL.PHP_EOL.$sql;
	$countsql = 'SELECT FOUND_ROWS()';
	//$res = $pdo->prepare('SELECT pl.* FROM plugins AS pl LEFT JOIN plugin_downloads AS pd ON pl.pid = pd.pid LEFT JOIN plugin_downloads_versions AS pdv ON pd.did = pdv.did ORDER BY '.$orderBy . ' LIMIT '.$limit);
	$res = $pdo->prepare($sql);
	$res->execute();
	$countres = $pdo->prepare($countsql);
	$countres->execute();
	
	$template_settings['HR_RESULTS_NUM'] = $numRows = $countres->fetchColumn(0);
	
	//$resRows = $res->fetchAll();
	$u = XenForo_Model::create('XenForo_Model_User');
	
	$resRows = array();
	while ($resRow = $res->fetch()) {
		$thisu = $u->getUserById($resRow['pauthor_id']);
		$newRow = array(
			'author' => $thisu['username'],
			'id' => $resRow['pid'],
			'name' => $resRow['pname'],
			'desc' => $resRow['pdesc'],
			'showButton' => true,
			'buttonText' => 'More Info',
			'buttonURI' => '/detail/'.$thisu['username'].'/'.$resRow['pname'].'/',
			'lastUpdated' => ''
		);
		$resRows[] = $newRow;
	}
	$template_settings['HR_RESULTS_SHOWN'] = count($resRows);
	$template_settings['HR_RESULTS'] = $resRows;
	
	
	//$pluginID = $dbQuery->fetchColumn();
	
	// GOT!
	//$thisPlugin = new Plugin($pluginID);
	
	/*$template_settings = array(
		'HR_PLUGIN_ID' => $thisPlugin->getID(),
		'HR_PLUGIN_AUTHOR_ID' => $thisPlugin->author_id,
		'HR_PLUGIN_AUTHOR_NAME' => $pluginName,
		'HR_PLUGIN_NAME' => $thisPlugin->name,
		'HR_PLUGIN_DESCRIPTION' => $thisPlugin->desc,
		'HR_PLUGIN_REQUIREMENTS' => $thisPlugin->reqs,
		//'HR_PLUGIN_NEEDSMYSQL' => $thisPlugin->requires_mysql,
		'HR_PLUGIN_DOWNLOADS' => $thisPlugin->downloads,
		'HR_PLUGIN_ADDED_DATE' => $thisPlugin->added_date,
		'HR_PLUGIN_RATING' => $thisPlugin->rating,
		'HR_PLUGIN_STATUS' => $thisPlugin->status,
		'HR_PLUGIN_STATUS_NAME' => $thisPlugin->getStatusName()
	);*/
