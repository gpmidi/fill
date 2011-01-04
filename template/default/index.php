<!DOCTYPE html>
<html>
	<head>
		<title>(TITLE GOES HERE) &lsaquo; Fill the Bukkit</title>

		<link href='http://fonts.googleapis.com/css?family=Nobile' rel='stylesheet' type='text/css'>
		<link rel="stylesheet" type="text/css" href="/css/fonts.css" />
		<link rel="stylesheet" type="text/css" href="/css/fill.css" />
		<link rel="stylesheet" type="text/css" href="/css/messages.css" />
		<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.4.4/jquery.min.js"></script>
	</head>
	<body>
	<div class="wrap">
		<div class="header">
			<div class="menu-links">
				<a class="home-button" href="/"></a>
			</div>
		</div>
	        <div class="featured-rotator">
	            <div class="featured-rotator-wrap">
					<a href="#prev" class="rprev"></a>
					<ul>
						<li>featured item 1</li>
						<li>featured item 2</li>
						<li>featured item 3</li>
						<li>featured item 4</li>
					</ul>
					<a href="#next" class="rnext"></a>
	            </div>
	        </div>
		<div class="content-wrap">
			<div class="cols1">
				<div class="item">
					<div class="item-t">
						<h1>Categories</h1>
					</div>
				</div>
			</div>
			<div class="cols2">
				<div class="item">
					<div class="item-t">
						<h1></h1>
					</div>
				</div>
			</div>
			<div class="cols3">
				<div class="item">
					<div class="item-t">
						<h1>Categories</h1>
					</div>
				</div>
			</div>
		</div>
		<div id="footer">
			<p>&copy; <?php echo date('Y'); ?> the Bukkit Team.</p>
			<p>Powered by <a href="http://hostiio.com">Hostiio</a> and <a href="http://aws.amazon.com/s3">Amazon S3</a>.</p>
			<p>Git Revision: <a href="http://github.com/robbiet480/hRepo/commit/<?php echo $gitCommit['long']; ?>"><?php echo $gitCommit['short']; ?></a> - by <?php echo $gitCommit['userid']; ?> at <?php echo date('jS M Y, H:i:s', strtotime($gitCommit['commitdate'])); ?></p>
		</div>

	</body>
</html>
