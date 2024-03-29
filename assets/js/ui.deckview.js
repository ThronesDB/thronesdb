/* global app, Translator */

(function ui_deck(ui, $)
{

    function confirm_delete()
    {
        $('#delete-deck-name').text(app.deck.get_name());
        $('#delete-deck-id').val(app.deck.get_id());
        $('#deleteModal').modal('show');
    }

    ui.do_action_deck = function do_action_deck(event)
    {

        var action_id = $(this).attr('id');
        if(!action_id)
            return;

        switch(action_id) {
            case 'btn-delete':
                confirm_delete();
                break;
            case 'btn-print':
                window.print();
                break;
            case 'btn-sort-default':
                app.deck.change_sort('type');
                break;
            case 'btn-sort-name':
                app.deck.change_sort('name');
                break;
            case 'btn-sort-set':
                app.deck.change_sort('set');
                break;
            case 'btn-sort-setnumber':
                app.deck.change_sort('setnumber');
                break;
            case 'btn-sort-faction':
                app.deck.change_sort('faction');
                break;
            case 'btn-sort-factionnumber':
                app.deck.change_sort('factionnumber');
                break;
            case 'btn-sort-cardnumber':
                app.deck.change_sort('cardnumber');
                break;
            case 'btn-sort-cost':
                app.deck.change_sort('cost');
                break;
            case 'btn-export-plaintext':
                ui.export_plaintext(app.deck);
                break;
            case 'btn-export-markdown':
                ui.export_markdown(app.deck);
                break;
            case 'btn-export-agotcards':
                ui.export_agotcards(app.deck);
                break;
            case 'btn-export-plotsanddrawdeckonly':
                ui.export_plotsanddrawdeckonly(app.deck);
                break;
        }

        if (action_id !== 'btn-publish' &&
            action_id !== 'btn-clone' ) {
            event.preventDefault();
        }
    };

    /**
     * sets up event handlers ; dataloaded not fired yet
     * @memberOf ui
     */
    ui.setup_event_handlers = function setup_event_handlers()
    {

        $('#btn-group-deck').on({
            click: ui.do_action_deck
        }, 'button[id],a[id]');
        $('#restricted_lists').on('change', 'input[type=radio]', ui.on_rl_change);

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
     * @param event
     */
    ui.on_rl_change = function on_rl_change(event) {
        var code = $(event.target).attr('data-rl-code');
        app.config.set('restriction', code);
        ui.refresh_deck();
    }

    /**
     * called when the DOM is loaded
     * @memberOf ui
     */
    ui.on_dom_loaded = function on_dom_loaded()
    {
        ui.setup_event_handlers();
        app.draw_simulator && app.draw_simulator.on_dom_loaded();
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
        app.markdown && app.markdown.update(app.deck.get_description_md() || Translator.trans('decks.defaultemptydesc'), '#description');
        ui.build_restrictions_selector('#restricted_lists');
        ui.refresh_deck();
        ui.refresh_rl_indicators();
    };

})(app.ui, jQuery);
