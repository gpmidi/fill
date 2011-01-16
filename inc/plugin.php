<?php

/**
 * This is the class that generically handles plugins!
 *
 * @author Luke
 */
class Plugin {

	private static $mappings = array(
		'pid' => 'id',
		'pauthor_id' => 'author_id',
		'pname' => 'name',
		'pdesc' => 'desc',
		'preqs' => 'reqs',
		'pmysql' => 'requires_mysql',
		'pdownloads' => 'downloads',
		'padded_date' => 'added_date',
		'prating' => 'rating',
		'pstatus' => 'status'
	);
	private $inited = 0; // shouldn't be modifiable. 0 = not inited, create new, 1 = from DB
	private $id = 0; // shouldn't be modifiable
	public $author_id = 0;
	public $name = '';
	public $desc = '';
	public $reqs = '';
	public $requires_mysql = ''; // 0 = no/na, 1 = can use, 2 = MUST use
	public $downloads = 0; // download counter
	public $added_date = ''; // date added to db
	public $categories = array(); // categories nums
	public $categories_names = array();
	
	public $rating = -1; // rating 1-5
	
	public $status = -2; // -3 = non-visible, claimed, trusted; -2 = non-visible, unclaimed; -1 = non-visible, claimed; 0 = unclaimed, visible; 1 = claimed, visible; 2 = trusted, claimed, visible; 3 = deprecated/out of date
	const STATE_HIDDEN_TRUSTED = -3; //
	const STATE_HIDDEN_UNCLAIMED = -2;
	const STATE_HIDDEN_CLAIMED = -1;
	const STATE_UNCLAIMED = 0;
	const STATE_CLAIMED = 1;
	const STATE_TRUSTED = 2;
	const STATE_DEPRECATED = 3;
	
	public $real_author_name = ''; // should only exist if $status = 0 or -2

	function Plugin($pluginid = -1) {
		if ($pluginid != -1)
			$this->loadData($pluginid);
	}

	function getID() {
		return $this->id;
	}

	function loadData($pluginid) {
		if (is_numeric($pluginid))
		{
			$dbh = Database::select('plugins', '*', array('pid = ?', $pluginid));
		}
		else
		{ // is a plugin name, shortcut function!
			$dbh = Database::select('plugins', '*', array('pname = ?', $pluginid));
		}
		if ($dbh->rowCount() != 1)
			throw new NoSuchPluginException();
		$dbr = $dbh->fetch(PDO::FETCH_ASSOC);
		foreach (self::$mappings as $fromdb => $toclassvar)
		{
			$this->$toclassvar = $dbr[$fromdb];
		}
		// now load categories
		$dbh = Database::select('plugin_cat_pivot', array('cid'), array('pid = ?', $this->id));
		$this->categories = $this->categories_names = array();
		while ($dbr = $dbh->fetch()) {
			$this->categories[] = $dbr['cid'];
		}
		// are there any?
		if (count($this->categories) > 0) { // load category names
			$catlist = implode(',', $this->categories);
			$a = Database::select('categories', '*', array('cid IN ('.$catlist.')'));
			while ($row = $a->fetch(PDO::FETCH_ASSOC)) {
				$this->categories_names[$row['cid']] = $row['cname'];
			}
		}
		$this->inited = 1;
	}

	function saveData() {
		$dbarray = array();
		foreach (self::$mappings as $todb => $fromclassvar)
		{
			$dbarray[$todb] = $this->$fromclassvar;
		}
		if ($this->inited == 1)
		{
			$a = Database::update('plugins', $dbarray, null, array('pid = ?', $this->id));
		}
		else
		{
			unset($dbarray['pid']); // auto increment
			$dbarray['padded_date'] = date('Y-m-d H:i:s');
			$a = Database::insert('plugins', $dbarray);
			if ($a == 1)
				$this->id = Database::getHandle()->lastInsertId();
		}
		Database::delete('plugin_cat_pivot', array('pid = ?', $this->id));
		if (is_array($this->categories)) {
			foreach ($this->categories as $category) {
				$category = (int)$category;
				Database::insert('plugin_cat_pivot', array('pid' => $this->id, 'cid' => $category));
			}
			// reload categories.
			$catlist = implode(',', $this->categories);
			$b = Database::select('categories', '*', array('cid IN ('.$catlist.')'));
			while ($row = $b->fetch(PDO::FETCH_ASSOC)) {
				$this->categories_names[$row['cid']] = $row['cname'];
			}
		} else {
			$this->categories = array();
			$this->categories_names = array();
		}
			
		return ($a == 1) ? true : false;
	}

	/**
	 * getVotes() - get the current aggregate votes for this plugin
         *
         * @args void
         * @returns array containing two elements, likes and dislikes
	**/
	function getVotes() {
		$dbR = Database::select('plugin_ratings', '*', array('pid = ?', $this->id));
		if ($dbR->rowCount() == 0) {
			Database::insert('plugin_ratings', array('pid' => $this->id, 'likes' => 0, 'dislikes' => 0));
			$dbR = Database::select('plugin_ratings', '*', array('pid = ?', $this->id));
		}
		$voteCs = $dbR->fetch();
		unset($voteCs['pid']);
		return $voteCs;
	}

	/**
	 * userHasVoted($userid) - returns the current vote of the given user
	 *
	 * @return int Value showing vote
	 * 0 = no vote
	 * -1 = dislike
	 * +1 = like
	 *
	 * @param int $userid XenForo user ID of the user to look up
	**/
	function userHasVoted($userid) {
		$a = XenForo_Model::create('XenForo_Model_User');
		$curGet = $a->getUserById($userid);
		$list = unserialize($curGet['plugins_voted_on']);
		if (isset($list[$this->id]) && $list[$this->id] != 0) {
			return $list[$this->id];
		}
		return 0;
	}

	/**
	 * userSetVote($userid, $vote)
	 * Sets a user's vote and adjusts the count
	 * accordingly.
	 *
	 * @param int $userid User ID of the XF user to change the vote for
	 * @param int $vote Vote to set to
	 *
	 * @see userGetVote
	**/
	function userSetVote($userid, $vote) {
		$a = XenForo_Model::create('XenForo_Model_User');
		$curGet = $a->getUserById($userid);
		$list = unserialize($curGet['plugins_voted_on']);
		$voteCs = $self->getVotes();
		if ($vote > 0)
			$voteCs['likes'] += 1;
		else
			$voteCs['dislikes'] += 1;
		if ($b = $this->userHasVoted($userid) && $b != 0) {
			if ($b > 0)
				$voteCs['likes'] -= 1;
			else
				$voteCs['dislikes'] -= 1;
		}
		$list[$this->id] = $vote;
		$curGet->update($curGet, 'plugins_voted_on', serialize($list));
		Database::update('plugin_ratings', $voteCs, null, array('pid = ?', $this->id));
	}
	
	function getStatusSpecifics() {
		if ($this->status < 0 || $this->status == 3) { // if hidden or deprecated/old
			$hidden = true;
		} else {
			$hidden = false;
		}
		if ($this->status == -3 || $this->status == 2) { // if trusted
			$trusted = true;
		} else {
			$trusted = false;
		}
		if ($this->status == -2 || $this->status == 0 || $this->status = 3) {
			$claimed = false;
		} else {
			$claimed = true;
		}
		return array('hidden' => $hidden, 'trusted' => $trusted, 'claimed' => $claimed);
	}
	
	
	function getDownloads() {
		inc('PluginUpload.php');
		$getQ = Database::select('plugin_downloads', 'did', array('pid = ?', $this->id));
		$outArr = array();
		while ($getR = $getQ->fetchColumn()) {
			$outArr[] = new PluginUpload($getR);
		}
		return $outArr;
	}
	
	function getPrimaryDownloadVersion() {
		inc('PluginUpload.php');
		inc('PluginUploadVersion.php');
		$getQ = Database::select('plugin_downloads', 'did', array('pid = ?', $this->id));
		$insideArr = array();
		while ($getR = $getQ->fetchColumn()) {
			$insideArr[] = $getR;
		}
		$getQ = Database::getHandle()->prepare('SELECT vid FROM plugin_downloads_version WHERE did IN ('.implode(',', $insideArr).') AND visprimary = 1');
		$getQ->execute();
		while ($getR = $getQ->fetchColumn()) {
			return new PluginUploadVersion($getR);
		}
		throw new HttpException(404);
	}
	
	function addDownload() {
		$this->downloads = $this->downloads + 1;
		$this->saveData();
	}
	
	function imprint($pageViewID) {
		Database::update('page_view_log', array('pid' => $this->id), null, array('lid = ?', $pageViewID));
	}
}

class NoSuchPluginException extends Exception {
}
class NoSuchUploadException extends Exception {
}
class NoSuchVersionException extends Exception {
}