<?php
	abstract class DownloadRequest extends Request {
		
		private $filePath = ""; // path to the file to download
		
		protected function setFilePath($path){
			$this->filePath = $path;
		}
		
		public function getFilePath(){
			if ($this->filePath == ""){
				throw new ApiException("Download file path not set.", 109);
			} else if (!file_exists($this->filePath)){
				throw new ApiException("Download file not found.", 110);
			}
			return $this->filePath;
		}
		
		protected function setResult($success){
			parent::setResult($success, "");
		}
		
		public function getType(){
			return TYPE::DOWNLOAD;
		}
	}