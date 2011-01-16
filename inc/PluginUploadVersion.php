<?php

/**
 * This is the class that generically handles plugin uploads versions!
 *
 * @author Luke
 */
 class PluginUploadVersion {
	private $inited = false; // shouldn't be modifiable. 0 = not inited, create new, 1 = from DB
	private $id = 0; // shouldn't be modifiable
	public $downloadid = 0; // download id
	public $vnumber = 0; // version number
	public $hash = 0; // MD5 hash
	public $date = ''; // datetime of adding
	public $changelog = ''; // changelog
	public $isons3 = false; // is on s3
	public $signature = ''; // file's signature
	public $isprimary = false; // is primary file
	
	public function PluginUploadVersion($vid = -1) {
		if ($vid != -1)
			return $this->loadData($vid);
	}
	
	public function loadData($vid) {
		$dbh = Database::select('plugin_downloads_version', '*', array('vid = ?', $vid));
		if ($dbh->rowCount() != 1)
			throw new NoSuchVersionException();
		$dbr = $dbh->fetch(PDO::FETCH_ASSOC);
		$this->inited = true;
		$this->id = $dbr['vid'];
		$this->downloadid = $dbr['did'];
		$this->vnumber = $dbr['vnumber'];
		$this->hash = $dbr['vhash'];
		$this->date = $dbr['vdate'];
		$this->changelog = $dbr['vchangelog'];
		$this->isons3 = $dbr['isons3'];
		$this->signature = $dbr['vsignature'];
		$this->isprimary = $dbr['visprimary'];
	}
	
	public function saveData() {
		if ($this->date == '') $this->date = time();
		$inputArray = array(
			'did' => $this->downloadid,
			'vnumber' => $this->vnumber,
			'vhash' => $this->hash,
			'vdate' => (is_numeric($this->date) ? date('Y-m-d H:i:s', $this->date) : $this->date),
			'vchangelog' => $this->changelog,
			'isons3' => $this->isons3,
			'vsignature' => $this->signature,
			'visprimary' => $this->isprimary
		);
		if (!$this->inited) { // not initialised
			if (Database::insert('plugin_downloads_version', $inputArray))
				$this->id = Database::getHandle()->lastInsertId();
			else
				throw new Exception("Database error!");
		} else {
			Database::update('plugin_downloads_version', $inputArray, null, array('did = ?', $this->id));
		}
		$this->inited = true;
	}
	
	public function getID() { return $this->id; }
 }