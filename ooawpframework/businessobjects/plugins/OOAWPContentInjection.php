<?php

include_once __DIR__."/OOAWPContentInjectionPostPosition.php";

interface OOAWPContentInjection {

	/**
	 * Get an array of post type positions for this injection.
	 * 
	 * @return [type] [description]
	 */
	public function getPostTypePositions();

	/**
	 * Create the content to inject
	 * 
	 * @return [type] [description]
	 */
	public function createContentModelAndView();


}


?>