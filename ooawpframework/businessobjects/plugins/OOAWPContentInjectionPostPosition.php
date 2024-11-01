<?php


class OOAWPContentInjectionPostPosition {
	
	const POSITION_ABOVE = "above";
	const POSITION_BELOW = "below";
	const POSITION_BOTH = "both";

	private $postType;
	private $position;	


	/**
	 * Construct a post position
	 * 
	 * @param [type] $postType [description]
	 * @param [type] $position [description]
	 */
	public function OOAWPContentInjectionPostPosition($postType, $position){
		$this->postType = $postType;
		$this->position = $position;
	}


    /**
     * Gets the value of postType.
     *
     * @return mixed
     */
    public function getPostType()
    {
        return $this->postType;
    }

    /**
     * Gets the value of position.
     *
     * @return mixed
     */
    public function getPosition()
    {
        return $this->position;
    }
}

?>