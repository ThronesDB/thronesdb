/* global moment, _, app */

(function ui_decks(ui, $)
{
    var tag_row_tpl = _.template('<span class="tag" data-tag="<%- tag %>"><%- tag %></span>');
    var tag_button_tpl = _.template('<button type="button" class="btn btn-default btn-xs" data-toggle="button" data-tag="<%- tag %>"><%- tag %></button>');

    ui.decks = [];

    ui.confirm_delete = function confirm_delete(event)
    {
        var tr = $(this).closest('tr');
        var deck_id = tr.data('id');
        var deck_name = tr.find('.deck-name').text();
        $('#delete-deck-name').text(deck_name);
        $('#delete-deck-id').val(deck_id);
        $('#deleteModal').modal('show');
    };

    ui.confirm_delete_all = function confirm_delete_all(ids)
    {
        $('#delete-deck-list-id').val(ids.join('-'));
        $('#deleteListModal').modal('show');
    };

    ui.set_tags = function set_tags(id, tags)
    {
        var elt = $('tr[data-id=' + id + ']');
        var div = elt.find('div.tags').empty();
        tags.forEach(function (tag)
        {
            div.append($(tag_row_tpl({tag: tag})));
        });

        ui.update_tag_toggles();
    };

    ui.tag_add = function tag_add(ids)
    {
        $('#tag_add_ids').val(ids);
        $('#tagAddModal').modal('show');
        setTimeout(function ()
        {
            $('#tag_add_tags').focus();
        }, 500);
    };

    ui.tag_add_process = function tag_add_process(event)
    {
        event.preventDefault();
        var ids = $('#tag_add_ids').val().split(/,/);
        var tags = $('#tag_add_tags').val().trim().split(/\s+/);
        if(!ids.length || !tags.length)
            return;
        ui.tag_process_any('tag_add', {ids: ids, tags: tags});
    };

    ui.tag_remove = function tag_remove(ids)
    {
        $('#tag_remove_ids').val(ids);
        $('#tagRemoveModal').modal('show');
        setTimeout(function ()
        {
            $('#tag_remove_tags').focus();
        }, 500);
    };

    ui.download_selected = function(ids)
    {
        var $form = $('#download-deck-list-form');
        var $input = $('#download-deck-list-id');
        $input.val(ids.join('-'));
        $form.submit();
    };

    ui.tag_remove_process = function tag_remove_process(event)
    {
        event.preventDefault();
        var ids = $('#tag_remove_ids').val().split(/,/);
        var tags = $('#tag_remove_tags').val().split(/\s+/);
        if(!ids.length || !tags.length)
            return;
        ui.tag_process_any('tag_remove', {ids: ids, tags: tags});
    };

    ui.tag_clear = function tag_clear(ids)
    {
        $('#tag_clear_ids').val(ids);
        $('#tagClearModal').modal('show');
    };

    ui.tag_clear_process = function tag_clear_process(event)
    {
        event.preventDefault();
        var ids = $('#tag_clear_ids').val().split(/,/);
        if(!ids.length)
            return;
        ui.tag_process_any('tag_clear', {ids: ids});
    };

    ui.tag_process_any = function tag_process_any(route, data)
    {
        $.ajax(Routing.generate(route), {
            type: 'POST',
            data: data,
            dataType: 'json',
            success: function (data, textStatus, jqXHR)
            {
                var response = jqXHR.responseJSON;
                if(!response.success) {
                    alert('An error occured while updating the tags.');
                    return;
                }
                $.each(response.tags, function (id, tags)
                {
                    ui.set_tags(id, tags);
                });
            },
            error: function (jqXHR, textStatus, errorThrown)
            {
                console.log('[' + moment().format('YYYY-MM-DD HH:mm:ss') + '] Error on ' + this.url, textStatus, errorThrown);
                alert('An error occured while updating the tags.');
            }
        });
    };

    ui.update_tag_toggles = function update_tag_toggles()
    {
        var tags = [];
        $('#decks span[data-tag]').each(function (index, elt)
        {
          // don't add empty tags
          if ($(elt).data('tag')) {
            tags.push($(elt).data('tag'));
          }
        });
        tags.sort();
        $('#tag_toggles').empty();
        _.uniq(tags).forEach(function (tag)
        {
            $(tag_button_tpl({tag: tag})).appendTo('#tag_toggles');
        });
    };

    ui.filter_decks = function filter_decks()
    {
        var buttons = $('#tag_toggles button.active');
        var tags = [];
        buttons.each(function (index, button)
        {
            tags.push($(button).data('tag'));
        });
        if(tags.length) {
            $('#decks tr').hide();
            tags.forEach(function (tag) {
                $('#decks span.tag').each(function (index, elt) {
                    if ($(elt).data('tag') === tag) {
                        $(elt).closest('tr').show();
                    }
                });
            });
        } else {
            $('#decks tr').show();
        }
    };

    ui.do_diff = function do_diff(ids)
    {
        location.href = Routing.generate('decks_diff', {deck1_uuid: ids[0], deck2_uuid: ids[1]});
    };

    ui.handle_deck_selection_change = function handle_deck_selection_change()
    {
        var $checked = $('#decks').find('input[type=checkbox]:checked');
        var $button = $('#btn-group-selection button');
        if ($checked.length) {
            $button.removeClass('btn-default').addClass('btn-primary')
        } else {
            $button.addClass('btn-default').removeClass('btn-primary')
        }
    }

    ui.do_action_selection = function do_action_selection(event)
    {
        event.stopPropagation();
        var action_id = $(this).attr('id');
        var ids = $('.list-decks input:checked').map(function (index, elt) {
            return $(elt).closest('tr').data('id');
        }).get();
        var uuids = $('.list-decks input:checked').map(function(index, elt) {
           return $(elt).closest('tr').data('uuid');
        });
        if(!action_id || !ids.length || !uuids.length) {
            return;
        }
        switch(action_id) {
            case 'btn-compare':
                ui.do_diff(uuids);
                break;
            case 'btn-tag-add':
                ui.tag_add(ids);
                break;
            case 'btn-tag-remove-one':
                ui.tag_remove(ids);
                break;
            case 'btn-tag-remove-all':
                ui.tag_clear(ids);
                break;
            case 'btn-delete-selected':
                ui.confirm_delete_all(ids);
                break;
            case 'btn-download-selected':
                ui.download_selected(ids);
                $(".selected-decks-dropdown-toggle").dropdown("toggle");
        }
        return false;
    };

    /**
     * called when the DOM is loaded
     * @memberOf ui
     */
    ui.on_dom_loaded = function on_dom_loaded()
    {
        $('.list-decks .toggle_all').on('click', function ()
        {
            $('.list-decks-deck input[type="checkbox"]').prop('checked', this.checked);
            ui.handle_deck_selection_change();
        });

        $('#decks').on('click', 'button.btn-delete-deck', ui.confirm_delete);
        $('#decks').on('click', 'input[type=checkbox]', function ()
        {
            var $checked = $('#decks').find('input[type=checkbox]:checked');
            var $all = $('#decks').find('input[type=checkbox]');
            var $toggle = $('.list-decks .toggle_all');
            if ($checked.length < $all.length) {
                $toggle.prop('checked', false);
            } else {
                $toggle.prop('checked', true);
            }
            ui.handle_deck_selection_change();
        });

        $('#btn-group-selection').on('click', 'button[id],a[id]', ui.do_action_selection);

        $('#tag_toggles').on('click', 'button', function (event)
        {
            var button = $(this);
            if(!event.shiftKey) {
                $('#tag_toggles button').each(function (index, elt)
                {
                    if($(elt).text() != button.text())
                        $(elt).removeClass('active');
                });
            }
            setTimeout(ui.filter_decks, 0);
        });
        ui.update_tag_toggles();

    };

    /**
     * called when the app data is loaded
     * @memberOf ui
     */
    ui.on_data_loaded = function on_data_loaded()
    {

    };

    /**
     * called when both the DOM and the data app have finished loading
     * @memberOf ui
     */
    ui.on_all_loaded = function on_all_loaded()
    {
    };


})(app.ui, jQuery);
