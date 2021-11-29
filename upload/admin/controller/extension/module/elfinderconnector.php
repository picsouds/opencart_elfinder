<?php

class ControllerExtensionModuleelfinderconnector extends Controller {

	public function index () {
		if (in_array($this->request->server['REQUEST_METHOD'],['GET','POST'],true)) {
			if ((isset($this->request->get['cmd'])) || (isset($this->request->post['cmd']))) {
				$this->showElfinderconnector();
			} else { // issu de marketplace/extension
				$this->showElfindeconnectorAdmin();
			}
		}
	}

	public function showElfinderconnector() {
			
		
        $autoload_path = 'view/javascript/elFinder/vendor/autoload.php';
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
				'attributes'	=> array( 
									//array('pattern' => '!^/cache!','hidden' => true),
									array( 'pattern'=>'/.+/', 'hidden'=>(isset($_SERVER['PHP_AUTH_USER']))? false : true ),									
								   )
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
			)
			)
		);	

        // Run elFinder
        $connector = new elFinderConnector(new elFinder($opts));
        $connector->run();  
    } 
    
    public function showElfindeconnectorAdmin() {
    	$this->load->language('extension/module/elfinderconnector');
    	
    	$this->document->setTitle($this->language->get('heading_title'));
    	
    	$this->load->model('setting/setting');
    	
    	if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
    		$this->model_setting_setting->editSetting('module_elfinderconnector', $this->request->post);
    		
    		$this->session->data['success'] = $this->language->get('text_success');
    		
    		$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
    	}
    	
    	if (isset($this->error['warning'])) {
    		$data['error_warning'] = $this->error['warning'];
    	} else {
    		$data['error_warning'] = '';
    	}
    	
    	$data['breadcrumbs'] = array();
    	
    	$data['breadcrumbs'][] = array(
    			'text' => $this->language->get('text_home'),
    			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
    	);
    	
    	$data['breadcrumbs'][] = array(
    			'text' => $this->language->get('text_extension'),
    			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
    	);
    	
    	$data['breadcrumbs'][] = array(
    			'text' => $this->language->get('heading_title'),
    			'href' => $this->url->link('extension/module/elfinder', 'user_token=' . $this->session->data['user_token'], true)
    	);
    	
    	$data['action'] = $this->url->link('extension/module/elfinderconnector', 'user_token=' . $this->session->data['user_token'], true);
    	
    	$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);
    	
    	if (isset($this->request->post['module_elfinderconnector_status'])) {
    		$data['module_elfinderconnector_status'] = $this->request->post['module_elfinderconnector_status'];
    	} else {
    		$data['module_elfinderconnector_status'] = $this->config->get('module_elfinderconnector_status');
    	}
    	
    	$data['header'] = $this->load->controller('common/header');
    	$data['column_left'] = $this->load->controller('common/column_left');
    	$data['footer'] = $this->load->controller('common/footer');
    	
    	$this->response->setOutput($this->load->view('extension/module/elfinderconnectoradmin', $data));
    }
    
    protected function validate() {
    	if (!$this->user->hasPermission('modify', 'extension/module/elfinderconnector')) {
    		$this->error['warning'] = $this->language->get('error_permission');
    	}
    	
    	return !$this->error;
    }
 
    // Activation par défaut
    public function install() {
		$this->load->model('setting/setting');
		$settings['module_elfinderconnector_status'] = 1;
		$this->model_setting_setting->editSetting('module_elfinderconnector', $settings);
	}
 
    public function uninstall() {}

}
