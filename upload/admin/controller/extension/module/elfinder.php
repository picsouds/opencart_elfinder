<?php

class ControllerExtensionModuleelfinder extends Controller
{
    /**
     * Show elFinder
     */
    public function index()
    {
        $this->load->language('extension/module/elfinder');

        $this->document->setTitle($this->language->get('heading_title'));
        
        $this->load->model('setting/module');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('module_id', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
		}
		
        $data['heading_title'] = $this->language->get('heading_title');

        $data['breadcrumbs'] = [];

        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('extension/module/elfinder', 'user_token='.$this->session->data['user_token'], true),
        ];

        $data['breadcrumbs'][] = [
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/module/elfinder', 'user_token='.$this->session->data['user_token'], true),
        ];

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];

            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        }        

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

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
    
    	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/elfinder')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}

}
