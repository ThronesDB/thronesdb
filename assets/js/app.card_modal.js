(function app_card_modal(card_modal, $)
{

    var modal = null;

    /**
     * @memberOf card_modal
     */
    card_modal.display_modal = function display_modal(event, element)
    {
        event.preventDefault();
        $(element).qtip('destroy', true);
        fill_modal($(element).data('code'));
    };

    /**
     * @memberOf card_modal
     */
    card_modal.typeahead = function typeahead(event, card)
    {
        fill_modal(card.code);
        $('#cardModal').modal('show');
    };

    function fill_modal(code)
    {
        var card = app.data.cards.findById(code),
                modal = $('#cardModal');
        var info = '<div class="card-faction">' + app.format.faction(card) + '</div>'
          + '<div class="card-info">' + app.format.info(card) + '</div>'
          + '<div class="card-traits">' + app.format.traits(card) + '</div>'
          + '<div class="card-text border-' + card.faction_code + '">' + app.format.text(card) + '</div>';

        if (card.errataed) {
            info += '<div class="card-errata-short">' +  Translator.trans('card.info.errataed_short')  + '</div>';
        }

        info += '<div class="card-pack">' + app.format.pack(card) + '</div>';
        if (card.rarity_code) {
            info += '<div class="card-code">' + Translator.trans('card.info.rarity') + ': ' + Translator.trans('rarity.' + card.rarity_code) + '</div>'
        }

        if (card.work_in_progress) {
            info = '<div class="alert alert-danger">' +  Translator.trans('card.info.workInProgress')  + '</div>' + info;
        }
        if(!card)
            return;

        modal.data('code', code);
        modal.find('.card-modal-link').attr('href', card.url);
        modal.find('h3.modal-title').html(app.format.name(card));
        modal.find('.modal-image').html('<img class="img-responsive" src="' + card.image_url + '">');
        modal.find('.modal-info').html(info);

        var qtyelt = modal.find('.modal-qty');
        if(qtyelt) {

            var qtyHtml = '';
            if(card.maxqty > 3) {
                qtyHtml = '<select class="qty-select form-control">';
                for(var i = 0; i <= card.maxqty; i++) {
                    qtyHtml += '<option value="' + i + '"' + (i === card.indeck ? ' selected' : '') + '>' + i + '</option>';
                }
                qtyHtml += '</select>';
            } else {
                for(var i = 0; i <= card.maxqty; i++) {
                    qtyHtml += '<label class="btn btn-default"><input type="radio" name="qty" value="' + i + '">' + i + '</label>';
                }
            }
            qtyelt.html(qtyHtml);

            qtyelt.find('label').each(function (index, element)
            {
                if(index == card.indeck)
                    $(element).addClass('active');
                else
                    $(element).removeClass('active');
            });

        } else {
            if(qtyelt)
                qtyelt.closest('.row').remove();
        }
    }

    $(function ()
    {

        $('body').on({click: function (event)
            {
                var element = $(this);
                if(event.shiftKey || event.altKey || event.ctrlKey || event.metaKey) {
                    event.stopPropagation();
                    return;
                }
                card_modal.display_modal(event, element);
            }}, '.card');

    })

})(app.card_modal = {}, jQuery);
