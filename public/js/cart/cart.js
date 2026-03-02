/*
 *@desc : Cart Modules used to handle cart page action
 *@author : Smoothgraph Connect Pvt. Ltd
 *@created : 28-june-2019
 */

//global variable section

(function cartModule($) {
  /*********
    *@desc : Quantity model handle qunatity related action 
            1. increase/decrease quantity 
            2. change quantity
            3. remove/delete product from product list
    ***********/
  let ship_method = $('input[name="ship_method"]:checked').val();
  if (ship_method == "3") {
    let shipId = $("#dd_shipping").val();
    getDeliveryFee(shipId);
    if (!shipId) {
      $("#shipping_address").html("");
    }
    $("#user_phone_no_div").hide();
  } else {
    $("#user_phone_no_div").show();
    let shipId = $("#dd_shipping").val();
    getDeliveryFee(shipId);
  }

  (function Quantity() {
    // update quantity on page load
    let hasChange = false;
    $("input.spinNum").each(function () {
      if ($(this).data("haschange") == "1") {
        hasChange = true;
      }
    });
    if (hasChange) {
      swal({
        title: "ราคาสินค้ามีการเปลี่ยนแปลง\nกรุณาตรวจสอบอีกครั้ง",
        confirmButtonText: "ตรวจสอบราคาสินค้า",
        confirmButtonColor: "#e3342f",
        allowOutsideClick: false,
        allowEscapeKey: false,
      }).then((willRun) => {
        if (willRun) {
          $("input.spinNum").each(function () {
            if ($(this).data("haschange") == "1") {
              quantityHandler($(this), "change");
            }
          });
        }
      });
    }

    //event
    $(document).on("click", ".increase", function () {
      quantityHandler($(this), "increase");
    });
    $(document).on("click", ".decrease", function () {
      quantityHandler($(this), "decrease");
    });
    $(document).on("change", "input.spinNum", function () {
      quantityHandler($(this), "change");
    });
    $(document).on("click", ".cart-remove", function (evt) {
      quantityHandler($(this), "removecartproduct");
    });

    //handler
    function maxQuantity(qty, maxQty, flag) {
      if (flag === "increase" || flag === "change")
        return parseInt(qty) < parseInt(maxQty) || false;
      else if (flag === "decrease") return parseInt(qty) > 1 || false;
    }

    function update(data) {
      return new Promise((resolve, reject) => {
        callAjaxRequest(updateCart, "post", data, (result) => {
          resolve(result);
        });
      });
    }

    function quantityHandler($that, flag) {
      let $input = $that.parent(".spiner").find("input.spinNum");
      // let $prd_total_price = $that.parents('div').find('div label.prd-total-price strong span');
      let $prd_unit_price = $that.parents("ul").find("li label.prd-unit-price");
      let data = {
        cartId: $that.parent(".spiner").data("cartid"),
        quantity: parseInt($input.val()),
      };
      switch (flag) {
        case "increase":
          data.quantity = parseInt($input.val()) + 1;
          update(data).then(
            (resp) => {
              let {
                tot_prd_price = 0,
                ordAmount = 0,
                totQty = 0,
                product_price = 0,
                maxvalue = 0,
                productQuantity = 0,
                cartid_ = "",
                msg = "",
              } = resp;
              // console.log(resp);

              if (resp && resp.status == "success") {
                let val = flag === "increase" && parseInt($input.val()) + 1;
                $input.val(val);
                $(
                  "#tot_all_product_price,#tot_order_amount,#total_before_fee"
                ).text("฿" + ordAmount);
                $("#tot_order_qty").text(totQty);
                $("#total_before_fee").text("฿" + resp.ordAmount);

                $input
                  .closest(".cart_shop_item")
                  .find(".prd-total-price")
                  .text("฿" + tot_prd_price);
                tot_prd_price &&
                  $("#prd-total-price_" + cartid_).text("฿" + tot_prd_price);
                product_price && $prd_unit_price.text(product_price);
              } else {
                if (data.quantity > parseInt(maxvalue)) {
                  $input.attr("max", maxvalue);
                  showSweetAlertError(msg);
                } else {
                  showSweetAlertError(msg);
                }
              }
              $input
                .closest("#cart_shop_item")
                .find(".product_quantity")
                .text(productQuantity);
            },
            (err) => {
              showSweetAlertError("ไม่สามารถเพิ่มสินค้าได้");
              location.reload();
            }
          );
          break;
        case "decrease":
          if ($input.val() && parseInt($input.val()) > 1) {
            data.quantity = parseInt($input.val()) - 1;
            data.flag = flag;
            update(data).then(
              (resp) => {
                let {
                  tot_prd_price = 0,
                  ordAmount = 0,
                  totQty = 0,
                  product_price = 0,
                  productQuantity = 0,
                  cartid_ = "",
                  msg = "",
                } = resp;

                if (resp && resp.status == "success") {
                  let val = parseInt($input.val() - 1);
                  $input.val(val);
                  $(
                    "#tot_all_product_price,#tot_order_amount,#total_before_fee"
                  ).text("฿" + ordAmount);

                  $("#tot_order_qty").text(totQty);
                  $("#total_before_fee").text("฿" + resp.ordAmount);
                  $input
                    .closest(".cart_shop_item")
                    .find(".product_quantity")
                    .text(productQuantity);

                  $input
                    .closest(".cart_shop_item")
                    .find(".prd-total-price")
                    .text("฿" + tot_prd_price);
                  tot_prd_price &&
                    $("#prd-total-price_" + cartid_).text("฿" + tot_prd_price);
                  product_price && $prd_unit_price.text(product_price);
                }
                // else{
                //     showSweetAlertError(msg);
                // }
              },
              (err) => {
                showSweetAlertError("ไม่สามารถลดสินค้าได้");
                location.reload();
              }
            );
          }
          break;
        case "change":
          update(data).then(
            (resp) => {
              let val = flag === "change" && $input.val();
              let {
                tot_prd_price = 0,
                ordAmount = 0,
                totQty = 0,
                product_price = 0,
                productQuantity = 0,
                cartid_ = "",
                msg = "",
                maxqty = 0,
                cartquantity = 0,
                min_order_qty = 0,
              } = resp;

              if (resp && resp.status == "success") {
                $input.val(val);
                $(
                  "#tot_all_product_price, #tot_order_amount, #total_before_fee"
                ).text("฿" + ordAmount);
                $("#total_before_fee").text("฿" + resp.ordAmount);
                $("#tot_order_qty").text(totQty);

                $input
                  .closest(".cart_shop_item")
                  .find(".prd-total-price")
                  .text("฿" + tot_prd_price);
                tot_prd_price &&
                  $("#prd-total-price_" + cartid_).text("฿" + tot_prd_price);
                product_price && $prd_unit_price.text(product_price);
              } else {
                // if(parseInt($input.val()) > maxqty){
                //     //var calnewqty =  parseInt($input.attr('max')) - parseInt($input.val()) ;
                //     showSweetAlertError(msg);
                //     $input.val(cartquantity);
                // }else if(parseInt($input.val()) < min_order_qty){
                //     showSweetAlertError(msg);
                //     $input.val(min_order_qty);
                //     $("#tot_all_product_price,#tot_order_amount,#total_before_fee").text("฿"+ordAmount);
                //         $('#total_before_fee').text("฿"+resp.ordAmount);
                //     $('#tot_order_qty').text(totQty);

                //     $input.closest('.cart_shop_item').find('.prd-total-price').text("฿"+tot_prd_price);
                //     tot_prd_price && $("#prd-total-price_"+cartid_).text("฿"+tot_prd_price);
                //     product_price && $prd_unit_price.text(product_price)
                // }else if(!$input.val() || $input.val() == '0'){
                //     showSweetAlertError(msg);
                //     $input.val(min_order_qty);
                // }else{
                //     showSweetAlertError(msg);
                //     $input.val(cartquantity);
                // }
                showSweetAlertError(msg);
                $input.val(cartquantity);
              }
              $input
                .closest("#cart_shop_item")
                .find(".product_quantity")
                .text(productQuantity ?? 0);
            },
            (err) => {
              showSweetAlertError("ไม่สามารถดำเนินการได้");
              location.reload();
            }
          );

          break;
        case "removecartproduct":
          let ul_id = $that.closest(".cart_item").attr("id");
          data.cartId = ul_id.replace("cart_", "");

          swal({
            title: error_msg.txt_delete_confirm,
            type: "warning",
            showCancelButton: true,
            confirmButtonText: error_msg.yes_delete_it,
            cancelButtonText: error_msg.txt_no,
            closeOnConfirm: true,
            closeOnCancel: true,
          })
            .then((isConfirm) => {
              if (isConfirm) {
                callAjaxRequest(removeCart, "post", data, (result) => {
                  if (result.status == "success") {
                    swal(lang_success, result.msg, "success").then(function () {
                      $(
                        "#tot_all_product_price,#tot_order_amount,#total_before_fee"
                      ).text("฿" + result.ordAmount);
                      $("#tot_order_qty").text(result.totQty);

                      $("#total_before_fee").text(
                        "฿" + (result.ordAmount || 0)
                      );
                      $(
                        "#tot_cart_items, #tot_cart_items_cart, .tot_prd_noti"
                      ).text(result.cart_item);
                      $("#" + ul_id).remove();
                      selectedCount();
                      handleCartItemSelection();
                      handleCloseProductHeader();
                    });
                    if (
                      typeof result.cart_item === "undefined" ||
                      result.cart_item <= 0
                    ) {
                      window.location.reload();
                    }
                  } else {
                    showSweetAlertError(result.msg);
                    window.location.reload();
                  }
                });
              } else {
              }
            })
            .catch((err) => {
              showSweetAlertError(
                err ?? "ไม่สามารถดำเนินการได้ กรุณาทำรายการใหม่อีกครั้ง"
              );
            });
          break;
      }
    }
  })();

  /*******
    *@desc : Buy now and selection model handle bunow & selection action
            1. buynow 
            2. select all product 
            3. select/ de-select product 
    *********/
  (function buyNowAndSelection() {
    //event
    $(document).on("click", ".buynow", function () {
      buyNowHandler($(this), "buynow");
    });
    $(document).on(
      "change",
      '.checkwrap-sel-all input[type="checkbox"]',
      function (evt) {
        buyNowHandler($(this), "select_all");
      }
    );
    $(document).on(
      "change",
      '.table-content ul input[type="checkbox"]',
      function (evt) {
        buyNowHandler($(this), "prd_select_change");
      }
    );
    $(document).on("click", ".all_pay_credit", function () {
      buyNowHandler($(this), "all_pay_credit");
    });

    //function
    function buyNowHandler($that, flag) {
      switch (flag) {
        case "buynow":
          let $checkedPrdList = $(
            '.table-content ul input[type="checkbox"]:checked'
          );

          if ($checkedPrdList.length === 0) {
            showSweetAlertError(error_msg.buynow_ckeck);
            return;
          }

          let data = [];
          $.each($checkedPrdList, function () {
            data.push({
              cartId: $(this).parents("ul").find("li .spiner").data("cartid"),
              quantity: $(this).parents("ul").find("li .spiner input").val(),
            });
          });
          //cart action after buy now
          try {
            swal({
              title: error_msg.buynow_title,
              showCloseButton: true,
              showCancelButton: true,
              showConfirmButton: true,
              cancelButtonText: error_msg.buynow,
              confirmButtonText: error_msg.end_shopping,
              confirmButtonColor: "#004CFF",
              cancelButtonColor: "#CE232A",
            }).then(
              (res) => {
                if (res) {
                  callAjaxRequest(
                    payProduct,
                    "post",
                    { data: JSON.stringify(data), type: "end_shopping" },
                    function (response) {
                      if (response && response.status === "success")
                        window.location.href = response.url;
                      else {
                        if (response.type == "price") {
                          $("#cart_" + response.cart_id).css(
                            "background-color",
                            "yellow"
                          );
                          $(
                            "#cart_" + response.cart_id + " li.price_li"
                          ).append(
                            '<br><a href="javascript:;" class="update_cart_price text-primary">' +
                              error_msg.update_price +
                              "</a>"
                          );
                        } else {
                          showSweetAlertError(response.msg);
                          $("#cart_" + response.cart_id).append(
                            '<p class="error">' + response.msg + "</p>"
                          );
                        }
                      }
                    }
                  );
                }
              },
              (rej) => {
                if (rej && rej === "cancel") {
                  callAjaxRequest(
                    payProduct,
                    "post",
                    { data: JSON.stringify(data), type: "buynow" },
                    function (response) {
                      if (response && response.status === "success")
                        window.location.href = response.url;
                      else showSweetAlertError(response.msg);
                      $("#cart_" + response.cart_id).append(
                        '<p class="error">' + response.msg + "</p>"
                      );
                    }
                  );
                }
              }
            );
          } catch (er) {
            // console.log;
          }
          break;
        case "select_all":
          let $checkedPrdLists = $('.table-content ul input[type="checkbox"]');
          $.each($checkedPrdLists, function () {
            if ($that.is(":checked")) $(this).prop("checked", true);
            else $(this).prop("checked", false);
          });
          break;
        case "prd_select_change":
          //in case all select is checkd then user uncheck any product then uncheck select all (check box)
          if (
            !$that.is(":checked") &&
            $('.checkwrap-sel-all input[type="checkbox"]').is(":checked")
          )
            $('.checkwrap-sel-all input[type="checkbox"]').prop(
              "checked",
              false
            );
          //in case product is checked then check rest all is checked if yes then checked all select box
          else if (
            $that.is(":checked") &&
            $('.table-content ul input[type="checkbox"]:checked').length ===
              $(".table-content ul").length
          )
            $('.checkwrap-sel-all input[type="checkbox"]').prop(
              "checked",
              true
            );
          break;
        case "all_pay_credit":
          let $checkPrdList = $(
            '.table-content ul input[type="checkbox"]:checked'
          );
          let act = $that.data("action");

          if (!act && $checkPrdList.length === 0) {
            showSweetAlertError(error_msg.buynow_ckeck);
            return;
          } else if (act === "single_credit") {
            $checkPrdList = $that.parents("ul").find('input[type="checkbox"]');
          }

          let data_credit = [];
          $.each($checkPrdList, function () {
            data_credit.push({
              cartId: $(this).parents("ul").find("li .spiner").data("cartid"),
              quantity: $(this).parents("ul").find("li .spiner input").val(),
            });
          });
          swal({
            title: error_msg.pay_cerdit,
            type: "warning",
            confirmButtonText: lang_ok,
            cancelButtonText: lang_cancel,
            showCloseButton: true,
            showConfirmButton: true,
            showCancelButton: true,
          }).then(
            (res) => {
              //cart action after buy now
              callAjaxRequest(
                payProduct,
                "post",
                { data: JSON.stringify(data_credit), type: "all_credit" },
                function (response) {
                  if (response && response.status === "success") {
                    swal(lang_success, response.msg, "success").then((res) => {
                      window.location.href = response.url || "";
                    });
                  } else {
                    if (response.type == "price") {
                      $("#cart_" + response.cart_id).css(
                        "background-color",
                        "yellow"
                      );
                      $("#cart_" + response.cart_id + " li.price_li").append(
                        '<br><a href="javascript:;" class="update_cart_price text-primary">' +
                          error_msg.update_price +
                          "</a>"
                      );
                    } else {
                      showSweetAlertError(response.msg);
                    }
                  }
                }
              );
            },
            (rej) => {
              // console.log;
            }
          );
          break;
      }
    }
  })();

  $("body").on("click", ".update_cart_price", function (e) {
    callAjaxRequest(updateCartPrice, "post", {}, function (response) {
      location.reload();
    });
  });

  // $('body').on('click', ".sel-pay-method", function(){
  //     if (jQuery(this).find('input[type="radio"]').is(':checked')) {
  //         jQuery('.sel-pay-method ul li').removeClass('active');
  //         jQuery(this).toggleClass('active');
  //     }

  // });

  $("body").on("click", ".sel-pay-method", function () {
    // เช็คว่า radio ถูกเลือกแล้ว
    const radio = $(this).find('input[type="radio"]');
    if (!radio.prop("checked")) {
      radio.prop("checked", true); // เลือก radio
    }

    // ลบ class active ออกทั้งหมด แล้วใส่เฉพาะอันที่เลือก
    $(".sel-pay-method").removeClass("active");
    $(this).addClass("active");
  });

  jQuery("body").on("click", "#shipTab li", function (e) {
    $(this)
      .closest("li")
      .find('input[type="radio"]')
      .prop("checked", "checked");

    let ship_method = $('input[name="ship_method"]:checked').val();
    if (ship_method === "1") {
      $("#user_phone_no_div").show();
      // เรียก getPickupSlotse สำหรับ pickup at center
      // getPickupSlotse();
      getDeliveryFee();
    } else {
      $("#user_phone_no_div").hide();
      // เรียก getDeliveryFee สำหรับ delivery at address
      let shipId = $("#dd_shipping").val();
      getDeliveryFee(shipId);
    }
  });

  if (typeof isCheckout === 'undefined') var isCheckout = false;

  jQuery("body").on("click", "#btn_checkout", async function (e) {
      e.preventDefault();
      if (isCheckout) return false;

      var $btn = jQuery("#btn_checkout");
      var originalBtnText = 'ดำเนินการชำระเงิน';

      isCheckout = true;
      $btn.prop("disabled", true)
          .html('<i class="fas fa-spinner fa-spin"></i> กำลังประมวลผลคำสั่งซื้อ...');
      
      if (typeof showHideLoader === "function") showHideLoader("showLoader");

      var unlockBtn = function() {
          isCheckout = false;
          var $btn = jQuery("#btn_checkout");
          $btn.prop("disabled", false).html('ดำเนินการชำระเงิน');
          if (typeof showHideLoader === "function") showHideLoader("hideLoader");
      };

      try {
          var error_str = "";

          if (typeof checkout_type !== 'undefined' && checkout_type != "buy-now") {
              var ship_method = jQuery("input[name=ship_method]:checked").val();
              if (typeof ship_method == "undefined") {
                  $("#e_ship_method").html(error_msg.select_shipping);
                  error_str += '<p class="error">' + error_msg.select_shipping + "</p>";
              } else if (ship_method == "1" || ship_method == "2" || ship_method == "3") {
                  $("#e_ship_method").html("");
                  if (ship_method == "3") {
                      var ship_addr = jQuery("select[name=ship_address]").val();
                      var bill_addr = jQuery("select[name=bill_address]").val();
                      if (!ship_addr) {
                          $("#e_ship_address").html(error_msg.select_shipping_address);
                          error_str += '<p class="error">' + error_msg.select_shipping_address + "</p>";
                      }
                      if (!bill_addr) {
                          $("#e_bill_address").html(error_msg.select_billing_address);
                          error_str += '<p class="error">' + error_msg.select_billing_address + "</p>";
                      }
                  } else {
                      if (!jQuery("#phone_no").val()) {
                          $("#e_phone_no").html(error_msg.enter_phone_no);
                          error_str += '<p class="error">' + error_msg.enter_phone_no + "</p>";
                      }
                  }
              }
          }

          if (!jQuery("select[name=pickup_time]").val()) {
              $("#e_pickup_time").html(error_msg.select_pickup_time);
              error_str += '<p class="error">' + error_msg.select_pickup_time + "</p>";
          }

          if (jQuery("#check_pay_method").val() == 1 && !jQuery("input[name=payment_method]:checked").val()) {
              $("#e_payment_method").html(error_msg.select_payment);
              error_str += '<p class="error">' + error_msg.select_payment + "</p>";
          }

          if (error_str != "") {
              showHideLoader("hideLoader");
              showSweetAlertError(error_str, unlockBtn); 
              return false;
          }

          let selectedCartIds = jQuery(".cartItem").map(function () { return jQuery(this).data("item-id"); }).get();
          const validateRes = await jQuery.ajax({
              url: validateCartItemsUrl,
              method: "POST",
              data: { 
                  cartItems: selectedCartIds, 
                  _token: jQuery('meta[name="csrf-token"]').attr("content") 
              }
          });

          if (validateRes.status === "success") {
              var formAction = jQuery("#checkout_form").attr("action");
              var form = jQuery("#checkout_form").serialize();

              callAjaxRequest(formAction, "post", form, function (response) {
                  showHideLoader("hideLoader");
                  if (response.status == "success") {
                      window.location.href = response.url;
                  } else {
                      var msg = (typeof response.msg === 'object') ? Object.values(response.msg).join(' ') : response.msg;
                      showSweetAlertError(msg || "Error", function() {
                          unlockBtn();
                          if (response.validation != true) window.location.reload();
                      });
                  }
              });
          } else {
              showHideLoader("hideLoader");
              showSweetAlertError(validateRes.message || "รายการสินค้าเปลี่ยนแปลง", unlockBtn);
          }

      } catch (error) {
          showHideLoader("hideLoader");
          showSweetAlertError("เกิดข้อผิดพลาด กรุณาลองใหม่อีกครั้ง", unlockBtn);
      }
  });

  // when user add shipping/billing address
  $("body").on("click", ".add_address", function () {
    var address_type = $(this).prev("select").attr("name");
    //alert(address_type);return;

    var ajax_url = address_form_url;
    var data = { call_type: "ajax_data", address_type: address_type };

    callAjaxRequest(ajax_url, "get", data, function (result) {
      $("#popupdiv").html(result);
    });
  });

  // $('body').on('click','#pick_up_at_center,#pick_up_at_the_store',function(event) {
  //     getDeliveryFee();
  // });

  $("body").on(
    "click",
    "#pick_up_at_center,#delivery_at_the_address",
    function (event) {
      let block_bill_address = $("#block_bill_address");
      block_bill_address.appendTo($($(this).attr("href")).children(".row"));
    }
  );

  // when user change shipping address
  $("body").on("change", "#dd_shipping", function (event) {
    var shipId = $(this).val();
    getDeliveryFee(shipId);
    if (shipId) {
    } else {
      $("#shipping_address").html("");
    }
  });

  // when user change billing address
  $("body").on("change", "#dd_billing", function (event) {
    var billId = $(this).val();
    if (billId) {
      callAjaxRequest(
        change_bill_address,
        "post",
        { billId: billId },
        function (response) {
          if (response.status == "success") {
            $("#billing_address").html(response.billVal);
          } else {
            $("#billing_address").html("");
          }
        }
      );
    } else {
      // Clear billing address when placeholder option is selected
      $("#billing_address").html("");
    }
  });
})(jQuery);

function SubmitCartAddressForm() {
    // ส่ง tax_invoice เฉพาะเมื่อกรอกข้อมูลบริษัท (ชื่อบริษัท หรือ เลขประจำตัวผู้เสียภาษี หรือ ที่อยู่บริษัท)
    var $frm = $("#addess_frm");
    var hasCompany = $.trim($frm.find('[name="company_name"]').val()) !== '' ||
        $.trim($frm.find('[name="tax_id"]').val()) !== '' ||
        $.trim($frm.find('[name="company_address"]').val()) !== '';
    $frm.find('input[name="tax_invoice"]').remove();
    if (hasCompany) {
        $frm.append('<input type="hidden" name="tax_invoice" value="1">');
    }
    var ajax_url = save_address_url;
    var data = $frm.serialize();

    callAjaxRequest(ajax_url, "POST", data, function (response) {
      response = JSON.parse(response);
      if (response.status == "success") {
        $("#dd_shipping").append(response.shipdd);
        $("#dd_billing").append(response.billdd);
        if (response.shipVal) $("#shipping_address").html(response.shipVal);
        if (response.billVal) $("#billing_address").html(response.billVal);
        $("#add-address").modal("hide");
        var shipId = $("#dd_shipping").val();
        if (shipId) {
          getDeliveryFee(shipId);
        } else {
          $("#shipping_address").html("");
        }
      } else if (response.status == "validate_error") {
      $(".error-msg").text("");
      $.each(response.message, function (key, val) {
        $("#error_" + key).text(val);
      });
    }
  });
}

$(document).on("change", "#dd_logistic", function (e) {
  var val = $(this).val();
  alert(val);
  var data = { val: val, tot_delivery_time: tot_delivery_time };
  callAjaxRequest(pickup_time_url, "get", data, function (result) {});
});

$(document).ready(function () {
  //shipping type click
  $(".ship-method-list").click(function (e) {
    makeOptionTempkae($(this).attr("href"));
  });

  // Ensure default shipping method is selected on page load
  var checkedShippingMethod = $('input[name="ship_method"]:checked').length;
  // console.log('Page load - checked shipping methods:', checkedShippingMethod);

  if (checkedShippingMethod === 0) {
    // console.log('No shipping method selected, setting default to delivery (3)');
    $("#ship-address").prop("checked", true);
    $("#delivery_at_the_address").addClass("active");
  }

  // Ensure billing address block is in the correct tab on page load
  let block_bill_address = $("#block_bill_address");
  if (!$("#select-address .row").has("#block_bill_address").length) {
    block_bill_address.appendTo("#select-address .row");
    // console.log('Moved billing address block to delivery tab on page load');
  }

  // on load get shipping type
  $(".ship-method-list").each(function () {
    if ($(this).hasClass("active")) {
      makeOptionTempkae($(this).attr("href"));
    }
  });
});

function makeOptionTempkae(flag) {
  let optHtml = "";
  getData().map((o) => {
    optHtml +=
      "<option value=" +
      o.key +
      '>\
                <font style="vertical-align: inherit;"><font style="vertical-align: inherit;">' +
      o.val +
      "</font></font>\
            </option>";
  });
  $("#pickup_time :not(:first-child)").remove();
  $("#pickup_time").append(optHtml);
  function getData() {
    if (flag == "#select-address") return delivery_time_arr["buyer_address"];
    else if (flag == "#shop_address") return delivery_time_arr["shop_address"];
    else if (flag == "#pick_up_center")
      return delivery_time_arr["pickup_center"];
  }
}

function getDeliveryFee(shipId) {
    const paymentOptionId = $('input[name="payment_method"]:checked').val();
    const shipMethod = $('input[name="ship_method"]:checked').val();
    const currentSlotVal = $('#pickup_time_n').val();
    
    // ดึงค่า Coupon เดิม (ถ้ามี) ป้องกัน ReferenceError
    const sendDiscountCode = (typeof activeDiscountCode !== 'undefined') ? activeDiscountCode : "";

    // เตรียมข้อมูลส่งไป Backend
    const data = {
        shipId: (shipMethod === "1") ? undefined : (shipId || ""),
        discountCode: sendDiscountCode,
        paymentOptionId: paymentOptionId,
        ship_method: shipMethod,
        currentPickupTimeId: (shipMethod === "1") ? currentSlotVal : null,
        currentDeliveryTimeId: (shipMethod !== "1") ? currentSlotVal : null
    };

    callAjaxRequest(change_ship_address, "post", data, function (result) {
        const response = jQuery.parseJSON(result);
        if (response.status !== "success") return;

        const resDiscountCode = response.discount_code || "";
        const baht = typeof baht_currency !== 'undefined' ? baht_currency : "บาท";

        // --- 1. อัปเดต Time Slots ---
        const timeSlotHtml = (shipMethod === "1") 
            ? (response.pickup_time_slots || '<option value="">ไม่มีรอบส่ง</option>')
            : (response.delivery_time_slots || '<option value="">ไม่มีรอบส่งสำหรับรหัสไปรษณีย์นี้</option>');
        
        $('#pickup_time_n').html(timeSlotHtml);
        if ($('#pickup_time_n').hasClass('selectpicker')) {
            $('#pickup_time_n').selectpicker('refresh');
        }

        // --- 2. อัปเดตค่าจัดส่ง ---
        let deliveryFeeHtml = "";
        if (shipMethod !== "1") {
            const feeVal = response.shipping_fee || 0;
            const feeTxt = response.shipping_fee_txt || "0.00";
            deliveryFeeHtml = `
                <div class="d-flex justify-content-around w-100 border-top pt-1">
                    <span class="col-6"><strong>ค่าจัดส่ง</strong></span>
                    <span class="col-6">
                        <span id="tot_ship_amount">${feeTxt} ${baht}</span>
                        <input type="hidden" name="shipping_fee_val" value="${feeVal}">
                    </span>
                </div>`;
        }
        $("#delvery_fee_div").html(deliveryFeeHtml);

        // --- 3. อัปเดตส่วนลดคูปอง ---
        $("#dcc_purchase").html("");
        $("#dcc_shipping").html("");

        if (resDiscountCode !== "") {
            $("#dcc_purchase").html(`
                <div class="d-flex justify-content-around p-2 pl-3 text-danger w-100">
                    <input type="hidden" name="dcc_discount_code" value="${resDiscountCode}">
                    <input type="hidden" name="dcc_purchase" value="${response.discount_code_purchase}">
                    <span class="flex-grow-1">ส่วนลดรหัสคูปอง</span>
                    <span>-${response.discount_code_purchase_txt} ${baht}</span>
                </div>`);

            if (shipMethod !== "1" && response.discount_code_shipping > 0) {
                $("#dcc_shipping").html(`
                    <div class="d-flex justify-content-around p-2 pl-3 text-danger w-100">
                        <input type="hidden" name="dcc_shipping" value="${response.discount_code_shipping}">
                        <span class="flex-grow-1">ส่วนลดค่าจัดส่ง</span>
                        <span>-${response.discount_code_shipping_txt} ${baht}</span>
                    </div>`);
            }
        }

        // --- 4. ค่าธรรมเนียมการโอน ---
        let tfHtml = "";
        const hasFee = !["3", "4"].includes(paymentOptionId) && response.transaction_fee > 0;
        if (paymentOptionId && hasFee) {
            tfHtml = `
                <div class='d-flex border-top pt-1 w-100 align-items-end'>
                    <span class="col-8 col-sm-9">
                        <strong>ค่าธรรมเนียมการโอน ${response.transaction_fee_name || ""}</strong>
                        <span class="text-danger"> (${(response.transaction_fee_rate ?? 0).toFixed(2)}%)</span>
                    </span>
                    <span class="col-4 col-sm-3 text-danger">${response.transaction_fee_txt || '0.00'} ${baht}</span>
                </div>
                <input type="hidden" name="transaction_fee" value="${response.transaction_fee || 0}">`;
        }
        $("#transaction_fee_row").html(tfHtml);

        // --- 5. อัปเดตยอดรวมและ UI อื่นๆ ---
        const canPay = response.totAmt > 0;
        const totalDisplay = response.total_amount || $("#tot_order_amount").text() || "0.00"; 
        const totalHidden = response.tot_amt_after_dc || $("input[name='tot_amt_after_discount']").val() || 0;
        $("#payment_method_div").toggle(canPay);
        $("#check_pay_method").val(canPay ? 1 : 0);

        $("#shipping_address, #ship-info").html(response.shipVal);
        
        const serverTotalAmount = parseFloat(String(response.total_amount).replace(/,/g, ''));

if (serverTotalAmount > 0) {
    // อัปเดตเมื่อมียอดเงินมากกว่า 0 เท่านั้น
    $("#tot_order_amount").html(`
        ${response.total_amount} ${baht}
        <input type="hidden" name="tot_amt_after_discount" value="${response.tot_amt_after_dc}">
    `);
} else {
    // ถ้า server ส่ง 0.00 มา (ซึ่งมักเกิดจาก Race Condition) ให้ใช้ค่าเดิมที่มีอยู่บนหน้าจอ
    // หรือไม่ต้องทำอะไรเลย เพื่อรักษาตัวเลขราคาเดิมไว้
    console.warn("Skipped updating total_amount because server returned 0.00");
}
    });
}
