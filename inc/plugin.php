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
	public $rating = -1; // rating 1-5
	public $status = -2; // -2 = non-visible, unclaimed; -1 = non-visible, claimed; 0 = unclaimed, visible; 1 = claimed, visible; 2 = deprecated/out of date
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
		$this->inited = 2;
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
		$a = new XenForo_Model_User();
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
		$a = new XenForo_Model_User();
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
}
