<?php

$data_h = "H";
$data_p = "P";
$data_p_product_code = "DCT";

$data_client_code = "HDONMUANG";
$data_client_account_no = "3731037174";

$data_customer_name_lastname = "Thanut Benjapattharaseth"; // Seller Name
$data_total_order_for_one_seller = 62504.00; // Seller Total Complete Order Amount
$data_total_order_date = "26/05/2017"; // Seller Total Complete Order Date

$data_i = "I";
$data_benef_bank_code = "004";
$data_benef_branch_code = "0040745";
$data_benef_bank_acc_no = ""; // Seller Account no.

echo "<hr>";

$myfile = fopen("h.txt", "r") or die("Unable to open file!");
$h_data = fread($myfile,filesize("h.txt"));
fclose($myfile);

// $record_identifier = substr($h_data,1-1,1);
$record_identifier = $data_h;
$no_use_2 = str_repeat(' ', 12);
$no_use_3 = str_repeat(' ', 10);
$no_use_4 = str_repeat(' ', 20);
$no_use_5 = str_repeat(' ', 10);
$no_use_6 = str_repeat(' ', 10);
$no_use_7 = str_repeat(' ', 10);
$no_use_8 = str_repeat(' ', 10);
$no_use_9 = str_repeat(' ', 20);

$h_data = $record_identifier . $no_use_2 . $no_use_3 . $no_use_4 . $no_use_5 . $no_use_6 . $no_use_7 . $no_use_8 . $no_use_9;

echo "Record Identifier : " . $record_identifier . "<br />";

echo "<strong>" . $h_data . "</strong><br />";

echo "<hr>";

$myfile = fopen("p.txt", "r") or die("Unable to open file!");
$p_data = fread($myfile,filesize("p.txt"));
fclose($myfile);

// $record_identifier = str_pad(substr($p_data,1-1,1), 1, " ", STR_PAD_LEFT);

$record_identifier = $data_p;

// $product_code = str_pad(substr($p_data,2-1,10), 10, " ", STR_PAD_LEFT);
$product_code = str_pad($data_p_product_code, 10, " ", STR_PAD_LEFT);

$no_use_3 = str_repeat(' ', 10);
$no_use_4 = str_repeat(' ', 10);
$no_use_5 = str_repeat(' ', 10);
$no_use_6 = str_repeat(' ', 5);
$no_use_7 = str_repeat(' ', 20);
// $client_code = str_pad(substr($p_data,67-1,20), 20, "0", STR_PAD_LEFT);
$client_code = str_pad($data_client_code, 20, " ", STR_PAD_LEFT);

$no_use_9 = str_repeat(' ', 10);
// $client_account_id = str_pad(substr($p_data,97-1,20), 20, " ", STR_PAD_LEFT);
$client_account_id = str_pad($data_client_account_no, 20, " ", STR_PAD_LEFT);
$no_use_11 = str_repeat(' ', 10);
$no_use_12 = str_repeat(' ', 10);
$no_use_13 = str_repeat(' ', 10);
$no_use_14 = str_repeat(' ', 10);
$no_use_15 = str_repeat(' ', 20);
$no_use_16 = str_repeat(' ', 10);
$no_use_17 = str_repeat(' ', 10);
$no_use_18 = str_repeat(' ', 10);
$no_use_19 = str_repeat(' ', 255);
$no_use_20 = str_repeat(' ', 10);

echo "Record Identifier : " . $record_identifier . "<br />";
echo "Product Code : " . $product_code . "<br />";
echo "Client code : " . $client_code . "<br />";
echo "Client account id : " . $client_account_id . "<br />";

$p_data = $record_identifier . $product_code . $no_use_3 . $no_use_4 . $no_use_5 . $no_use_6 . $no_use_7 . $client_code . $no_use_9 . $client_account_id . $no_use_11 . $no_use_12 . $no_use_13 . $no_use_14 . $no_use_15 . $no_use_16 . $no_use_17 . $no_use_18 . $no_use_19 . $no_use_20;

echo "<strong>" . $p_data . "</strong><br />";

echo "<hr>";

$myfile = fopen("i.txt", "r") or die("Unable to open file!");
$i_data = fread($myfile,filesize("i.txt"));
fclose($myfile);

// $record_identifier = str_pad(substr($i_data,1-1,1), 1, " ", STR_PAD_LEFT);
$record_identifier = $data_i = "I";
$no_use_2 = str_repeat(' ', 20);
$no_use_3 = str_repeat(' ', 10);
// $benef_desc = str_pad(substr($i_data,32-1,80), 80, " ", STR_PAD_LEFT);
$benef_desc = str_pad($data_customer_name_lastname, 80, " ", STR_PAD_LEFT);

$no_use_5 = str_repeat(' ', 10);
$no_use_6 = str_repeat(' ', 10);
$no_use_7 = str_repeat(' ', 10);
$no_use_8 = str_repeat(' ', 20);
// $inst_payment_amnt = str_pad(substr($i_data,162-1,20), 20, " ", STR_PAD_LEFT);
$inst_payment_amnt = sprintf("%'020.2f", $data_total_order_for_one_seller);

$no_use_10 = str_repeat(' ', 20);
// $inst_date = str_pad(substr($i_data,202-1,10), 10, " ", STR_PAD_LEFT);
$inst_date = str_pad($data_total_order_date, 10, " ", STR_PAD_LEFT);

$benef_bank_code = str_pad($data_benef_bank_code, 10, " ", STR_PAD_LEFT);
$benef_branch_code = str_pad($data_benef_branch_code, 10, " ", STR_PAD_LEFT);
$benef_bank_acc_no = str_pad($data_benef_bank_acc_no, 20, " ", STR_PAD_LEFT);

$no_use_15 = str_repeat(' ', 16);
$no_use_16 = str_repeat(' ', 4);
$no_use_17 = str_repeat(' ', 150);
$no_use_18 = str_repeat(' ', 150);
$no_use_19 = str_repeat(' ', 255);
$delivery_mode = str_repeat(' ', 10);
$no_use_21 = str_repeat(' ', 10);
$no_use_22 = str_repeat(' ', 10);
$no_use_23 = str_repeat(' ', 1);
$no_use_24 = str_repeat(' ', 1);
$no_use_25 = str_repeat(' ', 20);
$no_use_26 = str_repeat(' ', 20);
$no_use_27 = str_repeat(' ', 10);
$no_use_28 = str_repeat(' ', 24);
$no_use_29 = str_repeat(' ', 20);
$no_use_30 = str_repeat(' ', 20);
$no_use_31 = str_repeat(' ', 20);
// $payee_name = str_pad(substr($i_data,993-1,120), 120, " ", STR_PAD_LEFT);
$payee_name = str_pad($data_customer_name_lastname, 120, " ", STR_PAD_LEFT);

$no_use_33 = str_repeat(' ', 20);
$no_use_34 = str_repeat(' ', 54);
$no_use_35 = str_repeat(' ', 2);
$no_use_36 = str_repeat(' ', 1720);
$no_use_37 = str_repeat(' ', 1);
$no_use_38 = str_repeat(' ', 255);
$no_use_39 = str_repeat(' ', 1);
$no_use_40 = str_repeat(' ', 10);
$no_use_41 = str_repeat(' ', 20);
// $beneficiary_pickup_location_code = str_pad(substr($i_data,3196-1,30), 30, " ", STR_PAD_LEFT);
$beneficiary_pickup_location_code = str_repeat(' ', 30);

$no_use_43 = str_repeat(' ', 50);
$no_use_44 = str_repeat(' ', 50);

echo "Record Identifier : " . $record_identifier . "<br />";
echo "Benef Desc : " . $instruction_reference_nmbr . "<br />";
echo "Inst. Payment amnt : " . $inst_payment_amnt . "<br />";
echo "Inst. Date : " . $inst_date . "<br />";
echo "Delivery mode : " . $delivery_mode . "<br />";
echo "Payee Name : " . $payee_name . "<br />";
echo "Beneficiary Pickup Location Code : " . $beneficiary_pickup_location_code . "<br />";

$i_data = $record_identifier . $no_use_2 . $no_use_3 . $benef_desc . $no_use_5 . $no_use_6 . $no_use_7 . $no_use_8 . $inst_payment_amnt . $no_use_10 . $inst_date . $no_use_12 . $benef_bank_code . $benef_branch_code . $benef_bank_acc_no . $no_use_16 . $no_use_17 . $no_use_18 . $no_use_19 . $delivery_mode . $no_use_21 . $no_use_22 . $no_use_23 . $no_use_24 . $no_use_25 . $no_use_26 . $no_use_27 . $no_use_28 . $no_use_29 . $no_use_30 . $no_use_31 . $payee_name . $no_use_32 . $no_use_33 . $no_use_34 . $no_use_35 . $no_use_36 . $no_use_37 . $no_use_38 . $no_use_39 . $no_use_40 . $no_use_41 . $beneficiary_pickup_location_code . $no_use_43 . $no_use_44;

echo "<strong>" . $i_data . "</strong>";

echo "<hr>";

$myfile = fopen("t.txt", "r") or die("Unable to open file!");
$t_data = fread($myfile,filesize("t.txt"));
fclose($myfile);

$data_t = "T";

$record_identifier = $data_t;
$no_use_2 = str_repeat(' ', 5);
$no_use_3 = str_repeat(' ', 20);
$no_use_4 = str_repeat(' ', 5);
$no_use_5 = str_repeat(' ', 20);

echo "Record Identifier : " . $record_identifier . "<br />";

$i_data = $record_identifier . $no_use_2 . $no_use_3 . $no_use_4 . $no_use_5;

echo "<strong>" . $i_data . "</strong>";

echo "<hr>";

?>