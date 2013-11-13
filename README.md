concrete5-enhanced-image-helper
===============================

An enhanced image helper class override for concrete5 that provides more modern object-oriented methods and output control.

# Installation
- Copy `image.php` into your `SITE_ROOT/helpers/` folder.
- Optionally, copy the included HTML image element into `SITE_ROOT/elements/html/img.php`. This file is ultimately optional, but is required to enable some of our more advanced methods, such as custom tag properties.

# Features
- Robust API for controlling output
- Abstracted view layer

# Usage
As this was meant to be a drop-in replacement for the existing image helper, you don't have to do anything different one this is installed. You can, however, use the helper with a more object-oriented approach.

### Instantiating a Thumbnail object
```
$image = Loader::helper('image');
$file = File::getByID(1);

// old method. still valid & works great.
$thumb = $image->getThumbnail($file, 640, 480);

// new method 
$thumb = new Thumbnail($file)->setWidth(640);
```

### Outputting an img tag
```
// legacy method
echo '<img src="' . $thumb->src . '" />';

// our enhanced class includes a toString method that renders our tag automatically
echo $thumb;

// or you can call the method explicitly
$thumb->output();

// you can also set a custom element to alter the output
$thumb->setElementPath('html/img_gallery')->output();
```