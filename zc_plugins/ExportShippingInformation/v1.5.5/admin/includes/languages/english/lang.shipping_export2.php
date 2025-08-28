<?php
/**
 * Exports Order / Shipping Information From Zen Cart in various chosen formats
 *
 * @package Export Shipping and Order Information
 * @copyright Copyright 2009, Eric Leuenberger http://www.zencartoptimization.com
 * @copyright Portions Copyright 2003-2006 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: shipping_export.php, v 1.3.2 08.05.2010 11:41 Eric Leuenberger econcepts@zencartoptimization.com$
 * Thanks to dhcernese and Scott Wilson (That Software Guy) for contributing various portions that contained several bug-fixes.
 */
//  $Id: lang.shipping_export.php  v1.5.4 2025-08-28 OldNGrey $

$define = [
    'HEADING_SHIPPING_EXPORT_TITLE' => 'Export Shipping + Order Information II' ,
    'HEADING_ADDITIONAL_FIELDS_TITLE' => 'Additional Fields and Options' ,
    'HEADING_CUSTOM_DATE_TITLE' => 'Custom Date Range' ,
    'HEADING_PREVIOUS_EXPORTS_TITLE' => 'Previous Exports Inclusion' ,

    'TEXT_CUSTOM_DATE' => 'This is an optional component allowing more flexibility. Leave both fields blank to export all orders since last export was completed (the default). If you wish to include orders from date ranges that have already been downloaded, then you should complete the two boxes below. ',
    'TEXT_PREVIOUS_EXPORTS2' => 'Include Previously Exported Orders' ,
    'TEXT_PREVIOUS_EXPORTS' => 'By default the export file includes only those orders that have not already been exported. If you wish to include orders from date ranges that have already been downloaded, then you should checkbox the selection below. Combine this feature with the date range feature for even more flexibility.' ,
    'TEXT_VIDEO_TUTORIAL' => 'To view the video tutorial on how to use this module, visit <a href="http://www.zencartoptimization.com/2007/06/14/video-tutorial-export-shipping-and-order-information-from-zen-cart/" target="_blank"><strong><u>http://www.zencartoptimization.com</u></strong></a>.<br><br>' ,
    'TEXT_RUNIN_TEST' => 'Select whether you want to run in test mode or not. Test mode allows you to export without marking orders as "exported". This enables to you re-export them again.<br>' ,
    'TEXT_ADDITIONAL_FIELDS' => '<strong>Select additional fields</strong> to be added to the export below. Additional fields will be exported in the order listed.<br>' ,
    'TEXT_FILE_LAYOUT' => '<strong>Select File Layout to Export</strong><br>' ,
    'TEXT_SHIPPING_EXPORT_INSTRUCTIONS' =>'You can use this page to export your Zen Cart Order Shipping Information to CSV format for use in external programs.<br><br>
        The data is exported in the same order as you see it listed on the screen, and includes header row information. Each file is dynamically named with the date processed for easy record keeping on your end.
        <br><br>
        <strong>Features</strong>
        <ul>
        <li>Ability to export additional fields. To do that, checkmark the box of the field(s) you want to add to the export file.</li>
        <li>Option to export in two different file formats
        <ul>
        <li>1 Order per row (default)</li>
        <li>1 Product per row</li>
        </ul>
        </li>
        <li>Run in "Test" Mode. Enables you to run a test export without marking the orders as "exported" in the system. </li>
        </ul>
        <br>
        <span style="color: #ff0000"><strong>*</strong></span><strong>"Full Product Details" Export Notes</strong><br>
        When you choose to export "Full Product Details" the following fields will be exported in the format listed here:<br>
        <em>Product Qty, Product Model, Product Name, Products Price, Any Product Attributes, Line Cost, Line Tax</em>.<br><br>
        <strong>Sample "Full Product Details" Export:</strong> A few sample export files have been included with this install for reference. They are named according to the type of export that was utlized.
        <br><br>
        <u>NOTICE</u><br>
        The system searches for and finds any shipping order information that has not already been exported. If there are no records to be found then the "export" button will not show (i.e. It only shows if there is information to export).
        <br><br>
    ' ,

    'TEXT_RUNIN_TEST_FIELD' => 'Run In Test Mode' ,
    'TEXT_SPLIT_NAME_FIELD' => 'Export First and Last Name into Separate Fields.' ,
    'TEXT_PREVIOUS_EXPORTS_FIELD' => 'Include orders already downloaded in export.' ,
    'TEXT_HEADER_ROW_FIELD' => 'Include Header Row In Export' ,
    'TEXT_EMAIL_EXPORT_FORMAT' => 'Export file format type: ' ,
    'TEXT_FILE_LAYOUT_OPR_FIELD' => '1 Order per row' ,
    'TEXT_FILE_LAYOUT_PPR_FIELD' => '1 Product per row' ,
    'TEXT_SHIPPING_METHOD_FIELD' => 'Shipping Method' ,
    'TEXT_SHIPPING_TOTAL_FIELD' => 'Shipping Total' ,
    'TEXT_PHONE_NUMBER_FIELD' => 'Phone Number' ,
    'TEXT_ORDER_TOTAL_FIELD' => 'Order Total' ,
    'TEXT_ORDER_DATE_FIELD' => 'Order Date' ,
    'TEXT_ORDER_COMMENTS_FIELD' => '1st Order Comment / Note' ,
    'TEXT_PRODUCT_DETAILS_FIELD' => 'Full Product Details' ,
    'TEXT_TAX_AMOUNT_FIELD' => 'Order Tax Amount' ,
    'TEXT_SUBTOTAL_FIELD' => 'Order Subtotal' ,
    'TEXT_DISCOUNT_FIELD' => 'Order Discount' ,
    'TEXT_PAYMENT_METHOD_FIELD' => 'Payment Method' ,
    'TEXT_ORDER_STATUS_FIELD' => 'Order Status' ,
    'TEXT_ISO_COUNTRY2_FIELD' => 'ISO Country Code (2 Character)' ,
    'TEXT_ISO_COUNTRY3_FIELD' => 'ISO Country Code (3 Character)' ,
    'TEXT_STATE_ABBR_FIELD' => 'State Abbrv. Code' ,

    'TEXT_SPIFFY_START_DATE_FIELD' => 'Start Date:' ,
    'TEXT_SPIFFY_END_DATE_FIELD' => 'End Date:<br>(inclusive)' ,
    // Email Definitions
    'EMAIL_EXPORT_SUBJECT' => ''.STORE_NAME.' orders for processing.' ,
    'EMAIL_EXPORT_BODY' => 'Attached please find the most recent set of orders from '.STORE_NAME.'. If you have any questions please contact us.' ,
    'EMAIL_EXPORT_ADDRESS' => 'some-email@some-domain.com' ,  // to send to multiple addresses separate each email with a comma. Example:  firstemail@somedomain.com,secondemail@somedomain.com
    //Automatic email options
    'HEADING_AUTOMATIC_EMAIL_OPTION_TITLE' => '<strong>Automatic Email Options</strong>',
    'TEXT_AUTOMATIC_EMAIL_OPTION_FIELD' => 'Save file to server and automatically email to supplier.<br> (if not saving to server, you will be promoted to download the file to your computer.)',
    'TEXT_EMAIL_EXPORT_ADDRESS_FIELD' => 'Suppliers Email Address:&nbsp;',
    'TEXT_EMAIL_EXPORT_SUBJECT_FIELD' => 'Email Subject:&nbsp;',
    //Order status update and options
    'HEADING_UPDATE_ORDER_STATUS_TITLE' => '<strong>Update Order Status on Export<br>(If this is set, then the order status will update to what you select here after a successful export.)',
    'TEXT_UPDATE_ORDER_STATUS_FIELD' => 'Set Order Status After Export to&nbsp;',
    'HEADING_ORDER_STATUS_OPTIONS_TITLE' => '<strong>Order Status Export Options</strong> ',
    'TEXT_ORDER_STATUS_OPTIONS_ANY_FIELD' => 'Any Order Status',
    'TEXT_ORDER_STATUS_OPTIONS_ASSIGNED_FIELD' => 'Assigned Order Status (select below)',
    //Orders infos listing hearder titles
    'HEADING_ORDER_INFOS_ORDER_ID' => 'Order<br>ID',
    'HEADING_ORDER_INFOS_EMAIL' => 'Email',
    'HEADING_ORDER_INFOS_CUSTOMER_NAME' => 'Customer<br>Name',
    'HEADING_ORDER_INFOS_COMPANY' => 'Company',
    'HEADING_ORDER_INFOS_DELIVERY_STREET' => 'Delivery<br>Street',
    'HEADING_ORDER_INFOS_DELIVERY_SUBURB' => 'Delivery<br>Suburb',
    'HEADING_ORDER_INFOS_DELIVERY_CITY' => 'Delivery<br>City',
    'HEADING_ORDER_INFOS_POST_CODE' => 'Post<br>Code',
    'HEADING_ORDER_INFOS_STATE' => 'State',
    'HEADING_ORDER_INFOS_COUNTRY' => 'Country',
    'ERROR_ORDER_INFOS_NO_DATA' => '<b>No new orders were found!</b>',
    // Submit button
    'SUBMIT_BUTTON_ORDER_INFOS_EXPORT' => 'Export to Excel Spreadsheet',
    // Check / Uncheck all
    'CHECK_BUTTON_ORDER_INFOS_EXPORT' => 'Check / Uncheck all',
];

return $define;