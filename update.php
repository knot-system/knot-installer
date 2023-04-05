<?php

include_once( 'config.php' );



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


$module_found = false; // at least one module was found
foreach( $sources as $source => $options ) {

	$sources[$source]['installed_version'] = false;
	if( is_dir($abspath.$options['target'].'/') && file_exists($abspath.$options['target'].'/system/version.txt') ) {
		$sources[$source]['installed_version'] = trim(file_get_contents($abspath.$options['target'].'/system/version.txt'));

		$module_found = true;
	}

}


$update_allowed = false;
if( file_exists($abspath.'update') || file_exists($abspath.'update.txt') ) {
	$update_allowed = true;
}


if( ! $module_found ) {
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
	code.response {
		border: 1px solid grey;
		padding: 10px;
		display: block;
		pointer-events: none;
		opacity: 0.5;
	}
		code.response a {
			text-decoration: none;
			color: inherit;
			pointer-events: none;
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

	foreach( $sources as $source => $options ) {
		do_update( $source, $version );
	}

	echo '<p>Cleaning up …</p>';
	flush();

	@unlink($abspath.'update.txt');
	@unlink($abspath.'update');

	echo '<p>All done.</p>';

} else {
	?>
	<p>This script will update the selected modules. These modules are currently installed:</p>

	<form method="GET" action="<?= $baseurl ?>update.php">
		<input type="hidden" name="action" value="install">

		<ul style="list-style-type: none; padding: 0;">
			<?php
			foreach( $sources as $source => $options ) {
				if( ! $options['installed_version'] ) continue;
				?>
				<li>
					<label><input type="checkbox" name="modules[]" value="<?= $source ?>" checked> <?= $options['name'] ?> (v.<?= $options['installed_version'] ?>)</label>
				</li>
				<?php
			}
			?>
		</ul>

		<p><label>update to: <select name="version">
			<option value="latest" selected>latest stable release</option>
			<option value="dev">unstable dev release (not recommended)</option>
		</select></label></p>
		<p><button>update selected modules</button></p>

	</form>
	<?php
}
?>
</main>
</body>
</html><?php


function do_update( $source, $version = 'latest' ) {

	global $homestead_abspath, $homestead_baseurl, $sources;

	$options = $sources[$source];

	echo '<p>Updating '.$options['name'].' …</p>';
	flush();

	$abspath = $homestead_abspath.$options['target'];
	$baseurl = $homestead_baseurl.$options['target'];

	touch( $abspath.'update' );

	time_nanosleep(0,500000000); // sleep for 0.5 seconds

	$response = get_request( $baseurl.'?update=true&step=install&version='.$version );

	?>
	<code class="response">
		<?php echo strip_tags( $response, ['br','p','ul','ol','li'] ); ?>
	</code>
	<?php
	flush();

	time_nanosleep(0,500000000); // sleep for 0.5 seconds

	get_request( $baseurl ); // trigger re-creation of missing files

	flush();

}


function get_request( $url ) {

	global $useragent;

	$ch = curl_init( $url );
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
	curl_setopt( $ch, CURLOPT_USERAGENT, $useragent );
	curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
	$response = curl_exec( $ch );
	curl_close( $ch );

	return $response;
}
