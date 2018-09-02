<?php

class ControllerCommonelfinder extends Controller
{
    /**
     * Show elFinder
     */
    public function index()
    {
		
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

        $data['connector_url'] = $this->url->link('common/elfinderconnector', 'user_token='.$this->session->data['user_token']);

        $this->response->setOutput($this->load->view('common/elfinder', $data));
    }

}
