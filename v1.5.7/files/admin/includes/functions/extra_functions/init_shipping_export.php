<?php
if (!defined('IS_ADMIN_FLAG')) {
	    die('Illegal Access');
} 

if (function_exists('zen_register_admin_page')) {
	    if (!zen_page_key_exists('shipping_export')) {
		             zen_register_admin_page('shipping_export', 'BOX_TOOLS_SHIPPING_EXPORT',
				                 'FILENAME_SHIPPING_EXPORT','' , 'tools', 'Y', 20);
			         } 

	        
}

?>
