jQuery(document).ready(function($){

    $('body').on('click','#add_user_address',function(){
        // ถ้ามี map picker modal ฝังอยู่ในหน้า ให้แสดง popup modal
        if ($('#map-picker-modal').length) {
            $('#map-picker-modal').modal('show');
        } else {
            // Fallback: โหลดฟอร์มเพิ่มที่อยู่โดยตรง (สำหรับหน้าอื่นที่ไม่มี map modal)
            if(typeof create_address_url !== 'undefined'){
                $('#popupdiv').load(create_address_url, {call_type:'ajax_data'});
            }
        }
    });

    $('body').on('change','.address_dd',function(){    
        var $sel = $(this);
        // Auto-fill zip จาก data-zip เมื่อเลือกแขวง/ตำบล (SMM) - ทำก่อน dropDownHandler
        if(typeof use_smm_address !== 'undefined' && use_smm_address && $sel.attr('name') === 'sub_district'){
            var zip = $sel.find('option:selected').attr('data-zip') || $sel.find('option:selected').data('zip');
            if(zip) $('#add-address #zip_code, #zip_code').val(zip);
        }
        dropDownHandler($sel.val(), $sel.attr('name'), $sel.attr('address_seq'));
    });

    /**
     * นำข้อมูลที่อยู่จัดส่งไปใส่ในช่องที่อยู่บริษัท (เรียงยาวติดกันบรรทัดเดียว)
     * ใช้เมื่อกด checkbox "ที่อยู่เดียวกับที่อยู่จัดส่ง"
     */
    function updateCompAddr(){
        var $frm = $('#addess_frm');
        if (!$frm.length) return;

        var parts = [];
        var addr = $.trim($frm.find('[name="address"]').val());
        var road = $.trim($frm.find('[name="road"]').val());
        var province = $.trim($frm.find('[name="province_state"] option:selected').text()).replace(/^--.*--$/, '');
        var district = $.trim($frm.find('[name="city_district"] option:selected').text()).replace(/^--.*--$/, '');
        var subDistrict = '';
        var zipCode = '';
        var phNumber = $.trim($frm.find('[name="ph_number"]').val());

        if ($frm.find('[name="sub_district"]').length) {
            subDistrict = $.trim($frm.find('[name="sub_district"] option:selected').text()).replace(/^--.*--$/, '');
        }
        var $zip = $frm.find('[name="zip_code"]');
        zipCode = $zip.is('select') ? ($zip.val() || '') : ($zip.val() || '');
        zipCode = $.trim(String(zipCode));

        if (addr) parts.push(addr);
        if (road) parts.push('ถนน ' + road);
        if (subDistrict) parts.push(subDistrict);
        if (district) parts.push(district);
        if (province) parts.push(province);
        if (zipCode) parts.push(zipCode);
        if (phNumber) parts.push('โทร. ' + phNumber);

        var txt_address = parts.join(' ');
        if (txt_address) {
            $('#company_address').val(txt_address);
        }
    }

    // ส่วนใบกำกับภาษีแสดงเป็นมาตรฐาน (ไม่มี checkbox) - ส่ง tax_invoice เฉพาะเมื่อกรอกข้อมูลบริษัท
    $('body').on('click','#company_address',function(e){
        if($.trim($(this).val())== ''){
            updateCompAddr();
        }
    })

    $('body').on('change','#same_as_address',function(){    
        var isChecked = $(this).is(':checked');
        if(isChecked === true) {
            //$('#company_address').text($('#address').val());
            updateCompAddr();
        }
        else {
            $('#company_address').text('');
            $('#company_address').val('');
        }
    });         

    //Make element draggable
    $("#sortable .drag").draggable({
        helper: 'clone',
        handle : '.ui-draggable-handle',
        cursor: 'move',
        revert: true,
        revertDuration: 0,
    });

    //Make element sortable
    $("#sortable").sortable({

        items: "li:not(:first)",
        revert: true,
        //containment: "parent",
        cursor: 'move',
        update: function (event, ui) {

            var order = $(this).sortable('toArray');
            var ajax_url = sort_address_url;
            var data = {sequence:order};

            callAjaxRequest(ajax_url, 'post', data, function(response){
                if(response == 'success'){
                    swal(lang_json.success,lang_json.order_updated_successfully,'success')
                    .then(function () {
                        //setTimeout(function(){ location.reload(); }, 2000);
                    });                     
                } 
            });           
        }
    }).disableSelection();
});

function SubmitAddressForm() {
    // ส่ง tax_invoice เฉพาะเมื่อกรอกข้อมูลบริษัท (ชื่อบริษัท หรือ เลขประจำตัวผู้เสียภาษี หรือ ที่อยู่บริษัท)
    var $frm = $("#addess_frm");
    var hasCompany = $.trim($frm.find('[name="company_name"]').val()) !== '' ||
        $.trim($frm.find('[name="tax_id"]').val()) !== '' ||
        $.trim($frm.find('[name="company_address"]').val()) !== '';
    $frm.find('input[name="tax_invoice"]').remove();
    if (hasCompany) {
        $frm.append('<input type="hidden" name="tax_invoice" value="1">');
    }
    var ajax_url = store_address_url;
    var data = $frm.serialize();

    callAjaxRequest(ajax_url, 'POST', data, function(response){
        response = JSON.parse(response);
        if(response.status == 'success'){
           swal(lang_json.success, response.message, 'success')
           .then(function(){ location.reload(); }); 
        }
        else if(response.status == 'validate_error'){
            $('.error-msg').text('');
            $.each(response.message, function(key,val){
                $('#error_'+key).text(val);
            });
        }          
    });  
}

function deleteAddress(id){
    swal({
        //title: 'Are you sure?',
        text: lang_json.are_you_sure_to_delete_this_record,
        type: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        cancelButtonText: lang_cancel,
        confirmButtonText: lang_json.yes_delete_it

    }).then(function () {

        var ajax_url = delete_url; 
        var data = {action:'delete', id:id};   

        callAjaxRequest(ajax_url, 'post', data, function(response){
            if(response == 'success'){
                swal(lang_json.deleted, lang_json.records_deleted_successfully, 'success')
                .then(function() {location.reload();});                     
            } 
        });
    },function(){
        return false;
    });
}

function setDefault(type, id) {
    if(type == '1'){
        var lang_confirm = lang_json.are_you_sure_shipping;
    }else{
        var lang_confirm = lang_json.are_you_sure_to_billing;
    }
    swal({
        //title: 'Are you sure?',
        text: lang_confirm,
        type: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: lang_json.ok,
        cancelButtonText: lang_cancel,

    }).then(function () {

        var ajax_url = set_default_address_url;
        var data = {address_type:type, address_id:id};
      
        callAjaxRequest(ajax_url, 'post', data, function(response){
            if(response == 'success'){
                swal(lang_json.success+'!', lang_json.records_updated_successfully, 'success')
                .then(function () {location.reload();});                     
            } 
        });
    },function(){
        return false;
    });             
}

function dropDownHandler(address_id, address_type, address_seq){  
    var ajax_url = typeof address_dd_url !== 'undefined' ? address_dd_url : '';
    if (!ajax_url) return;
    var data = {address_id: address_id, address_type: address_type};

    callAjaxRequest(ajax_url, 'post', data, function(result){
        var response;
        try {
            response = (typeof result === 'string') ? JSON.parse(result) : result;
        } catch (e) {
            console.error('Address dropdown parse error:', e, 'Raw:', typeof result, result);
            return;
        }
        if (!response || !response.status) {
            if (response && response.error) console.error('Address DD error:', response.error);
            return;
        }

        if(response.status == 'success'){
            address_seq = parseInt(address_seq, 10) + 1;
            var $nextDd = $('#address_dd_'+address_seq);
            if($nextDd.length) {
                $nextDd.html(response.opt_str || '<option value="">--เลือก--</option>');
            }
        }

        // SMM Master (จังหวัด→เขต→ตำบล→รหัสไปรษณีย์ auto-fill)
        if(typeof use_smm_address !== 'undefined' && use_smm_address){
            if(address_type == 'province_state') {
                $('#zip_code').val('');
                if($('#address_dd_3').length) $('#address_dd_3').html('<option value="">--เลือก--</option>');
            } else if(address_type == 'city_district') {
                $('#zip_code').val('');
            } else if(address_type == 'sub_district') {
                var zip = response.zip_code || $('#address_dd_3 option:selected').attr('data-zip') || $('#address_dd_3 option:selected').data('zip');
                if(zip) $('#zip_code').val(String(zip));
            }
        } else {
            // ระบบเดิม (country_province_state)
            if(address_type == 'province_state') {
                $('#zip_code').val('');
            } else if(address_type == 'city_district') {
                $('#zip_code').html(response.zip_data || '');
            }
        }

        cleanChildSelectBox(address_seq);
    });
}

function cleanChildSelectBox(address_seq){
    var maxSeq = (typeof use_smm_address !== 'undefined' && use_smm_address) ? 3 : 2;
    var loop_ord = parseInt(address_seq)+1;
    for(var i=loop_ord; i<=maxSeq; i++) { 
        var $el = $('#address_dd_'+i);
        if($el.length) $el.html('<option value="">--เลือก--</option>');
    }
}

