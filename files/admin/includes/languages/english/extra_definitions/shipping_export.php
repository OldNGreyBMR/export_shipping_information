<?php
//
// +----------------------------------------------------------------------+
// |zen-cart Open Source E-commerce                                       |
// +----------------------------------------------------------------------+
// | Copyright (c) 2003 The zen-cart developers                           |
// |                                                                      |
// | http://www.zen-cart.com/index.php                                    |
// |                                                                      |
// | Portions Copyright (c) 2003 osCommerce                               |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the GPL license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available through the world-wide-web at the following url:           |
// | http://www.zen-cart.com/license/2_0.txt.                             |
// | If you did not receive a copy of the zen-cart license and are unable |
// | to obtain it through the world-wide-web, please send a note to       |
// | license@zen-cart.com so we can mail you a copy immediately.          |
// +----------------------------------------------------------------------+
//  $Id: shipping_export.php  2008-09-28 09:17  econcepts $
//
//define('VERSION', '1.2.3');  // Moved to shipping_export.php in root of Admin
define('HEADING_SHIPPING_EXPORT_TITLE', 'Export Shipping &amp; Order Information');
define('HEADING_VIDEO_TUTORIAL_TITLE', '<h2>"How To" Video Tutorial</h2>');
define('HEADING_FEED_TITLE', '<h2>Zen Cart Optimization and Marketing Tips&nbsp;&nbsp;&nbsp;<a href="http://feeds.feedburner.com/ZenCartOptimizationMarketing" target="_blank"><img src="images/icons/rss-24x24.gif" alt="Subscribe to this RSS Feed" border="0"></a> <a href="http://www.zencartoptimization.com/2007/06/04/what-is-rss-and-how-do-i-use-it" target="_blank">What is RSS?</a></h2>');
define('HEADING_ADDITIONAL_FIELDS_TITLE', 'Additional Fields and Options');
define('HEADING_CUSTOM_DATE_TITLE', 'Custom Date Range');
define('HEADING_PREVIOUS_EXPORTS_TITLE', 'Previous Exports Inclusion');

define('TEXT_CUSTOM_DATE', 'This is an optional component allowing more flexibility. Leave both fields blank to export all orders since last export was completed (the default). If you wish to include orders from date ranges that have already been downloaded, then you should complete the two boxes below.
');
define('TEXT_PREVIOUS_EXPORTS', 'Include Previously Exported Orders');
define('TEXT_PREVIOUS_EXPORTS', 'By default the export file includes only those orders that have not already been exported. If you wish to include orders from date ranges that have already been downloaded, then you should checkbox the selection below. Combine this feature with the date range feature for even more flexibility.');
define('TEXT_VIDEO_TUTORIAL', 'To view the video tutorial on how to use this module, visit <a href="http://www.zencartoptimization.com/2007/06/14/video-tutorial-export-shipping-and-order-information-from-zen-cart/" target="_blank"><strong><u>http://www.zencartoptimization.com</u></strong></a>.<br><br>');
define('TEXT_RUNIN_TEST', 'Select whether you want to run in test mode or not. Test mode allows you to export without marking orders as "exported". This enables to you re-export them again.<br />');
define('TEXT_ADDITIONAL_FIELDS', '<strong>Select additional fields</strong> to be added to the export below. Additional fields will be exported in the order listed.<br />');
define('TEXT_FILE_LAYOUT', '<strong>Select File Layout to Export</strong><br />');
define('TEXT_SHIPPING_EXPORT_INSTRUCTIONS','You can use this page to export your Zen Cart Order Shipping Information to CSV format for use in external programs.<br /><br />
The data is exported in the same order as you see it listed on the screen, and includes header row information. Each file is dynamically named with the date processed for easy record keeping on your end.
<br /><br />
<strong>Features</strong>
<ul>
<li>Ability export additional fields. To do that, checkmark the box of the field(s) you want to add to the export file.</li>
<li>Link to a video tutorial showing you how to use this module.</li>
<li>Zen Cart Optimization &amp; Marketing Feed. <strong>Automatically updates on a regular basis</strong> with information to help you increase sales using Zen Cart.</li>
<li>Option to export in two different file formats
<ul>
<li>1 Order per row (default)</li>
<li>1 Product per row</li>
</ul>
</li>
<li>Run in "Test" Mode. Enables you to run a test export without marking the orders as "exported" in the system. </li>
</ul>
<br />
<span style="color: #ff0000"><strong>*</strong></span><strong>"Full Product Details" Export Notes</strong><br />
When you choose to export "Full Product Details" the following fields will be exported in the format listed here:<br>
<em>Product Qty, Products Price, Product Name, Product Model, Any Product Attributes</em>.<br><br>
<strong>Sample "Full Product Details" Export:</strong> A few sample export files have been included with this install for reference. They are named according to the type of export that was utlized.
<br><br>
<u>NOTICE</u><br />
The system searches for and finds any shipping order information that has not already been exported. If there are no records to be found then the "export" button will not show (i.e. It only shows if there is information to export).
<br /><br />
');

define('TEXT_RUNIN_TEST_FIELD', 'Run In Test Mode');
define('TEXT_SPLIT_NAME_FIELD', 'Export First and Last Name into Separate Fields.');
define('TEXT_PREVIOUS_EXPORTS_FIELD', 'Include orders already downloaded in export.');
define('TEXT_HEADER_ROW_FIELD', 'Include Header Row In Export');
define('TEXT_EMAIL_EXPORT_FORMAT', 'Export file format type: ');
define('TEXT_FILE_LAYOUT_OPR_FIELD', '1 Order per row');
define('TEXT_FILE_LAYOUT_PPR_FIELD', '1 Product per row');
define('TEXT_SHIPPING_METHOD_FIELD', 'Shipping Method');
define('TEXT_SHIPPING_TOTAL_FIELD', 'Shipping Total');
define('TEXT_PHONE_NUMBER_FIELD', 'Phone Number');
define('TEXT_ORDER_TOTAL_FIELD', 'Order Total');
define('TEXT_ORDER_DATE_FIELD', 'Order Date');
define('TEXT_ORDER_COMMENTS_FIELD', '1st Order Comment / Note');
define('TEXT_PRODUCT_DETAILS_FIELD', 'Full Product Details');
define('TEXT_TAX_AMOUNT_FIELD', 'Order Tax Amount');
define('TEXT_SUBTOTAL_FIELD', 'Order Subtotal');
define('TEXT_DISCOUNT_FIELD', 'Order Discount');
define('TEXT_PAYMENT_METHOD_FIELD', 'Payment Method');
define('TEXT_ORDER_STATUS_FIELD', 'Order Status');
define('TEXT_ISO_COUNTRY2_FIELD', 'ISO Country Code (2 Character)');
define('TEXT_ISO_COUNTRY3_FIELD', 'ISO Country Code (3 Character)');
define('TEXT_STATE_ABBR_FIELD', 'State Abbrv. Code');

define('TEXT_SPIFFY_START_DATE_FIELD', 'Start Date:');
define('TEXT_SPIFFY_END_DATE_FIELD', 'End Date:<br>(inclusive)');
//Email Definitions
define('EMAIL_EXPORT_SUBJECT', ''.STORE_NAME.' orders for processing.');
define('EMAIL_EXPORT_BODY', 'Attached please find the most recent set of orders from '.STORE_NAME.'. If you have any questions please contact us.');
define('EMAIL_EXPORT_ADDRESS', 'someemail@somedomain.com'); // to send to multiple addresses separate each email with a comma. Example:  firstemail@somedomain.com,secondemail@somedomain.com


?>