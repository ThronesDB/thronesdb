/* global app, _ */

(function ui_deckinit(ui, $)
{
  function build_restrictions_selector(containerId) {
    var $container = $(containerId);
    if (! $container.length) {
      return;
    }
    var selectedRestriction;
    var out = '';
    var activeRestrictions = app.data.restrictions.find({
      'active': true
    }, {
      $orderBy: {
        'effectiveOn': -1
      }
    });

    if (2 > activeRestrictions.length) {
      return;
    }

    selectedRestriction = app.data.getBestSelectedRestrictedList();
    activeRestrictions.forEach(function(rl) {
      out += '<option value="' + rl.code + '"';
      if (rl.code === selectedRestriction.code) {
        out += ' selected="selected"';
      }
      out += '>'
      out += rl.title;
      out += '</option>';
    });
    $container.html(out);
    $container.removeClass('hidden');
  }

  function on_rl_change(event) {
    var code = event.target.value;
    app.config.set('restriction', code);
    refresh_agendas();
  }

  function refresh_agendas() {
    var rl = app.data.getBestSelectedRestrictedList();
    if (! rl) {
      $('.agenda').removeClass('hidden');
      return;
    }

    $('.agenda').each(function (index, elem) {
      var $elem = $(elem);
      var code = $(elem).attr('data-card-code');
      if (-1 === rl.contents.joust.banned.indexOf(code) || -1 === rl.contents.melee.banned.indexOf(code)) {
        $elem.removeClass('hidden');
      } else {
        $elem.addClass('hidden');
        $('input', $elem).each(function(idx, input) {
          var $input = $(input);
          $input.prop('checked', false);
        });
      }
    });

    if (! $('.agenda input:checked').length) {
      $('.no-agenda input[type="radio"]').prop('checked', true);
    }
  }

  /**
   * sets up event handlers ; dataloaded not fired yet
   * @memberOf ui
   */
  ui.setup_event_handlers = function setup_event_handlers() {
    $('#restricted_lists').on('change', on_rl_change);
  };

  /**
   * called when the DOM is loaded
   * @memberOf ui
   */
  ui.on_dom_loaded = function on_dom_loaded() {
    ui.setup_event_handlers();
  };

  /**
   * called when the app data is loaded
   * @memberOf ui
   */
  ui.on_data_loaded = function on_data_loaded() {
  };

  /**
   * called when both the DOM and the data app have finished loading
   * @memberOf ui
   */
  ui.on_all_loaded = function on_all_loaded() {
    build_restrictions_selector('#restricted_lists');
    refresh_agendas();
  };

})(app.ui, jQuery);
