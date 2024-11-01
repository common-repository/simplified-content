<?php

class OOAWPPluginLink {
	
	private $label;
	private $url;
	private $append;

	public function OOAWPPluginLink($label, $url, $append = 1){
		$this->label = $label;
		$this->url = $url;
		$this->append = $append;
	}

	
	public function getLabel(){
		return $this->label;
	}

	public function getUrl(){
		return $this->url;
	}

	public function getAppend(){
		return $this->append;
	}

	
}
