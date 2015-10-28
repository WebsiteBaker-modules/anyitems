Use this snippet in this way:

In a code page:

display_any_items(section_id, items_per_page, number_of_columns, use_lightbox2);

where:
section_id = the section id of your shop page section
items_per_page = number of items to display
number_of_columns = number of columns to display ( 1 = vertical and > 1 = horizontal)
use_lightbox2 (optional) = set 1 for using Lightbox2 to show the item images, default = 0

e.g.:  display_any_items(67, 3, 3);

in your template:

<?php display_any_items(67,3,3); ?>

have fun


