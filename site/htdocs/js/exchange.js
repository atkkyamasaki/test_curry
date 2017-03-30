
// デスティネーションにフォーカスしておく
$(document).ready(function () {
  $('select[name="destination"]').focus();
});

$(function () {

  // Zabbixユーザ情報
  var user = null;
  // ログイン中のエンドポイント
  var endpoint_origin = null;
  // その他のエンドポイント
  var endpoint_other = null;
  // ソースのエンドポイント
  var endpoint_source = null;
  // ディスティネーションのエンドポイント
  var endpoint_destination = null;
  // ソースのホストグループ
  var hostgroupid_source = null;

  // エンドポイントの状態を更新
  function updateEndpointState() {
    // ログイン中のエンドポイント
    var endpoint_origin_container = $('.endpoint-origin');
    endpoint_origin = endpoint_origin_container.find('select').val();
    console.log('endpoint_origin = ' + endpoint_origin);
    // その他のエンドポイント
    var endpoint_other_container = $('.endpoint-other');
    endpoint_other = endpoint_other_container.find('select').val();
    console.log('endpoint_other = ' + endpoint_other);
    // ソースのエンドポイント
    endpoint_source = $('select[name="source"]').val();
    console.log('endpoint_source = ' + endpoint_source);
    // ディスティネーションのエンドポイント
    endpoint_destination = $('select[name="destination"]').val();
    console.log('endpoint_destination = ' + endpoint_destination);
  }

  // その他のエンドポイントにログイン済みかどうか
  function isLoginOtherEndpoint() {
    return $('.endpoint-other').hasClass('is-connected');
  }

  // リソースエリアをリセット
  function resetResourceArea() {
    $('select[name="resource"]').val(0);
    var select_hostgroup_source = $('select[name="hostgroup-source"]');
    select_hostgroup_source.children('option:not(:first-child)').remove();
    select_hostgroup_source.hide();
    $('input[name="load-resource"]').hide();
    $('.js-resource-result').empty();
  }

  // 実行エリアをリセット
  function resetExecuteArea() {
    var select_hostgroup_destination = $('select[name="hostgroup-destination"]');
    select_hostgroup_destination.val(0);
    select_hostgroup_destination.children('option:not(:first-child)').remove();
    select_hostgroup_destination.hide();
    $('input[name="execute-exchange"]').hide();
    $('.js-execute-result').empty();
  }

  // SrouceとDestinationを入替え
  $('.js-exchange-swap').on('click', function (event) {
    event.preventDefault();

    // 前後の要素を取得
    var source_div = $(this).prev('div');
    var destination_div = $(this).next('div');

    // source/destinationを入替え
    source_div.find('select').attr('name', 'destination');
    source_div.find('.location').text('Destination');
    destination_div.find('select').attr('name', 'source');
    destination_div.find('.location').text('Source');

    // 要素を入替え
    $(this).before(destination_div);
    $(this).after(source_div);

    // リソースエリアをリセット
    resetResourceArea();

    // 実行エリアをリセット
    resetExecuteArea();

    // エンドポイント状態を更新
    updateEndpointState();
  });

  // その他のエンドポイント選択時
  $('.endpoint-other select').on('change', function (event) {
    event.preventDefault();
    var parent_div = $('.endpoint-other');

    // その他のエンドポイントに接続済み
    if (isLoginOtherEndpoint() === true) {
      // 既に接続していたらリセット
      parent_div.removeClass('is-connected');
      parent_div.find('.state').text('未接続');
      parent_div.find('.zabbix-id').val('');
      parent_div.find('.zabbix-password').val('');
      // 各種結果を初期化
      $('.result').empty();
      // ユーザも初期化
      user = null;
    }

    // エンドポイントURLを表示
    var url = $(this).val();
    var url_field = parent_div.find('.url');
    var login_field = $('.login');
    if (url === '0') {
      url_field.text('Select the other endpoint');
      login_field.hide('fast');
      return false;
    }
    login_field.show('fast');
    // idフィールドにフォーカスしておく
    login_field.find('input[name="id"]').focus();
    url_field.text(url);
  });

  // Zabbix API へログイン
  $('.zabbix-login').on('click', function (event) {
    event.preventDefault();
    // 結果フィールド
    var result = $('.js-endpoint-result');
    var endpoint_other = $('.endpoint-other');
    var endpoint = endpoint_other.find('select').val();
    var id = endpoint_other.find('.zabbix-id').val();
    var password = endpoint_other.find('.zabbix-password').val();

    // ID/Passフィールドが空だったら
    if (id === '' || password === '') {
      result.html('<p class="error">Please Enter Your ID / Password.</p>');
      return false;
    }

    // リソース
    var resource = $('.resource');

    // ローディングアイコン
    var loading_icon = endpoint_other.find('.fa-spinner');
    loading_icon.show();

    $.ajax({
      type: 'POST',
      url: '/users/login',
      dataType: 'JSON',
      cache: false,
      timeout: 5000,
      data: {
        'endpoint': endpoint,
        'id': id,
        'password': password
      },
      success: function (data) {
        // ログイン成功
        if (data !== null && $.isNumeric(data.userid)) {
          result.empty();
          endpoint_other.addClass('is-connected');
          endpoint_other.find('.state').text('接続中');
          $('.login').hide('slow');
          // ユーザを設定
          user = data;
          resource.show('fast');
          resource.focus();
          return;
        }

        // ログイン失敗
        endpoint_other.removeClass('is-connected');
        endpoint_other.find('.state').text('未接続');
        // ユーザも初期化
        user = null;
        result.html('<p class="error">Login Failed ' + endpoint + '</p>');
      },
      error: function (XMLHttpRequest, textStatus, errorThrown) {
        console.log('XMLHttpRequest : ' + XMLHttpRequest.status);
        console.log('textStatus : ' + textStatus);
        console.log('errorThrown : ' + errorThrown.message);
        result.html('<p class="error">Login Failed ' + endpoint + '</p>');
      },
      complete: function () {
        loading_icon.hide();
      }
    });
  });

  // リソース選択時
  $('.resource').on('change', function (event) {
    event.preventDefault();

    // エンドポイント状態を更新
    updateEndpointState();

    // ソースのホストグループセレクトボックス
    var hostgroup_source = $('.hostgroup-source');
    hostgroup_source.hide();

    // 結果フィールド
    var result = $('.js-resource-result');
    result.empty();

    // その他のエンドポイントに未接続だったら
    if (isLoginOtherEndpoint() === false) {
      result.html('<p class="error">Please Login Endpoint First.</p>');
      return false;
    }

    // 選択されていなかったら
    if ($(this).val() === '0') {
      // リソース読み込みボタンを非表示
      $('input[name="load-resource"]').hide();
      return false;
    }

    if ($(this).val() === 'template') {
      // ローディングアイコン
      var loading_icon = $(this).next('.fa-spinner');
      loading_icon.show();

      // Zabbix API インスタンスを使い分ける
      var is_singleton = true;
      var token = null;
      if (endpoint_source === endpoint_other) {
        // その他のエンドポイントがソースだったら
        is_singleton = false;
        token = user.token;
      }

      // ホストグループ一覧を取得
      $.ajax({
        type: 'GET',
        url: 'api/hostgroup',
        dataType: 'JSON',
        cache: false,
        timeout: 10000,
        data: {
          'is_singleton': is_singleton,
          'token': token,
          'endpoint': endpoint_source,
          'params': {
            'limit': 0,
            'templated_hosts': true
          }
        },
        success: function (data) {
          console.log(data);
          $.each(data, function (i, value) {
            hostgroup_source.append('<option value="' + value.groupid + '">' + value.name + '</option>');
          });
        },
        error: function (XMLHttpRequest, textStatus, errorThrown) {
          console.log('XMLHttpRequest : ' + XMLHttpRequest.status);
          console.log('textStatus : ' + textStatus);
          console.log('errorThrown : ' + errorThrown.message);
          result.html('<p class="error">An Error Has Occurred.</p>');
        },
        complete: function (data) {
          loading_icon.hide();
          hostgroup_source.show();
          $('.hostgroup-source').focus();
        }
      });
    }

  });

  // ソースのホストグループ選択時
  $('.hostgroup-source').on('change', function (event) {
    event.preventDefault();

    // 結果フィールド
    var result = $('.js-resource-result');
    result.empty();

    // リソース読み込みボタン
    var load_resource_submit = $('input[name="load-resource"]');

    // ホストグループID
    var hostgroupid = $(this).val();

    if (hostgroupid === '0') {
      // ソースのホストグループIDを初期化
      hostgroupid_source = null;
      load_resource_submit.hide();
      return false;
    }

    // ソースのホストグループを設定
    hostgroupid_source = hostgroupid;
    console.log(hostgroupid_source);

    load_resource_submit.show('fast');
  });

  // 選択したリソースを読み込み
  // TODO template以外も対応させる
  $('input[name="load-resource"]').on('click', function (event) {
    event.preventDefault();

    // エンドポイント状態を更新
    updateEndpointState();

    // 結果フィールド
    var result = $('.js-resource-result');
    result.empty();

    // その他のエンドポイントに未接続だったら
    if (isLoginOtherEndpoint() === false) {
      result.html('<p class="error">Please Login Endpoint First.</p>');
      return false;
    }

    // 選択されたリソース名
    var resource = $('.resource').val();

    // 選択されていない場合は通信しない
    if (resource === '0') {
      result.html('<p class="error">Please Select Resource.</p>');
      return false;
    }

    // ソースのホストグループが設定されていなかったら
    if (hostgroupid_source === null) {
      result.html('<p class="error">Please Select Source Hostgroup.</p>');
      return false;
    }

    // Zabbix API インスタンスを使い分ける
    var is_singleton = true;
    var token = null;
    if (endpoint_source === endpoint_other) {
      // その他のエンドポイントがソースだったら
      is_singleton = false;
      token = user.token;
    }

    // ローディングアイコン
    var loading_icon = $(this).next('.fa-spinner');
    loading_icon.show();

    // テンプレート一覧を取得
    $.ajax({
      type: 'GET',
      url: 'api/' + resource,
      dataType: 'JSON',
      cache: false,
      timeout: 20000,
      data: {
        'is_singleton': is_singleton,
        'token': token,
        'endpoint': endpoint_source,
        'params': {
          'limit': 0,
          'groupids': hostgroupid_source
        }
      },
      success: function (data) {
        console.log(data);
        // 結果フィールドを初期化
        result.empty();

        // 結果を表示
        result.append('<ul>');
        $.each(data, function (i, value) {
          result.find('ul').append('<li><label for="resource-result-' + i + '"><input type="checkbox" name="templateids[]" value="' + value.templateid + '" id="resource-result-' + i + '" class="aaa">' + value.name + '</label></li>').hide().fadeIn('slow');
        });
        result.append('</ul>');

        // 識別子書き換え
        result.append('<p>顧客識別子を書き換える場合。</p>').hide().fadeIn('slow');
        result.append('<input class="prefix-old" type="text" name="prefix-old" placeholder="例）ORG">').hide().fadeIn('slow');
        result.append(" → ").hide().fadeIn('slow');
        result.append('<input class="prefix-new" type="text" name="prefix-new" placeholder="例）ATKK">').hide().fadeIn('slow');

        var hostgroup_destination = $('select[name="hostgroup-destination"]');
        hostgroup_destination.show();
        $('input[name="execute-exchange"]').show();
      },
      error: function (XMLHttpRequest, textStatus, errorThrown) {
        console.log('XMLHttpRequest : ' + XMLHttpRequest.status);
        console.log('textStatus : ' + textStatus);
        console.log('errorThrown : ' + errorThrown.message);
        result.html('<p class="error">An Error Has Occurred.</p>');
      },
      complete: function (data) {
        loading_icon.hide();
      }
    });

    // リソースがテンプレートだったら送信先のホストグループを選択させる
    if (resource === 'template') {
      // ローディングアイコン
      var loading_icon = $(this).next('.fa-spinner');
      loading_icon.show();

      // デスティネーションのホストグループセレクトボックス
      var hostgroup_destination = $('.hostgroup-destination');
      hostgroup_destination.hide();

      // Zabbix API インスタンスを使い分ける
      var is_singleton = true;
      var token = null;
      if (endpoint_destination === endpoint_other) {
        // その他のエンドポイントがデスティネーションだったら
        is_singleton = false;
        token = user.token;
      }

      // ホストグループ一覧を取得
      $.ajax({
        type: 'GET',
        url: 'api/hostgroup',
        dataType: 'JSON',
        cache: false,
        timeout: 10000,
        data: {
          'is_singleton': is_singleton,
          'token': token,
          'endpoint': endpoint_destination,
          'params': {
            'limit': 0
          }
        },
        success: function (data) {
          console.log(data);
          $.each(data, function (i, value) {
            hostgroup_destination.append('<option value="' + value.groupid + '">' + value.name + '</option>');
          });
        },
        error: function (XMLHttpRequest, textStatus, errorThrown) {
          console.log('XMLHttpRequest : ' + XMLHttpRequest.status);
          console.log('textStatus : ' + textStatus);
          console.log('errorThrown : ' + errorThrown.message);
          result.html('<p class="error">An Error Has Occurred.</p>');
        },
        complete: function (data) {
          loading_icon.hide();
          hostgroup_destination.show();
          $('.hostgroup-destination').focus();
        }
      });
    }

  });

  // 実行ボタンクリック時
  $('input[name="execute-exchange"]').on('click', function (event) {

    // 結果フィールド
    var result = $('.js-execute-result');
    result.empty();

    // ディスティネーションのホストグループ
    var hostgroup_destination = $('select[name="hostgroup-destination"]').val();

    if (hostgroup_destination === '0') {
      event.preventDefault();
      result.html('<p class="error">Please Select Destination Hostgroup.</p>');
      return false;
    }

    var other_endpoint_token = user.token;
    $('form').append('<input type="hidden" name="other-endpoint-token" value="' + other_endpoint_token + '">');

    if (endpoint_other === null) {
      event.preventDefault();
      result.html('<p class="error">Please Login The Other Endpoint.</p>');
      return false;
    }
    $('form').append('<input type="hidden" name="endpoint-other" value="' + endpoint_other + '">');
  });

});
