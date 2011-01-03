<?php 
$gitCommit = unserialize(file_get_contents(HR_ROOT . '/gitcommit.txt'));
?><!DOCTYPE html>
<html>
	<head>
		<title><?php echo pagetitle(); ?> &lsaquo; Fill the Bukkit</title>

		<link href='http://fonts.googleapis.com/css?family=Nobile' rel='stylesheet' type='text/css'>
		<link rel="stylesheet" type="text/css" href="<?php echo HR_TEMPLATE_PUB_ROOT; ?>css/fonts.css" />
		<link rel="stylesheet" type="text/css" href="<?php echo HR_TEMPLATE_PUB_ROOT; ?>css/fill.css" />
		<link rel="stylesheet" type="text/css" href="<?php echo HR_TEMPLATE_PUB_ROOT; ?>css/messages.css" />
		<?php
		foreach (Content::$additionalCSS as $addssheet)
		{
			if (substr($addssheet, 0, 2) != '//')
			{
				$addssheet = HR_TEMPLATE_PUB_ROOT . 'css/' . $addssheet;
			}
			echo '		<link rel="stylesheet" type="text/css" href="' . $addssheet . '" />
';
		}
		?>
		<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.4.4/jquery.min.js"></script>
		<?php
		foreach (Content::$additionalJS as $addjs)
		{
			if (substr($addjs, 0, 2) != '//')
			{
				$addjs = HR_TEMPLATE_PUB_ROOT . 'js/' . $addjs;
			}
			echo '          <script type="text/javascript" src="' . $addjs . '"></script>
';
		}
		?>
	</head>
	<body>
	<div class="wrap">
		<div class="header">
			<div class="menu-links">
				<a class="home-button" href="/"></a>
				<?php echo nav(); ?>
			</div>
		</div>
	        <div class="featured-rotator">
	            <div class="featured-rotator-wrap">
					<div class="featured">
						<a href="#prev" class="rprev"></a>
					</div>
					<div class="featured">
						<ul>
							<li>featured item 1</li>
							<li>featured item 2</li>
							<li>featured item 3</li>
							<li>featured item 4</li>
						</ul>
					</div>
					<div class="featured">
						<a href="#next" class="rnext"></a>
					</div>
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
						<h1><?php echo Content::$pageHeader; ?></h1>
						<?php echo content(); ?>
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
