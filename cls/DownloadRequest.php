<?php
	abstract class DownloadRequest extends Request {
		
		private $filePath = null; // path to the file to download
		
		protected function setFilePath($path){
			$this->filePath = $path;
			
			if (!file_exists($this->filePath)){
				// throwing an exception here prevents the content type from downloading the file
				// which then only has the error in it
				throw new ApiException(sprintf("Download file '%s' not found.", $this->filePath), ErrorCode::E_FILE_NOT_FOUND);
			}
		}
		
		public function getFilePath(){
			if ($this->filePath == null){
				throw new ApiException("Download file path not set.", ErrorCode::E_NO_FILE_LOCATION);
			} 
			return $this->filePath;
		}
		
		final public function getType(){
			return TYPE::DOWNLOAD;
		}
		
		// don't even allow downloads to have a cache, make it final :)
		final public function canCache(){
			return false;
		}
		
		public function download(){
			// log the download
			$path = $this->getFilePath();
			$sth = $this->getDB()->prepare("INSERT INTO downloads (ip, filename, time)
						VALUES (:ip, :filename, UNIX_TIMESTAMP())");
			$sth->bindValue(":ip", $_SERVER["REMOTE_ADDR"]);
			$sth->bindValue(":filename", $path);
			$sth->execute();
		
			ob_end_flush(); // this seems to help with out of memory errors
			readfile($path);
		}
	}