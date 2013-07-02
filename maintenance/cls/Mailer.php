<?php
	class Mailer {
	
		private $subject = "";
		private $body = "";
		private $recipients = array();
		
		private $from = null;
		
		private $headers = array();
		
		private $m; // mustache instance
		
		public function __construct(MailAddress $from){
			$this->from = $from;
		
			$this->setHeader(new Header("MIME-Version", "1.0"));
			$this->setHeader(new Header("Content-type", "text/plain; charset=iso-8859-1"));
			$this->setHeader(new Header("From", $this->from));
			$this->setHeader(new Header("Reply-To", $this->from->getAddress()));
			$this->setHeader(new Header("X-Mailer", "PHP/" . phpversion()));
			
			$this->loadMustache();
		}
		
		private function loadMustache(){
			require_once sprintf("../cls/includes/Mustache/Autoloader.php", MBASE_PATH);
			Mustache_Autoloader::register();
			
			$this->m = new Mustache_Engine(array(
						"loader" => new Mustache_Loader_FilesystemLoader("../tpl/mail/")
			));
		}
		
		public function getMustache(){
			return $this->m;
		}
		
		public function addRecipient(MailAddress $r){
			if (!in_array($r, $this->recipients)){
				$this->recipients[] = $r;
			}
		}
		
		public function setSubject($subject){
			$this->subject = $subject;
		}
		
		public function setBody($body){
			$this->body = $body;
		}
		
		public function setHeader(Header $h){
			// replace content if header already exists
			$replaced = false;
			for ($i = 0; $i < count($this->headers) && !$replaced; $i++){
				if ($this->headers[$i]->getKey() == $h->getKey()){
					$this->headers[$i]->setValue($h->getValue());
					$replaced = true;
				}
			}
			// no matches found, add new header
			if (!$replaced){
				$this->headers[] = $h;
			}
		}
		
		public function send(){
			if ($this->subject == ""){
				throw new MException("No subject set for mail.");
			}
			if ($this->body == ""){
				throw new MException("No body set for mail.");
			}
			if (count($this->recipients) == 0){
				throw new MException("No recpients for mail.");
			}
			
			// format recipient string
			$recipientString = implode(", ", $this->recipients);
			
			// format header string
			$headerString = implode("\r\n", $this->headers);
			
			//send mail
			return mail($recipientString, $this->subject, $this->body, $headerString);
		}
	}
	
	class MailAddress {
	
		private $name = "";
		private $address = "";
		
		public function __construct($address, $name = ""){
			$this->name = $name;
			$this->address = $address;
		}
		
		public function getName(){
			return $this->name;
		}
		
		public function getAddress(){
			return $this->address;
		}
		
		public function __toString(){
			if ($this->name == ""){
				return $this->address;
			} else {
				return sprintf("%s <%s>", $this->name, $this->address);
			}
		}
	}