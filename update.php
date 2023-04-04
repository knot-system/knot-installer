<?php

$updater_version = '0.1.1';

$useragent = 'maxhaesslein/homestead/'.$updater_version;




$self = basename(__FILE__);

$abspath = realpath(dirname(__FILE__)).'/';
$abspath = preg_replace( '/system\/$/', '', $abspath );

$basefolder = str_replace( $self, '', $_SERVER['PHP_SELF']);

if( isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ) $baseurl = 'https://';
else $baseurl = 'http://';
$baseurl .= $_SERVER['HTTP_HOST'];
$baseurl .= $basefolder;


$homestead_abspath = $abspath;
$homestead_baseurl = $baseurl;


$eigenheim = false;
if( is_dir($abspath.'eigenheim/') && file_exists($abspath.'eigenheim/system/version.txt') ) {
	$eigenheim = trim(file_get_contents($abspath.'eigenheim/system/version.txt'));
}

$postamt = false;
if( is_dir($abspath.'postamt/') && file_exists($abspath.'postamt/system/version.txt') ) {
	$postamt = trim(file_get_contents($abspath.'postamt/system/version.txt'));
}

$sekretaer = false;
if( is_dir($abspath.'sekretaer/') && file_exists($abspath.'sekretaer/system/version.txt') ) {
	$sekretaer = trim(file_get_contents($abspath.'sekretaer/system/version.txt'));
}


$update_allowed = false;
if( file_exists($abspath.'update') || file_exists($abspath.'update.txt') ) {
	$update_allowed = true;
}


if( ! $eigenheim && ! $postamt && ! $sekretaer ) {
	echo '<strong>Error:</strong> it looks like homestead is not installed at this location!';
	exit;
}

?><!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width,initial-scale=1.0">
	<title>🏡 Homestead Updater</title>

	<style>
		fieldset {
			margin-top: 1.5em;
		}
			fieldset legend {
				font-weight: bolder;
			}
		fieldset.unimportant {
			opacity: 0.5;
		}
	hr {
		margin: 2em 0;
		border: 0;
		border-top: 1px solid;
	}
	iframe {
		border: 1px solid grey;
		padding: 10px;
		display: block;
		width: 100%;
		height: 500px;
		pointer-events: none;
		opacity: 0.5;
	}
	</style>
</head>
<body>
<main style="max-width: 600px; margin: 0 auto">

	<h1>🏡 Homestead Updater</h1>

<?php

if( ! $update_allowed ) {
	?>
	<p>Please create an empty file called <code>update</code> (or <code>update.txt</code>) in the root folder (so we are sure that you have access to the webserver), and then refresh this page</p>
	<p><a href="<?= $baseurl ?>update.php">refresh this page</a></p>
	<?php

} elseif( isset($_GET['action']) && $_GET['action'] == 'install' ) {

	$version = 'latest';
	if( ! empty($_REQUEST['version']) ) $version = $_REQUEST['version'];

	$modules = [];
	if( ! empty($_REQUEST['modules']) ) $modules = $_REQUEST['modules'];

	if( ! count($modules) ) {
		?>
		<p>please select at least one module to update</p>
		<p><a href="<?= $baseurl ?>update.php">back</a></p>
		<?php
		exit;
	}

	?>
	<p>Starting update …</p>
	<?php
	flush();

	if( $eigenheim && in_array('eigenheim', $modules) ) {
		do_update( 'eigenheim', $version );
	}

	if( $sekretaer && in_array('sekretaer', $modules) ) {
		do_update( 'sekretaer', $version );
	}

	if( $postamt && in_array('postamt', $modules) ) {
		do_update( 'postamt', $version );
	}

	echo '<p>Cleaning up …</p>';
	flush();

	@unlink($abspath.'update.txt');
	@unlink($abspath.'update');

	echo '<p>All done. <a href="'.$baseurl.'">Refresh this page</a></p>';

} else {
	?>
	<p>This script will update all modules. These modules are currently installed:</p>

	<form method="GET" action="<?= $baseurl ?>update.php">
		<input type="hidden" name="action" value="install">

		<ul style="list-style-type: none; padding: 0;">
			<?php
			if( $eigenheim ) echo '<li><label><input type="checkbox" name="modules[]" value="eigenheim" checked> Eigenheim (v.'.$eigenheim.')</label></li>';
			if( $sekretaer ) echo '<li><label><input type="checkbox" name="modules[]" value="sekretaer" checked> Sekretär (v.'.$sekretaer.')</label></li>';
			if( $postamt ) echo '<li><label><input type="checkbox" name="modules[]" value="postamt" checked> Postamt (v.'.$postamt.')</label></li>';
			?>
		</ul>

		<p><label>update to: <select name="version">
			<option value="latest" selected>latest stable release</option>
			<option value="dev">unstable dev release (not recommended)</option>
		</select></label></p>
		<p><button>update all modules</button></p>

	</form>
	<?php
}
?>
</main>
</body>
</html><?php


function do_update( $module, $version = 'latest' ) {

	global $homestead_abspath, $homestead_baseurl;

	$abspath = $homestead_abspath.$module.'/';

	$name = ucwords($module);
	if( $name == 'Sekretaer' ) $name = 'Sekretär';

	echo '<p>Updating '.$name.' …</p>';
	flush();

	touch( $abspath.'update' );

	time_nanosleep(0,500000000); // sleep for 0.5 seconds

	?>
	<iframe src="<?= $homestead_baseurl.$module.'/?update=true&step=install&version='.$version ?>">
	</iframe>
	<?php
}


function homestead_get_remote( $url ) {

	global $useragent;

	$ch = curl_init( $url );
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
	curl_setopt( $ch, CURLOPT_USERAGENT, $useragent );
	curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
	$response = curl_exec( $ch );
	curl_close( $ch );

	return $response;
}
