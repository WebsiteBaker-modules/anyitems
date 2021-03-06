<?php

/*
  Module developed for the Open Source Content Management System WebsiteBaker (http://websitebaker.org)
  Copyright (C) 2011, Christoph Marti

  LICENCE TERMS:
  This module is free software. You can redistribute it and/or modify it 
  under the terms of the GNU General Public License - version 2 or later, 
  as published by the Free Software Foundation: http://www.gnu.org/licenses/gpl.html.

  DISCLAIMER:
  This module is distributed in the hope that it will be useful, 
  but WITHOUT ANY WARRANTY; without even the implied warranty of 
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the 
  GNU General Public License for more details.

 -----------------------------------------------------------------------------------------
 
  Code snippet Anyitems for Bakery 1.3.x or later and Website Baker v2.7 or later

 -----------------------------------------------------------------------------------------
*/

// Function to display any bakery items on every page via (invoke function from template or code page)
if (!function_exists('display_any_items')) {
	function display_any_items($section_id, $items_per_page, $num_cols, $use_lightbox2 = 0) {

		// Look for language file
		if(LANGUAGE_LOADED && !isset($MOD_BAKERY)) {
			include(WB_PATH.'/languages/EN.php');
			if(file_exists(WB_PATH.'/languages/'.LANGUAGE.'.php')) {
				include(WB_PATH.'/languages/'.LANGUAGE.'.php');
			}
			include(WB_PATH.'/modules/bakery/languages/EN.php');
			if(file_exists(WB_PATH.'/modules/bakery/languages/'.LANGUAGE.'.php')) {
				include(WB_PATH.'/modules/bakery/languages/'.LANGUAGE.'.php');
			}
		}



		// MAKE YOUR MODIFICATIONS TO THE LAYOUT OF THE ITEMS DISPLAYED
		// ************************************************************
		
		// Uncomment the lines below if you want to use a different html template than saved in the page settings
		
/*
		// Unlock this template data
		$use_different_template = 1;
		
		// Use this html for the layout
		$different_header = '<table cellpadding="5" cellspacing="0" border="0" width="98%"><tr>';
		
		$different_item_loop = '<td class="mod_bakery_main_td_f">
		[THUMB]
		<br />
		<a href="[LINK]"><span class="mod_bakery_main_title_f">[TITLE]</span></a>
		<br />
		[DESCRIPTION]
		<br />
		[TXT_PRICE]: [CURRENCY] [PRICE]
		<br />
		[TXT_STOCK]: [STOCK]
		<br />
		<form action="[SHOP_URL]" method="post">
		[OPTION]
		<br />
		<input type="text" name="item[ITEM_ID]" class="mod_bakery_main_input_f" value="1" size="2" />
		<input type="submit" name="cart" class="mod_bakery_bt_add_f" value="[ADD_TO_CART]" />
		</form>
		</td>';
			
		$different_footer = '</tr>
		</table>
		<table cellpadding="0" cellspacing="0" border="0" width="98%" style="display: [DISPLAY_PREVIOUS_NEXT_LINKS]">
		<tr>
		<td colspan="3" align="left"><hr /></td>
		</tr>
		<tr>
		<td width="35%" align="left">[PREVIOUS_PAGE_LINK]</td>
		<td width="30%" align="center">[TXT_ITEM] [OF] </td>
		<td width="35%" align="right">[NEXT_PAGE_LINK]</td>
		</tr>
		</table>';	
				
		// End layout html	
*/



		// DO NOT CHANGE ANYTHING BEYOND THIS LINE UNLESS YOU KNOW WHAT YOU ARE DOING
		// **************************************************************************
				
		global $database;

		// Look for CSS
		echo "\n<style type='text/css'>";
		include(WB_PATH .'/modules/bakery/frontend.css');
		echo "\n</style>\n";

		// Check if there is a start point defined
		if (isset($_GET['p']) AND is_numeric($_GET['p']) AND $_GET['p'] >= 0) {
			$position = $_GET['p'];
		} else {
			$position = 0;
		}

		// Get page settings
		$query_settings = $database->query("SELECT * FROM ".TABLE_PREFIX."mod_bakery_page_settings WHERE section_id = '$section_id' ");
		if ($query_settings->numRows() > 0) {
			$fetch_settings = $query_settings->fetchRow();
			$setting_resize = stripslashes($fetch_settings['resize']);
			if (isset($use_different_template) && $use_different_template == 1) {
				$setting_header = $different_header;
				$setting_item_loop = $different_item_loop;
				$setting_footer = $different_footer;
			} else {
				$setting_header = stripslashes($fetch_settings['header']);
				$setting_item_loop = stripslashes($fetch_settings['item_loop']);
				$setting_footer = stripslashes($fetch_settings['footer']);
			}
			// Get continue url
			$query_continue_url = $database->query("SELECT p.link FROM ".TABLE_PREFIX."pages p INNER JOIN ".TABLE_PREFIX."mod_bakery_page_settings ps ON p.page_id = ps.page_id WHERE p.page_id = ps.continue_url AND ps.section_id = '$section_id'");
			if ($query_continue_url->numRows() > 0) {
				$fetch_continue_url = $query_continue_url->fetchRow();
				$setting_continue_url = WB_URL.PAGES_DIRECTORY.stripslashes($fetch_continue_url['link']).PAGE_EXTENSION;
			}

		} else {
			$setting_continue_url = '';
			$setting_header = '';
			$setting_item_loop = '';
			$setting_footer = '';
			$setting_resize = '';
			echo "The section id $section_id you have provided was not found in the database.";
		}

		// Get general settings
		$query_settings = $database->query("SELECT * FROM ".TABLE_PREFIX."mod_bakery_general_settings");
		if ($query_settings->numRows() > 0) {
			$fetch_settings = $query_settings->fetchRow();
			$setting_definable_field_0 = stripslashes($fetch_settings['definable_field_0']);
			$setting_definable_field_1 = stripslashes($fetch_settings['definable_field_1']);
			$setting_definable_field_2 = stripslashes($fetch_settings['definable_field_2']);
			$setting_stock_mode = stripslashes($fetch_settings['stock_mode']);
			$setting_stock_limit = stripslashes($fetch_settings['stock_limit']);
			$setting_shop_currency = stripslashes($fetch_settings['shop_currency']);
			$setting_shipping_domestic = stripslashes($fetch_settings['shipping_domestic']);
			$setting_shipping_abroad = stripslashes($fetch_settings['shipping_abroad']);
			$setting_shipping_d_a = $setting_shipping_domestic."/".$setting_shipping_abroad;
		}
		
		// Settings of the anyitems function
		$setting_items_per_page = $items_per_page;
		$setting_num_cols = $num_cols;

		// If requested include lightbox2 (css is appended to the frontend.css stylesheet)
		if ($use_lightbox2 == 1) {
			?>
			<script type="text/javascript">window.jQuery || document.write('<script src="http://localhost/wb283/modules/bakery/jquery/jquery-1.7.2.min.js"><\/script>')</script>
			<script type="text/javascript" src="http://localhost/wb283/modules/bakery/lightbox2/js/lightbox.js"></script>
			<script type="text/javascript">
			//  Lightbox2 options
			$(function () {
			    var lightbox, options;
			    options = new LightboxOptions;
		
			    options.fileLoadingImage = 'http://localhost/wb283/modules/bakery/lightbox2/images/loading.gif';
			    options.fileCloseImage   = 'http://localhost/wb283/modules/bakery/lightbox2/images/close.png';
			    options.labelImage       = 'Bild';
			    options.labelOf          = 'von';
		
			    return lightbox          = new Lightbox(options);
			});
			</script>
			<?php
		}

		// Get prictures
		$imgs = array();
		$query_images = $database->query("SELECT * FROM ".TABLE_PREFIX."mod_bakery_images WHERE position = '1' AND active = '1'");
		while ($img = $query_images->fetchRow()) {
			$imgs[$img['item_id']] = $img;
		}

		// Get total number of items
		$query_total_num = $database->query("SELECT item_id FROM ".TABLE_PREFIX."mod_bakery_items WHERE section_id = '$section_id' AND active = '1' AND title != ''");
		$total_num = $query_total_num->numRows();
	
		// Work-out if we need to add limit code to sql
		if ($setting_items_per_page != 0) {
			$limit_sql = " LIMIT $position,$setting_items_per_page";
		} else {
			$limit_sql = "";
		}
		
		// Query items (for this page)
		$query_items = $database->query("SELECT * FROM ".TABLE_PREFIX."mod_bakery_items WHERE section_id = '$section_id' AND active = '1' AND title != '' ORDER BY position ASC".$limit_sql);
		$num_items = $query_items->numRows();
		
		// Create previous and next links
		if ($setting_items_per_page != 0) {
			if ($position > 0) {
				if (isset($_GET['g']) AND is_numeric($_GET['g'])) {
					$pl_prepend = '<a href="?p='.($position-$setting_items_per_page).'&g='.$_GET['g'].'"><< ';
				} else {
					$pl_prepend = '<a href="?p='.($position-$setting_items_per_page).'"><< ';
				}
				$pl_append = '</a>';
				$previous_link = $pl_prepend.$GLOBALS['TEXT']['PREVIOUS'].$pl_append;
				$previous_page_link = $pl_prepend.$GLOBALS['TEXT']['PREVIOUS_PAGE'].$pl_append;
			} else {
				$previous_link = '';
				$previous_page_link = '';
			}
			if ($position+$setting_items_per_page >= $total_num) {
				$next_link = '';
				$next_page_link = '';
			} else {
				if(isset($_GET['g']) AND is_numeric($_GET['g'])) {
					$nl_prepend = '<a href="?p='.($position+$setting_items_per_page).'&g='.$_GET['g'].'"> ';
				} else {
					$nl_prepend = '<a href="?p='.($position+$setting_items_per_page).'"> ';
				}
				$nl_append = ' >></a>';
				$next_link = $nl_prepend.$GLOBALS['TEXT']['NEXT'].$nl_append;
				$next_page_link = $nl_prepend.$GLOBALS['TEXT']['NEXT_PAGE'].$nl_append;
			}
			if ($position+$setting_items_per_page > $total_num) {
				$num_of = $position+$num_items;
			} else {
				$num_of = $position+$setting_items_per_page;
			}
			$out_of = ($position+1).'-'.$num_of.' '.strtolower($GLOBALS['TEXT']['OUT_OF']).' '.$total_num;
			$of = ($position+1).'-'.$num_of.' '.strtolower($GLOBALS['TEXT']['OF']).' '.$total_num;
			$display_previous_next_links = '';
		} else {
			$display_previous_next_links = 'none';
		}
			
		// Print header
		if ($display_previous_next_links == 'none') {
			echo  str_replace(array('[SHOP_URL]','[VIEW_CART]','[NEXT_PAGE_LINK]','[NEXT_LINK]','[PREVIOUS_PAGE_LINK]','[PREVIOUS_LINK]','[OUT_OF]','[OF]','[DISPLAY_PREVIOUS_NEXT_LINKS]','[TXT_ITEM]'), array($setting_continue_url, $MOD_BAKERY['TXT_VIEW_CART'],'','','','','','', $display_previous_next_links, $MOD_BAKERY['TXT_ITEM']), $setting_header);
		} else {
			echo str_replace(array('[SHOP_URL]','[VIEW_CART]','[NEXT_PAGE_LINK]','[NEXT_LINK]','[PREVIOUS_PAGE_LINK]','[PREVIOUS_LINK]','[OUT_OF]','[OF]','[DISPLAY_PREVIOUS_NEXT_LINKS]','[TXT_ITEM]'), array($setting_continue_url, $MOD_BAKERY['TXT_VIEW_CART'], $next_page_link, $next_link, $previous_page_link, $previous_link, $out_of, $of, $display_previous_next_links, $MOD_BAKERY['TXT_ITEM']), $setting_header);
		}

		// Loop through and show items
		if ($num_items > 0) {
			$counter = 0;
			while ($item = $query_items->fetchRow()) {
				$item_id = stripslashes($item['item_id']);
				$title = htmlspecialchars(stripslashes($item['title']));
				$uid = $item['modified_by']; // User who last modified the item
				// Workout date and time of last modified item
				$item_date = gmdate(DATE_FORMAT, $item['modified_when']+TIMEZONE);
				$item_time = gmdate(TIME_FORMAT, $item['modified_when']+TIMEZONE);
				// Work-out the item link
				$item_link = WB_URL.PAGES_DIRECTORY.$item['link'].PAGE_EXTENSION;
				if (isset($_GET['p']) AND $position > 0) {
					$item_link .= '?p='.$position;
				}
				if (isset($_GET['g']) AND is_numeric($_GET['g'])) {
					if(isset($_GET['p']) AND $position > 0) { $item_link .= '&'; } else { $item_link .= '?'; }
					$item_link .= 'g='.$_GET['g'];
				}


				// Item thumb(s) and image(s)
				// Initialize or reset thumb(s) and image(s) befor laoding next item
				$thumb_arr = array();
				$image_arr = array();
				$thumb = "";
				$image = "";
							
				// Prepare thumb and image directory pathes and urls
				$thumb_dir = WB_PATH.MEDIA_DIRECTORY.'/bakery/thumbs/item'.$item_id.'/';
				$img_dir   = WB_PATH.MEDIA_DIRECTORY.'/bakery/images/item'.$item_id.'/';
				$thumb_url = WB_URL.MEDIA_DIRECTORY.'/bakery/thumbs/item'.$item_id.'/';
				$img_url   = WB_URL.MEDIA_DIRECTORY.'/bakery/images/item'.$item_id.'/';
				
				// Check if the thumb and image directories exist
				if(is_dir($thumb_dir) && is_dir($img_dir)) {
					// Open the image directory then loop through its contents
					$dir = dir($img_dir);
					while (false !== $image_file = $dir->read()) {
						// Skip index file and pointers
						if (stripos($image_file, ".php") !== false || substr($image_file, 0, 1) == ".") {
							continue;
						}
						// Thumbs use .jpg extension only
						$thumb_file = str_replace(".png", ".jpg", $image_file);
						
						// Convert filename to lightbox2 title
						$img_title = str_replace(array(".png", ".jpg"), "", $image_file);
						$img_title = str_replace("_", " ", $img_title);
	
						// Make array of all item thumbs and images
						if (file_exists($thumb_dir.$thumb_file) && file_exists($img_dir.$image_file)) {
							// If needed add lightbox2 link to the thumb/image...
							if ($use_lightbox2 == 1) {
								$thumb_prepend = "<a href='".$img_url.$image_file."' rel='lightbox[image_".$item_id."]' title='".$img_title."'><img src='";
								$img_prepend = "<a href='".$img_url.$image_file."' rel='lightbox[image_".$item_id."]' title='".$img_title."'><img src='";
								$thumb_append = "' alt='".$img_title."' title='".$img_title."' class='mod_bakery_main_thumb_f' /></a>";
								$img_append = "' alt='".$img_title."' title='".$img_title."' class='mod_bakery_main_img_f' /></a>";
							// ...else add thumb/image only
							} else {
								$thumb_prepend = "<a href='".$item_link."'><img src='";
								$img_prepend = "<img src='";
								$thumb_append = "' alt='".$img_title."' title='".$img_title."' class='mod_bakery_main_thumb_f' />";
								$img_append = "' alt='".$img_title."' title='".$img_title."' class='mod_bakery_main_img_f' />";
							}
							// Check if a main thumb/image is set
							//if ($image_file == $item['main_image']) {
							if (isset($imgs[$item_id])) {
								$thumb = $thumb_prepend.$thumb_url.$thumb_file.$img_append;
								$image = $thumb_prepend.$img_url.$imgs[$item_id]['filename'].$img_append;
								continue;
							}
							// Make array
							$thumb_arr[] = $thumb_prepend.$thumb_url.$thumb_file.$thumb_append;
							$image_arr[] = $img_prepend.$img_url.$image_file.$img_append;
						}
					}
				}
				
				// Make strings for use in the item templates
				$thumbs = implode("\n", $thumb_arr);
				$images = implode("\n", $image_arr);
	
	
	
				// Show item options and attributes if we have to
				
				// Initialize vars
				$option = "";
				$option_select = "";

				// Get number of item options and loop through them
				$query_num_options = $database->query("SELECT DISTINCT o.option_name, ia.option_id FROM ".TABLE_PREFIX."mod_bakery_options o INNER JOIN ".TABLE_PREFIX."mod_bakery_item_attributes ia ON o.option_id = ia.option_id WHERE ia.item_id = $item_id");			
				if ($query_num_options->numRows() > 0) {
					while ($num_options = $query_num_options->fetchRow()) {
						$option_name = stripslashes($num_options['option_name']);
						$option_id = stripslashes($num_options['option_id']);
	
						// Get item attributes
						$query_attributes = $database->query("SELECT o.option_name, a.attribute_name, ia.attribute_id, ia.price, ia.operator FROM ".TABLE_PREFIX."mod_bakery_options o INNER JOIN ".TABLE_PREFIX."mod_bakery_attributes a ON o.option_id = a.option_id INNER JOIN ".TABLE_PREFIX."mod_bakery_item_attributes ia ON a.attribute_id = ia.attribute_id WHERE item_id = $item_id AND ia.option_id = '$option_id' ORDER BY o.option_name, a.attribute_name ASC");
						if ($query_attributes->numRows() > 0) {
							$option_select .= $option_name.": <select name='attribute[]' class='mod_bakery_main_select_f'>"; 
							while ($attributes = $query_attributes->fetchRow()) {
								array_map('stripslashes', $attributes);
								// Make attribute select
								$ia_price = " (".$setting_shop_currency." ".$attributes['operator'].$attributes['price'].")";
								$ia_price = $attributes['price'] == 0 ? "" : $ia_price;
								$option_select .= "<option value='{$attributes['attribute_id']}'>{$attributes['attribute_name']}$ia_price</option>\n";
							}
							$option_select .= "</select><br />";
							$option = $option_select;
						}
					}
				}
	
				// Check if we should show number of items or "in stock" message or nothing at all
				$item_stock = stripslashes($item['stock']);
				// Display number of items
				if ($setting_stock_mode == "number") {
					if ($item_stock < 1) {
						$stock = 0;
					} else {
						$stock = $item_stock;
					}
				// Display text message	
				} elseif ($setting_stock_mode == "text" && is_numeric($setting_stock_limit) && $setting_stock_limit != "") {
					if ($item_stock < 1) {
						$stock = "<span class='mod_bakery_item_out_of_stock_f'>".$MOD_BAKERY['TXT_OUT_OF_STOCK']."</span>";
					} elseif ($item_stock > $setting_stock_limit) {
						$stock = "<span class='mod_bakery_item_in_stock_f'>".$MOD_BAKERY['TXT_IN_STOCK']."</span>";
					} else {
						$stock = "<span class='mod_bakery_item_short_of_stock_f'>".$MOD_BAKERY['TXT_SHORT_OF_STOCK']."</span>";
					}
				} else {
					$stock = "";
				}
	
				// Replace vars with values
				$vars = array('[ADD_TO_CART]', '[PAGE_TITLE]', '[THUMB]', '[THUMBS]', '[IMAGE]', '[IMAGES]', '[TITLE]', '[ITEM_ID]', '[SKU]', '[STOCK]', '[PRICE]', '[SHIPPING]', '[FIELD_1]', '[FIELD_2]', '[FIELD_3]', '[OPTION]', '[DESCRIPTION]', '[FULL_DESC]', '[SHOP_URL]', '[SHIPPING_D_A]', '[CURRENCY]', '[LINK]', '[DATE]', '[TIME]', '[USER_ID]', '[USERNAME]', '[DISPLAY_NAME]', '[EMAIL]', '[TEXT_READ_MORE]', '[TXT_ITEM]', '[TXT_PRICE]', '[TXT_STOCK]', '[TXT_FIELD_1]', '[TXT_FIELD_2]', '[TXT_FIELD_3]');
				if (isset($users[$uid]['username']) AND $users[$uid]['username'] != '') {
					$values = array($MOD_BAKERY['TXT_ADD_TO_CART'], PAGE_TITLE, $thumb, $thumbs, $image, $images, stripslashes($item['title']), $item_id, stripslashes($item['sku']), $stock, stripslashes($item['price']), stripslashes($item['shipping']), stripslashes($item['definable_field_0']), stripslashes($item['definable_field_1']), stripslashes($item['definable_field_2']), $option, stripslashes($item['description']), stripslashes($item['full_desc']), $setting_continue_url, $setting_shipping_d_a, $setting_shop_currency, $item_link, $item_date, $item_time, $uid, $users[$uid]['username'], $users[$uid]['display_name'], $users[$uid]['email'], $GLOBALS['TEXT']['READ_MORE'], $MOD_BAKERY['TXT_ITEM'], $MOD_BAKERY['TXT_PRICE'], $MOD_BAKERY['TXT_STOCK'], $setting_definable_field_0, $setting_definable_field_1, $setting_definable_field_2);
				} else {
					$values = array($MOD_BAKERY['TXT_ADD_TO_CART'], PAGE_TITLE, $thumb, $thumbs, $image, $images, stripslashes($item['title']), $item_id, stripslashes($item['sku']), $stock, stripslashes($item['price']), stripslashes($item['shipping']), stripslashes($item['definable_field_0']), stripslashes($item['definable_field_1']), stripslashes($item['definable_field_2']), $option, stripslashes($item['description']), stripslashes($item['full_desc']), $setting_continue_url, $setting_shipping_d_a, $setting_shop_currency, $item_link, $item_date, $item_time, '', '', '', '', $GLOBALS['TEXT']['READ_MORE'], $MOD_BAKERY['TXT_ITEM'], $MOD_BAKERY['TXT_PRICE'], $MOD_BAKERY['TXT_STOCK'], $setting_definable_field_0, $setting_definable_field_1, $setting_definable_field_2);
				}
				echo str_replace($vars, $values, $setting_item_loop);
				
				// Increment counter
				$counter = $counter + 1;
				// Check if we should end this row
				if ($counter % $setting_num_cols == 0 && $counter != $num_items) {
					echo "</tr><tr>";
				}
			}
		}
		
		// Print footer
		if ($display_previous_next_links == 'none') {
			echo  str_replace(array('[NEXT_PAGE_LINK]','[NEXT_LINK]','[PREVIOUS_PAGE_LINK]','[PREVIOUS_LINK]','[OUT_OF]','[OF]','[DISPLAY_PREVIOUS_NEXT_LINKS]','[TXT_ITEM]'), array('','','','','','', $display_previous_next_links, $MOD_BAKERY['TXT_ITEM']), $setting_footer);
		} else {
			echo str_replace(array('[NEXT_PAGE_LINK]','[NEXT_LINK]','[PREVIOUS_PAGE_LINK]','[PREVIOUS_LINK]','[OUT_OF]','[OF]','[DISPLAY_PREVIOUS_NEXT_LINKS]','[TXT_ITEM]'), array($next_page_link, $next_link, $previous_page_link, $previous_link, $out_of, $of, $display_previous_next_links, $MOD_BAKERY['TXT_ITEM']), $setting_footer);
		}
		
	}
}
?>
