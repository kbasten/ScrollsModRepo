<?php
	abstract class DownloadRequest extends Request {
		
		private $filePath = ""; // path to the file to download
		
		protected function setFilePath($path){
			$this->filePath = $path;
			
			if (!file_exists($this->filePath)){
				// throwing an exception here prevents the content type from downloading the file
				// which then only has the error in it
				throw new ApiException(sprintf("Download file '%s' not found.", $this->filePath), 110);
			}
		}
		
		public function getFilePath(){
			if ($this->filePath == ""){
				throw new ApiException("Download file path not set.", 109);
			} 
			return $this->filePath;
		}
		
		public function getType(){
			return TYPE::DOWNLOAD;
		}
		
		public function download(){
			// log the download
			$path = $this->getFilePath();
			$sth = $this->pdo->prepare("INSERT INTO downloads (ip, filename, time)
						VALUES (?, ?, UNIX_TIMESTAMP())");
			$sth->bindValue(1, $_SERVER["REMOTE_ADDR"]);
			$sth->bindValue(2, $path);
			$sth->execute();
		
			ob_end_flush(); // this seems to help with memory errors
			readfile($path);
		}
	}