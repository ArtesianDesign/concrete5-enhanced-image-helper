<?php defined('C5_EXECUTE') or die(_('Access Denied.'));

/**********************************************************************************************************************
 *     ______      __                              __   ____                              __  __     __               
 *    / ____/___  / /_  ____ _____  ________  ____/ /  /  _/___ ___  ____ _____ ____     / / / /__  / /___  ___  _____
 *   / __/ / __ \/ __ \/ __ `/ __ \/ ___/ _ \/ __  /   / // __ `__ \/ __ `/ __ `/ _ \   / /_/ / _ \/ / __ \/ _ \/ ___/
 *  / /___/ / / / / / / /_/ / / / / /__/  __/ /_/ /  _/ // / / / / / /_/ / /_/ /  __/  / __  /  __/ / /_/ /  __/ /    
 * /_____/_/ /_/_/ /_/\__,_/_/ /_/\___/\___/\__,_/  /___/_/ /_/ /_/\__,_/\__, /\___/  /_/ /_/\___/_/ .___/\___/_/     
 *                                                                      /____/                    /_/                 
 * --------------------------------------------------------------------------------------------------------------------
 *
 * @package concrete5 enhanced image helper
 * @category Concrete5
 * @author Andrew Householder <andrew@artesiandesigninc.com>
 * @copyright 2013 Artesian Design, Inc & Andrew Householder.
 * 
 */

class ImageHelper extends Concrete5_Helper_Image {

	/** 
	 * Runs getThumbnail on the path, and then prints it out as an XHTML image
	 */
	public function outputThumbnail($obj, $maxWidth, $maxHeight, $alt = null, $return = false, $crop = false) {
		$thumb = $this->getThumbnail($obj, $maxWidth, $maxHeight, $crop);
		if ($alt) {
			$thumb->setAlt($alt);
		}
		if ($return) {
			return $thumb;
		} else {
			$thumb->output();
		}
	}

	public function getThumbnail($obj, $maxWidth, $maxHeight, $crop = false) {
		$data = parent::getThumbnail($obj, $maxWidth, $maxHeight, $crop);
		$data->file = $obj;
		$data->alt = $obj->getTitle(); 
		$thumb = new Thumbnail($data);
		return $thumb;
	}

}

/**
 * Thumbnail class
 * ---------------
 *
 * concrete5 traditionally returns a simple stdClass object with a few properties to facilitate 
 * greater control to the thumbnail output. This class just extends the concept to allow for 
 * custom output via elements and a more flexible, chainable API.
 */
class Thumbnail extends Object {

	/**
	 * Legacy values carried over from the stdClass object created by the ImageHelper previously
	 * - NOTE: These must remain public for backward compatibility
	 */
	public $src, $alt, $file;
	public $width  = 9999; // defaults to no constraint
	public $height = 9999; // defaults to no constraint
	public $crop   = false;

	/**
	 * New options used for rendering.
	 */
	protected $_tag_properties = array(); // stored array of additional key=>value pairs for our <img/> tag
	protected $_is_responsive  = true; // used to disable output of height/width properties. if unset on the constructor, will default to AL_THUMBNAIL_IMG_TAG_RESPONSIVE define
	protected $_element_path   = 'html/img'; // the element partial that will be used to render a given thumbnail

	/**
	 * Our array of gettable/settable properties
	 */
	protected $_valid_properties = array('src', 'alt', 'file', 'width', 'height', 'crop');

	/**
	 * Used to get/set our allowed properties
	 * Verification steps are as follows:
	 * - string begins with "set" or "get" per isValidType()
	 * - remainder is converted to a handle (setWidth => width) and validated 
	 *   against our $_valid_properties array per isValidProperty()
	 * - if both check out, we run the internal get/set method
	 * - always return for chainability
	 */
	public function __call($method, $args) {
		$type = substr($method, 0, 3);
		$property = Loader::helper('text')->uncamelcase(substr($method, 3));
		if ($this->_isValidType($type) && $this->_isValidProperty($property)) {
			switch($type) {
				case 'get':
					return $this->_get($property);
					break;
				case 'set':
					return $this->_set($property, $args[0]);
					break;
			}
		}
	}

	/**
	 * Optional constructor arguments are as such:
	 * - File object
	 * - stdClass object with any of our "valid properties" set (typically src/height/width via legacy method)
	 * @param mixed $options
	 */
	function __construct($options = false) {
		if ($options instanceof File) {
			$this->setFile($options);
		} elseif ($options) {
			$this->setPropertiesFromArray($options);
			// if we have not explicitly set our responsive via this instance, fall back to the config define
			if (!isset($options->is_responsive) && defined('AL_THUMBNAIL_IMG_TAG_RESPONSIVE')) {
				$this->setResponsive((Boolean) AL_THUMBNAIL_IMG_TAG_RESPONSIVE);
			}
		}
	}

	/**
	 * Method that allows us to simply echo our Thumbnail object to get the rendered output
	 * @return [string] html
	 */
	function __toString() {
		return $this->getTag();
	}

	/**
	 * The validate method checks that the minimum requirements are set before outputting the image tag.
	 * - we require either a File object or a src property be set to render a tag
	 * @return [boolean]
	 */
	public function validate() {
		if (is_null($this->getSrc())) {
			if (!is_object($this->getFile())) {
				$this->loadError(t('You must provide either a src attribute or a file object before you can output the tag.'));
			} else {
				$this->update();
			}
		}
		return !$this->isError();
	}

	/**
	 * This method will repopulate the src for the thumbnail from the currently assigned File object and settings
	 * @return [object] Thumbnail
	 */
	public function update() {
		$thumb = Loader::helper('image')->getThumbnail($this->getFile(), $this->getWidth(), $this->getHeight(), $this->getCrop());
		$this->setSrc($thumb->src);
		return $this;
	}

	/**
	 * Validates and then attempts to render the tag with our element inclusion, with a fallback to the legacy string
	 * @return [string] html
	 */
	public function getTag() {
		$this->validate();
		if ($this->isError()) {
			return $this->getError();
		} else {
			ob_start();
				@Loader::element($this->getElementPath(), $this->getAllProperties());
				$tag = ob_get_contents();
			ob_end_clean();
			return ($tag) ? $tag : $this->getLegacyTag();
		}
	}

	/**
	 * This will render the tag as the legacy ImageHelper->outputThumbnail() method 
	 * @return [string] html
	 */
	public function getLegacyTag() {
		$html = '<img class="ccm-output-thumbnail" alt="' . $this->getAlt() . '" src="' . $this->getSrc() . '"';
		if (!$this->isResponsive() && $width = $this->getWidth()) {
			$html .=  ' width="' . $width . '"';
		}
		if (!$this->isResponsive() && $height = $this->getHeight()) {
			$html .=  ' height="' . $height . '"';
		}
		$html .= '/>';
		return $html;
	}

	/**
	 * Renders the final output of the Thumbnail and echos to stdout
	 * @return [object] Thumbnail
	 */
	public function output() {
		echo $this->getTag();
		return $this;
	}

	/**
	 * get property method to be called by the magic function only
	 * @param  [string] $property
	 * @return [mixed]
	 */
	protected function _get($property) {
		if (in_array($property, $this->_valid_properties)) {
			return $this->{$property};
		}
		return null;
	}

	/**
	 * set property method to be called by the magic function only
	 * @param  [string] $property
	 * @return [object] for chaining
	 */
	protected function _set($property, $value) {
		if (in_array($property, $this->_valid_properties)) {
			$this->{$property} = $value;
		}
		return $this;
	}

	/**
	 * Legacy-style convenience method to assign all dimensions at once
	 * @param [int] $width
	 * @param [int] $height
	 * @param [bool] $crop
	 * @return [object] Thumbnail
	 */
	public function setDimensions($width, $height, $crop) {
		$this->setWidth($width);
		$this->setHeight($height);
		$this->setCrop($crop);
		return $this;
	}

	/**
	 * This can be used to override the partial used to render the <img/> tag
	 * - NOTE: Not included in magic method simply to be more verbose about this capability
	 * @example $thumb->setElementPath('html/img_srcset');
	 * @param [string] $_element_path
	 * @return [object] Thumbnail
	 */
	public function setElementPath($element_path) {
		$this->_element_path = $element_path;
		return $this;
	}

	/**
	 * This returns the currently assigned element path
	 * @return [string]
	 */
	public function getElementPath() {
		return $this->_element_path;
	}

	/**
	 * Sets the responsive status of our thumbnail (disables height/width attributes)
	 * @param Boolean $_is_responsive
	 * @return [object] Thumbnail
	 */
	public function setResponsive(Boolean $is_responsive) {
		$this->_is_responsive = $is_responsive;
		return $this;
	}

	/**
	 * This will be used to subvert the output of the width="" and height="" attributes
	 * @return boolean
	 */
	public function isResponsive() {
		return $this->_is_responsive;
	}

	/**
	 * Setter for property setter for the tag attributes to be output
	 * @example $image->setTagProperty('data-value', 1);
	 * @param  [string] $property
	 * @return [object] Thumbnail
	 */
	public function setTagProperty($property, $value) {
		$this->_tag_properties[$property] = $value;
		return $this;
	}

	/**
	 * Return the value set to a tag property
	 * @return [mixed]
	 */
	public function getTagProperty($property) {
		return (isset($this->_tag_properties[$property])) ? $this->_tag_properties[$property] : false;
	}

	/**
	 * This is used to generate our properties for output with the Element render
	 * @return [array] $properties
	 */
	protected function _getAllProperties() {
		// set our base required properties
		$properties = array(
			'src' => $this->getSrc(),
			'alt' => $this->getAlt()
		);
		// if we are not doing responsive images, output width and height
		if (!$this->isResponsive()) {
			$properties['width']  = $this->getWidth();
			$properties['height'] = $this->getHeight();
		}
		// if we have any custom properties set to the image tag, merge that in as well
		if (count($this->_tag_properties)) {
			$properties = array_merge(array('properties' => $this->_getEncodedTagProperties()), $properties);
		}
		return $properties;
	}

	/**
	 * Internal test used to ensure the __call method has a valid prefix
	 * @param  [string]  $type
	 * @return boolean
	 */
	protected function _isValidType($type) {
		return ($type == 'set' || $type == 'get');
	}

	/**
	 * Internal test used to ensure the __call method is attempting to set a valid property
	 * @param  [string]  $property
	 * @return boolean
	 */
	protected function _isValidProperty($property) {
		return (in_array($property, $this->_valid_properties));
	}

	/**
	 * This sanitizes the tag properties that have been set for the tag by JSON encoding non-string types
	 * @return [array]
	 */
	protected function _getEncodedTagProperties() {
		$encoded_properties = array();
		foreach ($this->_tag_properties as $property => $value) {
			if (is_object($value) || is_array($value)) {
				$value = Loader::helper('json')->encode($value);
			} else {
				$value = (string) $value;
			}
			$encoded_properties[$property] = $value;
		}
		return $encoded_properties;
	}

}