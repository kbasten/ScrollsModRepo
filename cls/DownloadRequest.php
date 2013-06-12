<?php
	abstract class DownloadRequest extends Request {
		
		private $filePath = ""; // path to the file to download
		
		protected function setFilePath($path){
			$this->filePath = $path;
			
			if (!file_exists($this->filePath)){
				// throwing an exception here prevents the content type from downloading the file
				// which then only has the error in it
				throw new ApiException("Download file not found.", 110);
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
	}