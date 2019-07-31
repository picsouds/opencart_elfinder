<?php

class ControllerExtensionModuleelfinder extends Controller
{
	
	public function index () {
		if ($this->request->server['REQUEST_METHOD'] == 'GET') {
			if ((isset($this->request->get['target']) || isset($this->request->get['thumb']) || isset($this->request->get['textarea']))) {
				$this->showElfinder();	
			} else {
				$this->showElfinderAdmin();
			}
		} elseif ($this->request->server['REQUEST_METHOD'] == 'POST') {
			$this->showElfinderAdmin();
		}
	}
	
    /**
     * Show elFinder
     */
	public function showElfinder()
    {
				
        $this->load->language('extension/module/elfinder');					
		$data['module_elfinder_status'] = $this->config->get('module_elfinder_status');
		$data['module_elfinderconnector_status'] = $this->config->get('module_elfinderconnector_status');
		
		// Activation obligatoire des modules elfinder et elfinder connection
		if (empty($data['module_elfinder_status']) || empty($data['module_elfinderconnector_status'])) {
			$data['error_actived']   = $this->language->get('error_actived');
			$data['error_actived_elfinder'] = !empty($data['module_elfinder_status']) ? '' : $this->language->get('error_actived_elfinder');
			$data['error_actived_elfinderconnector'] = !empty($data['module_elfinderconnector_status']) ? '' : $this->language->get('error_actived_elfinderconnector');
		} else {
			$data['error_actived'] = '';
			$data['error_actived_elfinder'] = '';
			$data['error_actived_elfinderconnector'] = '';
		}
		
		// Find which protocol to use to pass the full image link back
		if ($this->request->server['HTTPS']) {
			$server = HTTPS_CATALOG;
		} else {
			$server = HTTP_CATALOG;
		}
		
		// Make sure we have the correct directory
		if (isset($this->request->get['directory'])) {
			$directory = rtrim(DIR_IMAGE . 'catalog/' . str_replace('*', '', $this->request->get['directory']), '/');
		} else {
			$directory = DIR_IMAGE . 'catalog';
		}
		       
        $data['user_token'] = $this->session->data['user_token'];
        
        // Return the target ID for the file manager to set the value
		if (isset($this->request->get['target'])) {
			$data['target'] = $this->request->get['target'];
		} else {
			$data['target'] = '';
		}

		// Return the thumbnail for the file manager to show a thumbnail
		if (isset($this->request->get['thumb'])) {
			$data['thumb'] = $this->request->get['thumb'];
		} else {
			$data['thumb'] = '';
		}

		// Return the image for the file manager to insert in summernote
		if (isset($this->request->get['textarea'])) {
			$data['textarea'] = $this->request->get['textarea'];
		} else {
			$data['textarea'] = '';
		}

        $data['connector_url'] = $this->url->link('extension/module/elfinderconnector', 'user_token='.$this->session->data['user_token']);
        $this->response->setOutput($this->load->view('extension/module/elfinder', $data));
    }
    
    public function showElfinderAdmin() {
    	$this->load->language('extension/module/elfinder');
    	
    	$this->document->setTitle($this->language->get('heading_title'));
    	
    	$this->load->model('setting/setting');
    	
    	if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
    		$this->model_setting_setting->editSetting('module_elfinder', $this->request->post);
    		
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
    	
    	$data['action'] = $this->url->link('extension/module/elfinder', 'user_token=' . $this->session->data['user_token'], true);
    	
    	$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);
    	
    	if (isset($this->request->post['module_elfinder_status'])) {
    		$data['module_elfinder_status'] = $this->request->post['module_elfinder_status'];
    	} else {
    		$data['module_elfinder_status'] = $this->config->get('module_elfinder_status');
    	}
    	
    	$data['header'] = $this->load->controller('common/header');
    	$data['column_left'] = $this->load->controller('common/column_left');
    	$data['footer'] = $this->load->controller('common/footer');
    	
    	$this->response->setOutput($this->load->view('extension/module/elfinderadmin', $data));
    }    
    
    protected function validate() {
    	if (!$this->user->hasPermission('modify', 'extension/module/elfinder')) {
    		$this->error['warning'] = $this->language->get('error_permission');
    	}
    	
    	return !$this->error;
    }
 
	// Activation par défaut
    public function install() {
		$this->load->model('setting/setting');
		$settings['module_elfinder_status'] = 1;
		$this->model_setting_setting->editSetting('module_elfinder', $settings);
	}
 
    public function uninstall() {}

}
