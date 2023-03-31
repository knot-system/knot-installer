<?php

$installer_version = '0.1.0';

$php_min_version_major = 8;

$sources = [
	'eigenheim' => [
		'zipball' => 'https://api.github.com/repos/maxhaesslein/eigenheim/releases',
		'target' => 'eigenheim/',
	],
	'sekretaer' => [
		'zipball' => 'https://api.github.com/repos/maxhaesslein/sekretaer/releases',
		'target' => 'sekretaer/',
	],
	'postamt' => [
		'zipball' => 'https://api.github.com/repos/maxhaesslein/postamt/releases',
		'target' => 'postamt/',
	]
];

$useragent = 'maxhaesslein/homestead/'.$installer_version;



$local_phpversion = explode( '.', phpversion() );

if( $local_phpversion[0] < $php_min_version_major ) {
	echo '<strong>Error:</strong> your PHP version is too old (you need at least PHP '.$php_min_version_major.')';
	exit;
}

$self = basename(__FILE__);

$abspath = realpath(dirname(__FILE__)).'/';
$abspath = preg_replace( '/system\/$/', '', $abspath );

$basefolder = str_replace( $self, '', $_SERVER['PHP_SELF']);

if( isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ) $baseurl = 'https://';
else $baseurl = 'http://';
$baseurl .= $_SERVER['HTTP_HOST'];
$baseurl .= $basefolder;


if( is_dir($abspath.'eigenheim/') || is_dir($abspath.'postamt/') || is_dir($abspath.'sekretaer/') ) {
	echo '<strong>Error:</strong> it looks like homestead is already installed at this location!';
	exit;
}


$temp_folder = $abspath.'tmp/';
if( ! is_dir($temp_folder) ) {
	if( mkdir( $temp_folder, 0777, true ) === false ) {
		echo 'could not create temp folder';
		exit;
	}
}

?><!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width,initial-scale=1.0">
	<title>🏡 Homestead Installer</title>

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
	</style>
</head>
<body>
<main style="max-width: 600px; margin: 0 auto">
<?php

if( ! isset($_POST['action'])
 || $_POST['action'] != 'install'
 || empty($_POST['eigenheim'])
 || empty($_POST['eigenheim']['auth_mail'])
 || empty($_POST['eigenheim']['author_name'])
 || empty($_POST['eigenheim']['site_title'])
) {
	?>

	<h1>🏡 Homestead Installer</h1>

	<p>This script will install all modules required for a full <strong>Homestead</strong> installation. These modules are:</p>
	<ul>
		<li><strong>Eigenheim</strong> as the website (and micropub server), that visitors will see</li>
		<li><strong>Sekretär</strong> as the micropub & microsub client, where you can write new posts and read posts from websites you follow</li>
		<li><strong>Postamt</strong> as the microsub server, that will manage the websites you follow and collect new posts they publish</li>
	</ul>

	<p>Your server should meet all the necessary requirements.</p>

	<form id="install_form" method="POST" action="<?= $self ?>">

		<fieldset>
			<legend>Settings</legend>
	
			<p>You need to provide some basic information before we can begin the installation:</p>

			<p><label><strong>Site Title</strong><br><input type="text" name="eigenheim[site_title]" required><br><small>(the title of your website)</small></label></p>
			<p><label><strong>Author Name</strong><br><input type="text" name="eigenheim[author_name]" required><br><small>(your name, displayed on your website)</small></label></p>
			<p><label><strong>Authorization Mail</strong><br><input type="email" name="eigenheim[auth_mail]" required><br><small>(your email address; this is were we send the login token to, when you log into the Sekretär backend. It is not displayed publicly, but is added to the Eigenheim HTML source code. This option will be removed later, when we have our own authorization module)</small></label></p>
			<p><label><input type="checkbox" name="eigenheim[testcontent]" value="true" checked> create Eigenheim test content<br><small>(add some test content to your website, so you can check that everything works; this is optional)</small></label>

		</fieldset>


		<fieldset class="unimportant">

			<legend>Environment Variables</legend>
			<p>if something goes wrong, this helps to debug the issue:</p>
			<ul>
				<li>ABSPATH: <em><?= $abspath ?></em></li>
				<li>BASEFOLDER: <em><?= $basefolder ?></em></li>
				<li>BASEURL: <em><?= $baseurl ?></em></li>
			</ul>

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

	foreach( $sources as $source => $source_info ) {

		flush();

		echo '<h3>Installing '.$source.'</h3>';

		$target_folder = $abspath.$source_info['target'];

		install_module( $source, $target_folder );

		flush();

		$config = [];
		if( ! empty($_POST[$source]) ) {
			$config = $_POST[$source];
		}

		// automatically set some additional options:
		$config['setup'] = true;
		if( $source == 'eigenheim') {
			$config['baseurl_overwrite'] = $baseurl;
			$config['basefolder_overwrite'] = $basefolder;
			$config['microsub'] = $baseurl.'postamt/';
		} elseif( $source == 'sekretaer' ) {
			$config['authorized_urls'] = $baseurl;
			$config['start'] = true;
		} elseif( $source == 'postamt' ) {
			$config['authorized_urls'] = $baseurl;
			$config['refresh_on_connect'] = true;
		}


		// call setup of this module to create the config
		$setup_url = $baseurl.$source_info['target'].'index.php';

		echo '<p>updating confing … ';
		flush();

		post_request( $setup_url, $config );

		echo 'done.</p>';

	}

	flush();

	$content = "<IfModule mod_rewrite.c>\r\nRewriteEngine on\r\nRewriteBase ".$basefolder."\r\n\r\nRewriteCond %{REQUEST_FILENAME} !-d\r\nRewriteCond %{REQUEST_FILENAME} !-f\r\nRewriteRule (.*) eigenheim/$1 [L,QSA]\r\nRewriteRule ^$ eigenheim/ [L,QSA]\r\n\r\n</IfModule>";
	file_put_contents( $abspath.'.htaccess', $content );

	$cookie_lifetime = 60*60*24*10; // 10 days, in seconds
	setcookie( 'sekretaer-url', $baseurl, array(
		'expires' => time()+$cookie_lifetime,
		'path' => '/'
	));

	echo '<h3>cleaning up</h3>';
	flush();

	delete_directory($temp_folder);

	unlink( $abspath.'install.php' );

	echo '<p>all done. please <a href="'.$baseurl.'">refresh this page</a></p>';

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


function install_module( $source, $target ) {

	global $temp_folder, $sources, $useragent;

	$zipball = $sources[$source]['zipball'];

	$json = get_remote_json( $zipball );
	if( ! $json || ! is_array($json) || ! count($json) ) {
		?>
		<p><strong>Error:</strong> could not get release information from GitHub</p>
		<?php
		exit;
	}

	$latest_release = $json[0];

	if( empty($latest_release->zipball_url) ) {
		?>
		<p><strong>Error:</strong> could not get zip download url from GitHub</p>
		<?php
		exit;	
	}
	$zipball = $latest_release->zipball_url;

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
	mkdir( $module_temp_folder );

	$zip = new ZipArchive;
	$res = $zip->open($temp_zip_file);
	if( $res !== TRUE ) {
		echo '<p><strong>Error:</strong> could not extract .zip file</p>';
		exit;
	}
	$zip->extractTo( $module_temp_folder );
	$zip->close();

	echo 'done.</p>';

	$subfolder = false;
	foreach( scandir( $module_temp_folder ) as $obj ) {
		if( $obj == '.' || $obj == '..' ) continue;
		if( ! is_dir($module_temp_folder.$obj) ) continue;
		if( ! str_starts_with($obj, 'maxhaesslein-'.$source.'-') ) continue;
		// the zip file should have exactly one subfolder, called 'maxhaesslein-{source}-{hash}'. this is what we want to get here
		$subfolder = $module_temp_folder.$obj.'/';
	}

	if( ! $subfolder ) {
		echo '<p><strong>Error:</strong> something went wrong with the .zip file</p>';
		exit;
	}

	echo '<p>Moving files to new location … ';

	flush();

	move_folder_to( $subfolder, $target );

	echo 'done.</p>';

	flush();

}


function move_folder_to( $source, $target ){
    if( ! is_dir($target) ) {
    	mkdir( $target, null, true );
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
