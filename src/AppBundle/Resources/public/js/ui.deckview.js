/* global app, Translator */

(function ui_deck(ui, $)
{

    var DisplaySort = 'type';

    function build_plaintext(deck) {
        var lines = [];
        var included_packs = deck.get_included_packs();
        var agendas = deck.get_agendas();
        var sortOrder = { "name": 1 };
        var sections = {
            "Plots": deck.get_plot_deck(sortOrder),
            "Characters": deck.get_characters(sortOrder),
            "Attachments": deck.get_attachments(sortOrder),
            "Locations":  deck.get_locations(sortOrder),
            'Events': deck.get_events(sortOrder)
        };

        lines.push(deck.get_name());
        lines.push("");
        lines.push(deck.get_faction_name());
        agendas.forEach(function(agenda) {
            lines.push(agenda.name);
        });
        lines.push("");
        if (included_packs.length > 1) {
            lines.push("Packs: From " + included_packs[0].name + ' to ' + included_packs[included_packs.length - 1].name);
        } else {
            lines.push("Packs: From " + included_packs[0].name);
        }
        Object.getOwnPropertyNames(sections).forEach(function(section) {
            lines.push("");
            lines.push(section + ":");
            sections[section].forEach(function(card) {
                lines.push(card.indeck + "x " + card.name + " (" + card.pack_code + ")");
            });
        });
        return lines;
    }

    function export_text() {
        $('#export-deck').html(build_plaintext(app.deck).join("\n"));
        $('#exportModal').modal('show');
    }

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
            case 'btn-sort-type':
                DisplaySort = 'type';
                ui.refresh_deck()();
                break;
            case 'btn-sort-position':
                DisplaySort = 'position';
                ui.refresh_deck()();
                break;
            case 'btn-sort-faction':
                DisplaySort = 'faction';
                ui.refresh_deck()();
                break;
            case 'btn-sort-name':
                DisplaySort = 'name';
                ui.refresh_deck()();
                break;
            case 'btn-export-theironthrone':
                export_text();
                break;
            case 'btn-export-octgn':
                export_octgn();
                break;
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
        ui.refresh_deck();
    };

})(app.ui, jQuery);
