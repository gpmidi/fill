<?php

	if (count($hr_URI) > 1) {
		chdir(HR_ROOT);
		$template_settings['HR_TEMPLATE_TITLE'] = 'Updating website from github';
		$template_settings['HR_TEMPLATE_CONTENT'] = "
			<pre>".`echo Running git reset --hard && git reset --hard 2>&1 && echo Running git pull && git pull`."</pre><br />
			<h3>Now pulling latest commit information from Github...</h3>
		";
		inclib('github/lib/phpGitHubApi.php');
		$phpGH = new phpGitHubApi();
		$phpGH->authenticate('lukegb', '3b4e0c11ee0681db035b0e885147e236', phpGitHubAPI::AUTH_HTTP_TOKEN);
		$latestCommits = $phpGH->getCommitApi()->getBranchCommits('Bukkit', 'fill', 'master');
		$gitCommit = array(
			'long' => $latestCommits[0]['id'],
			'short' => substr($latestCommits[0]['id'], 0, 7),
			'userid' => $latestCommits[0]['author']['login'],
			'commitdate' => $latestCommits[0]['committed_date']
		);
		file_put_contents(HR_ROOT . '/gitcommit.txt', serialize($gitCommit));
		$template_settings['HR_TEMPLATE_CONTENT'] .= '<p>Last git commit: ' . $gitCommit['long'] . ' by ' . $gitCommit['userid']. '</p>';
	} else {
		$template_settings['HR_TEMPLATE_TITLE'] = 'Auth code invalid';
		$template_settings['HR_TEMPLATE_CONTENT'] = "
			<p>Git update did not go through.</p>
		";
	}