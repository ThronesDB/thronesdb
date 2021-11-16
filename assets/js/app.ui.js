/* global Translator, app */

(function ui_deck(ui, $)
{
    var dom_loaded = new $.Deferred(),
      data_loaded = new $.Deferred();

    function build_plaintext(deck) {
        var lines = [];
        var included_packs = deck.get_included_packs({ 'cycle_position': 1, 'position': 1 });

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
            var title = agenda.name;
            if (agenda.is_multiple) {
                title = title + " (" + agenda.pack_code + ")";
            }
            lines.push(title);
        });
        lines.push("");
        if (included_packs.length > 1) {
            lines.push("Packs: From " + included_packs[0].name + ' to ' + included_packs[included_packs.length - 1].name);
        } else {
            lines.push("Packs: From " + included_packs[0].name);
        }
        Object.getOwnPropertyNames(sections).forEach(function(section) {
            lines.push("");
            lines.push(section + " (" + deck.get_nb_cards(sections[section]) + "):");
            sections[section].forEach(function(card) {
                lines.push(card.indeck + "x " + card.name + " (" + card.pack_code + ")");
            });
        });
        return lines;
    }

    function build_markdown(deck) {
        var lines = [];
        var included_packs = deck.get_included_packs({ 'cycle_position': 1, 'position': 1 });

        var agendas = deck.get_agendas();
        var sortOrder = { "name": 1 };
        var sections = {
            "Plots": deck.get_plot_deck(sortOrder),
            "Characters": deck.get_characters(sortOrder),
            "Attachments": deck.get_attachments(sortOrder),
            "Locations":  deck.get_locations(sortOrder),
            'Events': deck.get_events(sortOrder)
        };

        var print_card_line = function(card, show_quantity) {
            var out = "";
            show_quantity = !!show_quantity;

            if (show_quantity) {
                out = out + card.indeck + 'x ';
            }
            out  = out + '[' + card.name + ' \\('+ card.pack_code +'\\)](https://thronesdb.com/card/' + card.code + ')';
            return out;
        };

        lines.push("## " + deck.get_faction_name());
        lines.push("");
        agendas.forEach(function(card) {
            lines.push(print_card_line(card, false) + "  ");
        });

        lines.push("");
        if (included_packs.length > 1) {
            lines.push("Packs: From " + included_packs[0].name + ' to ' + included_packs[included_packs.length - 1].name);
        } else {
            lines.push("Packs: From " + included_packs[0].name);
        }

        Object.getOwnPropertyNames(sections).forEach(function(section) {
            lines.push("");
            lines.push("### " + section + " (" + deck.get_nb_cards(sections[section]) + "):");
            lines.push("");
            sections[section].forEach(function(card) {
                lines.push("- " + print_card_line(card, true));
            });
        });

        return lines;
    }

    function build_agotcards(deck) {
        var lines = [];
        var included_packs = deck.get_included_packs({ 'cycle_position': 1, 'position': 1 });

        var agendas = deck.get_agendas();
        var sortOrder = { "name": 1 };
        var sections = {
            "Plots": deck.get_plot_deck(sortOrder),
            "Characters": deck.get_characters(sortOrder),
            "Attachments": deck.get_attachments(sortOrder),
            "Locations":  deck.get_locations(sortOrder),
            'Events': deck.get_events(sortOrder)
        };

        var print_card_line = function(card, show_quantity) {
            var out = "";
            show_quantity = !!show_quantity;

            if (show_quantity) {
                out = out + card.indeck + 'x ';
            }
            out  = out + '[agot]' + card.name + ' ('+ card.pack_code + ')[/agot]';
            return out;
        };

        lines.push(deck.get_faction_name());
        lines.push("");
        agendas.forEach(function(card) {
            lines.push(print_card_line(card, false) + "  ");
        });

        lines.push("");
        if (included_packs.length > 1) {
            lines.push("Packs: From " + included_packs[0].name + ' to ' + included_packs[included_packs.length - 1].name);
        } else {
            lines.push("Packs: From " + included_packs[0].name);
        }

        Object.getOwnPropertyNames(sections).forEach(function(section) {
            lines.push("");
            lines.push(section + " (" + deck.get_nb_cards(sections[section]) + "):");
            lines.push("");
            sections[section].forEach(function(card) {
                lines.push(print_card_line(card, true));
            });
        });

        return lines;
    }

    function build_theironthrone(deck) {
        var lines = [];
        var agendas = deck.get_agendas();
        var sortOrder = { "name": 1 };
        var cardsInDrawDeck = [
            deck.get_plot_deck(sortOrder),
            deck.get_characters(sortOrder),
            deck.get_attachments(sortOrder),
            deck.get_locations(sortOrder),
            deck.get_events(sortOrder)
        ];

        lines.push(deck.get_faction_name());
        agendas.forEach(function(agenda) {
            var title = agenda.name;
            if (agenda.is_multiple) {
                title = title + " (" + agenda.pack_code + ")";
            }
            lines.push(title);
        });
        cardsInDrawDeck.forEach(function(cards) {
            cards.forEach(function(card) {
                lines.push(card.indeck + "x " + card.name + " (" + card.pack_code + ")");
            });
        });
        return lines;
    }

    /**
     * called when the DOM is loaded
     * @memberOf ui
     */
    ui.on_dom_loaded = function on_dom_loaded()
    {};

    /**
     * called when the app data is loaded
     * @memberOf ui
     */
    ui.on_data_loaded = function on_data_loaded()
    {};

    /**
     * called when both the DOM and the app data have finished loading
     * @memberOf ui
     */
    ui.on_all_loaded = function on_all_loaded()
    {};

    ui.insert_alert_message = function ui_insert_alert_message(type, message)
    {
        var alert = $('<div class="alert" role="alert"></div>').addClass('alert-' + type).append(message);
        $('#wrapper>div.container').first().prepend(alert);
    };

    ui.export_plaintext = function export_plaintext(deck) {
        $('#export-deck').html(build_plaintext(deck).join("\n"));
        $('#exportModal').modal('show');
    };

    ui.export_markdown = function export_markdown(deck) {
        $('#export-deck').html(build_markdown(deck).join("\n"));
        $('#exportModal').modal('show');
    };

    ui.export_agotcards = function export_agotcards(deck) {
        $('#export-deck').html(build_agotcards(deck).join("\n"));
        $('#exportModal').modal('show');
    };

    ui.export_theironthrone = function export_theironthrone(deck) {
        $('#export-deck').html(build_theironthrone(deck).join("\n"));
        $('#exportModal').modal('show');
    };



    /**
     * builds selector component for restricted lists
     * @memberOf ui
     */
    ui.build_restrictions_selector = function build_restrictions_selector(containerId) {
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

        if (! activeRestrictions.length) {
            return;
        }

        selectedRestriction = app.data.getBestSelectedRestrictedList();
        $('<h4> ' + Translator.trans('tournamentLegality.title') +  '</h4>').appendTo($container);
        out += '<table class="tournament-legality-info">';
        activeRestrictions.forEach(function(rl) {
            out += '<tr>';
            if (1 < activeRestrictions.length) {
                out += '<td><input name="restriction" type="radio" data-rl-code="' + rl.code + '"';
                if (selectedRestriction && selectedRestriction.code === rl.code) {
                    out += ' checked="checked"';
                }
                out += '></td>';
            }

            out += '<td class="rl-title">' + rl.title + '</td>';
            out += '<td class="rl-indicator"><span data-rl-joust="' + rl.code + '"><i class="fas fa-spinner"></i></span></td>';
            out += '<td class="joust-title">' + Translator.trans('tournamentLegality.joust') + '</td>';
            out += '<td class="rl-indicator"><span data-rl-melee="' + rl.code + '"><i class="fas fa-spinner"></i></span></td>';
            out += '<td class="melee-title">' + Translator.trans('tournamentLegality.melee') + '</td>';
            out += '</tr>';
        });
        out += '</table>';
        $(out).appendTo($container);
    };

    /**
     * Refreshes the restricted list indicators for joust and melee.
     * @memberOf ui
     */
    ui.refresh_rl_indicators = function refresh_rl_indicators() {
        var refresh = function($elem, rl, isLegal) {
            $elem.empty();
            if (isLegal(rl)) {
                $elem.addClass('text-success');
                $elem.removeClass('text-danger');
                $elem.html('<i class="fas fa-check"></i>')
            } else {
                $elem.addClass('text-danger');
                $elem.removeClass('text-success');
                $elem.html('<i class="fas fa-times"></i>')
            }
        };
        $('[data-rl-joust]').each(function() {
            var $elem = $(this);
            var code = $elem.attr('data-rl-joust');
            var rl = app.data.findRestrictedList(code);
            if (!rl) {
                return;
            }
            refresh($elem, rl, app.deck.isTournamentLegalInJoust);
        });
        $('[data-rl-melee]').each(function() {
            var $elem = $(this);
            var code = $elem.attr('data-rl-melee');
            var rl = app.data.findRestrictedList(code);
            if (!rl) {
                return;
            }
            refresh($elem, rl, app.deck.isTournamentLegalInMelee);
        });
    };


    $(document).ready(function ()
    {
        $('[data-toggle="tooltip"]').tooltip();
        $('time').each(function (index, element)
        {
            var datetime = moment($(element).attr('datetime'));
            $(element).html(datetime.fromNow());
            $(element).attr('title', datetime.format('LLLL'));
        });
        if(typeof ui.on_dom_loaded === 'function')
            ui.on_dom_loaded();
        dom_loaded.resolve();
    });
    $(document).on('data.app', function ()
    {
        if(typeof ui.on_data_loaded === 'function')
            ui.on_data_loaded();
        data_loaded.resolve();
    });
    $(document).on('start.app', function () {
        var title, keyword;
        if (typeof ui.on_all_loaded === 'function') {
            ui.on_all_loaded();
        }
        $('abbr').each(function (index, element) {
            if ($(this).data('title')) {
                title = $(this).data('title');
            } else if ($(this).data('keyword')) {
                keyword = $(this).data('keyword');
                title = Translator.trans('keyword.' + keyword + '.title');
            }
            if (title) {
                $(element).attr('title', title).tooltip();
            }
        });
    });
    $.when(dom_loaded, data_loaded).done(function ()
    {
        setTimeout(function ()
        {
            $(document).trigger('start.app');
        }, 0);
    });

})(app.ui = {}, jQuery);
