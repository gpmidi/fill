<?php

/**
 * This is the class that generically handles plugin uploads!
 *
 * @author Luke
 */
 class PluginUpload {
	private $inited = false; // shouldn't be modifiable. 0 = not inited, create new, 1 = from DB
	private $id = 0; // shouldn't be modifiable
	public $filename = '';
	public $friendlyname = '';
	public $description = '';
	public $pluginid = 0;
	
	public function PluginUpload($did = -1) {
		if ($did != -1)
			$this->loadData($did);
	}
	
	public function getID() {
		return $this->id;
	}
	
	public function loadData($did) {
		$dbh = Database::select('plugin_downloads', '*', array('did = ?', $did));
		if ($dbh->rowCount() != 1)
			throw new NoSuchUploadException();
		$dbr = $dbh->fetch(PDO::FETCH_ASSOC);
		$this->filename = $dbr['dfname'];
		$this->friendlyname = $dbr['dfriendlyname'];
		$this->description = $dbr['ddesc'];
		$this->id = $dbr['did'];
		$this->pluginid = $dbr['pid'];
		$this->inited = true;
	}
	
	public function saveData() {
		$inputArray = array(
			'pid' => $this->pluginid,
			'dfname' => $this->filename,
			'dfriendlyname' => $this->friendlyname,
			'ddesc' => $this->description
		);
		if (!$this->inited) { // not initialised
			if (Database::insert('plugin_downloads', $inputArray))
				$this->id = Database::getHandle()->lastInsertId();
			else
				throw new Exception("Database error!");
		} else {
			Database::update('plugin_downloads', $inputArray, null, array('did = ?', $this->id));
		}
		$this->inited = true;
	}
	
	public function getPlugin() {
		inc('plugin.php');
		return new Plugin($this->pluginid);
	}
	
	public function getVersions() {
		inc('PluginUploadVersion.php');
		$getQ = Database::select('plugin_downloads_version', 'vid', array('did = ?', $this->id));
		$outArr = array();
		while ($getR = $getQ->fetchColumn()) {
			$outArr[] = new PluginUploadVersion($getR);
		}
		return $outArr;
	}
	
	public function getLatest() {
		inc('PluginUploadVersion.php');
		$getQ = Database::select('plugin_downloads_version', 'vid', array('did = ?', $this->id), array('vid', 'desc'), 1);
		$outArr = array();
		while ($getR = $getQ->fetchColumn()) {
			$outArr = new PluginUploadVersion($getR);
		}
		return $outArr;
	}
 }
