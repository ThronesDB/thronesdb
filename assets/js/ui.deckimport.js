/* global app, _ */

(function ui_deckimport(ui, $)
{
    ui.on_content_change = function on_content_change(event)
    {
        var text = $('#content').val(),
                slots = {},
                faction_code,
                faction_name;

        text.match(/[^\r\n]+/g).forEach(function (token)
        {
            var qty = 1, name = token.trim(), card, packName;
            if (name.match(/^(\d+)x? ([^(]+) \(([^)]+)\)/)) {
                qty = parseInt(RegExp.$1, 10);
                name = RegExp.$2.trim();
                packName = RegExp.$3.trim();
            } else if(name.match(/^(\d+)x? (.*)/)) {
                qty = parseInt(RegExp.$1, 10);
                name = RegExp.$2.trim();
            }
            if (packName) {
                card = app.data.cards.findOne({name: name, pack_name: packName});
                if (!card) {
                    card = app.data.cards.findOne({name: name, pack_code: packName});
                }
            } else {
                card = app.data.cards.findOne({name: name});
            }
            faction = app.data.factions.findOne({name: name});
            if(card) {
                slots[card.code] = qty;
            } else if(faction) {
                faction_code = faction.code;
                faction_name = faction.name;
            } else {
                console.log('rejecting string [' + name + ']');
            }
        });

        app.deck.init({
            faction_code: faction_code,
            faction_name: faction_name,
            slots: slots
        });
        app.deck.display('#deck');
        $('input[name=content').val(app.deck.get_json());
        $('input[name=faction_code').val(faction_code);
    };

    /**
     * called when the DOM is loaded
     * @memberOf ui
     */
    ui.on_dom_loaded = function on_dom_loaded()
    {
        $('#content').change(ui.on_content_change);
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
