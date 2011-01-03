<?php

$nav['contact'] = array('url' => '/contact', 'slug' => 'contact', 'name' => 'Contact', 'loggedInOnly' => false, 'weight' => 5); // -1 for only not logged in
if($slug == "contact") {
	Content::setTitle('Contact Us!');
	Content::setContent(<<<EOT
	<h2>IRC</h2>
	Join our IRC chatroom!<br />
	Just point your favorite client to irc.esper.net, port 6667 and join #bukkit!

EOT
	);
	
}
