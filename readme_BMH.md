Module Name
==================
**Export Shipping Information **


Version Date
============
v 1.4.1c 2023-06-17

Updated by:
=======
OldNGrey (BMH)

Compatibility
=============
Compatible with Zen Cart v1.5.8 PHP 8.2
http://www.zen-cart.com

Support Thread
======
http://www.zen-cart.com/forum/showthread.php?t=75406


Description
===========
This module enables you, from within the Admin, to export shipping information in various formats for orders placed.

What you do with that file is up to you. Some ideas: Drop shipping fulfillment, Analysis of data in Excel spreadsheets,
bulk printing of labels, bulk printing of envelopes, etc., you get the idea. 
Changes
=======
BMH 2023-06-17
    ln50 set version to 1.4.1c
    discounts now include all (ot_coupon' , 'ot_custom', 'ot_group_pricing', 'ot_payment_type' , 'ot_paymentmodule) summed and made negative
    DISTINCT values only returned for when multiple discounts applied
    moved shipping total heading and values to follow after subtotal headings and values
    
BMH 2023-03-03
    admin\shipping_export.php
        ln50 set version to 1.4.1a
        reordered data output to match reconciliation spreadsheet
            Order ID, Customer Email, First Name, Last Name, Company, Delivery Street, Delivery Suburb, Delivery City, 
                Delivery State, Delivery Post Code, Delivery Country, Ship Dest Type, Order Date, Product Qty, 
                Product Model, Product Name, Products Price, Product Attributes, Line cost, Line tax, 
                Shipping Total, Order Subtotal, Order Discount, Order Total, Order Tax, Payment Method
        calculated value line cost, line tax is a filler but not calculated 
        defaulted most commonly used checkboxes to "Checked" 
        reordered options to most commonly used  to suit reconciliation spreadsheet

BMH 2023-01-15  
    admin\shipping_export.php
        ln12 set version to 1.4.1
        ln668 $success_message not set on initial load
        ln84, 87, 96, 104, 243, 246, 249, 283, 936, 938, 940, 942, 944 ,946, 949, 951, 953, 955, 957, 959, 961, 968 check var isset - for initial load
        ln374 include other discount types - only picks up first one
    \admin\includes\languages\extra_definitions
        create zc158 lang file lang-shipping_export.php
----------------------------
**Boxes ticked by default are:**
============================
Run In Test Mode
Export First and Last Name into Separate Fields.
Include Header Row In Export
Any Order Status
Include orders already downloaded in export.
1 Product per row
Shipping Total
Order Total
Order Date
Order Tax Amount
Order Subtotal
Order Discount
Payment Method
Full Product Details *






