<?php

$nav['faq'] = array('url' => '/faq', 'slug' => 'faq', 'name' => 'FAQ', 'loggedInOnly' => false, 'weight' => 3);
if($slug == "faq") {
	Content::setTitle('Frequently Asked Questions');
	Content::setContent(<<<EOT
	<h3>1. Why is Sturmeh so cool?</h3>
	<p>Many scientists have tried to derive the answer to the question but even the world's best minds are unsure of what causes the effect.</p>
	<h3>2. Why is sk89q so lame?</h3>
	<p>Many scientists have tried to derive the answer to the question but even the world's best minds are unsure of what causes the effect. Also, his plugins suck</p>
	<h3>3. Why now?</h3>
	<p>Why not?</p>


EOT
	);
	
}
