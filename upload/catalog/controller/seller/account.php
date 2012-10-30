<?php

class ControllerSellerAccount extends Controller {
	public function __construct($registry) {
		parent::__construct($registry);

		$this->data = array_merge($this->data, $this->load->language('module/multiseller'),$this->load->language('account/account'));
		$parts = explode('/', $this->request->request['route']);

		// Uploadify checks
		//$this->_log = new Log("uploadify.log");
		if (isset($parts[2]) && in_array($parts[2], array('jxUpdateFile','jxUploadImages', 'jxUploadDownloads', 'jxUploadSellerAvatar'))) {
			if (empty($_POST) || empty($_FILES))
				return;
			// Re-create session as Flash doesn't pass session info
	  		if (isset($_POST['session_id'])) {
	  			session_destroy();
	  			$_COOKIE['PHPSESSID'] = $_POST['session_id'];
	  			$registry->set('session', new Session());
	  			//session_start();
	  			if (isset($_SESSION['customer_id'])) {
	  				$salt = $this->MsLoader->MsSeller->getSalt($_SESSION['customer_id']);
	  				if (isset($_POST['token']) && isset($_POST['timestamp']) && $_POST['token'] == md5($salt . $_POST['timestamp'])) {
	  					$this->session->data['customer_id'] = $_SESSION['customer_id'];
	  					$this->customer = new Customer($this->registry);
	  					// todo re-initialize seller object
	  				}
	  			}
	  		}
		}
		
	  	if (!$this->customer->isLogged()) {
	  		$this->session->data['redirect'] = $this->url->link('account/account', '', 'SSL');
	  		$this->redirect($this->url->link('account/login', '', 'SSL')); 
    	} else if (!$this->MsLoader->MsSeller->isSeller()) {
    		if (isset($parts[2]) && !in_array($parts[2], array('sellerinfo','jxsavesellerinfo','jxUploadSellerAvatar'))) {
    			$this->redirect($this->url->link('seller/account-profile', '', 'SSL'));
    		}
    	} else if ($this->MsLoader->MsSeller->getStatus() != MsSeller::STATUS_ACTIVE) {
    		if (isset($parts[2]) && !in_array($parts[2], array('sellerinfo'))) {
    			$this->redirect($this->url->link('seller/account-profile', '', 'SSL'));
    		}
    	}
		
		if (isset($this->session->data['success'])) {
			$this->data['success'] = $this->session->data['success'];
    		unset($this->session->data['success']);
		} else {
			$this->data['success'] = '';
		}
	
		$this->MsLoader->MsHelper->addStyle('multiseller');
		
		if (!isset($this->session->data['multiseller']['files']))
			$this->session->data['multiseller']['files'] = array();
	}
}
?>