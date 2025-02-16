<?php
declare(strict_types=1);
/**
 * Exports Order / Shipping Information From Zen Cart in various chosen formats
 *
 * @package Export Shipping and Order Information II
 * @copyright Copyright 2009, Eric Leuenberger http://www.zencartoptimization.com
 * @copyright Portions Copyright 2003-2023 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: shipping_export.php, v 1.3.2 08.05.2010 11:41 Eric Leuenberger econcepts@zencartoptimization.com$
 * Thanks to dhcernese and Scott Wilson (That Software Guy) for contributing various portions that contained several bug-fixes.
 * Version: 1.4.0 2023-12-20 BMH (OldNGrey)
 * Version: 1.5.0 2023-12-21 Convert to admin zc_plugins format for zc 1.5.8; Renamed files to shipping_export2 for zc_plugins folder
 * Version: 1.5.0.a 2024-02-16 remove ASC from all group by statements issue #4 resolved
 *          1.5.1 2024-02-16 ln348 add 4 names option; added extra group by fields to avoid group by error when SQL mode 'ONLY_FULL_GROUP_BY' is set
 *			1.5.1a ln582 add space before GROUP BY
 *          1.5.1b mod for group by and distinct for one order per row; improve parsing of names to include unicode chars;
 *          1.5.1c improve opr to output correct number of columns
 *          1.5.1d-dev formatted code; build GROUP BY as we go; try to get order summary for one product per row
 *          1.5.2 2024-03-03 removed ref to includes/stylesheet.css and used admin_html_head.php [ref: https://docs.zen-cart.com/dev/plugins/admin_head_content/]
 *                  distinguish builds EISVersion_II label
 * Version: 1.5.3 check each optional array key eg tickboxes; tidy up html; correct bugfix; check for order with no products (products previously deleted);
 *                fix headers ref https://docs.zen-cart.com/dev/plugins/admin_head_content/
 */
if (!isset($success_message))               { $success_message = '';}
if (!isset($linevalue))                     { $linevalue = '';}
if (!isset($html_msg))                      { $html_msg = '';}
if (!isset($export_test_checked))           { $export_test_checked = '';}
if (!isset($export_split_checked))          { $export_split_checked = '';}
if (!isset($date_status))                   { $date_status = '';}
if (!isset($export_header_row_checked))     { $export_header_row_checked = '';}
if (!isset($order_status_setting))          { $order_status_setting = '';}
if (!isset($order_status_setting_checked))  { $order_status_setting_checked = '';}
if (!isset($order_status))                  { $order_status = '';}
if (!isset($dload_include_checked))         { $dload_include_checked = '';}
if (!isset($shipping_total_checked))        { $shipping_total_checked = '';}
if (!isset($order_total_checked))           { $order_total_checked = '';}
if (!isset($date_purchased_checked))        { $date_purchased_checked = '';}
if (!isset($order_tax_checked))             { $order_tax_checked = '';}
if (!isset($order_subtotal_checked))        { $order_subtotal_checked = '';}
if (!isset($order_discount_checked))        { $order_discount_checked = '';}
if (!isset($order_pmethod_checked))         { $order_pmethod_checked = '';}
if (!isset($shipping_method_checked))       { $shipping_method_checked = '';}
if (!isset($order_comments_checked))        { $order_comments_checked = '';}
if (!isset($phone_number_checked))          { $phone_number_checked = '';}
if (!isset($order_status_checked))          { $order_status_checked = '';}
if (!isset($iso_country2_code_checked))     { $iso_country2_code_checked = '';}
if (!isset($iso_country3_code_checked))     { $iso_country3_code_checked = '';}
if (!isset($prod_details_checked))          { $prod_details_checked = '';}
if (!isset($dload_include))                 { $dload_include = '';}

define('VERSION', '1.5.3');
define('ESIVERSION', '1.5.3');
require('includes/application_top.php');
require(DIR_WS_CLASSES . 'currencies.php');
$currencies = new currencies();
include(DIR_WS_CLASSES . 'order.php');
define('ESI_DEBUG', "No");   // No or Yes   // BMH DEBUG switch

// change destination here for path when using "save to file on server"
if (!defined('DIR_FS_EMAIL_EXPORT')) define('DIR_FS_EMAIL_EXPORT', DIR_FS_CATALOG . 'images/uploads/');

/** Set Available Export Formats **/
$available_export_formats[0] = array('id' => '0', 'text' => 'CSV');
$available_export_formats[1] = array('id' => '1', 'text' => 'TXT');
//  $available_export_formats[2]=array('id' => '2', 'text' => 'HTML');
//  $available_export_formats[3]=array('id' => '3', 'text' => 'XML');
/**********************************/
/** Set Variables **/
$save_to_file_checked = (isset($_POST['savetofile']) && zen_not_null($_POST['savetofile']) ? $_POST['savetofile'] : 0);
$post_format = (isset($_POST['format']) && zen_not_null($_POST['format']) ? $_POST['format'] : 1);
$format = $available_export_formats[$post_format]['text'];
/* Get file types */
if ($format == 'CSV') {
    $file_extension = '.csv';
    $FIELDSTART = '"';
    $FIELDEND = '"';
    $FIELDSEPARATOR = ',';
    $LINESTART = '';
    $LINEBREAK = "\n";
    $ATTRIBSEPARATOR = ' | '; //Be Careful with this option. Setting it to a 'comma' for example could throw off the remaining fields.
}
if ($format == 'TXT') {
    $file_extension = '.txt';
    $FIELDSTART = '';
    $FIELDEND = '';
    $FIELDSEPARATOR = "\t"; // Tab separated
    //$FIELDSEPARATOR = ','; // Comma separated
    $LINESTART = '';
    $LINEBREAK = "\n";
    $ATTRIBSEPARATOR = ' | '; //Be Careful with this option. Setting it to a 'comma' for example could throw off the remaining fields.
} // BMH change file name date format from 'mdy-Hi' to 'Ymd-Hi ISO standards'

$file = (isset($_POST['filename']) ? $_POST['filename'] : "Orders" . date('Ymd-Hi') . $file_extension . "");
$to_email_address = (isset($_POST['auto_email_supplier']) ? $_POST['auto_email_supplier'] : "" . EMAIL_EXPORT_ADDRESS . "");
$email_subject = (isset($_POST['auto_email_subject']) ? $_POST['auto_email_subject'] : "Order export from " . STORE_NAME . "");
/*******************/

if (isset($_POST['download_csv'])) {
    // If form was submitted then do processing and begin streaming file contents
    /*
       Header('Content-type: application/csv');
       Header("Content-disposition: attachment; filename=\"". $file ."");
    */
    // if date_range is set then gather form vars for SQL processing
    if (!empty($_POST['start_date']) != '') {            // BMH 153
        $start_date = $_POST['start_date'] . ' 00:00';
    }
    if ($_POST['end_date'] != '') {
        $end_date = $_POST['end_date'] . ' 23:59';
    }
    //**************************************************************

    if (($_POST['filelayout']) == 2) { // 1 Product Per row RADIO
        // if (ESI_DEBUG == 'Yes') echo '<br> ln104 filelayout=2 product per row' . "\n"; //BMH DEBUG
        $order_info = "SELECT o.orders_id, customers_email_address, delivery_name, delivery_company, delivery_street_address,
            delivery_suburb, delivery_city, delivery_postcode, delivery_state, delivery_country, shipping_method,
            customers_telephone, order_total, op.products_model, products_name, op.products_price, final_price,
            op.products_quantity, op.products_tax, date_purchased, ot.value, orders_products_id, order_tax, o.orders_status, o.payment_method";

        $order_group_info = " GROUP BY o.orders_id, customers_email_address, delivery_name, delivery_company, delivery_street_address,
            delivery_suburb, delivery_city, delivery_postcode, delivery_state, delivery_country, shipping_method,
            customers_telephone, order_total, op.products_model, products_name, op.products_price, final_price,
            op.products_quantity, op.products_tax, date_purchased, ot.value, orders_products_id, order_tax, o.orders_status, o.payment_method ";


        if (!empty($_POST['iso_country2_code']) == 1) {
            $order_info = $order_info . ", cc.countries_iso_code_2";
            $order_group_info = $order_group_info . ", cc.countries_iso_code_2";
        }
        ;

        if (!empty($_POST['iso_country3_code']) == 1) {
            $order_info = $order_info . ", cc.countries_iso_code_3";
            $order_group_info = $order_group_info . ", cc.countries_iso_code_3";
        }
        ;

        $order_info = $order_info . " FROM (" . TABLE_ORDERS . " o LEFT JOIN " . TABLE_ORDERS_PRODUCTS . " op ON o.orders_id = op.orders_id), "
            . TABLE_ORDERS_TOTAL . " ot";

        if (!empty($_POST['iso_country2_code']) == 1 || !empty($_POST['iso_country3_code']) == 1) {
            $order_info = $order_info . ", " . TABLE_COUNTRIES . " cc";
        }
        ;

        $order_info = $order_info . " WHERE o.orders_id = ot.orders_id ";

        if (!empty($_POST['iso_country2_code']) == 1 || !empty($_POST['iso_country3_code']) == 1) {   // BMH isset
            $order_info = $order_info . " AND cc.countries_name = o.delivery_country ";
        }
        ;

        $order_info = $order_info . "AND ot.class = 'ot_shipping'";

        if ((!empty($_POST['dload_include'])) != 1) { //BMH isset 153
            $order_info = $order_info . " AND downloaded_ship='no'";
        }
        if ((($_POST['status_target'])) == 2) {  //BMH isset 153
            $order_info = $order_info . " AND o.orders_status = '" . $_POST['order_status'] . "'";
        }

        if (($_POST['start_date']) != '' && ($_POST['end_date']) != '') {     // BMH isset
            $order_info = $order_info . " AND date_purchased BETWEEN '" . $start_date . "' AND '" . $end_date . "'";  // BMH Note: BETWEEN operator is inclusive:
        }
        $order_info = $order_info . $order_group_info;  // BMH build GROUP BY
        $order_info = $order_info . " ORDER BY orders_id ASC";
        // complete the sql for  Product Per row
        // ++++++++++++++++ //

    } else { //  1 Order Per row (filelayout==1)
        if (ESI_DEBUG == 'Yes') echo '<br> ln172 filelayout should = 1 SHOULD BE one OPR = '. $_POST['filelayout'] . " \n"; //BMH DEBUG
        // BMH Added DISTINCT
        $order_info = "SELECT DISTINCT o.orders_id, customers_email_address, delivery_name, delivery_company,
            delivery_street_address, delivery_suburb, delivery_city, delivery_postcode, delivery_state,
            delivery_country, shipping_method, customers_telephone, order_total, date_purchased,  ot.value,
            order_tax, o.orders_status, o.payment_method"; // removed os.comments BMH correct ANY_VALUE(os.comments) NO support in MariaDB

        $order_group_info = " GROUP BY o.orders_id, customers_email_address, delivery_name, delivery_company,
            delivery_street_address, delivery_suburb, delivery_city, delivery_postcode, delivery_state,
            delivery_country, shipping_method,
                customers_telephone, order_total, date_purchased,    ot.value,  order_tax, o.orders_status, o.payment_method ";

        if (!empty($_POST['iso_country2_code']) == 1) {
            $order_info = $order_info . ", cc.countries_iso_code_2";
            $order_group_info = $order_group_info . ", cc.countries_iso_code_2";
        }
        ;

        if (!empty($_POST['iso_country3_code']) == 1) {
            $order_info = $order_info . ", cc.countries_iso_code_3";
            $order_group_info = $order_group_info . ", cc.countries_iso_code_3";
        }
        ;

        $order_info = $order_info . " FROM " . TABLE_ORDERS . " o, " . TABLE_ORDERS_STATUS_HISTORY . " os, " . TABLE_ORDERS_TOTAL . " ot";

        if (!empty($_POST['iso_country2_code']) == 1 || !empty($_POST['iso_country3_code']) == 1) {
            $order_info = $order_info . ", " . TABLE_COUNTRIES . " cc";
        }
        ;

        $order_info = $order_info . " WHERE o.orders_id = ot.orders_id AND ot.class = 'ot_shipping' ";

        if (!empty($_POST['iso_country2_code']) == 1 || !empty($_POST['iso_country3_code']) == 1) {
            $order_info = $order_info . " AND cc.countries_name = o.delivery_country ";
        }
        ;

        $order_info = $order_info . "AND o.orders_id = os.orders_id";

        if (!empty(($_POST['dload_include'])) != 1) {  // BMH  153
            $order_info = $order_info . " AND downloaded_ship='no'";
        }

        if (($_POST['status_target']) == 2) {      // BMH isset 153
            $order_info = $order_info . " AND o.orders_status = '" . $_POST['order_status'] . "'";
        }
        if ($_POST['start_date'] != '' && $_POST['end_date'] != '') {
            $order_info = $order_info . " AND date_purchased BETWEEN '" . $start_date . "' AND '" . $end_date . "'"; // BMH Note: BETWEEN operator is inclusive:
        }

        $order_info = $order_info . $order_group_info;  // BMH build GROUP BY

        // complete the sql statement for 1 order per row

        // count how many products in orders
        $max_num_products = "SELECT COUNT( * ) AS max_num_of_products
                FROM (" . TABLE_ORDERS . " o LEFT JOIN " . TABLE_ORDERS_PRODUCTS . " op ON  o.orders_id = op.orders_id), " . TABLE_ORDERS_TOTAL . " ot
                WHERE o.orders_id = ot.orders_id
                AND ot.class = 'ot_shipping'";

        if (!empty($_POST['dload_include']) != 1) {  //BMH 153
            $max_num_products = $max_num_products . " AND downloaded_ship='no'";
        }

        if (($_POST['status_target']) == 2) { //BMH 153
            $max_num_products = $max_num_products . " AND o.orders_status = '" . $_POST['order_status'] . "'";
        }

        if ($_POST['start_date'] != '' && $_POST['end_date'] != '') {
            $max_num_products = $max_num_products . " AND date_purchased BETWEEN '" . $start_date . "' AND '" . $end_date . "'";
        }

        //$max_num_products = $max_num_products . " GROUP BY o.orders_id ASC // BMH removed ASC; invalid syntax
        $max_num_products = $max_num_products . " GROUP BY o.orders_id
                ORDER BY max_num_of_products DESC
                LIMIT 1";

        $max_num_products_result = $db->Execute($max_num_products);

        // check for results
        if (count($max_num_products_result) < 1) {
            echo ' NO records were returned for the selected target dates <br>';
            // $results_check = count($max_num_products_result);
            die('Press the BACK button in your browser to return to the previous page.');
        }

        $max_products = $max_num_products_result->fields['max_num_of_products'];

        //if (ESI_DEBUG == 'Yes') echo '<br> ln214 $max_products= ' . $max_products; //BMH DEBUG
    } // End File layout sql

    $order_details = $db->Execute($order_info);     // run the sql

    /******************Begin Set Header Row Information*****************************/
    //if (ESI_DEBUG == 'Yes') echo '<br> ln220 begin headers' . " \n"; //BMH DEBUG
    $str_header = "Order ID,Customer Email";

    if (isset($_POST['split_name']) == 1) { //If name split is desired then split it. //BMH 153
        $str_header = $str_header . ",First Name,Last Name";
    } else {
        $str_header = $str_header . ",Delivery Name";
    }
    $str_header = $str_header . ",Company,Delivery Street,Delivery Suburb,Delivery City,Delivery State,Delivery Post Code,Delivery Country,Ship Dest Type"; // swguy

    if (isset($_POST['shipmethod']) == 1)              { $str_header = $str_header . ",Shipping Method"; } ;
    if (isset($_POST['customers_telephone']) == 1)     { $str_header = $str_header . ",Customers Telephone"; } ;
    if (isset($_POST['orders_status_export']) == 1)    { $str_header = $str_header . ",Order Status"; } ;
    if (isset($_POST['iso_country2_code']) == 1)       { $str_header = $str_header . ",ISO Country Code 2"; } ;
    if (isset($_POST['iso_country3_code']) == 1)       { $str_header = $str_header . ",ISO Country Code 3"; } ;
    if (isset($_POST['order_comments']) == 1)          { $str_header = $str_header . ",Order Notes"; } ;
    if (isset($_POST['date_purchased']) == 1)          { $str_header = $str_header . ",Order Date"; } ;

    //if (($_POST['product_details']) == 1 ) { // add to header row
    if ((!empty($_POST['product_details']) ? $_POST['product_details'] : 0) == 1) { // BMH  if unticked add to header row) ? == 1 ) { // add to header row
         if (ESI_DEBUG == 'Yes') echo '<br> ln287 filelayout= ' . $_POST['filelayout'] . " \n"; //BMH DEBUG
        if (($_POST['filelayout']) == 2) { // 1 Product Per row RADIO
            if (ESI_DEBUG == 'Yes') echo '<br> ln289 product filelayout= ' . $_POST['filelayout'] . " \n"; //BMH DEBUG
            $str_header = $str_header . ",Product Qty,Product Model,Product Name,Product Attributes,Products Price"; //BMH reposition attributes
            $str_header = $str_header . ",Line cost";
            $str_header = $str_header . ",Line tax";

            if (!empty($_POST['order_subtotal']) == 1)    { $str_header = $str_header . ",Order Subtotal"; } ;
            if (!empty($_POST['shiptotal']) == 1)         { $str_header = $str_header . ",Shipping Total"; } ;
            if (!empty($_POST['order_discount']) == 1)    { $str_header = $str_header . ",Order Discount"; } ;
            if (!empty($_POST['order_total']) == 1)       { $str_header = $str_header . ",Order Total"; } ;
            if (!empty($_POST['order_tax']) == 1)         { $str_header = $str_header . ",Order Tax"; } ;
            if (!empty($_POST['payment_method']) == 1)    { $str_header = $str_header . ",Payment Method"; } ;
        } else { // File layout is 1 OPR
            /**************the following exports 1 OPR attribs****************/
            $oID = zen_db_prepare_input($order_details->fields['orders_id']);
            $oIDME = $order_details->fields['orders_id'];
            $order = new order($oID);
            for ($i = 0, $n = $max_products; $i < $n; $i++) { // BMH rearrange order
                $str_header = $str_header . ",Product " . $i . " Qty";
                $str_header = $str_header . ",Product " . $i . " Model";
                $str_header = $str_header . ",Product " . $i . " Name";
                $str_header = $str_header . ",Product " . $i . " Attributes";
                $str_header = $str_header . ",Product " . $i . " Price";
            }
            /*****************************************************************/
            if (!empty($_POST['shiptotal']) == 1)         { $str_header = $str_header . ",Shipping Total"; } ;
            if (!empty($_POST['order_discount']) == 1)    { $str_header = $str_header . ",Order Discount"; } ;
            if (!empty($_POST['order_total']) == 1)       { $str_header = $str_header . ",Order Total"; } ;
            if (!empty($_POST['order_tax']) == 1)         { $str_header = $str_header . ",Order Tax"; } ;
            if (!empty($_POST['payment_method']) == 1)    { $str_header = $str_header . ",Payment Method"; } ;
        } // End if to determine which header to use
    } else {        // 1 OPR with no product details
            if (!empty($_POST['shiptotal']) == 1)         { $str_header = $str_header . ",Shipping Total"; } ;
            if (!empty($_POST['order_discount']) == 1)    { $str_header = $str_header . ",Order Discount"; } ;
            if (!empty($_POST['order_total']) == 1)       { $str_header = $str_header . ",Order Total"; } ;
            if (!empty($_POST['order_tax']) == 1)         { $str_header = $str_header . ",Order Tax"; } ;
            if (!empty($_POST['payment_method']) == 1)    { $str_header = $str_header . ",Payment Method"; } ;
    }
    // end Row header if product details selected

    $str_header = $str_header . "\n";  // Print header row - data is on the next line

    /* dhc */ // DEBUG line [to keep]
    //  $str_header = $str_header . $order_info . "<br>\n" . $order_details->RecordCount() . "<br>\n" ;
    /******************End Header Row Information*****************************/

    $str_full_export = "";
    /* bof prepare data lines  */
    while (!$order_details->EOF) {
        $str_export = $FIELDSTART . $order_details->fields['orders_id'] . $FIELDEND . $FIELDSEPARATOR . $FIELDSTART . $order_details->fields['customers_email_address'] . $FIELDEND;
        if (!empty($_POST['split_name']) == 1) {   // BMH isset
            $fullname = $order_details->fields['delivery_name'];
            // BMH ++ new code for unicode string
            $s1 = array_map('trim', explode(' ', $fullname));
            $s2 = array_filter($s1, function ($value) {
                return $value !== '';
            });
            $count = count($s2);

            switch ($count) {
                case 4:
                    list($first, $middle, $third, $last) = preg_split("/[\s,]+/", $fullname); // BMH add 4 word option 2024-02-16
                    break;
                case 3:
                    list($first, $middle, $last) = preg_split("/[\s,]+/", $fullname);
                    break;
                case 2:
                    list($first, $last) = preg_split("/[\s,]+/", $fullname);   // BMH remove middle
                    break;
                case 1:
                    list($last) = preg_split("/[\s,]+/", $fullname);
                    break;
            }

            $str_export .= $FIELDSEPARATOR . $FIELDSTART . $first . $FIELDEND . $FIELDSEPARATOR . $FIELDSTART . $last . $FIELDEND;
        } else {
            $str_export .= $FIELDSEPARATOR . $FIELDSTART . $order_details->fields['delivery_name'] . $FIELDEND;
        }
        ;
        // swguy
        if ($order_details->fields['delivery_company'] == '') {
            $dest_type = 'Residential';
        } else {
            $dest_type = 'Commercial';
        }
        // end swguy
        $str_export .= $FIELDSEPARATOR . $FIELDSTART . $order_details->fields['delivery_company'] . $FIELDEND . $FIELDSEPARATOR . $FIELDSTART . $order_details->fields['delivery_street_address'] . $FIELDEND .
            $FIELDSEPARATOR . $FIELDSTART . $order_details->fields['delivery_suburb'] . $FIELDEND . $FIELDSEPARATOR . $FIELDSTART . $order_details->fields['delivery_city'] . $FIELDEND .
            $FIELDSEPARATOR . $FIELDSTART . $order_details->fields['delivery_state'] . $FIELDEND . $FIELDSEPARATOR . $FIELDSTART . $order_details->fields['delivery_postcode'] . $FIELDEND .
            $FIELDSEPARATOR . $FIELDSTART . $order_details->fields['delivery_country'] . $FIELDEND .
            $FIELDSEPARATOR . $FIELDSTART . $dest_type . $FIELDEND;
        // swguy last line changed
        if (isset($_POST['shipmethod']) == 1) {
            $str_export .= $FIELDSEPARATOR . $FIELDSTART . $order_details->fields['shipping_method'] . $FIELDEND;
        }
        ;

        if (isset($_POST['customers_telephone']) == 1) {
            $str_export .= $FIELDSEPARATOR . $FIELDSTART . "'" . $order_details->fields['customers_telephone'] . $FIELDEND;
        }
        ; //BMH prepend single quote for Excel to retain any leading zero

        //*********Add Payment Method if selected***************/
        if (isset($_POST['orders_status_export']) == 1) {    // BMH isset
            // if order status was selected, then run the query to pull the data for adding it to the export string.
            // Run a query to pull the Order Status if present
            $orders_status_query = "SELECT orders_status_name
                FROM (" . TABLE_ORDERS_STATUS . ")
                WHERE orders_status_id=" . $order_details->fields['orders_status'] . "";
            $orders_status = $db->Execute($orders_status_query);

            $num_rows = $orders_status->RecordCount();
            if ($num_rows > 0) { // if records were found
                $str_export .= $FIELDSEPARATOR . $FIELDSTART . $orders_status->fields['orders_status_name'] . $FIELDEND; //add discount amt to export string
            } else { // add a BLANK field to the export file for "consistency"
                $str_export .= $FIELDSEPARATOR . $FIELDSTART . $FIELDEND; // add blank space for filler
            } // end if
        }  // End if for determining if order discount was selected to export.

        //*************bof ISO Country Codes********************//
        if (isset($_POST['iso_country2_code']) == 1) { // if iso country 2 was selected, then add it to the export string.
            $str_export .= $FIELDSEPARATOR . $FIELDSTART . $order_details->fields['countries_iso_code_2'] . $FIELDEND; //add ISO country code to export string
        }
        if (isset($_POST['iso_country3_code']) == 1) { // BMH isset //if iso country 3 was selected, then add it to the export string.
            $str_export .= $FIELDSEPARATOR . $FIELDSTART . $order_details->fields['countries_iso_code_3'] . $FIELDEND; //add ISO country code to export string
        }
        //*************eof ISO Country Codes********************//

        // bof comments
        if (isset($_POST['order_comments']) == 1) {
            $orders_comments_query = "SELECT *  FROM " . TABLE_ORDERS_STATUS_HISTORY . " WHERE orders_id = " . $order_details->fields['orders_id'] . "
                GROUP BY orders_id, orders_status_id, orders_status_history_id, date_added, customer_notified, comments, updated_by
                ORDER BY orders_status_history_id ASC"; // BMH MySQLD added extra fields to avoid group by error when SQL mode 'ONLY_FULL_GROUP_BY' is set
            $orders_comments = $db->Execute($orders_comments_query);
            $str_safequotes = str_replace('"', "'", $orders_comments->fields['comments']); // replace quotes with single quotes if present
            $str_export .= $FIELDSEPARATOR . $FIELDSTART . str_replace(array("\r\n", "\r", "\n"), " ", $str_safequotes) . $FIELDEND; // Remove any line breaks in first comment and print to export string
        }
        // eof comments

        if (!empty($_POST['date_purchased']) == 1) {
            $str_export .= $FIELDSEPARATOR . $FIELDSTART . $order_details->fields['date_purchased'] . $FIELDEND;
        }
        ;

        //******************************************************************//
        // bof sub-totals
        if (isset($_POST['order_subtotal']) == 1) { // BMH isset
            $orders_subtotal_query = "SELECT o.orders_id, customers_email_address, delivery_name, delivery_company,
                delivery_street_address, delivery_suburb, delivery_city, delivery_postcode, delivery_state, delivery_country,
                shipping_method, customers_telephone, order_total, products_model, products_name, products_price, final_price,
                products_quantity, date_purchased, ot.value, orders_products_id, order_tax
                FROM (" . TABLE_ORDERS . " o LEFT JOIN " . TABLE_ORDERS_PRODUCTS . " op ON o.orders_id = op.orders_id), " . TABLE_ORDERS_TOTAL . " ot
                WHERE o.orders_id = ot.orders_id
                AND ot.class = 'ot_subtotal'
                AND ot.orders_id = " . $order_details->fields['orders_id'] . "";
            if (!empty($_POST['dload_include']) != 1) {      //BMH 153
                $orders_subtotal_query = $orders_subtotal_query . " AND downloaded_ship='no'";
            }
            if ($_POST['status_target'] == 2) {
                $orders_subtotal_query = $orders_subtotal_query . " AND o.orders_status = '" . $_POST['order_status'] . "'";
            }
            if ($_POST['start_date'] != '' && $_POST['end_date'] != '') {
                $orders_subtotal_query = $orders_subtotal_query . " AND date_purchased BETWEEN '" . $start_date . "' AND '" . $end_date . "'";
            }
            $orders_subtotal_query = $orders_subtotal_query . " ORDER BY orders_id ASC";

            $orders_subtotal = $db->Execute($orders_subtotal_query);    // run sql

        } // eof sub-totals

        if (!empty($_POST['product_details']) == 1) {     // Order details should be added to the export string.
            if (ESI_DEBUG == 'Yes') {echo '<br> ln459 product_details=1 ' . " \n"; } //BMH DEBUG
            if (isset($_POST['filelayout']) == 2) {      // 1 PPR RADIO         // BMH 153
                if (ESI_DEBUG == 'Yes') { echo '<br> ln461 filelayout = 2 is ' . $_POST['filelayout'] . "\n"; } //BMH DEBUG
                // bmh CHANGE ORDER of fields
                if (!isset($linevalue))
                    $linevalue = 0;   // BMH calc line cost ; change from defined to isset
                if (!isset($linetax))
                    $linetax = 0;       // BMH calc line tax
                //BMH if no product in the Order
                if (isset($order_details->fields['orders_products_id']) <> '' ) {   //BMH 153x
                    $str_export .= $FIELDSEPARATOR . $FIELDSTART . $order_details->fields['products_quantity'] . $FIELDEND;
                    $str_export .= $FIELDSEPARATOR . $FIELDSTART . $order_details->fields['products_model'] . $FIELDEND;
                } else {  // BMH end product qty Check
                    if (ESI_DEBUG == 'Yes') { echo '<br> ln472 no product in order' ."\n"; } //BMH DEBUG
                }
                //$str_export .= $FIELDSEPARATOR . $FIELDSTART . (str_replace('"', "", $order_details->fields['products_name'] ?? '')) . $FIELDEND; // replace quotes with space if present

                //$str_safequotes = str_replace('"', "", $order_details->fields['products_name']); // replace quotes with nothing if present //BMH 153
                $str_safequotes = str_replace('"', "", $order_details->fields['products_name'] ?? ''); // replace quotes with nothing if present //BMH 153

                $str_export .= $FIELDSEPARATOR . $FIELDSTART . str_replace(array("\r\n", "\r", "\n"), " ", $str_safequotes) . $FIELDEND;

                // BMH what if no product. Database entry deleted. order exists with no product.
                if (isset($order_details->fields['orders_products_id']) <> '' ) {  //BMH 153x
                    // find product attributes
                    $product_attributes_rows = "SELECT Count(*) as num_rows
                    FROM " . TABLE_ORDERS_PRODUCTS_ATTRIBUTES . "
                    WHERE orders_id = " . $order_details->fields['orders_id'] . "
                    AND orders_products_id = " . $order_details->fields['orders_products_id'] . "";
                    $attributes_query_rows = $db->Execute($product_attributes_rows);

                    $num_rows = $attributes_query_rows->fields['num_rows'];

                    if ($num_rows > 0) {
                        $product_attributes_query = "SELECT *
                        FROM " . TABLE_ORDERS_PRODUCTS_ATTRIBUTES . "
                        WHERE orders_id = " . $order_details->fields['orders_id'] . "
                        AND orders_products_id = " . $order_details->fields['orders_products_id'] . "";
                        $attributes_query_results = $db->Execute($product_attributes_query);
                        $str_export .= $FIELDSEPARATOR . $FIELDSTART;        // BMH include $FIELDSEPARATOR
                        for ($i = 0, $n = $num_rows; $i < $n; $i++) {
                            //dhc
                            $str_safequotes = str_replace('"', "'", $attributes_query_results->fields['products_options_values']);
                            $str_export .= $attributes_query_results->fields['products_options'] . ': ' . str_replace(array("\r\n", "\r", "\n"), " ", $str_safequotes) . $ATTRIBSEPARATOR;
                            $attributes_query_results->MoveNext();
                        }
                        $str_export .= $FIELDEND;
                    } else {
                        $str_export .= $FIELDSEPARATOR . $FIELDSTART . $FIELDEND; // BMH make blank placeholder if no attributes
                    }
                    // BMH eof attributes
                }
                // BMH end check for product

                $str_export .= $FIELDSEPARATOR . $FIELDSTART . $order_details->fields['final_price'] . $FIELDEND;
                // BMH calc product line value and tax
                $linevalue = $order_details->fields['products_quantity'] * $order_details->fields['final_price'];
                $linetax = $order_details->fields['products_tax'] / 100 * $order_details->fields['final_price'];
                $str_export .= $FIELDSEPARATOR . $FIELDSTART . $linevalue . $FIELDEND;
                $str_export .= $FIELDSEPARATOR . $FIELDSTART . $linetax . $FIELDEND;
                // BMH

                $num_rows = $orders_subtotal->RecordCount();                // how many are returned
                if ($num_rows > 0) {
                    $str_export .= $FIELDSEPARATOR . $FIELDSTART . $orders_subtotal->fields['value'] . $FIELDEND; //add subtotal amt to export string
                } else { // add a BLANK field to the export file for "consistency"
                    $str_export .= $FIELDSEPARATOR . $FIELDSTART . $FIELDEND; // add blank space for filler
                } // end if

            } else { // 1 OPR
                if (ESI_DEBUG == 'Yes')
                    echo '<br> ln542 product_details = 1 & OPR' . "\n"; // BMH DEBUG
                /**************the following exports 1 OPR w/ attributes) ****************/
                $oID = zen_db_prepare_input($order_details->fields['orders_id']);
                $oIDME = $order_details->fields['orders_id'];
                $order = new order($oID);

                for ($i = 0, $n = sizeof($order->products); $i < $n; $i++) {
                    $str_export .= $FIELDSEPARATOR . $FIELDSTART . $order->products[$i]['qty'] . $FIELDEND;
                    $str_export .= $FIELDSEPARATOR . $FIELDSTART . $order->products[$i]['model'] . $FIELDEND;

                    //$str_export .= $FIELDSEPARATOR . $FIELDSTART . $order->products[$i]['name'] . $FIELDEND . $FIELDSEPARATOR;

                    $str_safequotes = str_replace('"', "", $order->products[$i]['name']);  // BMH PARSE NAME
                    $str_export .= $FIELDSEPARATOR . $FIELDSTART . $str_safequotes . $FIELDEND . $FIELDSEPARATOR;

                    // attributes
                    if (isset($order->products[$i]['attributes']) && (($k = sizeof($order->products[$i]['attributes'])) > 0)) {
                        //$str_export .= $FIELDSEPARATOR;
                        $str_export .= $FIELDSTART;
                        for ($j = 0; $j < $k; $j++) {
                            $str_safequotes = str_replace('"', "'", $order->products[$i]['attributes'][$j]['value']);
                            $str_export .= $order->products[$i]['attributes'][$j]['option'] . ': ' . str_replace(array("\r\n", "\r", "\n"), " ", $str_safequotes) . $ATTRIBSEPARATOR;
                        }
                        $str_export .= $FIELDEND;
                    } else { // add a BLANK field to the export file for "consistency"
                        // $str_export .= $FIELDSEPARATOR . $FIELDSTART . $FIELDEND; // add blank space for filler
                    }
                    $str_export .= $FIELDSEPARATOR . $FIELDSTART . $order->products[$i]['final_price'] . $FIELDEND;
                }
                if ($n < $max_products) {
                    //for ($f = 0, $g = $max_products; $f < $g-1; $f++) {
                    for ($f = 0, $g = $max_products; $f < $g - $n; $f++) {
                        $str_export .= $FIELDSEPARATOR . $FIELDSTART . "" . $FIELDEND;
                        $str_export .= $FIELDSEPARATOR . $FIELDSTART . "" . $FIELDEND;
                        $str_export .= $FIELDSEPARATOR . $FIELDSTART . "" . $FIELDEND;
                        $str_export .= $FIELDSEPARATOR . $FIELDSTART . "" . $FIELDEND;
                        $str_export .= $FIELDSEPARATOR . $FIELDSTART . "" . $FIELDEND;
                    }
                }
                /*************************************************************************/
            } // End if for determining type of export

        } // End if to determine if the order details should be added to the export string.
        // BMH extend the export string XXXXXXXXXXXXXXXXXXXXXXXXXXXXXX

        if (!empty($_POST['shiptotal']) == 1) {
            $str_export .= $FIELDSEPARATOR . $FIELDSTART . $order_details->fields['value'] . $FIELDEND;
        }
        ;   // BMH isset

        if ((!empty($_POST['order_discount'])) == 1) {  // BMH isset
            // if order discount was selected, then run the query to pull the data for adding it to the export string.
            // Run a query to pull the Order Discount total if present ; no discount on order-product so remove from inner join not all discounts are negative numbers for force by -ABS
            $orders_discount_query = "SELECT DISTINCT o.orders_id,  sum(-ABS(round(ot.value,2))) AS value
                FROM (" . TABLE_ORDERS . " o LEFT JOIN  " . TABLE_ORDERS_TOTAL . " ot ON o.orders_id = ot.orders_id)
                WHERE o.orders_id = ot.orders_id
                AND ot.class IN  ('ot_coupon' , 'ot_custom', 'ot_group_pricing', 'ot_payment_type', 'ot_paymentmodulefee')
                AND ot.orders_id = " . $order_details->fields['orders_id'] . ""; /* BMH changed AND ot.class = 'ot_coupon' TO include other discounts
                        AND ot.class IN  ('ot_coupon' , 'ot_custom', 'ot_group_pricing',
                        'ot_payment_type' , 'ot_paymentmodule) */
            if (!empty($_POST['dload_include']) != 1) {
                $orders_discount_query = $orders_discount_query . " AND downloaded_ship='no'";
            }
            if (($_POST['status_target']) == 2) {          //BMH 153
                $orders_discount_query = $orders_discount_query . " AND o.orders_status = '" . $_POST['order_status'] . "'";
            }
            if ($_POST['start_date'] != '' && $_POST['end_date'] != '') {
                $orders_discount_query = $orders_discount_query . " AND date_purchased BETWEEN '" . $start_date . "' AND '" . $end_date . "'";
                //$orders_discount_query = $orders_discount_query . " AND date_purchased >= '". $start_date ."' AND date_purchased <= '". $end_date ."'";
            }
            $orders_discount_query = $orders_discount_query . " GROUP BY o.orders_id";
            $orders_discount_query = $orders_discount_query . " ORDER BY orders_id ASC";

            $orders_discount = $db->Execute($orders_discount_query);

            //$recordcount = mysql_query($orders_discount_query);
            $num_rows = $orders_discount->RecordCount();
            if ($num_rows > 0) { // if records were found
                $str_export .= $FIELDSEPARATOR . $FIELDSTART . $orders_discount->fields['value'] . $FIELDEND; //add discount amt to export string // BMH add negative symbol
            } else { // add a BLANK field to the export file for "consistency"
                $str_export .= $FIELDSEPARATOR . $FIELDSTART . $FIELDEND; // add blank space for filler
            } // end if
        } // End if for determining if order discount was selected to export.

        if (!empty($_POST['order_total']) == 1) {    // BMH isset
            $str_export .= $FIELDSEPARATOR . $FIELDSTART . $order_details->fields['order_total'] . $FIELDEND;
        }
        ;
        if (!empty($_POST['order_tax']) == 1) {
            $str_export .= $FIELDSEPARATOR . $FIELDSTART . $order_details->fields['order_tax'] . $FIELDEND;
        }
        ;  // BMH isset

        //*********Add Payment Method if selected***************/
        if (!empty($_POST['payment_method']) == 1) {  // BMH isset
            $str_export .= $FIELDSEPARATOR . $FIELDSTART . $order_details->fields['payment_method'] . $FIELDEND;
        }
        ;

        /* print the export string  *********************** */
        $str_export = $str_export . "\n";
        $str_full_export .= $str_export;

        //If order status is to be updated, then update it for this order now.
        if (isset($_POST['status_setting']) == 1) {            //Update the order status upon export
            $db->execute('UPDATE ' . TABLE_ORDERS . ' SET orders_status="' . $_POST['order_status_setting'] .
                '" WHERE orders_id="' . $order_details->fields['orders_id'] . '"');
        }
        //********************************************************************
        $order_details->MoveNext();
    } // End Outer While statement to loop through all non downloaded orders.

    /**************************************Process the export file**************************************************/
    if ($save_to_file_checked == 1) { // saving to a file for email attachement, so write and ready else do regular output (prompt for download)
        // Do not set headers becuase we are going to email the file to the supplier.
        //open output file for writing
        $f = fopen(DIR_FS_EMAIL_EXPORT . $file, 'w+');
        //fwrite($f,$str_export);
        // swguy
        if ($_POST['include_header_row'] == 1) { //Include the Header Row In The Export Else Leave out
            fwrite($f, $str_header);
        }
        // End swguy
        fwrite($f, $str_full_export);
        fclose($f);
        unset($f);
        //Email File to Supplier
        // send the email
        zen_mail('Supplier Name', $to_email_address, $email_subject, EMAIL_EXPORT_BODY, STORE_NAME, EMAIL_FROM, $html_msg, 'default', DIR_FS_EMAIL_EXPORT . $file);
        //Set Success Message
        $success_message = "<span style='color:#ff0000;font-weight:bold;font-size:14px;'>File processed successfully!</span>";
        /***************************Begin Update records in db if selected by user***************************/
        if ($_POST['export_test'] != 1) { //Not testing so update
            $orders_update_query = "UPDATE " . TABLE_ORDERS . " SET downloaded_ship='yes' WHERE downloaded_ship='no'";
            if ($_POST['start_date'] != '' && $_POST['end_date'] != '') {
                $orders_update_query = $orders_update_query . " AND date_purchased BETWEEN '" . $start_date . "' AND '" . $end_date . "'";
            }
            $db->Execute($orders_update_query);
        }
        /***************************End Update records in db if selected by user***************************/
    } else { // This export should be in the format of a file download so set page headers.
        Header('Content-type: application/csv');
        Header("Content-disposition: attachment; filename=" . $file . "");
        if (isset($_POST['include_header_row']) == 1) { //Include the Header Row In The Export Else Leave out  // BMH isset
            echo $str_header;
        }

        echo $str_full_export;
        /***************************Begin Update records in db if selected by user***************************/
        if ((isset($_POST['export_test'])) != 1) { //Not testing so update export_test // BMH isset 153

            $orders_update_query = "UPDATE " . TABLE_ORDERS . " SET downloaded_ship='yes' WHERE downloaded_ship='no'";
            if ($_POST['start_date'] != '' && $_POST['end_date'] != '') {
                $orders_update_query = $orders_update_query . " AND date_purchased BETWEEN '" . $start_date . "' AND '" . $end_date . "'";
                //$orders_discount_query = $orders_discount_query . " AND date_purchased >= '". $start_date ."' AND date_purchased <= '". $end_date ."'";
            }
            $db->Execute($orders_update_query);
        }
        /***************************End Update records in db if selected by user***************************/
        exit;
    }
    /*******************************************************************************************************/
}   // end export csv

// build arrays for dropdowns in order status search menu
$status_array = array();
$status_table = array();
$orders_status = $db->Execute("select orders_status_id, orders_status_name from " . TABLE_ORDERS_STATUS . "
                                   where language_id = '" . (int) $_SESSION['languages_id'] . "'
                                   order by orders_status_id ASC");
while (!$orders_status->EOF) {
    $status_array[] = array(
        'id' => $orders_status->fields['orders_status_id'],
        'text' => $orders_status->fields['orders_status_name'] . ' [' . $orders_status->fields['orders_status_id'] . ']'
    );
    $status_table[$orders_status->fields['orders_status_id']] = $orders_status->fields['orders_status_name'];
    $orders_status->MoveNext();
}
//
?>
<?php
// -----
// This section provides compatibility to load the 'legacy' stylesheets and javascript formerly used
// in Zen Cart versions prior to v2.0.0.  Here we determine the Zen Cart base version in use
// to maintain the downwardly-compatible use of this module.
//
$admin_html_head_supported = ((PROJECT_VERSION_MAJOR . '.' . PROJECT_VERSION_MINOR) >= '1.5.7');
$body_onload = ($admin_html_head_supported === true) ? '' : ' onload="init();"';
?>
<!doctype html>
<html <?php echo HTML_PARAMS; ?>>

<head>
<meta charset=<?php echo CHARSET; ?>">
<title> <?php echo TITLE; ?> </title>
<?php
    require DIR_WS_INCLUDES . 'admin_html_head.php';
?>
<link rel="stylesheet" href="includes/stylesheet.css">
<link rel="stylesheet" href="includes/cssjsmenuhover.css" media="all" id="hoverJS">
<script src="includes/menu.js"></script>
<script src="includes/general.js"></script>
<link rel="stylesheet"  href="includes/javascript/spiffyCal/spiffyCal_v2_1.css">
<script src="includes/javascript/spiffyCal/spiffyCal_v2_1.js"></script>

<script>
/* Check / Uncheck all boxes script
 * download_csv = name of form
 * checked = set checkbox checked or unchecked (true|false)
 *
*/
    checked = false;

    function checkedAll(download_csv) {
        /* document.write('<br> ln 775 checkall button pressed');
        */
        var aa = document.getElementById('download_csv');
        if (checked == false) {
            checked = true
        } else {
            checked = false
        }
        for (var i = 0; i < aa.elements.length; i++) {
            aa.elements[i].checked = checked;
        }
    }
    /* eof Check / Uncheck all Script */
</script>

</head>
<body>
    <div id="spiffycalendar" class="text"></div>
    <?php require(DIR_WS_INCLUDES . 'header.php'); ?>
    <script>
        var StartDate = new ctlSpiffyCalendarBox("StartDate", "download_csv", "start_date", "btnDate1", "", scBTNMODE_CALBTN);
        var EndDate = new ctlSpiffyCalendarBox("EndDate", "download_csv", "end_date", "btnDate2", "", scBTNMODE_CALBTN);
    </script>
    <table border="0" width="100%" cellspacing="2" cellpadding="2">
        <tr>
            <td width="100%" valign="top">
                <table border="0" width="100%" cellspacing="0" cellpadding="2">
                    <tr>
                        <td>

                            <table border="0" width="100%" cellspacing="0" cellpadding="0">
                                <tr>
                                    <td valign="top" style="padding-right:7px;">
                                        <table border="0" width="100%" cellspacing="0" cellpadding="0">
                                            <tr>
                                                <td valign="top"><span class="pageHeading">
                                                        <?php echo HEADING_SHIPPING_EXPORT_TITLE; ?>
                                                    </span> </td>
                                                <td valign="top" align="right">ESIVERSION_II:
                                                    <?php echo ESIVERSION; // BMH
                                                    ?>
                                                </td>
                                                <td align="right">
                                                    <?php echo zen_draw_separator('pixel_trans.gif', HEADING_IMAGE_WIDTH, HEADING_IMAGE_HEIGHT); ?>
                                                </td>
                                            </tr>
                                            <?php if ($success_message != "") { ?>
                                                <tr>
                                                    <td colspan=2 valign="top">
                                                        <?php echo $success_message; ?>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td colspan=2 valign="top">&nbsp;</td>
                                                </tr>
                                            <?php } //end if Success Message
                                            ?>

                                            <tr> <!-- BMH included style  export_instr -->
                                                <td class="export_instr" colspan=2 valign="top">
                                                    <?php echo TEXT_SHIPPING_EXPORT_INSTRUCTIONS; ?>
                                                </td>
                                            </tr>
                                        </table>

                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <table border="0" width="100%" cellspacing="0" cellpadding="2">
                                            <tr>
                                                <td valign="top">

                                                    <table border="0">
                                                        <tr>
                                                            <td valign="top">

                                                                <table border="0" width="100%" cellspacing="2"
                                                                    cellpadding="0">
                                                                    <tr class="dataTableHeadingRow">
                                                                        <td class="dataTableHeadingContent"
                                                                            align="center" valign="top"><?= HEADING_ORDER_INFOS_ORDER_ID ?></td>
                                                                        <td class="dataTableHeadingContent"
                                                                            align="center" valign="top"><?= HEADING_ORDER_INFOS_EMAIL ?></td>
                                                                        <td class="dataTableHeadingContent"
                                                                            align="center" valign="top"><?= HEADING_ORDER_INFOS_CUSTOMER_NAME ?></td>
                                                                        <td class="dataTableHeadingContent"
                                                                            align="center" valign="top"><?= HEADING_ORDER_INFOS_COMPANY ?></td>
                                                                        <td class="dataTableHeadingContent"
                                                                            align="center" valign="top"><?= HEADING_ORDER_INFOS_DELIVERY_STREET ?></td>
                                                                        <td class="dataTableHeadingContent"
                                                                            align="center" valign="top"><?= HEADING_ORDER_INFOS_DELIVERY_SUBURB ?></td>
                                                                        <td class="dataTableHeadingContent"
                                                                            align="center" valign="top"><?= HEADING_ORDER_INFOS_DELIVERY_CITY ?></td>
                                                                        <td class="dataTableHeadingContent"
                                                                            align="center" valign="top"><?= HEADING_ORDER_INFOS_POST_CODE ?></td>
                                                                        <td class="dataTableHeadingContent"
                                                                            align="center" valign="top"><?= HEADING_ORDER_INFOS_STATE ?></td>
                                                                        <td class="dataTableHeadingContent"
                                                                            align="center" valign="top"><?= HEADING_ORDER_INFOS_COUNTRY ?></td>
                                                                        <td class="dataTableHeadingContent"
                                                                            align="center" valign="top">&nbsp;</td>
                                                                    </tr>
                                                                    <?php
                                                                    $query = "SELECT o.orders_id, customers_email_address, delivery_name, delivery_company,
                                                            delivery_street_address, delivery_suburb, delivery_city, delivery_postcode,
                                                            delivery_state, delivery_country, shipping_method, customers_telephone,
                                                            order_total, date_purchased
                                                            FROM " . TABLE_ORDERS . " o
                                                            WHERE downloaded_ship='no'
                                                            ORDER BY orders_id ASC";

                                                                    $query = strtolower($query);

                                                                    $order_pages = new splitPageResults($_GET['page'], MAX_DISPLAY_SEARCH_RESULTS_REPORTS, $query, $rows);
                                                                    $order = $db->execute($query);

                                                                    while (!$order->EOF) {
                                                                        list(
                                                                            $order_id,
                                                                            $cust_email,
                                                                            $delivery_name,
                                                                            $delivery_company,
                                                                            $delivery_street,
                                                                            $delivery_suburb,
                                                                            $delivery_city,
                                                                            $delivery_postcode,
                                                                            $delivery_state,
                                                                            $delivery_country,
                                                                            $shipping_method,
                                                                            $customers_telephone,
                                                                            $order_total
                                                                        )
                                                                            = array_values($order->fields);   // BMH added isset
                                                                        ?>
                                                                        <!--<tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)"
                                                        onclick="window.open('<?php echo zen_href_link(FILENAME_ORDERS, 'page=1&oID=' . $order_id . '&action=edit', 'NONSSL'); ?>')"> -->
                                                                        <!-- BMH   <tr class="dataTableRow" onMouseOver="rowOverEffect(this)" onMouseOut="rowOutEffect(this)" > -->
                                                                        <tr class="dataTableRow">
                                                                            <td class="dataTableContent" align="right">
                                                                                <?php echo $order_id; ?>&nbsp;&nbsp;
                                                                            </td>
                                                                            <td class="dataTableContent">
                                                                                <?php echo $cust_email; ?>
                                                                            </td>
                                                                            <td class="dataTableContent">
                                                                                <?php echo $delivery_name; ?>
                                                                            </td>
                                                                            <td class="dataTableContent">
                                                                                <?php echo $delivery_company; ?>
                                                                            </td>
                                                                            <td class="dataTableContent">
                                                                                <?php echo $delivery_street; ?>
                                                                            </td>
                                                                            <td class="dataTableContent">
                                                                                <?php echo $delivery_suburb; ?>
                                                                            </td>
                                                                            <td class="dataTableContent">
                                                                                <?php echo $delivery_city; ?>
                                                                            </td>
                                                                            <td class="dataTableContent">
                                                                                <?php echo $delivery_postcode; ?>
                                                                            </td>
                                                                            <td class="dataTableContent">
                                                                                <?php echo $delivery_state; ?>
                                                                            </td>
                                                                            <td class="dataTableContent">
                                                                                <?php echo $delivery_country; ?>
                                                                            </td>
                                                                            <td class="dataTableContent"> <a
                                                                                    href="<?php echo zen_href_link(FILENAME_ORDERS, 'page=1&oID=' . $order_id . '&action=edit', 'NONSSL'); ?>">
                                                                                    <img src="images/icons/preview.gif"
                                                                                        border="0"
                                                                                        ALT="Preview Order Details"></a>
                                                                            </td>
                                                                        </tr>
                                                                        <?php

                                                                        $order->MoveNext();
                                                                    }

                                                                    if (!isset($order_id)) {
                                                                        ?>
                                                                        <tr class="dataTableRow">
                                                                            <td class="dataTableContent" align="center"
                                                                                colspan="30"><?= ERROR_ORDER_INFOS_NO_DATA ?></td>
                                                                        </tr>
                                                                    <?php } ?>
                                                                    <?php
                                                                    $SUBMIT_BUTTON = "<input style=\"font-weight: bold\" name=\"download_csv\" type=\"submit\" value=\"" . SUBMIT_BUTTON_ORDER_INFOS_EXPORT . "\" />";
                                                                    ?>
                                                            </td>
                                                        </tr>
                                                    </table>

                                                </td>
                                                <td width="25%" valign="top">

                                                    <table border="0" width="100%" cellspacing="0" cellpadding="2">
                                                        <tr class="infoBoxHeading">
                                                            <td class="infoBoxHeading">
                                                                <b>
                                                                    <?php echo HEADING_ADDITIONAL_FIELDS_TITLE; ?>
                                                                </b>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                    <table border="0" width="100%" cellspacing="0" cellpadding="2">
                                                        <tr>
                                                            <td class="infoBoxContent">
                                                                <?php echo TEXT_RUNIN_TEST; ?><br>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="infoBoxContent">
                                                                <form name="download_csv" id="download_csv" method="post">
                                                                    <input type='button' name='checkall' value="<?= CHECK_BUTTON_ORDER_INFOS_EXPORT ?>" onclick='checkedAll(download_csv)'><br><br>
                                                                    <?php echo zen_draw_checkbox_field('export_test', '1', $export_test_checked . 'checked'); ?> &nbsp; <?php echo TEXT_RUNIN_TEST_FIELD; ?><br>
                                                                    <?php echo zen_draw_checkbox_field('split_name', '1', $export_split_checked . 'checked'); ?> &nbsp;
                                                                    <?php echo TEXT_SPLIT_NAME_FIELD; ?><br>
                                                                    <!--  Order Status:
                                                                    <?php echo zen_draw_pull_down_menu('date_status', $status_array, $_POST['date_status'] ?? '', 'id="date_status"'); ?><br>
                                                                 -->
                                                                    <?php echo zen_draw_checkbox_field('include_header_row', '1', $export_header_row_checked . 'checked'); ?>
                                                                    &nbsp;
                                                                    <?php echo TEXT_HEADER_ROW_FIELD; ?><br><br>
                                                                    <!-- BMH -->
                                                                    <?php echo TEXT_EMAIL_EXPORT_FORMAT; ?>
                                                                    <?php echo zen_draw_pull_down_menu('format', $available_export_formats, $format); ?>

                                                                    <hr />
                                                                    <table border="0" cellspacing="0" cellpadding="2">
                                                                        <tr>
                                                                            <td><?= HEADING_AUTOMATIC_EMAIL_OPTION_TITLE ?>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td>
                                                                                <input type="checkbox" name="savetofile"
                                                                                    value="0"><?= TEXT_AUTOMATIC_EMAIL_OPTION_FIELD ?>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td><?= TEXT_EMAIL_EXPORT_ADDRESS_FIELD ?><input
                                                                                    type="text"
                                                                                    name="auto_email_supplier"
                                                                                    value="<?= EMAIL_EXPORT_ADDRESS ?>">
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td><?= TEXT_EMAIL_EXPORT_SUBJECT_FIELD ?><input
                                                                                    type="text"
                                                                                    name="auto_email_subject"
                                                                                    value="<?= EMAIL_EXPORT_SUBJECT ?>">
                                                                            </td>
                                                                        </tr>
                                                                    </table>
                                                                    <hr />
                                                                    <table border="0" cellspacing="0" cellpadding="2">
                                                                        <tr>
                                                                            <td><?= HEADING_UPDATE_ORDER_STATUS_TITLE ?></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td>
                                                                                <?php echo zen_draw_checkbox_field('status_setting', '', $order_status_setting_checked); ?>
                                                                                <?= TEXT_UPDATE_ORDER_STATUS_FIELD ?>
                                                                                <!-- </td>  </tr>  <tr>   <td> -->
                                                                                <?php echo zen_draw_pull_down_menu('order_status_setting' ?? '', $status_array, isset($_POST['order_status_setting']), '1', 'id="order_status_setting"'); ?>
                                                                            </td>
                                                                        </tr>
                                                                    </table>
                                                                    <hr />
                                                                    <table border="0" cellspacing="0" cellpadding="2">
                                                                        <tr>
                                                                            <td><?= HEADING_ORDER_STATUS_OPTIONS_TITLE ?></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td>
                                                                                <input type="radio" name="status_target"
                                                                                    value="1" checked><?= TEXT_ORDER_STATUS_OPTIONS_ANY_FIELD ?><br>
                                                                                <input type="radio" name="status_target"
                                                                                    value="2"><?= TEXT_ORDER_STATUS_OPTIONS_ASSIGNED_FIELD ?>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td>
                                                                                <?php echo zen_draw_pull_down_menu('order_status' ?? '', $status_array, $_POST['order_status'] ?? '', 'id="order_status"'); ?>
                                                                            </td>
                                                                        </tr>
                                                                    </table>
                                                                    <hr>
                                                                    <table border="0" cellspacing="0" cellpadding="2"
                                                                        width="100%" id="tbl_date_custom">
                                                                        <tr>
                                                                            <td colspan="2"> <strong>
                                                                                    <?php echo HEADING_PREVIOUS_EXPORTS_TITLE; ?>
                                                                                </strong><br>
                                                                                <?php echo TEXT_PREVIOUS_EXPORTS; ?>
                                                                                <br>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td>
                                                                                <?php echo zen_draw_checkbox_field('dload_include', '1', $dload_include_checked . 'checked'); ?>
                                                                                &nbsp;
                                                                                <?php echo TEXT_PREVIOUS_EXPORTS_FIELD; ?>
                                                                                <br><br>
                                                                            </td>
                                                                        </tr>
                                                                    </table>
                                                                    <hr>
                                                                    <?php echo TEXT_FILE_LAYOUT; ?><br>
                                                                    <?php echo zen_draw_radio_field('filelayout', '1') ?>
                                                                    &nbsp;
                                                                    <?php echo TEXT_FILE_LAYOUT_OPR_FIELD; ?><br>
                                                                    <?php echo zen_draw_radio_field('filelayout', '2', 'checked') ?>
                                                                    &nbsp;
                                                                    <?php echo TEXT_FILE_LAYOUT_PPR_FIELD; ?><br>
                                                                    <hr>
                                                                    <?php echo TEXT_ADDITIONAL_FIELDS; ?><br>
                                                                    <!-- BMH rearranged field order -->
                                                                    <?php echo zen_draw_checkbox_field('shiptotal', '1', $shipping_total_checked . 'checked'); ?>&nbsp;
                                                                    <?php echo TEXT_SHIPPING_TOTAL_FIELD; ?><br>
                                                                    <?php echo zen_draw_checkbox_field('order_total', '1', $order_total_checked . 'checked'); ?>&nbsp;
                                                                    <?php echo TEXT_ORDER_TOTAL_FIELD; ?><br>
                                                                    <?php echo zen_draw_checkbox_field('date_purchased', '1', $date_purchased_checked . 'checked'); ?>&nbsp;
                                                                    <?php echo TEXT_ORDER_DATE_FIELD; ?><br>
                                                                    <?php echo zen_draw_checkbox_field('order_tax', '1', $order_tax_checked . 'checked'); ?>&nbsp;
                                                                    <?php echo TEXT_TAX_AMOUNT_FIELD; ?><br>
                                                                    <?php echo zen_draw_checkbox_field('order_subtotal', '1', $order_subtotal_checked . 'checked'); ?>&nbsp;
                                                                    <?php echo TEXT_SUBTOTAL_FIELD; ?><br>
                                                                    <?php echo zen_draw_checkbox_field('order_discount', '1', $order_discount_checked . 'checked'); ?>&nbsp;
                                                                    <?php echo TEXT_DISCOUNT_FIELD; ?><br>
                                                                    <?php echo zen_draw_checkbox_field('payment_method', '1', $order_pmethod_checked . 'checked'); ?>&nbsp;
                                                                    <?php echo TEXT_PAYMENT_METHOD_FIELD; ?><br>
                                                                    <hr />
                                                                    <?php echo zen_draw_checkbox_field('shipmethod', '1', $shipping_method_checked); ?>&nbsp;
                                                                    <?php echo TEXT_SHIPPING_METHOD_FIELD; ?><br>
                                                                    <?php echo zen_draw_checkbox_field('order_comments', '1', $order_comments_checked); ?>&nbsp;
                                                                    <?php echo TEXT_ORDER_COMMENTS_FIELD; ?><br>
                                                                    <?php echo zen_draw_checkbox_field('customers_telephone', '1', $phone_number_checked); ?>&nbsp;
                                                                    <?php echo TEXT_PHONE_NUMBER_FIELD; ?><br>
                                                                    <?php echo zen_draw_checkbox_field('orders_status_export', '1', $order_status_checked); ?>&nbsp;
                                                                    <?php echo TEXT_ORDER_STATUS_FIELD; ?><br>
                                                                    <?php echo zen_draw_checkbox_field('iso_country2_code', '1', $iso_country2_code_checked); ?>&nbsp;
                                                                    <?php echo TEXT_ISO_COUNTRY2_FIELD; ?><br>
                                                                    <?php echo zen_draw_checkbox_field('iso_country3_code', '1', $iso_country3_code_checked); ?>&nbsp;
                                                                    <?php echo TEXT_ISO_COUNTRY3_FIELD; ?><br>
                                                                    <?php //if (ACCOUNT_STATE == 'true') {
                                                                    //echo zen_draw_checkbox_field('abbr_state_code', '1', $abbr_state_code_checked);
                                                                    ?> <!-- nbsp; -->
                                                                    <?php //echo TEXT_STATE_ABBR_FIELD;
                                                                    //}
                                                                    ?> <!-- <br> -->
                                                                    <hr>
                                                                    <?php echo zen_draw_checkbox_field('product_details', '1', $prod_details_checked . 'checked'); ?> &nbsp;
                                                                    <?php echo TEXT_PRODUCT_DETAILS_FIELD; ?>
                                                                    <span style="color: #ff0000"><strong>*</strong></span><br>
                                                                    <hr>
                                                                    <table border="0" cellspacing="0" cellpadding="2" width="100%" id="tbl_date_custom">
                                                                        <tr>
                                                                            <!--<td class="smallText" colspan="2">-->
                                                                            <td colspan="2"> <strong>
                                                                                    <?php echo HEADING_CUSTOM_DATE_TITLE; ?>
                                                                                </strong><br>
                                                                                <?php echo TEXT_CUSTOM_DATE; ?> <br>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td class="main">
                                                                                <?php echo TEXT_SPIFFY_START_DATE_FIELD; ?>
                                                                                &nbsp;
                                                                            </td>
                                                                            <td class="main">
                                                                                <script>
                                                                                    StartDate.writeControl();
                                                                                    StartDate.dateFormat = "yyyy-MM-dd";
                                                                                </script>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td class="main">
                                                                                <?php echo TEXT_SPIFFY_END_DATE_FIELD; ?>
                                                                                &nbsp;
                                                                            </td>
                                                                            <td class="main">
                                                                                <script>
                                                                                    EndDate.writeControl();
                                                                                    EndDate.dateFormat = "yyyy-MM-dd";
                                                                                </script>
                                                                            </td>
                                                                        </tr>
                                                                    </table>
                                                </td>
                                            </tr>
                                                                    <tr>
                                                                        <td align="center" class="infoBoxContent"><br>
                                                                            <?php echo $SUBMIT_BUTTON; ?>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td class="infoBoxContent"></td>
                                                                    </tr>
                                                                </form>
                                                            </td>
                                                        </tr>
                                                    </table>


                                                </td>
                                            </tr>
                                            <tr>
                                                <td colspan="3">
                                                    <table border="0" width="100%" cellspacing="0" cellpadding="2">
                                                        <tr>
                                                            <td class="smallText" valign="top">
                                                                <?php echo $order_pages->display_count($rows, MAX_DISPLAY_SEARCH_RESULTS_REPORTS, $_GET['page'], 'Displaying <b>%d</b> to <b>%d</b> (of <b>%d</b> orders)'); ?>
                                                            </td>
                                                            <td class="smallText" align="right">
                                                                <?php echo $order_pages->display_links($rows, MAX_DISPLAY_SEARCH_RESULTS_REPORTS, MAX_DISPLAY_PAGE_LINKS, $_GET['page']); ?>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                         </td>
                                                           <td colspan="3">&nbsp;</td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                                </table>
            </td>
        </tr>
    </table>
    <?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
</body>

</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
