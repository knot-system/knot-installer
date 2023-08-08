<?php

$installer_version = '0.2.0';


$sources = [
	'knot-site' => [
		'name' => 'Knot Site',
		'target' => 'knot-site/',
		'zipball_stable' => 'https://api.github.com/repos/maxhaesslein/knot-site/releases',
		'zipball_dev' => 'https://github.com/maxhaesslein/knot-site/archive/refs/heads/main.zip',
	],
	'knot-home' => [
		'name' => 'Knot Home',
		'target' => 'knot-home/',
		'zipball_stable' => 'https://api.github.com/repos/maxhaesslein/knot-home/releases',
		'zipball_dev' => 'https://github.com/maxhaesslein/knot-home/archive/refs/heads/main.zip',
	],
	'knot-daemon' => [
		'name' => 'Knot Daemon',
		'target' => 'knot-daemon/',
		'zipball_stable' => 'https://api.github.com/repos/maxhaesslein/knot-daemon/releases',
		'zipball_dev' => 'https://github.com/maxhaesslein/knot-daemon/archive/refs/heads/main.zip',
	],
	'knot-auth' => [
		'name' => 'Knot Auth',
		'target' => 'knot-auth/',
		'zipball_stable' => 'https://api.github.com/repos/maxhaesslein/knot-auth/releases',
		'zipball_dev' => 'https://github.com/maxhaesslein/knot-auth/archive/refs/heads/main.zip',
	],
	'knot-control' => [
		'name' => 'Knot Control',
		'target' => 'knot-control/',
		'zipball_stable' => 'https://api.github.com/repos/maxhaesslein/knot-control/releases',
		'zipball_dev' => 'https://github.com/maxhaesslein/knot-control/archive/refs/heads/main.zip',
	]
];



if( ! isset($_REQUEST['debug']) ) {
	error_reporting(0);
}


$php_min_version_major = 8;

$useragent = 'knot/installer/'.$installer_version;


$local_phpversion = explode( '.', phpversion() );

$self = basename(__FILE__);
$abspath = realpath(dirname(__FILE__)).'/';
$basefolder = str_replace( $self, '', $_SERVER['PHP_SELF']);

if( isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ) $baseurl = 'https://';
else $baseurl = 'http://';
$baseurl .= $_SERVER['HTTP_HOST'];
$baseurl .= $basefolder;


$is_installed = false;
foreach( $sources as $source => $options ) {
	if( is_dir($abspath.$options['target']) ) {
		$is_installed = true;
	}
}


$required_extensions = array(
	'gd',
	'simplexml',
	'curl',
	'dom'
);

$missing_extensions = array();
foreach( $required_extensions as $required_extension ) {
	if( ! extension_loaded($required_extension) ) {
		$missing_extensions[] = $required_extension;
	}
}


?><!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width,initial-scale=1.0">
	<title>🏡 Knot Installer</title>

	<style>
		fieldset {
			margin-top: 1.5em;
		}
			fieldset legend {
				font-weight: bolder;
			}
	hr {
		margin: 2em 0;
		border: 0;
		border-top: 1px solid;
	}
	code {
		background: rgba(0,0,0,.1);
	}
	</style>
</head>
<body>
<main style="max-width: 600px; margin: 0 auto">

	<h1>🏡 Knot Installer</h1>

<?php

if( $local_phpversion[0] < $php_min_version_major ) {

	?>
	<p><strong>Error:</strong> your PHP version is too old (you need at least <code>PHP <?php echo $php_min_version_major; ?></code>).</p>
	<p>Please upgrade your PHP version to at least version <code><?php echo $php_min_version_major ?></code></p>
	<p>Then <a href="<?= $self ?>">refresh this page</a>.</p>
	<?php

} elseif( count($missing_extensions) ) {

	?>
	<p><strong>Error:</strong> it looks like your server is missing one or more extension that we depend on. Please make sure the following extensions are installed and working:</p>
	<ul>
		<?php
		foreach( $missing_extensions as $missing_extension ) {
			?>
			<li>
				<code><?php echo $missing_extension ?></code>
			</li>
			<?php
		}
		?>
	</ul>
	<p>Then <a href="<?= $self ?>">refresh this page</a>.</p>
	<?php

} elseif( $is_installed ) {

	?>
	<p>It looks like a Knot System is already installed at this location!</p>
	<p>Please delete the existing subfolders, then <a href="<?= $self ?>">refresh this page</a>.</p>
	<?php

} elseif( ! isset($_POST['action'])
 || $_POST['action'] != 'install'
 || empty($_POST['knot-site'])
 || empty($_POST['knot-site']['author_name'])
 || empty($_POST['knot-site']['site_title'])
) {

	?>

	<p>This script will install all modules required for a full <strong>Knot System</strong> installation. These modules are:</p>
	<ul>
		<li><strong>Knot Site</strong> as the website (and micropub server), that visitors will see</li>
		<li><strong>Knot Home</strong> as the micropub & microsub client, where you can write new posts and read posts from websites you follow</li>
		<li><strong>Knot Daemon</strong> as the microsub server, that will manage the websites you follow and collect new posts they publish</li>
		<li><strong>Knot Auth</strong> as the IndieAuth server, that will authorize you via a password, when you want to log in</li>
		<li><strong>Knot Control</strong> as a configuration interface where you can change some config options and search for updates</li>
	</ul>

	<p>Your server should meet all the necessary requirements.</p>

	<form id="install_form" method="POST" action="<?= $self ?>">

		<fieldset>
			<legend>Settings</legend>
	
			<p>You need to provide some basic information before we can begin the installation:</p>

			<p><label><strong>Version</strong><br><select name="version">
				<option value="latest" selected>latest stable release (recommended)</option>
				<option value="dev">unstable dev release (not recommended)</option>
			</select></label></p>

			<p><label><strong>Site Title</strong><br><input type="text" name="knot-site[site_title]" required><br><small>(the title of your website)</small></label></p>
			<p><label><strong>Author Name</strong><br><input type="text" name="knot-site[author_name]" required><br><small>(your name, displayed on your website)</small></label></p>
			<p><label id="password-label"><strong>Password</strong><br><input type="text" class="password-field" name="knot-auth[password]" minlength="8" required> <span class="password-toggle" style="display: none;"></span><br><small>(you use this password to log into the backend, where you can write new posts and read posts from pages you follow; the password needs to be at least 8 characters long)</small></label></p>
			<p><label><input type="checkbox" name="knot-site[testcontent]" value="true" checked> create Knot Site test content<br><small>(add some test content to your website, so you can check that everything works; this is optional)</small></label>

			<script>
			(function(){
				var label = document.getElementById('password-label'),
					field = label.querySelector('.password-field'),
					toggle = label.querySelector('.password-toggle');

				toggle.style.display = 'inline-block';
				field.type = 'password';
				toggle.innerHTML = '(<a href="#">show password</a>)';

				toggle.addEventListener( 'click', function(){
					if( field.type == 'password' ) {
						field.type = 'text';
						toggle.innerHTML = '(<a href="#">hide password</a>)';
					} else {
						field.type = 'password';
						toggle.innerHTML = '(<a href="#">show password</a>)';
					}
				});
			})();
			</script>

		</fieldset>

		<p><strong>This is an early beta version!</strong> Some things may break, or change in the future!</p>

		<p>Your website will be installed at <strong><?= $baseurl ?></strong>. Please make sure that this is correct.</p>

		<input type="hidden" name="action" value="install">
		<button>start installation</button>

	</form>

	<script>
	(function(){
		var form = document.getElementById('install_form');
		form.addEventListener('submit', function(e){
			var button = form.querySelector('button');
			button.disabled = true;
		});
	})();
	</script>

	<?php

} else {

	// running install:

	$temp_folder = $abspath.'tmp/';
	if( ! is_dir($temp_folder) ) {
		$oldumask = umask(0); // we need this for permissions of mkdir to be set correctly
		if( @mkdir( $temp_folder, 0774, true ) === false ) {
			$temp_folder = false;
		}
		umask($oldumask); // we need this after changing permissions with mkdir
	}

	if( ! $temp_folder ) {
		?>
		<p><strong>Error:</strong> could not create temporary <code>tmp/</code> folder. Please check the permissions of this directory.<br>The permission of the folder <em><?= $abspath ?></em> should be set to <code>644</code> (or <code>664</code>).</p>
		<p>After changing the permission, <a href="<?= $self ?>">refresh this page</a>.</p>
		<?php
		exit;
	}

	foreach( $sources as $source => $source_info ) {

		$version = 'stable';
		if( isset($_POST['version']) && $_POST['version'] == 'dev' ) {
			$version = 'dev';
		}

		flush();

		echo '<h3>Installing '.$source.'</h3>';

		$target_folder = $abspath.$source_info['target'];

		install_module( $source, $target_folder, $version );

		flush();

		time_nanosleep(0,500000000); // sleep for 0.5 seconds

		$config = [];
		if( ! empty($_POST[$source]) ) {
			$config = $_POST[$source];
		}


		// automatically set some additional options:
		$config['setup'] = true;
		if( $source == 'knot-site') {
			$config['baseurl_overwrite'] = $baseurl;
			$config['basefolder_overwrite'] = $basefolder;
			$config['microsub'] = $baseurl.$sources['knot-daemon']['target'];
			$config['indieauth-metadata'] = $baseurl.$sources['knot-auth']['target'].'metadata';
		} elseif( $source == 'knot-home' ) {
			$config['authorized_urls'] = $baseurl;
			$config['start'] = true;
		} elseif( $source == 'knot-daemon' ) {
			$config['authorized_urls'] = $baseurl;
			$config['refresh_on_connect'] = 'true';
		} elseif( $source == 'knot-auth' ) {
			$config['me'] = $baseurl;
		}


		// call setup of this module to create the config
		$setup_url = $baseurl.$source_info['target'].'index.php';

		echo '<p>updating config … ';
		flush();

		post_request( $setup_url, $config );

		time_nanosleep(0,500000000); // sleep for 0.5 seconds

		if( ! file_exists($abspath.$source_info['target'].'config.php') ) {
			echo '<span style="color: red;">could not create config file!</span>';
		} else {
			echo 'done.';
		}
		echo '</p>';

	}

	flush();

	$content = "# BEGIN knot\r\n<IfModule mod_rewrite.c>\r\nRewriteEngine on\r\nRewriteBase ".$basefolder."\r\n\r\nRewriteCond %{REQUEST_FILENAME} !-d\r\nRewriteCond %{REQUEST_FILENAME} !-f\r\nRewriteRule (.*) knot-site/$1 [L,QSA]\r\nRewriteRule ^$ knot-site/ [L,QSA]\r\n</IfModule>\r\n# END knot";
	file_put_contents( $abspath.'.htaccess', $content );

	echo '<h3>cleaning up</h3>';
	flush();

	delete_directory($temp_folder);

	unlink( $abspath.'<?= $self ?>' );

	echo '<p>all done.</p>';

	if( array_key_exists('knot-site', $sources) ) {
		echo '<p>&raquo; you can view your Knot Site at <a href="'.$baseurl.'" target="_blank" rel="noopener">'.$baseurl.'</a> - this is your personal website.</p>';
	}
	if( array_key_exists('knot-home', $sources) ) {
		echo '<p>&raquo; you can log into Knot Home at <a href="'.$baseurl.$sources['knot-home']['target'].'?login_url='.urlencode($baseurl).'" target="_blank" rel="noopener">'.$baseurl.$sources['knot-home']['target'].'</a> to write new posts and read posts from other websites.</p>';
	}
	if( array_key_exists('knot-control', $sources) ) {
		echo '<p>&raquo; you can log into Knot Control at <a href="'.$baseurl.$sources['knot-control']['target'].'?login_url='.urlencode($baseurl).'" target="_blank" rel="noopener">'.$baseurl.$sources['knot-control']['target'].'</a> to edit settings and update all modules.</p>';
	}

	flush();

}

?>
</main>
</body>
</html>
<?php


function post_request( $url, $post_data = array() ){
	global $useragent;

	$ch = curl_init( $url );
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
	curl_setopt( $ch, CURLOPT_HEADER, true );
	curl_setopt( $ch, CURLOPT_USERAGENT, $useragent );
	curl_setopt( $ch, CURLOPT_POST, 1 );
	if( count($post_data) ) {
		curl_setopt( $ch, CURLOPT_POSTFIELDS, $post_data );
	}

	curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
	curl_setopt( $ch, CURLOPT_TIMEOUT, 10 );

	$response = curl_exec( $ch );

	curl_close( $ch );

	return $response;
}


function install_module( $source, $target, $version ) {

	global $temp_folder, $sources, $useragent;

	if( $version == 'dev' ) {

		$zipball = $sources[$source]['zipball_dev'];

		$zip_folder_name_start = $source.'-';

		echo '<p>Installing unstable dev release</p>';

	} else {

		$zipball = $sources[$source]['zipball_stable'];

		$json = get_remote_json( $zipball );
		if( ! $json || ! is_array($json) || ! count($json) ) {
			?>
			<p><strong>Error:</strong> could not get release information from GitHub</p>
			<?php
			return;
		}

		$latest_release = $json[0];

		if( empty($latest_release->zipball_url) ) {
			?>
			<p><strong>Error:</strong> could not get zip download url from GitHub</p>
			<?php
			exit;	
		}
		$zipball = $latest_release->zipball_url;

		$zip_folder_name_start = 'maxhaesslein-'.$source.'-';

		echo '<p>Installing latest stable release</p>';

	}

	echo '<p>Downloading .zip from GitHub … ';
	flush();

	$temp_zip_file = $temp_folder.$source.'.zip';
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


	$module_temp_folder = $temp_folder.$source.'/';
	if( is_dir($module_temp_folder) ) delete_directory($module_temp_folder);
	$oldumask = umask(0); // we need this for permissions of mkdir to be set correctly
	mkdir( $module_temp_folder );
	umask($oldumask); // we need this after changing permissions with mkdir

	$zip = new ZipArchive;
	$res = $zip->open($temp_zip_file);
	if( $res !== TRUE ) {
		echo '<p><strong>Error:</strong> could not extract .zip file</p>';
		return;
	}
	$zip->extractTo( $module_temp_folder );
	$zip->close();

	echo 'done.</p>';

	$subfolder = false;
	foreach( scandir( $module_temp_folder ) as $obj ) {
		if( $obj == '.' || $obj == '..' ) continue;
		if( ! is_dir($module_temp_folder.$obj) ) continue;
		if( ! str_starts_with($obj, $zip_folder_name_start) ) continue;
		// the zip file should have exactly one subfolder. this is what we want to get here
		$subfolder = $module_temp_folder.$obj.'/';
	}

	if( ! $subfolder ) {
		echo '<p><strong>Error:</strong> something went wrong with the .zip file</p>';
		return;
	}

	echo '<p>Moving files to new location … ';

	flush();

	move_folder_to( $subfolder, $target );

	echo 'done.</p>';

	flush();

}


function move_folder_to( $source, $target ){
    if( ! is_dir($target) ) {
    	$oldumask = umask(0); // we need this for permissions of mkdir to be set correctly
    	mkdir( $target, null, true );
    	umask($oldumask); // we need this after changing permissions with mkdir
    }
    rename( $source, $target );
}


function get_remote_json( $url ) {

	global $installer_version, $useragent;

	$ch = curl_init( $url );
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
	curl_setopt( $ch, CURLOPT_USERAGENT, $useragent );
	$response = curl_exec($ch);
	curl_close( $ch );

	$json = json_decode($response);

	return $json;
}


function delete_directory( $dirPath ) {

	if( ! is_dir($dirPath) ) return;

	$objects = scandir($dirPath);
	foreach ($objects as $object) {
		if( $object == "." || $object == "..") continue;

		if( is_dir($dirPath . DIRECTORY_SEPARATOR . $object) ){
			delete_directory($dirPath . DIRECTORY_SEPARATOR . $object);
		} else {
			unlink($dirPath . DIRECTORY_SEPARATOR . $object);
		}
	}
	rmdir($dirPath);
}
