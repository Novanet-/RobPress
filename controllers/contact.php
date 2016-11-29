<?php

class Contact extends Controller {

	public function index($f3) {
		if($this->request->is('post')) {
			extract($this->request->data);
			$from = "From: $from";

			$to = $this->Model->Settings->getSetting('email');	//Hardcore the to parameter, so the user can't edit it within the request
			
			mail($to,$subject,$message,$from);

			StatusMessage::add('Thank you for contacting us');
			return $f3->reroute('/');
		}	
	}

}

?>
