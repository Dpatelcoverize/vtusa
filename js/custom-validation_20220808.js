// Validation for dealer agreement form submission
$(document).ready(function () {
  $("#dealAgreementSubmit").click(function () {
    var flag1,
      flag2,
      flag3,
      flag4,
      flag5,
      flag6,
      flag7,
      flag8,
      flag9,
      flag10,
      flag11,
      flag12,
      flag13,
      flag14,
      flag15,
      flag16,
      flag17,
      flag18,
      flag19,
      flag20 = 0;
    if ($("#dealerName").val() == "") {
      $("#dealerName").focus();
      $("#dealerNameE").css("display", "block");
    } else {
      $("#dealerNameE").css("display", "none");
      flag1 = 1;
    }

    if (
      $("#taxID").val() == "" ||
      !/^(\d{2})[-](\d{7})$/.test($("#taxID").val())
    ) {
      $("#taxID").focus();
      $("#taxIDE").css("display", "block");
    } else {
      $("#taxIDE").css("display", "none");
      flag2 = 1;
    }

    if ($("#dealerAddress").val() == "") {
      $("#dealerAddress").focus();
      $("#dealerAddressE").css("display", "block");
    } else {
      $("#dealerAddressE").css("display", "none");
      flag3 = 1;
    }

    if ($("#dealerCity").val() == "") {
      $("#dealerCity").focus();
      $("#dealerCityE").css("display", "block");
    } else {
      $("#dealerCityE").css("display", "none");
      flag4 = 1;
    }

    if ($("#dealerState").val() == "" || $("#dealerState").val() == null) {
      $("#dealerState").focus();
      $("#dealerStateE").css("display", "block");
    } else {
      $("#dealerStateE").css("display", "none");
      flag5 = 1;
    }

    if ($("#dealerZip").val() == "") {
      $("#dealerZip").focus();
      $("#dealerZipE").css("display", "block");
    } else {
      $("#dealerZipE").css("display", "none");
      flag6 = 1;
    }

    if ($("#dealerPhone").val() == "") {
      $("#dealerPhone").focus();
      $("#dealerPhoneE").css("display", "block");
    } else {
      $("#dealerPhoneE").css("display", "none");
      flag7 = 1;
    }

    if ($("#businessEmail").val() == "") {
      $("#businessEmail").focus();
      $("#businessEmailE").css("display", "block");
    } else {
      $("#businessEmailE").css("display", "none");
      flag8 = 1;
    }

    if ($("#primaryContactFirstName").val() == "") {
      $("#primaryContactFirstName").focus();
      $("#primaryContactFirstNameE").css("display", "block");
    } else {
      $("#primaryContactFirstNameE").css("display", "none");
      flag9 = 1;
    }

    if ($("#primaryContactLastName").val() == "") {
      $("#primaryContactLastName").focus();
      $("#primaryContactLastNameE").css("display", "block");
    } else {
      $("#primaryContactLastNameE").css("display", "none");
      flag10 = 1;
    }

    if ($("#primaryContactPhone").val() == "") {
      $("#primaryContactPhone").focus();
      $("#primaryContactPhoneE").css("display", "block");
    } else {
      $("#primaryContactPhoneE").css("display", "none");
      flag11 = 1;
    }

    if ($("#primaryContactEmail").val() == "") {
      $("#primaryContactEmail").focus();
      $("#primaryContactEmailE").css("display", "block");
    } else {
      $("#primaryContactEmailE").css("display", "none");
      flag12 = 1;
    }

    flag13 = 1;
    flag14 = 1;
    flag15 = 1;
    flag16 = 1;
    /*
        if($("#shipAddress").val() == ''){
            $("#shipAddress").focus();
            $("#shipAddressE").css("display","block");
        } else {
            $("#shipAddressE").css("display","none");
            flag13=1;
        }

        if($("#shipCity").val() == ''){
            $("#shipCity").focus();
            $("#shipCityE").css("display","block");
        } else {
            $("#shipCityE").css("display","none");
            flag14=1;
        }

        if($("#shipState").val() == '' || $("#shipState").val() == null){
            $("#shipState").focus();
            $("#shipStateE").css("display","block");
        } else {
            $("#shipStateE").css("display","none");
            flag15=1;
        }

        if($("#shipZip").val() == ''){
            $("#shipZip").focus();
            $("#shipZipE").css("display","block");
        } else {
            $("#shipZipE").css("display","none");
            flag16=1;
        }
*/

    if (
      $("input[type=radio][name=signatureOption]:checked").val() == "online" &&
      $(".jSignature").jSignature("getData", "native").length == 0
    ) {
      $("#signatureE").css("display", "block");
    } else {
      $("#signatureE").css("display", "none");
      flag17 = 1;
    }

    if (
      $("input[type=radio][name=signatureOption]:checked").val() == "online" &&
      $("#retailerName").val() == ""
    ) {
      $("#retailerName").focus();
      $("#retailerNameE").css("display", "block");
    } else {
      $("#retailerNameE").css("display", "none");
      flag18 = 1;
    }

    if (
      $("input[type=radio][name=signatureOption]:checked").val() == "online" &&
      $("#retailerTitle").val() == ""
    ) {
      $("#retailerTitle").focus();
      $("#retailerTitleE").css("display", "block");
    } else {
      $("#retailerTitleE").css("display", "none");
      flag19 = 1;
    }

    if ($("#signedOnDate").val() == "") {
      $("#signedOnDate").focus();
      $("#signedOnDateE").css("display", "block");
    } else {
      $("#signedOnDateE").css("display", "none");
      var signedOnDate1 = $("#signedOnDate").val();
      var validDate = "^(1[0-2]|0[1-9])/(3[01]|[12][0-9]|0[1-9])/[0-9]{4}$";
      if (signedOnDate1.match(validDate)) {
        $("#signedOnDateED").css("display", "none");
        flag20 = 1;
      } else {
        $("#signedOnDateED").css("display", "block");
      }
    }

    if (
      flag1 == 1 &&
      flag2 == 1 &&
      flag3 == 1 &&
      flag4 == 1 &&
      flag5 == 1 &&
      flag6 == 1 &&
      flag7 == 1 &&
      flag8 == 1 &&
      flag9 == 1 &&
      flag10 == 1 &&
      flag11 == 1 &&
      flag12 == 1 &&
      flag13 == 1 &&
      flag14 == 1 &&
      flag15 == 1 &&
      flag16 == 1 &&
      flag17 == 1 &&
      flag18 == 1 &&
      flag19 == 1 &&
      flag20 == 1
    ) {
      //$("#dealer_agreement_v3").submit();
      let form = $("#dealer_agreement_v3");
      let signature = $(".signature").jSignature("getData", "svgbase64");
      $(".sign_hash").val(signature);
      let signatureBase30 = $(".signature").jSignature("getData", "base30");
      $(".base30").val(signatureBase30);
      form.submit();
    }
  });
});

// Validation for auto fill shipping address
$(document).ready(function () {
  $("#copyToShipAddress").change(function () {
    if (this.checked) {
      $("#shipAddress").val($("#dealerAddress").val());
      $("#shipCity").val($("#dealerCity").val());
      $("#shipState").val($("#dealerState").val());
      $("#shipZip").val($("#dealerZip").val());
      $("#shipZip").focus();

      var bState = $("#dealerState").val();
      var changeSState = "changeSState";
      $.ajax({
        url: "../common-script.php",
        type: "post",
        data: {
          bState: bState,
          changeSState: changeSState,
        },
        success: function (data) {
          $("#shipState")
            .html("'" + data + "'")
            .selectpicker("refresh");
        },
      });
      $("#dealerState").change(function () {
        var bState = $("#dealerState").val();
        var changeSState = "changeSState";
        $.ajax({
          url: "../common-script.php",
          type: "post",
          data: {
            bState: bState,
            changeSState: changeSState,
          },
          success: function (data) {
            //$("#shipState").html("").selectpicker('refresh');
            $("#shipState")
              .html("'" + data + "'")
              .selectpicker("refresh");
          },
        });
      });
    } else {
      $("#shipAddress").val("");
      $("#shipCity").val("");
      $("#shipState").val("");
      $("#shipZip").val("");
      var selectState = "selectState";
      $.ajax({
        url: "../select-default-state.php",
        type: "post",
        data: {
          selectState: selectState,
        },
        success: function (data) {
          $("#shipState")
            .html("'" + data + "'")
            .selectpicker("refresh");
        },
      });
    }
  });
  //   $("#dealerAddress").keyup(function() {
  //     if ($("#copyToShipAddress").is(":checked")) {
  //       $("#shipAddress").val($(this).val());
  //     }
  //   });
  //   $("#dealerCity").keyup(function() {
  //     if ($("#copyToShipAddress").is(":checked")) {
  //       $("#shipCity").val($(this).val());
  //     }
  //   });
  //   $("#dealerState").keyup(function() {
  //     if ($("#copyToShipAddress").is(":checked")) {
  //       $("#shipState").val($(this).val());
  //     }
  //   });
  //   $("#dealerZip").keyup(function() {
  //     if ($("#copyToShipAddress").is(":checked")) {
  //       $("#shipZip").val($(this).val());
  //     }
  //   });
});

// Validation for dealer w9 form submission
$(document).ready(function () {
  $("#dealer_w9_submit").click(function () {
    var flag1 = 1;
    var flag2 = 1;
    var flag3 = 0;
    var flag4 = 0;
    /*
        var flag1,flag2,flag3,flag4=0;

        if($("#exemptionPayeeCode").val() == ''){
            $("#exemptionPayeeCodeE").css("display","block");
        } else {
            $("#exemptionPayeeCodeE").css("display","none");
            flag1=1;
        }

        if($("#exemptionFATCACode").val() == ''){
            $("#exemptionFATCACodeE").css("display","block");
        } else {
            $("#exemptionFATCACodeE").css("display","none");
            flag2=1;
        }
*/
    if ($("#dealerEIN").val() == "") {
      $("#dealerEINE").css("display", "block");
    } else {
      $("#dealerEINE").css("display", "none");
      flag3 = 1;
    }

    if ($(".jSignature").jSignature("getData", "native").length == 0) {
      $("#signatureE").css("display", "block");
    } else {
      $("#signatureE").css("display", "none");
      flag4 = 1;
    }

    if (flag1 == 1 && flag2 == 1 && flag3 == 1 && flag4 == 1) {
      //$("#dealer_w9_form").submit();
      let form = $("#dealer_w9_form");
      let signature = $(".signature").jSignature("getData", "svgbase64");
      $(".sign_hash").val(signature);
      let signatureBase30 = $(".signature").jSignature("getData", "base30");
      $(".base30").val(signatureBase30);
      form.submit();
    }
  });
});

// Validation for dealer addendum form submission
$(document).ready(function () {
  $("#dealer_addendum_submit").click(function () {
    var flag1 = 0;

    if ($(".jSignature").jSignature("getData", "native").length == 0) {
      $("#signatureE").css("display", "block");
    } else {
      $("#signatureE").css("display", "none");
      flag1 = 1;
    }

    if (flag1 == 1) {
      //$("#dealer_addendum_form").submit();
      let form = $("#dealer_addendum_form");
      let signature = $(".signature").jSignature("getData", "svgbase64");
      $(".sign_hash").val(signature);
      let signatureBase30 = $(".signature").jSignature("getData", "base30");
      $(".base30").val(signatureBase30);
      form.submit();
    }
  });
});

// Validation for dealer banking form submission
$(document).ready(function () {
  $("#dealBankingSubmit").click(function () {
    var flag1 = 0;
    var flag2 = 0;
    var flag3 = 0;
    var flag4 = 0;
    var flag5 = 0;
    var flag6 = 0;
    var flag7 = 0;
    var flag8 = 0;
    var flag9 = 0;

    if ($("#businessBankName").val() == "") {
      $("#businessBankName").focus();
      $("#businessBankNameE").css("display", "block");
    } else {
      $("#businessBankNameE").css("display", "none");
      flag1 = 1;
    }

    if ($("#businessBankAccountName").val() == "") {
      $("#businessBankAccountName").focus();
      $("#businessBankAccountNameE").css("display", "block");
    } else {
      $("#businessBankAccountNameE").css("display", "none");
      flag2 = 1;
    }

    if ($("#businessBankBillingAddress").val() == "") {
      $("#businessBankBillingAddress").focus();
      $("#businessBankBillingAddressE").css("display", "block");
    } else {
      $("#businessBankBillingAddressE").css("display", "none");
      flag3 = 1;
    }

    if ($("#businessBankBillingCity").val() == "") {
      $("#businessBankBillingCity").focus();
      $("#businessBankBillingCityE").css("display", "block");
    } else {
      $("#businessBankBillingCityE").css("display", "none");
      flag4 = 1;
    }

    if (
      $("#businessBankBillingState").val() == "" ||
      $("#businessBankBillingState").val() == null
    ) {
      $("#businessBankBillingState").focus();
      $("#businessBankBillingStateE").css("display", "block");
    } else {
      $("#businessBankBillingStateE").css("display", "none");
      flag5 = 1;
    }

    if ($("#businessBankBillingZip").val() == "") {
      $("#businessBankBillingZip").focus();
      $("#businessBankBillingZipE").css("display", "block");
    } else {
      $("#businessBankBillingZipE").css("display", "none");
      flag7 = 1;
    }

    if ($("#businessBankAccountNumber").val() == "") {
      $("#businessBankAccountNumber").focus();
      $("#businessBankAccountNumberE").css("display", "block");
    } else {
      $("#businessBankAccountNumberE").css("display", "none");
      flag8 = 1;
    }

    if ($("#businessBankRoutingNumber").val() == "") {
      $("#businessBankRoutingNumber").focus();
      $("#businessBankRoutingNumberE").css("display", "block");
    } else {
      $("#businessBankRoutingNumberE").css("display", "none");
      flag9 = 1;
    }

    if (
      flag1 == 1 &&
      flag2 == 1 &&
      flag3 == 1 &&
      flag4 == 1 &&
      flag5 == 1 &&
      flag7 == 1 &&
      flag8 == 1 &&
      flag9 == 1
    ) {
      $("#dealerBankingForm").submit();
    }
  });
});

// Validation for dealer Affiliate form submission
$(document).ready(function () {
  $("#dealAffiliateSubmit").click(function () {
    var flag1 = 0;
    var flag2 = 0;
    var flag3 = 0;
    var flag4 = 0;
    var flag5 = 0;
    var flag6 = 0;
    var flag7 = 0;
    var flag8 = 0;
    var flag9 = 0;
    var flag10 = 0;

    if ($("#personDOB").val() == "") {
      $("#personDOB").focus();
      $("#personDOBE").css("display", "block");
    } else {
      $("#personDOBE").css("display", "none");
      flag1 = 1;
    }

    if ($("#personSSN").val() == "") {
      $("#personSSN").focus();
      $("#personSSNE").css("display", "block");
    } else {
      $("#personSSNE").css("display", "none");
      flag2 = 1;
    }

    if ($("#personBankName").val() == "") {
      $("#personBankName").focus();
      $("#personBankNameE").css("display", "block");
    } else {
      $("#personBankNameE").css("display", "none");
      flag3 = 1;
    }

    if ($("#personBankAccountName").val() == "") {
      $("#personBankAccountName").focus();
      $("#personBankAccountNameE").css("display", "block");
    } else {
      $("#personBankAccountNameE").css("display", "none");
      flag4 = 1;
    }

    if ($("#personBankBillingAddress").val() == "") {
      $("#personBankBillingAddress").focus();
      $("#personBankBillingAddressE").css("display", "block");
    } else {
      $("#personBankBillingAddressE").css("display", "none");
      flag5 = 1;
    }

    if ($("#personBankBillingCity").val() == "") {
      $("#personBankBillingCity").focus();
      $("#personBankBillingCityE").css("display", "block");
    } else {
      $("#personBankBillingCityE").css("display", "none");
      flag6 = 1;
    }

    if (
      $("#personBankBillingState").val() == "" ||
      $("#personBankBillingState").val() == null
    ) {
      $("#personBankBillingState").focus();
      $("#personBankBillingStateE").css("display", "block");
    } else {
      $("#personBankBillingStateE").css("display", "none");
      flag7 = 1;
    }

    if ($("#personBankBillingZip").val() == "") {
      $("#personBankBillingZip").focus();
      $("#personBankBillingZipE").css("display", "block");
    } else {
      $("#personBankBillingZipE").css("display", "none");
      flag8 = 1;
    }

    if ($("#personRoutingNumber").val() == "") {
      $("#personRoutingNumber").focus();
      $("#personRoutingNumberE").css("display", "block");
    } else {
      $("#personRoutingNumberE").css("display", "none");
      flag9 = 1;
    }

    if ($("#personAccountNumber").val() == "") {
      $("#personAccountNumber").focus();
      $("#personAccountNumberE").css("display", "block");
    } else {
      $("#personAccountNumberE").css("display", "none");
      flag10 = 1;
    }

    if (
      flag1 == 1 &&
      flag2 == 1 &&
      flag3 == 1 &&
      flag4 == 1 &&
      flag5 == 1 &&
      flag6 == 1 &&
      flag7 == 1 &&
      flag8 == 1 &&
      flag9 == 1 &&
      flag10 == 1
    ) {
      $("#dealerAffiliateForm").submit();
    }
  });
});
