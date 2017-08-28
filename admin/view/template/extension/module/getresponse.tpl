<?php echo $header; ?>
<?php echo $column_left; ?>
<div id="content">
  <div class="page-header">
    <div class="container-fluid">
      <div class="pull-right">
        <a onclick="$('#form').submit();" class="btn btn-primary"><?php echo $button_save; ?></a>
        <a onclick="location = '<?php echo $cancel; ?>';"
           class="btn btn-default"><?php echo $button_cancel; ?></a>
      </div>
      <h1><?php echo $heading_title; ?></h1>
      <ul class="breadcrumb">
        <?php foreach ($breadcrumbs as $breadcrumb) { ?>
        <li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
        <?php } ?>
      </ul>
    </div>
  </div>
  <div class="container-fluid">

    <?php if (isset($error_warning) && !empty($error_warning)) { ?>
    <div class="alert alert-danger" role="alert"><?php echo $error_warning; ?></div>
    <?php } ?>

    <?php if (isset($save_success) && !empty($save_success)) { ?>
    <div class="alert alert-success" role="alert"><?php echo $save_success; ?></div>
    <?php } ?>

    <div class="panel panel-default">
      <div class="panel-body">
        <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form"
              class="form-horizontal">
          <input type="hidden" value="1" name="getresponse_status">
          <ul class="nav nav-tabs" role="tablist">
            <li role="presentation" class="<?php if ($active_tab == 'home') {echo "active";} ?>"><a href="#settings" aria-controls="home" role="tab" data-toggle="tab"><?php echo $apikey_title; ?></a></li>
            <?php if (!empty($getresponse_apikey)) { ?>
            <li role="presentation" class="<?php if ($active_tab == 'export') {echo "active";} ?>"><a href="#export" aria-controls="export" role="tab" data-toggle="tab"><?php echo $export_title; ?></a></li>
            <li role="presentation" class="<?php if ($active_tab == 'registration') {echo "active";} ?>"><a href="#registration" aria-controls="registration" role="tab" data-toggle="tab"><?php echo $register_title; ?></a></li>
            <li role="presentation" class="<?php if ($active_tab == 'webform') {echo "active";} ?>"><a href="#webform" aria-controls="webform" role="tab" data-toggle="tab"><?php echo $webform_title; ?></a></li>
            <?php } ?>
          </ul>
          <div class="tab-content">
            <div role="tabpanel" class="tab-pane <?php if ($active_tab == 'home') {echo "active";} ?>" id="settings">
              <h3><?php echo $apikey_title; ?></h3>
              <p><?php echo $apikey_info; ?></p>
              <div class="form-group required">
                <label for="getresponse-apikey"
                       class="col-sm-2 control-label"><?php echo $entry_apikey; ?></label>
                <div class="col-sm-10">
                  <input id="getresponse-apikey" class="form-control" type="text" name="getresponse_apikey"
                         value="<?php echo $getresponse_apikey; ?>" size="50"/>
                  <span id="gr-apikey-help" class="help-block"><?php echo $entry_apikey_hint; ?></span>
                </div>
              </div>
            </div>
            <?php if (!empty($getresponse_apikey)) { ?>
            <div role="tabpanel" class="tab-pane <?php if ($active_tab == 'export') {echo "active";} ?>" id="export">
              <h3><?php echo $export_title; ?></h3>
              <p><?php echo $export_info; ?></p>
              <div class="form-group">
                <label for="getresponse-campaign"
                       class="col-sm-2 control-label"><?php echo $label_campaign; ?></label>
                <div class="col-sm-10">
                  <select id="getresponse-campaign" class="form-control" name="getresponse_campaign">
                    <?php foreach ($campaigns as $campaign) { ?>
                    <option value="<?php echo $campaign->campaignId; ?>"<?php if (isset($getresponse_campaign) && $getresponse_campaign == $campaign->campaignId) { ?> selected<?php } ?>><?php echo $campaign->name; ?></option>
                    <?php } ?>
                  </select>
                  <span id="config-campaign-help" class="help-block"><?php echo $entry_campaign_hint; ?></span>
                </div>
              </div>
              <div class="col-sm-offset-2 col-sm-3">
                <a id="gr-export" class="button btn btn-primary"><?php echo $button_export; ?></a>
              </div>
              <div class="col-sm-7">
                <div class="gr-info"></div>
                <div class="rollling"></div>
              </div>
            </div>
            <div role="tabpanel" class="tab-pane <?php if ($active_tab == 'registration') {echo "active";} ?>" id="registration">
              <h3><?php echo $register_title; ?></h3>
              <p><?php echo $register_info; ?></p>
              <div class="form-group">
                <label for="getresponse-reg-active" class="col-sm-2 control-label"><?php echo $label_active; ?></label>
                <div class="col-sm-10">
                  <div class="radio-inline">
                    <input type="radio" id="getresponse-reg-active" class="form-control getresponse-reg-active"<?php if (isset($getresponse_reg['active']) && $getresponse_reg['active'] == 1) { ?> checked<?php } ?> name="getresponse_reg[active]" value="1"> <?php echo $label_yes; ?>
                  </div>
                  <div class="radio-inline">
                    <input type="radio" class="form-control getresponse-reg-active"<?php if (!isset($getresponse_reg['active']) || $getresponse_reg['active'] != 1) { ?> checked<?php } ?> name="getresponse_reg[active]" value="0"> <?php echo $label_no; ?>
                  </div>
                </div>
              </div>
              <div class="form-group">
                <label for="getresponse-reg-campaign" class="col-sm-2 control-label"><?php echo $label_campaign; ?></label>
                <div class="col-sm-10">
                  <select name="getresponse_reg[campaign]" id="getresponse-reg-campaign" class="form-control">
                    <?php foreach ($campaigns as $campaign) { ?>
                    <option value="<?php echo $campaign->campaignId; ?>"<?php if ($getresponse_reg['campaign'] == $campaign->campaignId) { ?> selected<?php } ?>><?php echo $campaign->name; ?></option>
                    <?php } ?>
                  </select>
                </div>
              </div>
              <div class="form-group sequence-day-label">
                <div class="col-sm-2"></div>
                <div class="col-sm-10 checkbox">
                  <label>
                    <input type="checkbox" value="1"<?php if (isset($getresponse_reg['sequence_active']) && $getresponse_reg['sequence_active'] == 1) { ?> checked<?php } ?> name="getresponse_reg[sequence_active]"> <?php echo $label_auto_queue; ?>
                  </label>
                </div>
              </div>
              <div class="form-group sequence-day-label">
                <label for="getresponse-reg-day" class="col-sm-2 control-label"><?php echo $label_day_of_cycle; ?></label>
                <div class="col-sm-10">
                  <select name="getresponse_reg[day]" id="getresponse-reg-day" class="form-control"></select>
                </div>
              </div>
            </div>
            <div role="tabpanel" class="tab-pane <?php if ($active_tab == 'webform') {echo "active";} ?>" id="webform">
              <h3><?php echo $webform_title; ?></h3>
              <p><?php echo $webform_info; ?></p>
              <div class="form-group">
                <label for="getresponse-form-active" class="col-sm-2 control-label"><?php echo $label_active; ?></label>
                <div class="col-sm-10">
                  <div class="radio-inline">
                    <input name="getresponse_form[active]" type="radio" id="getresponse-form-active" class="form-control getresponse-form-active"<?php if (isset($getresponse_form['active']) && $getresponse_form['active'] == 1) { ?> checked<?php } ?> name="getresponse_form[active]" value="1"> <?php echo $label_yes; ?>
                  </div>
                  <div class="radio-inline">
                    <input name="getresponse_form[active]" type="radio" class="form-control getresponse-reg-active"<?php if (!isset($getresponse_form['active']) || (isset($getresponse_form['active']) && $getresponse_form['active'] != 1)) { ?> checked<?php } ?> name="getresponse_form[active]" value="0"> <?php echo $label_no; ?>
                  </div>
                </div>
              </div>
              <div class="form-group">
                <label for="getresponse-reg-active" class="col-sm-2 control-label"><?php echo $label_form; ?></label>
                <div class="col-sm-10">
                  <select name="getresponse_form[id]" id="getresponse-form-id" class="form-control">
                    <?php if (isset($new_forms) && !empty($new_forms)) { ?>
                    <optgroup label="<?php echo $label_new_forms; ?>">
                    <?php foreach ($new_forms as $form) { ?>
                    <option value="<?php echo $form['id']; ?>"<?php if (isset($getresponse_form['id']) && $getresponse_form['id'] == $form['id']) { ?> selected<?php } ?> data-url="<?php echo $form['url']; ?>"><?php echo $form['name']; ?></option>
                    <?php } ?>
                    </optgroup>
                    <?php } ?>
                    <?php if (isset($old_forms) && !empty($old_forms)) { ?>
                    <optgroup label="<?php echo $label_old_forms; ?>">
                      <?php foreach ($old_forms as $form) { ?>
                      <option value="<?php echo $form['id']; ?>"<?php if (isset($getresponse_form['id']) && $getresponse_form['id'] == $form['id']) { ?> selected<?php } ?> data-url="<?php echo $form['url']; ?>"><?php echo $form['name']; ?></option>
                      <?php } ?>
                    </optgroup>
                    <?php } ?>
                  </select>
                </div>
              </div>
            </div>
            <?php } ?>
          </div>
          <input type="hidden" name="getresponse_form[current_tab]" id="getresponse-current-tab" value="">
        </form>
      </div>
    </div>
  </div>
</div>

<script type="text/javascript">
  <!--
  $(function () {
    var api_key_element = $('#getresponse-apikey');
    var campaign_element = $('#getresponse-campaign');
    var export_element = $('#gr-export');
    var tab_element = $( "li[role='presentation']" );
    var current_tab_input = $('#getresponse-current-tab');

    $('#getresponse-reg-campaign').change(function () {
      getCycleDays();
    });

    function getCycleDays() {
      var campaign_id = $('#getresponse-reg-campaign').val(),
              day_element = $('#getresponse-reg-day'),
              cycles_label = $('.sequence-day-label'),
              cycles = $.parseJSON('<?php echo isset($campaign_days) ? addslashes(json_encode($campaign_days)) : "{}"; ?>');

      cycles_label.hide();
      day_element.text('');

      if (cycles.hasOwnProperty(campaign_id)) {
          cycles_label.show();
          var obj = cycles[campaign_id];
          var current_cycle_day = "<?php echo isset($getresponse_reg['day']) ? $getresponse_reg['day'] : null ?>";
          for (var prop in obj) {
              if (obj.hasOwnProperty(prop)) {
                  var select = (current_cycle_day === obj[prop].day) ? 'selected' : '';
                  var option = '<option value="' + parseInt(obj[prop].day) + '" ' + select + '>Day ' +
                          obj[prop].day + ': ' + obj[prop].name + ' (' + obj[prop].status + ')' +
                          '</option>';

                  day_element.append(option);
              }
          }
      }
    }

    getCycleDays();

    function ajax_export() {
      var api_key = api_key_element.val();
      var campaign = campaign_element.val();

      $.ajax({
        url: "index.php?route=extension/module/getresponse/export&token=<?php echo $token; ?>",
        beforeSend: function () {
          $('.gr-info').html('<div class="alert alert-info"><?php echo $info_loading; ?></div>')
        },
        data: {
          'api_key': api_key,
          'campaign': campaign
        },
        success: function (data) {
          $('.gr-info').html('<div class="alert alert-success">' + data.response + '</div>');
        },
        error: function (response) {
          $('.gr-info').html(' <div class="alert alert-danger"><?php echo $info_ajax_error; ?></div>');
        },
        type: "POST",
        async: false,
        dataType: "json"
      });
    }

    $(export_element).click(function () {
      ajax_export();
    });

    $(tab_element).click(function() {
      var new_value = $(this).find('a').attr('aria-controls');
      current_tab_input.val(new_value);
    });
    current_tab_input.val('<?php echo $active_tab; ?>');
  });
  //-->
</script>
<?php echo $footer; ?>