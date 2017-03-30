$(function () {
  $('.js-exchange-swap').on('click', function (event) {
    event.preventDefault();
  });
});
//    $(function () {
//      $('.exchange-swap').on('click', function (event) {
//        event.preventDefault();
//        var param = $('.js-inupt-text').val();
//    //    console.log(param);
//        $.ajax({
//          type: 'GET',
//          url: '/host',
//          dataType: 'JSON',
//          data: {
//            'limit': 100
//          },
//          success: function (data) {
//            console.log(data);
//            $('#result').html('Result : ' + data['param']);
//                $.each(data, function (i, value) {
//                      $('#result').append(i + '：' + value.name + '<br>');
//                });
//          },
//          error: function (XMLHttpRequest, textStatus, errorThrown) {
//            $('#XMLHttpRequest').html('XMLHttpRequest : ' + XMLHttpRequest.status);
//            $('#textStatus').html('textStatus : ' + textStatus);
//            $('#errorThrown').html('errorThrown : ' + errorThrown.message);
//          },
//          complete: function () {
//          }
//        });
//      });
//    });
