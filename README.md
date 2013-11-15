concrete5-enhanced-image-helper
===============================

An enhanced image helper class override for concrete5 that provides more modern object-oriented methods and output control. The inspiration for this borne for the desire to provide a way to abstract the `<img/>` rendering into a view, as a responsive image solution is currently on the horizon.

# Installation
- Copy `image.php` into your `SITE_ROOT/helpers/` folder.
- Optionally, copy the included HTML image element into `SITE_ROOT/elements/html/img.php`. This file is ultimately optional, but is required to enable some of our more advanced methods, such as custom tag properties.

# Features
- Robust API for controlling output
- Abstracted view layer

# Usage
As this was meant to be a drop-in replacement for the existing image helper, you don't have to do anything different once this is installed. You can, however, use the helper with a more object-oriented approach.

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
# License

(The MIT License)

Copyright (c) 2013 [Artesian Design Inc](http://artesiandesigninc.com) and [Andrew Householder](http://aghouseh.com)

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.

MIT: http://rem.mit-license.org
