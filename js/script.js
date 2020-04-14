var cloudkassirModule = (function() {
  
  return { 
    init: function() {      
       // Сохраняет базовые настроки
      $('body').on('click', '.section-cloudkassir .base-setting-save', function() {

        $.ajax({
          type: "POST",
          url: mgBaseDir+"/ajaxrequest",
          data: {
            pluginHandler: 'cloudkassir', // имя папки в которой лежит данный плагин
            actionerClass: "Pactioner", 
            action: "saveBaseOption", // название действия в классе 
            data:{
              public_id : $(".base-settings input[name=public_id]").val(),
              secret_key : $(".base-settings input[name=secret_key]").val(),
              inn : $(".base-settings input[name=inn]").val(),
              taxation_system : $(".base-settings select[name=taxation_system]").val(),
              vat : $(".base-settings select[name=vat]").val(),
              vat_delivery : $(".base-settings select[name=vat_delivery]").val(),
              payment_enable : $(".base-settings select[name=payment_enable]").val(),
              method : $(".base-settings select[name=method]").val(),
              object : $(".base-settings select[name=object]").val(),
              status_delivered : $(".base-settings select[name=status_delivered]").val(),
              status_refund : $(".base-settings select[name=status_refund]").val(),
            },
          },
          dataType: "json",
          success: function(response){
            admin.indication(response.status, response.msg);
          }
        });
      });

      $('body').on('click', '.section-cloudkassir #clearPayment', function() {
          $("select[name=payment_enable] option:selected").prop("selected", false);
      });
      $('body').on('click', '.section-cloudkassir #clearRefundStatus', function() {
          $("select[name=status_refund] option:selected").prop("selected", false);
      });

    },
  }
})();

$(document).ready(function() {
    cloudkassirModule.init();
});