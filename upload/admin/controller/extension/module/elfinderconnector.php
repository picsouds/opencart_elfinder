<?php

use Aws\S3\S3Client;
use League\Flysystem\AwsS3v3\AwsS3Adapter;
use League\Flysystem\Filesystem;

class ControllerExtensionModuleelfinderconnector extends Controller {

    public function index() {
			
		
        $autoload_path = 'view/javascript/elFinder/php/autoload.php';
        $autoload_full_path = DIR_APPLICATION.$autoload_path;
        
        if (!file_exists($autoload_full_path)) {
            echo 'Error! File '.$autoload_path.' not exists!';
            exit(1);
        }
        require $autoload_full_path;
        require DIR_APPLICATION.'view/javascript/elFinder/vendor/autoload.php';

		function access($attr, $path, $data, $volume, $isDir, $relpath) {
			$basename = basename($path);
			return $basename[0] === '.'                  // if file/folder begins with '.' (dot)
					&& strlen($relpath) !== 1           // but with out volume root
				? !($attr == 'read' || $attr == 'write') // set read+write to false, other (locked+hidden) set to true
				:  null;                                 // else elFinder decide it itself
		}							

		function logger($cmd, $result, $args, $elfinder, $volume) {
			$log = sprintf('[%s] %s:', date('r'), strtoupper($cmd));
			foreach ($result as $key => $value) {
				if (empty($value)) {
					continue;
				}
				$data = array();
				if (in_array($key, array('error', 'warning'))) {
					array_push($data, implode(' ', $value));
				} else {
					if (is_array($value)) { // changes made to files
						foreach ($value as $file) {
							$filepath = (isset($file['realpath']) ? $file['realpath'] : $elfinder->realpath($file['hash']));
							array_push($data, $filepath);
						}
					} else { // other value (ex. header)
						array_push($data, $value);
					}
				}
				$log .= sprintf(' %s(%s)', $key, implode(', ', $data));
			}
			$log .= "\n";

			$logfile = DIR_LOGS . 'elfinder.log';
			$dir = dirname($logfile);
			if (!is_dir($dir) && !mkdir($dir)) {
				return;
			}
			if (($fp = fopen($logfile, 'a'))) {
				fwrite($fp, $log);
				fclose($fp);
			}
		}

		// Config minio 
		$config_minio = [
				'key' => 'xxxxxxxxxxxxxxxxxxxxxxxxx',
				'secret' => 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx',
				'region' => 'us-east-1',  
				'bucket' => 'xxxxxxxxxxxxxxx',     					
				'endpoint' => 'http://xxx.xxx.xxx.xxx:xxxx/'	//endpoint-url
		];
		
		$client = S3Client::factory([
        	'driver' => 's3',
			'credentials' => [
				'key'    => $config_minio['key'],
				'secret' => $config_minio['secret']
			],
        	'region' =>  $config_minio['region'],
			'version' => 'latest',
         	'bucket_endpoint' => false,
        	'use_path_style_endpoint' => true,
        	'endpoint' => $config_minio['endpoint'],        		
		]);                     
               
		$options = [
				'override_visibility_on_copy' => true
		];
	
		$adapter = new AwsS3Adapter($client,  $config_minio['bucket'], '', $options);	 
		$filesystem = new Filesystem($adapter); 
				
		$opts = array(
		 //'debug' => true,
	     'bind' => array(
	     //	'*' => 'logger',	
		 	'rename duplicate upload rm paste resize' => 'logger',
		 ),
		'roots' => array(
			// Items volume
			array(
				'driver'        => 'LocalFileSystem',           // driver for accessing file system (REQUIRED)
				'path'          => DIR_IMAGE,                   // path to files (REQUIRED)
				'URL'           => '../image',                  // URL to files (REQUIRED)
				'tmbPath'       => DIR_IMAGE . '.LocalFileSystem/.tmb/',
				'quarantine'    => DIR_IMAGE . '.LocalFileSystem/.quarantine',
				'trashHash'     => 't1_Lw',                     // elFinder's hash of trash folder
				'winHashFix'    => DIRECTORY_SEPARATOR !== '/', // to make hash same to Linux one on windows too
				'uploadDeny'    => array('all'),                // All Mimetypes not allowed to upload
				'uploadAllow'   => array('image')				,// Mimetype `image`  allowed to upload
				'uploadOrder'   => array('deny', 'allow'),      // allowed Mimetype `image` and `text/plain` only
				'accessControl' => 'access',                    // disable and hide dot starting files (OPTIONAL)
				'tmbSize'       => 100,
				'attributes'	=> array( array( 'pattern'=>'/.+/', 'hidden'=>(isset($_SERVER['PHP_AUTH_USER']))? false : true ))
			),
			// Trash volume
			array(
				'id'            => '1',
				'driver'        => 'Trash',
				'path'          => DIR_IMAGE . '.Trash',
				'URL'           => '../image/.Trash',           // URL to files (REQUIRED)				
				'tmbPath'       => DIR_IMAGE . '.Trash/.tmb/',
				'winHashFix'    => DIRECTORY_SEPARATOR !== '/', // to make hash same to Linux one on windows too
				'uploadDeny'    => array('all'),                // Recomend the same settings as the original volume that uses the trash
				'uploadAllow'   => array('image'),				// Same as above
				'uploadOrder'   => array('deny', 'allow'),      // Same as above
				'accessControl' => 'access',                    // Same as above,
				'attributes'	=> array( array( 'pattern'=>'/.+/', 'hidden'=>(isset($_SERVER['PHP_AUTH_USER']))? false : true ))
			),
			array (
			    'driver'        => 'Flysystem',
				'filesystem'    => $filesystem,			
				'alias'			=> 'minio '.$config_minio['bucket'],				
				'path'          => '/',							
				'URL'			=>  $config_minio['endpoint'].$config_minio['bucket'],	// bucket need policy public (s3api example https://github.com/minio/minio/issues/1508)	
				'trashHash'     => 't1_Lw',                    	// elFinder's hash of trash folder
				'tmbPath'		=> DIR_IMAGE . '.Flysystem/.tmb',
				'tmbURL'        => '../image/.Flysystem/.tmb/',				
				'winHashFix'    => DIRECTORY_SEPARATOR !== '/', // to make hash same to Linux one on windows too
				'uploadDeny'    => array('all'),                // Recomend the same settings as the original volume that uses the trash
				'uploadAllow'   => array('image'),				// Same as above
				'uploadOrder'   => array('deny', 'allow'),      // Same as above
				'accessControl' => 'access',                    // Same as above,
				'icon'          => './view/javascript/elFinder/img/Amazon-Simple-Storage-Service-S3_Bucket-with-Objects_light-bg.svg',
				'cache'         => false,
				'attributes'    => array( array( 'pattern'=>'/.+/', 'hidden'=>(isset($_SERVER['PHP_AUTH_USER']))? false : true )),
				'tmbSize'       => 100,
				'useRemoteArchive' => true,
				//'disabled'	    => array ('edit'),
			)
			)
		);	

        // Run elFinder
        $connector = new elFinderConnector(new elFinder($opts));
        $connector->run();  
    } 

}
