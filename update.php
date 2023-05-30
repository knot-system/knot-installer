<?php

include_once( 'config.php' );


if( ! isset($_REQUEST['debug']) ) {
	error_reporting(0);
}


$api_url = 'https://api.github.com/repos/maxhaesslein/homestead/releases';
$dev_zip = 'https://github.com/maxhaesslein/homestead/archive/refs/heads/main.zip';

$homestead_version = file_get_contents('version.txt');

$useragent = 'maxhaesslein/homestead/'.$homestead_version;


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

if( ! $module_found ) {
	
	echo '<p>It looks like Homestead is not installed at this location!</p>';

} elseif( ! $update_allowed ) {
	?>
	<p>Please create an empty file called <code>update</code> (or <code>update.txt</code>) in the root folder (so we are sure that you have access to the webserver), and then refresh this page</p>
	<p><a href="<?= $baseurl ?>update.php">refresh this page</a></p>
	<?php

} elseif( isset($_GET['action']) && $_GET['action'] == 'update_homestead' ) {

	if( (isset($_GET['step']) && $_GET['step'] == 'install') ) {

		if( empty($_REQUEST['version']) || $_REQUEST['version'] == 'latest' ) {

			$json = get_remote_json( $api_url );
			
			if( ! $json || ! is_array($json) ) {
				?>
				<p><strong>Error:</strong> could not get release information from GitHub</p>
				<?php
				exit;
			}

			$latest_release = $json[0];

			$zipball = $latest_release->zipball_url;

			$zip_folder_name_start = 'maxhaesslein-homestead-';

		} elseif( $_REQUEST['version'] == 'dev' ) {

			$zipball = $dev_zip;

			$zip_folder_name_start = 'homestead-';

		} else {

			?>
			<p><strong>Error:</strong> unknown version</p>
			<?php
			exit;

		}

		if( ! $zipball ) {
			?>
			<p><strong>Error:</strong> could not get new .zip file from GitHub</p>
			<?php
			exit;
		}

		$temp_folder = $abspath.'_homestead_temp/';
		if( ! is_dir($temp_folder) ) {
			$oldumask = umask(0); // we need this for permissions of mkdir to be set correctly
			if( mkdir( $temp_folder, 0777, true ) === false ) {
				echo '<p><strong>Error:</strong> could not create temp folder</p>';
				exit;
			}
			umask($oldumask); // we need this after changing permissions with mkdir
		}

		echo '<p>Downloading new .zip from GitHub … ';
		flush();

		$temp_zip_file = $temp_folder.'_new_release.zip';
		if( file_exists($temp_zip_file) ) unlink($temp_zip_file);

		$file_handle = fopen( $temp_zip_file, 'w+' );

		$ch = curl_init( $zipball );
		curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1 );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $ch, CURLOPT_USERAGENT, $useragent );
		curl_setopt( $ch, CURLOPT_FILE, $file_handle );
		curl_exec( $ch );
		curl_close( $ch );

		fclose($file_handle);

		echo 'done.</p>';

		echo '<p>Extracting .zip file … ';
		flush();
		
		$temp_zip_folder = $temp_folder.'/_new_release/';
		if( is_dir($temp_zip_folder) ) deleteDirectory($temp_zip_folder);
		$oldumask = umask(0); // we need this for permissions of mkdir to be set correctly
		if( mkdir( $temp_zip_folder, 0777, true ) === false ) {
			echo '<p><strong>Error:</strong> could not create temp folder for zip unpacking</p>';
			exit;
		}
		umask($oldumask); // we need this after changing permissions with mkdir

		$zip = new ZipArchive;
		$res = $zip->open($temp_zip_file);
		if( $res !== TRUE ) {
			echo '<p><strong>Error:</strong> could not extract .zip file</p>';
			exit;
		}
		$zip->extractTo( $temp_zip_folder );
		$zip->close();

		echo 'done.</p>';

		$subfolder = false;
		foreach( scandir( $temp_zip_folder ) as $obj ) {
			if( $obj == '.' || $obj == '..' ) continue;
			if( ! is_dir($temp_zip_folder.$obj) ) continue;
			if( ! str_starts_with($obj, $zip_folder_name_start) ) continue;
			// the zip file should have exactly one subfolder. this is what we want to get here
			$subfolder = $temp_zip_folder.$obj.'/';
		}

		if( ! $subfolder ) {
			echo '<p><strong>Error:</strong> something went wrong with the .zip file</p>';
			exit;
		}

		echo '<p>Deleting old files … ';
		flush();

		// TODO: the new version should have a list of files to delete and copy, so that we can add new files with an update

		@unlink( $abspath.'.gitignore' );
		unlink( $abspath.'changelog.txt' );
		unlink( $abspath.'config.php' );
		unlink( $abspath.'install.php' );
		unlink( $abspath.'README.md' );
		unlink( $abspath.'update.php' );

		echo 'done.</p>';

		echo '<p>Moving new files to new location … ';
		flush();

		rename( $subfolder.'changelog.txt', $abspath.'changelog.txt' );
		rename( $subfolder.'config.php', $abspath.'config.php' );
		rename( $subfolder.'install.php', $abspath.'install.php' );
		rename( $subfolder.'README.md', $abspath.'README.md' );
		rename( $subfolder.'update.php', $abspath.'update.php' );

		echo 'done.</p>';
		echo '<p>Cleaning up …';
		@unlink( $abspath.'update.txt' );
		@unlink( $abspath.'update' );

		deleteDirectory( $temp_folder );

		echo 'done.</p>';

		echo '<p>Please <a href="'.$baseurl.'update.php">refresh this page</a></p>';

	} else {

		$json = get_remote_json( $api_url );
		
		if( ! $json || ! is_array($json) ) {
			?>
			<p><strong>Error:</strong> could not get release information from GitHub</p>
			<?php
			exit;
		}

		$latest_release = $json[0];

		$release_name = $latest_release->name;

		?>
		<p>Latest release: <strong><?= $release_name ?></strong><br>
		Currently installed: <strong><?= $homestead_version ?></strong></p>
		<?php

		$release_notes = array();

		$version_number_old = explode('.', $homestead_version);
		$version_number_new = explode('.', $release_name);

		if( $version_number_new[0] > $version_number_old[0] 
		 || ($version_number_new[0] == $version_number_old[0] && $version_number_new[1] > $version_number_old[1] )
		 || ($version_number_new[0] == $version_number_old[0] && $version_number_new[1] == $version_number_old[1] && $version_number_new[2] > $version_number_old[2] )
		){

			foreach( $json as $release ) {
				$tag_name = str_replace('v.', '', $release->tag_name);
				$release_number = explode('.', $tag_name);

				$newer_version = false;
				if( $release_number[0] > $version_number_old[0] 
				 || ($release_number[0] == $version_number_old[0] && $release_number[1] > $version_number_old[1] )
				 || ($release_number[0] == $version_number_old[0] && $release_number[1] == $version_number_old[1] && $release_number[2] > $version_number_old[2] )
				){
				 	$newer_version = true;
				}

				if( ! $newer_version ) break;

				$release_notes[] = [
					'title' => $release->tag_name,
					'body' => $release->body
				];
			}
		
			echo '<p><strong>New version available!</strong> You should update your system.</p>';

			if( count($release_notes) ) {
				?>
				<h2>Release notes:</h2>
				<?php
				
				foreach( $release_notes as $release_note ) {
					echo '<h3>'.$release_note['title'].'</h3>';

					$body = htmlentities($release_note['body']);
					$body = nl2br($body);
				
					echo $body;
				}

			}

		} else {
			echo '<p>You are running the latest version.</p>';
		}

		?>
		<hr>

		<form action="<?= $baseurl ?>update.php" method="GET">
			<input type="hidden" name="action" value="update_homestead">
			<input type="hidden" name="step" value="install">
			<p><label>Version: <select name="version">
				<option value="latest" selected>latest stable release (v.<?= $release_name ?>)</option>
				<option value="dev">unstable dev release (not recommended)</option>
			</select></label></p>
			<p><button>update Homestead system</button></p>
		</form>

		<?php

	}

} elseif( isset($_GET['action']) && $_GET['action'] == 'update_modules' ) {

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
	
	<form method="GET" action="<?= $baseurl ?>update.php">

		<fieldset>
			<legend>Update Homestead</legend>
			
			<input type="hidden" name="action" value="update_homestead">

			<p><button>search for Homestead update</button></p>
		</fieldset>

	</form>


	<form method="GET" action="<?= $baseurl ?>update.php">

		<fieldset>
			<legend>Update Modules</legend>

			<p>This will update the selected modules. These modules are currently installed:</p>

			<input type="hidden" name="action" value="update_modules">

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
		</fieldset>

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

function get_remote_json( $url, $headers = array() ) {

	global $useragent;

	$ch = curl_init( $url );
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
	curl_setopt( $ch, CURLOPT_USERAGENT, $useragent );
	curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
	$response = curl_exec($ch);
	curl_close( $ch );

	$json = json_decode($response);

	return $json;
}

function deleteDirectory( $dirPath ) {

	if( ! is_dir($dirPath) ) return;

	$objects = scandir($dirPath);
	foreach ($objects as $object) {
		if( $object == "." || $object == "..") continue;

		if( is_dir($dirPath . DIRECTORY_SEPARATOR . $object) ){
			deleteDirectory($dirPath . DIRECTORY_SEPARATOR . $object);
		} else {
			unlink($dirPath . DIRECTORY_SEPARATOR . $object);
		}
	}
	rmdir($dirPath);
}
