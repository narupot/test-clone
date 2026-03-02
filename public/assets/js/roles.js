(function ($) {
    $("input[type='checkbox']").change(function () {
        $(this).parent().siblings('ul').find("input[type='checkbox']").prop('checked', this.checked);
        if (this.checked) {
            //console.log($(this).parentsUntil('.admin_menu_wrapper'));
           // console.log($(this).parentsUntil('.admin_menu_wrapper').siblings('label').find(':checkbox'));
           // $(this).parentsUntil('.admin_menu_wrapper').siblings().find(':checkbox').prop('checked', true);
            //$(this).parentsUntil('.admin_menu_wrapper').siblings(':checkbox').prop('checked', true);
            $(this).parentsUntil('.admin_menu_wrapper').siblings('label').find(':checkbox').prop('checked', true);
        } else {
            $(this).parentsUntil('.admin_menu_wrapper').each(function () {
                var $this = $(this);
                //console.log($this);
                var childSelected = $this.find(':checkbox:checked').length;
                //console.log($this.find(':checkbox:checked'));
                if (!childSelected) {
                    //console.log('check true');
                    //console.log($this.prev());
                    $this.prev().find(':checkbox').prop('checked', false);
                    //$this.prev('li').find(':checkbox').prop('checked', false);
                    // $this.prev(':checkbox').prop('checked', false);
                }
            });
        }
    });

    $('#addGroupFrm').submit(function () {
        $('#error_div').html('');
        if (validateRole() & validateMenu() & validateDepartment()) {
            return true;
        } else {
            $('#error_div_main').fadeIn();
            return false;
        }
    });

    $('#role').blur(function () {
        validateRole();
    });

    function validateRole() {

        if ($("#role").val() == '') {
            $('#role_error').text(langMsg['please_enter_role_name']);
            $('#error_div').append('<h3 class="red">'+langMsg['please_enter_role_name']+'</h3>');
            return false;
        } else {
            $('#role_error').text('');
            return true;
        }
    }

    function validateMenu() {

        if ($("input[type='checkbox']").is(":checked") == false) {
            $('#role_menu_error').text(langMsg['please_select_role_resources']);
            $('#error_div').append('<h3 class="red">'+langMsg['please_select_role_resources']+'</h3>');
            return false;
        } else {
            $('#role_menu_error').text('');
            return true
        }
    }

    function validateDepartment() {


        var department_name = $.trim($(".dpt-name").first().val());      

        if (department_name == '') {
            $('#department_name_error').text(langMsg['please_enter_department_name']);
            $('#error_div').append('<h3 class="red">'+langMsg['please_enter_department_name']+'</h3>');
            return false;
        } else {
            $('#department_name_error').text('');
            return true;
        }
    }

})(jQuery); 

jQuery(document).ready(function(){
    jQuery('input[name=rproduct]').click(function () {
        if (this.id == "roleproduct-list") {
            jQuery(".filter-table-container").show('fast');
        } else {
            jQuery(".filter-table-container").hide('fast');
        }
    });
});