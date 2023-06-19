OLD README file - NOT Updated
Included for historic purposes only
------------------------------------

Module Name
==================
Export Shipping Information


Version Date
============
v 1.3.4 04.19.2018


Author
======
Eric Leuenberger <econcepts@zencartoptimization.com>
http://www.zencartoptimization.com

1.3.4 Updated by: 
Lynda Leung x-rogue@mermaid.org

Support Thread
======
http://www.zen-cart.com/forum/showthread.php?t=75406


Description
===========
This module enables you, from within the Admin, to export shipping information in various formats for orders placed.

What you do with that file is up to you. Some ideas: Drop shipping fulfillment, Analysis of data in Excel spreadsheets,
bulk printing of labels, bulk printing of envelopes, etc.. you get the idea. 


Compatibility
=============
Compatible with Zen Cart v1.5.5
http://www.zen-cart.com


Files to Overwrite
==================
None (unless you are upgrading in which case you should overwrite all previous files with these new ones).


Affects DB
==========
Yes (creates new field "downloaded_ship" in Orders table)


DISCLAIMER
==========
Installation of this contribution is done at your own risk.
Backup your ZenCart database and any and all applicable files before proceeding.


Features:
=========
- Exports shipping data in various formats for use in external programs like MS Excel
- After records are exported, they are marked as such in the database so duplicate data is not exported again
- Generates unique filenames for each export based on date and time of export
- Ability to run in Test Mode
- Option to export in 2 different layout formats
	1) 1 Order per row (default)
	2) 1 Product per row
- Added option to export the following new fields:  Order Tax Amt, Order Subtotal, Order Discounts, Payment Method, Order Status
- Added option to split first and last name into 2 separate columns in export
- Added abililty to re-export data that had previously been exported
- Added date range select ability so reports could be run based on a selected date range.
- Added ability to export on any order status or a pre-selected order status.
- Ability to have the exported file either downloaded to your computer or automatically sent to a desired email address.
- Ability to automatically update the order status after a successful export. Status can be updated to what ever you choose (from dropdown).
- Ability to include header row or leave it out of your exported file. 


Install / Ugrade:
=================
1. Upload the entire contents of the "admin" folder to your website. All directories are
   already named for you and there are no files to overwrite so it should be easy.
   NOTE: If you changed the name of your "admin" directory to something else, then upload
         to that directory.

2. Login Admin > Tools > Install SQL Patches and run the query (copy and paste into the admin to run) found in the "INSTALL.sql" file provided with the download.
   NOTE: If you are upgrading from a previous version, you do not need to re-run this SQL command as your
         database should already contain this field.

3. Set directory permissions on /images/uploads/ to "777" (WRITE access) if not already done.
   If emailing files to suppliers, you will need to write the file info to a directory on your
   server. The directory the files are written to is /images/uploads/.

4. Enjoy!


Upgrading from version 1.2.1 or higher:
==============================
If you are upgrading from version 1.2.1 or higher all you have to upload is the files contained in
this release overwriting your current ones (be sure to back up and or merge if you made your own
custom changes or they will be lost.)

You do NOT need to apply any SQL patches under these conditions.


Uninstall
=========
1. To uninstall, delete each of the files you uploaded from this package and then

2. Login Admin > Tools > Install SQL Patches and run the following file query:

ALTER TABLE orders DROP downloaded_ship;


How To Use:
===========

1. From within the admin, go to Tools > Export Shipping/Order Information

2. If there are any new orders placed since your last export, you'll see them listed on the screen. Click on the "Export to Excel Spreadsheet" button.

3. You'll be prompted to save the file down to your computer. Save it to your desired location and you're all set


NOTE: If there are no orders found to export, then the "Export" button will not show.

A video tutorial is available for learning how to use this module. It can be accessed by clicking the link in the upper right hand corner
of the screen from within the Module itself (in the Admin of your cart).


History
=======
v1.0.0 Initial Release
-------
v1.2.0 Add the ability for users to export indiviual prouduct and attribute information for an order
-------
v1.2.1
- Added the ability to export in two different file formats
	- 1 Order per row (default)
	- 1 Product per row
	
- Added "Test" feature. This enables you to run a test export without marking the orders as "exported" in the system. 
   It can be handy if you want to export items and test import into other programs beefore you actually want them to
   bee marked as "exported" (which removes them from future abilities to download). Once you are satisfied with your
   test, de-select that option, and export as normal. The orders will all export and be marked as "exported" so they
   will not show up in future downloads.
   
- Corrected several bugs from version 1.2.0
	- Text qualifier (quote) " added to eliminate records breaking to a new line when a comma was present (mainly in addresses).

	- Added header information and set number of columns to each export making it easier to import into other programs.

	- Corrected issues with multiple lines being exported when more than one comment was present for an order. Now only the first comment
      is exported with the list. This is typically the comment that the customer left while placing the order the first time.

	- All product attributes are added to a single column and are separated by a pipe delimiter " | "

	- Corrected issues with order comments that span multiple lines throwing export off
---------
v1.2.2
- Bugfix: Corrected "table not found" error message when exporting all attributes / 1 product per row
- Bugfix: Corrected placement of Zip code and State fields in export.

---------
v1.2.3
- Bugfix: Corrected comments bug that caused the comment field to split across multile columns when apostrophe was present.

1) Changed File layout option to radio field from checkboxes
2) Added option to export the following new fields:  Order Tax Amt, Order Subtotal, Order Discounts, Payment Method, Order Status
3) Added option to split first and last name into 2 separate columns in export
4) Added abililty to re-export data that had previously been exported
5) Added date range select ability so reports could be run based on a selected date range.
6) Added ability to export on any order status or a pre-selected order status.

----------
v1.2.4
- Bugfix: Corrected issue with the Order Export button not showing up on some users machines.

1) Added option to export ISO country codes. Both 2 character and 3 character codes were added.
----------

v1.2.5
1) Added option to export Abbreviations for State codes.

----------
v1.2.6
1) Added ability to export manufacturers name with order.


----------
v1.3.0
- Added ability to have the exported file either downloaded to your computer or automatically sent to a desired email address.
- Added ability to automatically update the order status after a successful export. Status can be updated to what ever you choose (from dropdown).
- Added ability to include header row or leave it out of your exported file. 
- Added the ability to export different file types. (CSV / TXT)
- Bugfix: REMOVED the option to export manufacturing data as it was causing problems. (This will be added in the next release after worked on a bit.)
- Bugfix: Corrected issues with Company Name not showing up in export.
- Bugfix: Corrected issue with some people getting a header row but no orders exported.
- Moved product details field data to the end of the export again. After recent additions to new fields the product details were showing up in the middle of the exported file and this could throw off columns if exporting 1 order per line on mixed orders with attributes and without.

------------
V1.3.1
- Bugfix: corrected error when all orders were marked as exported even if a certain date range was used.
- Bugfix: Corrected issues with query that only exported the first order row even when multiple orders were present to export.
- Bugfix: Corrected section to clean up extra line breaks etc... in comments and attributes.

------------
V1.3.2

Includes following updates by Scott Wilson (That Software Guy - swg)
- Bugfix: Corrected header written (if so selected) when writing to a file. Previously this was not exporting.
- ADDITION: output includes flag for residential/commercial which is required by some shippers (sucah as UPS.)  Also includes business name if commercial.
Additional Edits:
- REMOVED: Removed the option to export state abbreviations. This is a temporary removal as it was causing orders to be dropped when activated. Future versions will attempt to address this again (when added back in.)
- Bugfix: Altered the way ISO country codes are exported to remedy issues with orders possibly being dropped do to this feature.

------------
V1.3.3
- Added files for compatibility with Zen Cart version 1.5.3 or newer in a separate folder (it should work with earlier versions as well).
  
V1.3.4
- Removed support for anything lower than Zencart 1.5.5.
- Updated MagpieRSS version to 0.72 
- Updated to work with PHP 7.x



RESET the orders downloaded to "no"
----------------------------------------
UPDATE orders SET downloaded_ship="no" WHERE downloaded_ship="yes"

v.1.3.5

- Bugfix: updated line 234 which previously contained function split() which was DEPRECATED in PHP 5.3.0, and REMOVED in PHP 7.0.0.
