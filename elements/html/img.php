<?php defined('C5_EXECUTE') or die(_('Access Denied.'));

/**
 * <img/> tag partial view
 * -----------------------
 * Provides a view layer for the output of our enhanced image object.
 *
 * @package concrete5 enhanced image helper
 * @category Concrete5
 * @author Andrew Householder <andrew@artesiandesigninc.com>
 * @copyright 2013 Artesian Design, Inc & Andrew Householder.
 * 
 */

?>
<img alt="<?php echo $alt; ?>" src="<?php echo $src; ?>" <?php if (isset($width)) { ?>width="<?php echo $width; ?>"<?php } ?> <?php if (isset($height)) { ?>height="<?php echo $height; ?>"<?php } ?>
<?php
if (isset($properties) && count($properties)) {
	foreach ($properties as $property => $value) {
		echo $property; ?>="<?php echo htmlentities($value); ?>"<?php
	}
}
?>/>