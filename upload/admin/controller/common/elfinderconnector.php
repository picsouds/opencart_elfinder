<?php

class Controllercommonelfinderconnector extends Controller {

    public function index() {
		
        $autoload_path = 'view/javascript/elFinder/php/autoload.php';
        $autoload_full_path = DIR_APPLICATION.$autoload_path;

        if (!file_exists($autoload_full_path)) {
            echo 'Error! File '.$autoload_path.' not exists!';
            exit(1);
        }
        require $autoload_full_path;

		function access($attr, $path, $data, $volume, $isDir, $relpath) {
			$basename = basename($path);
			return $basename[0] === '.'                  // if file/folder begins with '.' (dot)
					&& strlen($relpath) !== 1           // but with out volume root
				? !($attr == 'read' || $attr == 'write') // set read+write to false, other (locked+hidden) set to true
				:  null;                                 // else elFinder decide it itself
		}	

		$opts = array(
		// 'debug' => true,
		'roots' => array(
			// Items volume
			array(
				'driver'        => 'LocalFileSystem',           // driver for accessing file system (REQUIRED)
				'path'          => DIR_IMAGE,                 // path to files (REQUIRED)
				'URL'           => '../image', // URL to files (REQUIRED)
				'trashHash'     => 't1_Lw',                     // elFinder's hash of trash folder
				'winHashFix'    => DIRECTORY_SEPARATOR !== '/', // to make hash same to Linux one on windows too
				'uploadDeny'    => array('all'),                // All Mimetypes not allowed to upload
				'uploadAllow'   => array('image', 'text/plain'),// Mimetype `image` and `text/plain` allowed to upload
				'uploadOrder'   => array('deny', 'allow'),      // allowed Mimetype `image` and `text/plain` only
				'accessControl' => 'access',                     // disable and hide dot starting files (OPTIONAL)
				'tmbSize'       => 100,
				'attributes'	=> array( array( 'pattern'=>'/.+/', 'hidden'=>(isset($_SERVER['PHP_AUTH_USER']))? false : true ))
			),
			// Trash volume
			array(
				'id'            => '1',
				'driver'        => 'Trash',
				'path'          => DIR_IMAGE . '.trash',
				'tmbURL'        => './image/.trash/.tmb/',
				'winHashFix'    => DIRECTORY_SEPARATOR !== '/', // to make hash same to Linux one on windows too
				'uploadDeny'    => array('all'),                // Recomend the same settings as the original volume that uses the trash
				'uploadAllow'   => array('image', 'text/plain'),// Same as above
				'uploadOrder'   => array('deny', 'allow'),      // Same as above
				'accessControl' => 'access'                     // Same as above
			)
		)
);
		

        // Run elFinder
        $connector = new elFinderConnector(new elFinder($opts));
        $connector->run();  
    } 

}
