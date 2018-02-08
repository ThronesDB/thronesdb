(function app_deck_gallery(deck_gallery, $)
{

    /**
     * @memberOf deck_gallery
     */
    deck_gallery.display = function display(container)
    {
        var table = $('<table>').appendTo(container),
                row = $('<tr>').appendTo(table),
                cards = app.deck.get_cards({'type_code': 1});

        cards.forEach(function (card)
        {
            var card_element;
            if(card.image_url) {
                card_element = '<img src="' + card.image_url + '">';
            } else {
                card_element = '<div class="card-proxy"><div>' + card.label + '</div></div>';
            }

            var cell = $('<td><div>' + card_element + '<div>' + card.indeck + '</div></div></td>');
            cell.appendTo(row);
        })
    };

})(app.deck_gallery = {}, jQuery);
