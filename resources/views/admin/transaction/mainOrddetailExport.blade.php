<html>
<meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <!-- <meta name="viewport" content="width=device-width, initial-scale=1"> -->
    <meta http-equiv="Content-Type" content="800; charset=utf-8" />
    <title>Main order PDF Table</title>

<style>
    @font-face {
        font-family: "THSarabunNew";
        font-style: normal;
        font-weight: normal;
        src: url("/pdf_fonts/THSarabunNew.ttf") format("truetype");
    }
    html { -webkit-print-color-adjust: exact; }
    body {    
        font-family: Helvetica, sans-serif;
        color: #000; font-size: 16px; line-height: 1.3;
    }
    table {
        border-collapse: collapse;
        border: 0; 
    }
    th,
    td {
        border-collapse: collapse;
        padding: 3px 8px;
        /* border: 1px solid #CED4DA; */
        border:none; font-size: 16px;
    }
    th {
        font-weight: bold;
    }
    td {
        vertical-align: top;
    }
    img {
        line-height: 1; max-width: 100%;
        vertical-align: top;
    }
    a {
        text-decoration: none;
    }
    .data-tables td, .data-tables th {
        padding-top: 15px; padding-bottom: 15px;
        text-align: center;
    }
    .data-tables td {
        border-bottom: 1px solid #000;        
    }
    .border-0 {
        border: 0;
    }
    .border-0 td,
    .border-0 th {
        border: 0
    }
    .red {
        color: #F00;
    }

    /*  */
    @media  print {    
        .red, .dont-forget { background: #DC3545; }  
        .voucher-page {page-break-after: always; margin-top: 30px;}
    }
</style>

<body style="font-family: Helvetica, sans-serif; -webkit-font-smoothing: antialiased; line-height: 1.3; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%;  margin: 0; padding: 0;">
    <div class="container">
        <table border="0" cellpadding="0" cellspacing="0" align="center" style="font-family: Helvetica, sans-serif; width: 1000px; color:#17499c; line-height:1.3;">
            <!-- First row starts -->
            <tr>              
                <td style="line-height: 18px; padding:10px 15px; box-shadow: 0px 4px 10px 0px rgba(0, 0, 0, 0.10);">
                   <span style="color: #F00;">Main Order ID:</span> SMM24010100000
                </td>
            </tr>
            <tr><td style="height:40px;"></td></tr> 
            <!-- Second row starts -->
            <tr>
                <td>
                    <table border="0" cellpadding="0" cellspacing="0" width="100%">
                        <tr>
                            <td style="text-align: center; border-right: 1px solid #000; width: 50%; padding: 30px 5px; font-size:22px;">
                                <div style="margin-bottom: 12px;">Main order status :</div>
                                รอยืนยันการชำระเงิน
                            </td>
                            <td style="text-align: center; padding: 30px 5px; font-size:22px; width: 50%;">
                                <div style="margin-bottom: 12px;">ยอดรวม</div>
                                <span style="font-size: 28px;">THB 60</span>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
         
            <tr><td style="height:40px;"></td></tr> 
            <!-- Third row starts -->
            <tr>
                <td style="border:1px solid #000; padding:12px 10px;">
                    <table border="0" cellpadding="0" cellspacing="0" width="100%">
                        <tr>
                            <td colspan="4" style="font-size:24px;">Buyer Infomation</td>
                        </tr>
                        <tr><td style="height:22px;"></td></tr> 
                        <tr>
                            <td style="width:25%;">
                                ชื่อ : Name <br>
                                อีเมล : Email <br>
                                เบอร์โทรศัพท์ :0990990999
                            </td>
                            <td style="width:25%;">
                                <div style="margin-bottom:15px;">ที่อยู่ในการจัดส่ง : Location name</div>
                                <div style="margin-bottom:15px;">name</div>
                                <div style="margin-bottom:15px;">123 <br> 
                                    location <br> 12002</div>
                                <div style="margin-bottom:15px;">0990990999</div>

                            </td>
                            <td style="width:25%;">
                                <div style="margin-bottom:15px;">ที่อยู่ในการออกใบเสร็จ : Location name</div>
                                <div style="margin-bottom:15px;">name</div>
                                <div style="margin-bottom:15px;">123 <br> 
                                    location <br> 12002</div>
                                <div style="margin-bottom:15px;">0990990999</div>

                            </td>
                            <td style="width:25%;">
                                <div style="margin-bottom:15px;">Shipping : จัดส่งตามที่อยู่จัดส่ง</div>
                                <div style="margin-bottom:15px;">Pickup Date : <br>2024-01-11 09:00:00</div>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
 

            <!-- Forth row starts -->
            <tr><td style="height:30px;"></td></tr> 
            <tr>
                <td style="padding-left: 0; padding-right: 0px;">
                    <table border="0" cellpadding="0" cellspacing="0" width="100%">
                        <tr>
                            <td style="width:50%; font-size: 24px; padding-left:0px;">Shop order id : AA000000000001</td>
                            <td class="red" style="text-align:right; width:50%; font-size: 22px; padding-right:0px;">Shop order status : รอยืนยันการชำระเงิน</td>
                        </tr>
                        <tr><td style="height:5px;"></td></tr>
                    </table>
                </td>
            </tr>

            <tr>
                <td style="border:1px solid #000; border-bottom:none; padding:0;">
                    <table border="0" cellpadding="0" cellspacing="0" width="100%" class="data-tables">
                        <tr>
                            <th style="text-align:center; font-weight: normal;">สินค้า</th>
                            <th style="text-align:center; font-weight: normal;">ร้านค้า</th>
                            <th style="text-align:center; font-weight: normal;">ราคา <br> ต่อหน่วย</th>
                            <th style="text-align:center; font-weight: normal;">จำนวน</th>
                            <th style="text-align:center; font-weight: normal;">ราคารวม <br></th>
                            <th style="text-align:center; font-weight: normal;">ช่องทางการ <br>ชำระเงิน</th>
                            <th style="text-align:center; font-weight: normal;">สถานะ </th>
                            <th style="text-align:center; font-weight: normal;">รายละเอียด <br>สินค้า</th>
                            <th style="text-align:center; font-weight: normal;">Action</th>
                        </tr>
                        <tr>
                            <td style="text-align:left;">
                                <div style="margin-bottom:4px;"><img src="images/prod.png" alt="img"> </div>
                                <div style="margin-bottom: 2px;">ส้ม</div>
                                <div>
                                    <span style="border:1px solid green; font-size: 8px;
                                    padding:2px; display: inline-block; border-radius: 50%; width:20px; height:20px; line-height: 20px; text-align: center;">XLA</span>
                                     จัมโบ้ | สวย
                                </div>
                            </td>
                            <td style="text-align:left;">
                                <div style="margin-bottom:4px;"><img src="images/prod2.png" alt="img"> </div>
                                Name
                            </td>
                            <td>60 บาท / <br>กล่อง</td>
                            <td>1 กล่อง <br> <span class="red">10 กล่อง <br> / กล่อง</span></td>
                            <td>60 บาท</td>
                            <td>QR Code</td>
                            <td class="red">ชำระเงิน</td>
                            <td>Test</td>
                            <td></td>
                        </tr>
                        <tr>
                            <td style="text-align:left;">
                                <div style="margin-bottom:4px;"><img src="images/prod.png" alt="img"> </div>
                                <div style="margin-bottom: 2px;">ส้ม</div>
                                <div>
                                    <span style="border:1px solid green; font-size: 8px;
                                    padding:2px; display: inline-block; border-radius: 50%; width:20px; height:20px; line-height: 20px; text-align: center;">XLA</span>
                                     จัมโบ้ | สวย
                                </div>
                            </td>
                            <td style="text-align:left;">
                                <div style="margin-bottom:4px;"><img src="images/prod2.png" alt="img"> </div>
                                Name
                            </td>
                            <td>60 บาท / <br>กล่อง</td>
                            <td>1 กล่อง <br> <span class="red">10 กล่อง <br> / กล่อง</span></td>
                            <td>60 บาท</td>
                            <td>QR Code</td>
                            <td class="red">ชำระเงิน</td>
                            <td>Test</td>
                            <td></td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr><td style="height:40px;"></td></tr> 


            
            <!-- Table footer starts -->
            <tr>
                <td style="padding-left:0px; padding-right:0px;">
                    <table border="0" cellpadding="0" cellspacing="0" width="100%">
                        <tr>
                            <td style="width:50%">checkout.shop_remark <br>
                            -</td>
                            <td style="width:50%; border:1px solid #000; padding:0; border-bottom:none;">
                                <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                    <tr>
                                        <td style="padding: 10px; border-bottom:1px solid #000;">ยอดรวม</td>
                                        <td style="padding: 10px; border-bottom:1px solid #000; text-align: right;">60 บาท</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 10px; border-bottom:1px solid #000;">ยอดรวมทั้งหมด</td>
                                        <td style="padding: 10px; border-bottom:1px solid #000; text-align: right;">60 บาท</td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr><td style="height:50px;"></td></tr>

            <!-- Footer starts -->


        </table>
    </div>
</body>

</html>