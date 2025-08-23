/* global app, _, Translator */

(function ui_deck(ui, $)
{

    var DisplayColumnsTpl = '',
            SortKey = 'type_code',
            SortOrder = 1,
            CardDivs = [[], [], []];

    /**
     * inits the state of config buttons
     * @memberOf ui
     */
    ui.init_config_buttons = function init_config_buttons()
    {
        // radio
        ['display-column', 'core-set', 'buttons-behavior'].forEach(function (radio)
        {
            $('input[name=' + radio + '][value=' + app.config.get(radio) + ']').prop('checked', true);
        });
        // checkbox
        ['show-unusable', 'show-only-deck', 'hide-banned-melee', 'hide-banned-joust'].forEach(function (checkbox)
        {
            if(app.config.get(checkbox))
                $('input[name=' + checkbox + ']').prop('checked', true);
        });

    };

    /**
     * removes titles, which cannot be used in decks
     * @memberOf ui
     */
    ui.remove_melee_titles = function remove_melee_titles()
    {
        app.data.cards.remove({
            'type_code': 'title'
        });
    };

    /**
     * sets the maxqty of each card
     * @memberOf ui
     */
    ui.set_max_qty = function set_max_qty()
    {
        app.data.cards.find().forEach(function (record)
        {
            var max_qty = Math.min(3, record.deck_limit);
            if(record.pack_code === 'Core')
                max_qty = Math.min(max_qty, record.quantity * app.config.get('core-set'));
            app.data.cards.updateById(record.code, {
                maxqty: max_qty
            });
        });
    };

    /**
     * builds the faction selector
     * @memberOf ui
     */
    ui.build_faction_selector = function build_faction_selector()
    {
        $('[data-filter=faction_code]').empty();
        var faction_codes = app.data.cards.distinct('faction_code').sort();
        var neutral_index = faction_codes.indexOf('neutral');
        faction_codes.splice(neutral_index, 1);
        faction_codes.unshift('neutral');

        faction_codes.forEach(function (faction_code)
        {
            var example = app.data.cards.find({"faction_code": faction_code})[0];
            var label = $('<label class="btn btn-default btn-sm" data-code="'
                    + faction_code + '" title="' + example.faction_name + '"><input type="checkbox" name="' + faction_code
                    + '"><span class="icon-' + faction_code + '"></span></label>');
            label.tooltip({container: 'body'});
            $('[data-filter=faction_code]').append(label);
        });
        $('[data-filter=faction_code]').button();
    };

    /**
     * builds the type selector
     * @memberOf ui
     */
    ui.build_type_selector = function build_type_selector()
    {
        $('[data-filter=type_code]').empty();
        ['agenda', 'plot', 'character', 'attachment', 'location', 'event'].forEach(function (type_code)
        {
            var example = app.data.cards.find({"type_code": type_code})[0];
            var label = $('<label class="btn btn-default btn-sm" data-code="'
                    + type_code + '" title="' + example.type_name + '"><input type="checkbox" name="' + type_code
                    + '"><span class="icon-' + type_code + '"></span></label>');
            label.tooltip({container: 'body'});
            $('[data-filter=type_code]').append(label);
        });
        $('[data-filter=type_code]').button();
    };

    /**
     * builds the pack selector
     * @memberOf ui
     */
    ui.build_pack_selector = function build_pack_selector()
    {
        $('[data-filter=pack_code]').empty();
        app.data.packs.find({
            name: {
                '$exists': true
            }
        }, {
            $orderBy: {
                cycle_position: 1,
                position: 1
            }
        }).forEach(function (record)
        {
            // checked or unchecked ? checked by default
            var checked = true;

            // if publication date is not yet available, or if it's in the future then uncheck pack, or if it's
            // one of the variant/"special" products like Valyrian Draft Set (VDS).
            if(! record.available
              || Date.parse(record.available) > Date.now()
              || ['VDS', 'VKm', 'VHotK', 'ToJ'].includes(record.code)
            ) {
                checked = false;
            }

            // if pack used by cards in deck, check pack
            var cards = app.data.cards.find({
                pack_code: record.code,
                indeck: {
                    '$gt': 0
                }
            });
            if(cards.length) {
                checked = true;
            }

            // if user checked or unchecked it previously, check or uncheck it
            if (localStorage) {
                if (localStorage.getItem('set_code_' + record.code) === '1') {
                    checked = true;
                } else if (localStorage.getItem('set_code_' + record.code) === '0') {
                    checked = false;
                }
            }

            $('<li><a href="#"><label><input type="checkbox" name="' + record.code + '"' + (checked ? ' checked="checked"' : '') + '>' + record.name + '</label></a></li>').appendTo('[data-filter=pack_code]');
        });
    };

    /**
     * @memberOf ui
     */
    ui.init_selectors = function init_selectors()
    {
        $('[data-filter=faction_code]').find('input[name=neutral]').prop("checked", true).parent().addClass('active');
        $('[data-filter=faction_code]').find('input[name=' + app.deck.get_faction_code() + ']').prop("checked", true).parent().addClass('active');

        var agendas = app.deck.get_agendas() || [];
        var faction_codes = [];
        var i;

        // special case:
        // for "The Conclave", "Kingdom of Shadows", "The Free Folk", "The White Book",
        // "Mummer's Farce", "Uniting the Realm", "The Small Council", and "Trading with Braavos" pre-select all factions.
        if (agendas.length
            && -1 !== ["09045", "13079", "17148", "13099", "17150", "20051", "25120", "26040", "26620"].indexOf(agendas[0]['code'])) {
            faction_codes = app.deck.get_all_faction_codes();
        } else {
            faction_codes = app.deck.get_minor_faction_codes();
        }
        for(i = 0; i < faction_codes.length; i++) {
            $('[data-filter=faction_code]').find('input[name=' + faction_codes[i] + ']').prop("checked", true).parent().addClass('active');
        }

        $('[data-filter=type_code]').find('input[name=character]').prop("checked", true).parent().addClass('active');
    };

    function uncheck_all_others()
    {
        $(this).closest('[data-filter]').find("input[type=checkbox]").prop("checked", false);
        $(this).children('input[type=checkbox]').prop("checked", true).trigger('change');
    }

    function check_all_others()
    {
        $(this).closest('[data-filter]').find("input[type=checkbox]").prop("checked", true);
        $(this).children('input[type=checkbox]').prop("checked", false);
    }

    function uncheck_all_active()
    {
        $(this).closest('[data-filter]').find("label.active").button('toggle');
    }

    function check_all_inactive()
    {
        $(this).closest('[data-filter]').find("label:not(.active)").button('toggle');
    }

    /**
     * @memberOf ui
     * @param event
     */
    ui.on_click_filter = function on_click_filter(event)
    {
        var dropdown = $(this).closest('ul').hasClass('dropdown-menu');
        if(dropdown) {
            if(event.shiftKey) {
                if(!event.altKey) {
                    uncheck_all_others.call(this);
                } else {
                    check_all_others.call(this);
                }
            }
            event.stopPropagation();
        } else {
            if(!event.shiftKey && app.config.get('buttons-behavior') === 'exclusive'
              || event.shiftKey && app.config.get('buttons-behavior') === 'cumulative') {
                if(!event.altKey) {
                    uncheck_all_active.call(this);
                } else {
                    check_all_inactive.call(this);
                }
            }
        }
    };

    /**
     * @memberOf ui
     * @param event
     */
    ui.on_input_smartfilter = function on_input_smartfilter(event)
    {
        var q = $(this).val();
        if(q.match(/^\w[:<>!]/))
            app.smart_filter.update(q);
        else
            app.smart_filter.update('');
        ui.refresh_list();
    };

    /**
     * @memberOf ui
     * @param event
     */
    ui.on_submit_form = function on_submit_form(event)
    {
        var deck_json = app.deck.get_json();
        $('input[name=content]').val(deck_json);
        $('input[name=description]').val($('textarea[name=description_]').val());
        $('input[name=tags]').val($('input[name=tags_]').val());
    };

    /**
     * @memberOf ui
     * @param event
     */
    ui.on_config_change = function on_config_change(event)
    {
        var name = $(this).attr('name');
        var type = $(this).prop('type');
        switch(type) {
            case 'radio':
                var value = $(this).val();
                if(!isNaN(parseInt(value, 10)))
                    value = parseInt(value, 10);
                app.config.set(name, value);
                break;
            case 'checkbox':
                app.config.set(name, $(this).prop('checked'));
                break;
        }
        switch(name) {
            case 'buttons-behavior':
                break;
            case 'core-set':
                ui.set_max_qty();
                ui.reset_list();
                break;
            case 'display-column':
                ui.update_list_template();
                ui.refresh_list();
                break;
            default:
                ui.refresh_list();
        }
    };

    /**
     * @memberOf ui
     * @param event
     */
    ui.on_table_sort_click = function on_table_sort_click(event)
    {
        event.preventDefault();
        var new_sort = $(this).data('sort');
        if(SortKey === new_sort) {
            SortOrder *= -1;
        } else {
            SortKey = new_sort;
            SortOrder = 1;
        }
        ui.refresh_list();
        ui.update_sort_caret();
    };

    /**
     * @memberOf ui
     * @param event
     */
    ui.on_list_quantity_change = function on_list_quantity_change(event)
    {
        var row = $(this).closest('.card-container');
        var code = row.data('code');
        var quantity = parseInt($(this).val(), 10);
//	row[quantity ? "addClass" : "removeClass"]('in-deck');
        ui.on_quantity_change(code, quantity);
    };

    /**
     * @memberOf ui
     * @param event
     */
    ui.on_rl_change = function on_rl_change(event) {
        var code = $(event.target).attr('data-rl-code');
        app.config.set('restriction', code);
        ui.on_deck_modified();
    }

    /**
     * @memberOf ui
     * @param event
     */
    ui.on_modal_quantity_change = function on_modal_quantity_change(event)
    {
        var modal = $('#cardModal');
        var code = modal.data('code');
        var quantity = parseInt($(this).val(), 10);
        modal.modal('hide');
        ui.on_quantity_change(code, quantity);

        setTimeout(function ()
        {
            $('#filter-text').typeahead('val', '').focus();
        }, 100);
    };

    ui.refresh_row = function refresh_row(card_code, quantity)
    {
        // for each set of divs (1, 2, 3 columns)
        CardDivs.forEach(function (rows)
        {
            var row = rows[card_code];
            if(!row)
                return;

            // rows[card_code] is the card row of our card
            // for each "quantity switch" on that row
            row.find('input[name="qty-' + card_code + '"]').each(function (i, element)
            {
                // if that switch is NOT the one with the new quantity, uncheck it
                // else, check it
                if($(element).val() !== ''+quantity) {
                    $(element).prop('checked', false).closest('label').removeClass('active');
                } else {
                    $(element).prop('checked', true).closest('label').addClass('active');
                }
            });
        });
    };

    /**
     * @memberOf ui
     */
    ui.on_quantity_change = function on_quantity_change(card_code, quantity)
    {
        var update_all = app.deck.set_card_copies(card_code, quantity);
        ui.refresh_rl_indicators();
        ui.refresh_deck();

        if(update_all) {
            ui.refresh_list();
        } else {
            ui.refresh_row(card_code, quantity);
        }
    };

    /**
     * @memberOf ui
     * @param event
     */
    ui.on_pack_code_change = function(event) {
        if ($(event.target).is('[type="checkbox"]') && event.target.name && localStorage) {
            if (event.target.checked) {
                localStorage.setItem("set_code_" + event.target.name, '1');
            } else {
                localStorage.setItem("set_code_" + event.target.name, '0');
            }
        }
        ui.refresh_list();
    };

    /**
     * sets up event handlers ; dataloaded not fired yet
     * @memberOf ui
     */
    ui.setup_event_handlers = function setup_event_handlers()
    {
        $('[data-filter=pack_code]').on('change', 'label', ui.on_pack_code_change);

        $('[data-filter=faction_code]').on({
            change: ui.refresh_list,
            cqlick: ui.on_click_filter
        }, 'label');

        $('[data-filter=type_code]').on({
            change: ui.refresh_list,
            click: ui.on_click_filter
        }, 'label');

        $('#filter-text').on('input', ui.on_input_smartfilter);

        $('#save_form').on('submit', ui.on_submit_form);

        $('#btn-save-as-copy').on('click', function (event)
        {
            $('#deck-save-as-copy').val(1);
        });

        $('#btn-cancel-edits').on('click', function (event)
        {
            var unsaved_edits = app.deck_history.get_unsaved_edits();
            if(unsaved_edits.length) {
                var confirmation = confirm("This operation will revert the changes made to the deck since " + unsaved_edits[0].date_creation.calendar() + ". The last " + (unsaved_edits.length > 1 ? unsaved_edits.length + " edits" : "edit") + " will be lost. Do you confirm?");
                if(!confirmation)
                    return false;
            } else {
                if(app.deck_history.is_changed_since_last_autosave()) {
                    var confirmation = confirm("This operation will revert the changes made to the deck. Do you confirm?");
                    if(!confirmation)
                        return false;
                }
            }
            $('#deck-cancel-edits').val(1);
        });

        $('#config-options').on('change', 'input', ui.on_config_change);
        $('#collection').on('change', 'input[type=radio]', ui.on_list_quantity_change);
        $('#restricted_lists').on('change', 'input[type=radio]', ui.on_rl_change);

        $('#cardModal').on('keypress', function (event)
        {
            var num = parseInt(event.which, 10) - 48;
            $('#cardModal input[type=radio][value=' + num + ']').trigger('change');
        });
        $('#cardModal').on('change', 'input[type=radio]', ui.on_modal_quantity_change);

        $('thead').on('click', 'a[data-sort]', ui.on_table_sort_click);

        $('#btn-sort-default').on('click', function(event) { event.preventDefault(); app.deck.change_sort('type'); });
        $('#btn-sort-name').on('click', function(event) { event.preventDefault(); app.deck.change_sort('name'); });
        $('#btn-sort-set').on('click', function(event) { event.preventDefault(); app.deck.change_sort('set'); });
        $('#btn-sort-setnumber').on('click', function(event) { event.preventDefault(); app.deck.change_sort('setnumber'); });
        $('#btn-sort-faction').on('click', function(event) { event.preventDefault(); app.deck.change_sort('faction'); });
        $('#btn-sort-factionnumber').on('click', function(event) { event.preventDefault(); app.deck.change_sort('factionnumber'); });
        $('#btn-sort-cardnumber').on('click', function(event) { event.preventDefault(); app.deck.change_sort('cardnumber'); });
        $('#btn-sort-cost').on('click', function(event) { event.preventDefault(); app.deck.change_sort('cost'); });
   };

    /**
     * returns the current card filters as an array
     * @memberOf ui
     */
    ui.get_filters = function get_filters()
    {
        var filters = {};
        $('[data-filter]').each(
                function (index, div)
                {
                    var column_name = $(div).data('filter');
                    var arr = [];
                    $(div).find("input[type=checkbox]").each(
                            function (index, elt)
                            {
                                if($(elt).prop('checked'))
                                    arr.push($(elt).attr('name'));
                            }
                    );
                    if(arr.length) {
                        filters[column_name] = {
                            '$in': arr
                        };
                    }
                }
        );
        return filters;
    };

    /**
     * updates internal variables when display columns change
     * @memberOf ui
     */
    ui.update_list_template = function update_list_template()
    {
        switch(app.config.get('display-column')) {
            case 1:
                DisplayColumnsTpl = _.template(
                        '<tr>'
                        + '<td><div class="btn-group" data-toggle="buttons"><%= radios %></div></td>'
                        + '<td><a class="card card-tip" data-code="<%= card.code %>" href="<%= url %>" data-target="#cardModal" data-remote="false" data-toggle="modal"><%= card.label %></a><span class="rl-labels"><%= labels %></span></td>'
                        + '<td class="cost"><%= card.cost %><%= card.income %></td>'
                        + '<td class="cost"><%= card.strength %><%= card.initiative %></td>'
                        + '<td class="type"><span class="icon-<%= card.type_code %>" title="<%= card.type_name %>"></span></td>'
                        + '<td class="faction"><span class="icon-<%= card.faction_code %> fg-<%= card.faction_code %>" title="<%= card.faction_name %>"></span></td>'
                        + '</tr>'
                        );
                break;
            case 2:
                DisplayColumnsTpl = _.template(
                        '<div class="col-sm-6">'
                        + '<div class="media">'
                        + '<div class="media-left"><img class="media-object" src="<%= card.image_url %>" alt="<%= card.name %>"></div>'
                        + '<div class="media-body">'
                        + '<h4 class="media-heading"><a class="card card-tip" data-code="<%= card.code %>" href="<%= url %>" data-target="#cardModal" data-remote="false" data-toggle="modal"><%= card.name %></a><span class="rl-labels"><%= labels %></span></h4>'
                        + '<div class="btn-group" data-toggle="buttons"><%= radios %></div>'
                        + '</div>'
                        + '</div>'
                        + '</div>'
                        );
                break;
            case 3:
                DisplayColumnsTpl = _.template(
                        '<div class="col-sm-4">'
                        + '<div class="media">'
                        + '<div class="media-left"><img class="media-object" src="<%= card.image_url %>" alt="<%= card.name %>"></div>'
                        + '<div class="media-body">'
                        + '<h5 class="media-heading"><a class="card card-tip" data-code="<%= card.code %>" href="<%= url %>" data-target="#cardModal" data-remote="false" data-toggle="modal"><%= card.name %></a><span class="rl-labels"><%= labels %></span></h5>'
                        + '<div class="btn-group" data-toggle="buttons"><%= radios %></div>'
                        + '</div>'
                        + '</div>'
                        + '</div>'
                        );
        }
    };

    /**
     * builds a row for the list of available cards
     * @memberOf ui
     */
    ui.build_row = function build_row(card)
    {
        var radios = '', radioTpl = _.template(
                '<label class="btn btn-xs btn-default <%= active %>"><input type="radio" name="qty-<%= card.code %>" value="<%= i %>"><%= i %></label>'
                );

        for(var i = 0; i <= card.maxqty; i++) {
            radios += radioTpl({
                i: i,
                active: (i === card.indeck ? ' active' : ''),
                card: card
            });
        }

        var html = DisplayColumnsTpl({
            radios: radios,
            url: Routing.generate('cards_zoom', {card_code: card.code}),
            card: card,
            labels: app.deck.get_card_labels(card)
        });
        return $(html);
    };

    ui.reset_list = function reset_list()
    {
        CardDivs = [[], [], []];
        ui.refresh_list();
    };

    /**
     * destroys and rebuilds the list of available cards
     * don't fire unless 250ms has passed since last invocation
     * @memberOf ui
     */
    ui.refresh_list = _.debounce(function refresh_list()
    {
        $('#collection-table').empty();
        $('#collection-grid').empty();

        var counter = 0,
                container = $('#collection-table'),
                filters = ui.get_filters(),
                query = app.smart_filter.get_query(filters),
                orderBy = {};

        SortKey.split('|').forEach(function (key)
        {
            orderBy[key] = SortOrder;
        });
        if(SortKey !== 'name')
            orderBy['name'] = 1;
        var cards = app.data.cards.find(query, {'$orderBy': orderBy});
        var divs = CardDivs[ app.config.get('display-column') - 1 ];
        var rl = app.data.getBestSelectedRestrictedList();

        cards.forEach(function (card)
        {
            if(app.config.get('show-only-deck') && !card.indeck)
                return;
            var unusable = !app.deck.can_include_card(card);
            if(!app.config.get('show-unusable') && unusable)
                return;
            if (rl) {
                if (app.config.get('hide-banned-joust') && -1 !== rl.contents.joust.banned.indexOf(card.code)) {
                    return;
                }
                if (app.config.get('hide-banned-melee') && -1 !== rl.contents.melee.banned.indexOf(card.code)) {
                    return;
                }
            }

            var row = divs[card.code];
            if(!row)
                row = divs[card.code] = ui.build_row(card);

            row.data("code", card.code).addClass('card-container');

            row.find('input[name="qty-' + card.code + '"]').each(
                    function (i, element)
                    {
                        if($(element).val() === ''+card.indeck) {
                            $(element).prop('checked', true).closest('label').addClass('active');
                        } else {
                            $(element).prop('checked', false).closest('label').removeClass('active');
                        }
                    }
            );

            row.find('.rl-labels').html(app.deck.get_card_labels(card));

            if(unusable) {
                row.find('label').addClass("disabled").find('input[type=radio]').attr("disabled", true);
            }

            if(app.config.get('display-column') > 1 && (counter % app.config.get('display-column') === 0)) {
                container = $('<div class="row"></div>').appendTo($('#collection-grid'));
            }

            container.append(row);
            counter++;
        });
    }, 250);

    /**
     * called when the deck is modified and we don't know what has changed
     * @memberOf ui
     */
    ui.on_deck_modified = function on_deck_modified()
    {
        ui.refresh_deck();
        ui.refresh_rl_indicators();
        ui.refresh_list();
    };


    /**
     * @memberOf ui
     */
    ui.refresh_deck = function refresh_deck()
    {
        app.deck.display('#deck');
        app.draw_simulator && app.draw_simulator.reset();
        app.deck_charts && app.deck_charts.setup();
    };

    /**
     * @memberOf ui
     */
    ui.setup_typeahead = function setup_typeahead()
    {

        function findMatches(q, cb)
        {
            if(q.match(/^\w:/))
                return;
            var regexp = new RegExp(q, 'i');
            cb(app.data.cards.find({name: regexp}));
        }

        $('#filter-text').typeahead({
            hint: true,
            highlight: true,
            minLength: 2
        }, {
            name: 'cardnames',
            displayKey: 'label',
            source: findMatches
        });

    };

    ui.update_sort_caret = function update_sort_caret()
    {
        var elt = $('[data-sort="' + SortKey + '"]');
        $(elt).closest('tr').find('th').removeClass('dropup').find('span.caret').remove();
        $(elt).after('<span class="caret"></span>').closest('th').addClass(SortOrder > 0 ? '' : 'dropup');
    };

    ui.init_filter_help = function init_filter_help()
    {
        $('#filter-text-button').popover({
            container: 'body',
            content: app.smart_filter.get_help(),
            html: true,
            placement: 'bottom',
            title: Translator.trans('decks.smartfilter.title')
        });
    };

    /**
     * called when the DOM is loaded
     * @memberOf ui
     */
    ui.on_dom_loaded = function on_dom_loaded()
    {
        ui.init_config_buttons();
        ui.init_filter_help();
        ui.update_sort_caret();
        ui.setup_event_handlers();
        app.textcomplete && app.textcomplete.setup('#description');
        app.markdown && app.markdown.setup('#description', '#description-preview');
        app.draw_simulator && app.draw_simulator.on_dom_loaded();
        app.card_modal && $('#filter-text').on('typeahead:selected typeahead:autocompleted', app.card_modal.typeahead);
    };

    /**
     * called when the app data is loaded
     * @memberOf ui
     */
    ui.on_data_loaded = function on_data_loaded()
    {
        ui.remove_melee_titles();
        ui.set_max_qty();
        app.draw_simulator && app.draw_simulator.on_data_loaded();
    };

    /**
     * called when both the DOM and the data app have finished loading
     * @memberOf ui
     */
    ui.on_all_loaded = function on_all_loaded()
    {
        ui.update_list_template();
        ui.build_faction_selector();
        ui.build_type_selector();
        ui.build_pack_selector();
        ui.build_restrictions_selector('#restricted_lists');
        ui.init_selectors();
        ui.refresh_deck();
        ui.refresh_rl_indicators();
        ui.refresh_list();
        ui.setup_typeahead();
        app.deck_history && app.deck_history.setup('#history');
    };

})(app.ui, jQuery);
