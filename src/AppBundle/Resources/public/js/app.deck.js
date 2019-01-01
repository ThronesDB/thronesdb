/* global _, app, Translator */

(function app_deck(deck, $)
{

    var date_creation,
            date_update,
            description_md,
            id,
            name,
            tags,
            faction_code,
            faction_name,
            unsaved,
            user_id,
            problem_labels = _.reduce(
                    ['too_many_plots', 'too_few_plots', 'too_many_different_plots', 'too_many_agendas', 'too_few_cards', 'too_many_copies', 'invalid_cards', 'agenda'],
                    function (problems, key)
                    {
                        problems[key] = Translator.trans('decks.problems.' + key);
                        return problems;
                    },
                    {}),
            header_tpl = _.template('<h5><span class="icon icon-<%= code %>"></span> <%= name %> (<%= quantity %>)</h5>'),
            card_line_tpl = _.template('<span class="icon icon-<%= card.type_code %> fg-<%= card.faction_code %>"></span> <a href="<%= card.url %>" class="card card-tip" data-toggle="modal" data-remote="false" data-target="#cardModal" data-code="<%= card.code %>"><%= card.label %></a>'),
            layouts = {},
            layout_data = {};

    /*
     * Templates for the different deck layouts, see deck.get_layout_data
     */
    layouts[1] = _.template('<div class="deck-content"><%= meta %><%= plots %><%= characters %><%= attachments %><%= locations %><%= events %></div>');
    layouts[2] = _.template('<div class="deck-content"><div class="row"><div class="col-sm-6 col-print-6"><%= meta %></div><div class="col-sm-6 col-print-6"><%= plots %></div></div><div class="row"><div class="col-sm-6 col-print-6"><%= characters %></div><div class="col-sm-6 col-print-6"><%= attachments %><%= locations %><%= events %></div></div></div>');
    layouts[3] = _.template('<div class="deck-content"><div class="row"><div class="col-sm-4"><%= meta %><%= plots %></div><div class="col-sm-4"><%= characters %></div><div class="col-sm-4"><%= attachments %><%= locations %><%= events %></div></div></div>');
    layouts[4] = _.template('<div class="deck-content"><div class="row"><div class="col-sm-6 col-print-6"><%= meta %></div><div class="col-sm-6 col-print-6"><%= plots %></div></div><div class="row"><div class="col-sm-12 col-print-12"><%= cards %></div></div></div>');
    layouts[5] = _.template('<div class="deck-content"><div class="row"><div class="col-sm-12 col-print-12"><%= meta %></div></div><div class="row"><div class="col-sm-12 col-print-12"><%= cards %></div></div></div>');

    /**
     * @memberOf deck
     * @param {object} data 
     */
    deck.init = function init(data)
    {
        date_creation = data.date_creation;
        date_update = data.date_update;
        description_md = data.description_md;
        id = data.id;
        name = data.name;
        tags = data.tags;
        faction_code = data.faction_code;
        faction_name = data.faction_name;
        unsaved = data.unsaved;
        user_id = data.user_id;

        if(app.data.isLoaded) {
            deck.set_slots(data.slots);
        } else {
            console.log("deck.set_slots put on hold until data.app");
            $(document).on('data.app', function ()
            {
                deck.set_slots(data.slots);
            });
        }
    };

    /**
     * Sets the slots of the deck
     * 
     * @memberOf deck
     * @param {object} slots 
     */
    deck.set_slots = function set_slots(slots)
    {
        app.data.cards.update({}, {
            indeck: 0
        });
        for(var code in slots) {
            if(slots.hasOwnProperty(code)) {
                app.data.cards.updateById(code, {indeck: slots[code]});
            }
        }
    };

    /**
     * @memberOf deck
     * @returns string
     */
    deck.get_id = function get_id()
    {
        return id;
    };

    /**
     * @memberOf deck
     * @returns string
     */
    deck.get_name = function get_name()
    {
        return name;
    };

    /**
     * @memberOf deck
     * @returns string
     */
    deck.get_faction_code = function get_faction_code()
    {
        return faction_code;
    };

    /**
     * @returns {String}
     */
    deck.get_faction_name = function get_faction_name()
    {
        return faction_name;
    };

    /**
     * @memberOf deck
     * @returns string
     */
    deck.get_description_md = function get_description_md()
    {
        return description_md;
    };

    /**
     * @memberOf deck
     */
    deck.get_agendas = function get_agendas()
    {
        return deck.get_cards(null, {
            type_code: 'agenda'
        });
    };

    /**
     * @memberOf deck
     * @returns boolean
     */
    deck.is_alliance = function is_alliance()
    {
        return !(_.isUndefined(_.find(deck.get_agendas(), function (card)
        {
            return card.code === '06018';
        })));
    };

    /**
     * @memberOf deck
     * @param {object} sort
     * @param {object} query
     * @param {object} group
     */
    deck.get_cards = function get_cards(sort, query, group)
    {
        sort = sort || {};
        sort['code'] = 1;

        query = query || {};
        query.indeck = {
            '$gt': 0
        };

        var options = {
            '$orderBy': sort
        };
        if (group){
            options.$groupBy = group;
        }
        return app.data.cards.find(query, options);
    };

    /**
     * @memberOf deck
     * @param {object} sort 
     */
    deck.get_draw_deck = function get_draw_deck(sort)
    {
        return deck.get_cards(sort, {
            type_code: {
                '$nin': ['agenda', 'plot']
            }
        });
    };

    /**
     * @memberOf deck
     * @param {object} sort 
     */
    deck.get_draw_deck_size = function get_draw_deck_size(sort)
    {
        var draw_deck = deck.get_draw_deck();
        return deck.get_nb_cards(draw_deck);
    };

    /**
     * @memberOf deck
     * @param {object} sort 
     */
    deck.get_plot_deck = function get_plot_deck(sort)
    {
        return deck.get_cards(sort, {
            type_code: 'plot'
        });
    };

    /**
     * @memberOf deck
     * @param {object} sort
     */
    deck.get_attachments = function get_plot_deck(sort)
    {
        return deck.get_cards(sort, {
            type_code: 'attachment'
        });
    };

    /**
     * @memberOf deck
     * @param {object} sort
     */
    deck.get_characters = function get_plot_deck(sort)
    {
        return deck.get_cards(sort, {
            type_code: 'character'
        });
    };

    /**
     * @memberOf deck
     * @param {object} sort
     */
    deck.get_events = function get_plot_deck(sort)
    {
        return deck.get_cards(sort, {
            type_code: 'event'
        });
    };

    /**
     * @memberOf deck
     * @param {object} sort
     */
    deck.get_locations = function get_plot_deck(sort)
    {
        return deck.get_cards(sort, {
            type_code: 'location'
        });
    };

    /**
     * @memberOf deck
     * @returns the number of plot cards
     * @param {object} sort 
     */
    deck.get_plot_deck_size = function get_plot_deck_size(sort)
    {
        var plot_deck = deck.get_plot_deck();
        return deck.get_nb_cards(plot_deck);
    };

    /**
     * @memberOf deck
     * @returns the number of different plot cards
     * @param {object} sort 
     */
    deck.get_plot_deck_variety = function get_plot_deck_variety(sort)
    {
        var plot_deck = deck.get_plot_deck();
        return plot_deck.length;
    };

    deck.get_nb_cards = function get_nb_cards(cards)
    {
        if(!cards)
            cards = deck.get_cards();
        var quantities = _.pluck(cards, 'indeck');
        return _.reduce(quantities, function (memo, num)
        {
            return memo + num;
        }, 0);
    };

    /**
     * @memberOf deck
     * @param {Object} sort
     * @return {Array}
     */
    deck.get_included_packs = function get_included_packs(sort)
    {
        var cards = deck.get_cards();
        var nb_packs = {};
        sort = sort || { 'available': 1 };
        cards.forEach(function (card)
        {
            nb_packs[card.pack_code] = Math.max(nb_packs[card.pack_code] || 0, card.indeck / card.quantity);
        });
        var pack_codes = _.uniq(_.pluck(cards, 'pack_code'));
        var packs = app.data.packs.find({
            'code': {
                '$in': pack_codes
            }
        }, {
            '$orderBy': sort
        });
        packs.forEach(function (pack)
        {
            pack.quantity = nb_packs[pack.code] || 0;
        });
        return packs;
    };

    deck.change_sort = function(sort_type) {
        if (localStorage) {
            localStorage.setItem('ui.deck.sort', sort_type);
        }
        deck.sort_type = sort_type;
        if ($("#deck")) {
            deck.display('#deck');
        }

        if ($("#deck-content")) {
            deck.display('#deck-content');
        }

        if ($("#decklist")) {
            deck.display('#decklist');
        }
    };

        /**
     * @memberOf deck
     * @param {object} container 
     * @param {object} options
     */
    deck.display = function display(container, options)
    {
        options = _.extend({sort: 'type', cols: 2}, options);

        var deck_content = deck.get_layout_data(options);

        $(container)
          .removeClass('deck-loading')
          .empty();

        $(container).append(deck_content);
    };

    deck.get_layout_data = function get_layout_data(options)
    {

        var data = {
            images: '',
            meta: '',
            plots: '',
            characters: '',
            attachments: '',
            locations: '',
            events: '',
            cards: ''
        };

        var problem = deck.get_problem();
        var agendas = deck.get_agendas();
        
        deck.update_layout_section(data, 'images', $('<div style="margin-bottom:10px"><img src="/bundles/app/images/factions/' + deck.get_faction_code() + '.png" class="img-responsive">'));
        agendas.forEach(function (agenda) {
            deck.update_layout_section(data, 'images', $('<div><img src="' + agenda.image_url + '" class="img-responsive">'));
        });

        deck.update_layout_section(data, 'meta', $('<h4 style="font-weight:bold">' + faction_name + '</h4>'));
        agendas.forEach(function (agenda) {
            var agenda_line = $('<h5>').append($(card_line_tpl({card: agenda})));
            agenda_line.find('.icon').remove();
            deck.update_layout_section(data, 'meta', agenda_line);
        });
        var drawDeckSection = $('<div>' + Translator.transChoice('decks.edit.meta.drawdeck', deck.get_draw_deck_size(), {count: deck.get_draw_deck_size()}) + '</div>');
        drawDeckSection.addClass(problem && problem.indexOf('cards') !== -1 ? 'text-danger' : '');
        deck.update_layout_section(data, 'meta', drawDeckSection);

        var plotDeckSection = $('<div>' + Translator.transChoice('decks.edit.meta.plotdeck', deck.get_plot_deck_size(), {count: deck.get_plot_deck_size()}) + '</div>');
        plotDeckSection.addClass(problem && problem.indexOf('plots') !== -1 ? 'text-danger' : '');
        deck.update_layout_section(data, 'meta', plotDeckSection);

        var packs = _.map(deck.get_included_packs({ 'cycle_position': 1, 'position': 1 }), function (pack) {
            return pack.name + (pack.quantity > 1 ? ' (' + pack.quantity + ')' : '');
        }).join(', ');
        deck.update_layout_section(data, 'meta', $('<div>' + Translator.trans('decks.edit.meta.packs', {"packs": packs}) + '</div>'));

        if(problem) {
            deck.update_layout_section(data, 'meta', $('<div class="text-danger small"><span class="fa fa-exclamation-triangle"></span> ' + problem_labels[problem] + '</div>'));
        }

        var layout_template = 2;

        switch (deck.sort_type) {
            case "faction":
                deck.update_layout_section(data, "cards", deck.get_layout_section({'faction_name': 1, 'name': 1}, {'faction_name': 1}, null));
                layout_template = 5;
                break;
            case "factionnumber":
                deck.update_layout_section(data, "cards", deck.get_layout_section({'faction_name': 1, 'code': 1}, {'faction_name': 1}, null, "number"));
                layout_template = 5;
                break;
            case "name":
                deck.update_layout_section(data, "cards", $('<br>'));
                deck.update_layout_section(data, "cards", deck.get_layout_section({'name': 1},  null, null, "number"));
                layout_template = 5;
                break;
            case "set":
                deck.update_layout_section(data, "cards", deck.get_layout_section_for_cards_sorted_by_set(true));
                layout_template = 5;
                break;
            case "setnumber":
                deck.update_layout_section(data, "cards", deck.get_layout_section_for_cards_sorted_by_set(false));
                layout_template = 5;
                break;
            case "cardnumber":
                deck.update_layout_section(data, "cards", $('<br>'));
                deck.update_layout_section(data, "cards", deck.get_layout_section({'code': 1},  null, null, "number"));
                layout_template = 5;
                break;
            case "cost":
                deck.update_layout_section(data, 'plots', deck.get_layout_data_one_section('type_code', 'plot', 'type_name'));
                deck.update_layout_section(data, "cards", deck.get_layout_section({'cost': 1, 'name': 1}, {'cost': 1}, { type_code: { '$nin': ['agenda', 'plot'] }}));
                layout_template = 4;
                break;
            case "type":
            default:
                deck.update_layout_section(data, 'plots', deck.get_layout_data_one_section('type_code', 'plot', 'type_name'));
                deck.update_layout_section(data, 'characters', deck.get_layout_data_one_section('type_code', 'character', 'type_name'));
                deck.update_layout_section(data, 'attachments', deck.get_layout_data_one_section('type_code', 'attachment', 'type_name'));
                deck.update_layout_section(data, 'locations', deck.get_layout_data_one_section('type_code', 'location', 'type_name'));
                deck.update_layout_section(data, 'events', deck.get_layout_data_one_section('type_code', 'event', 'type_name'));
        }

        if (options && options.layout) {
            layout_template = options.layout;
        }

        return layouts[layout_template](data);
    };

    deck.update_layout_section = function update_layout_section(data, section, element)
    {
        data[section] = data[section] + element[0].outerHTML;
    };

    deck.get_layout_section = function(sort, group, query, context){
        var cards;
        var section = $('<div>');
        cards = deck.get_cards(sort, query, group);
        if(cards.length) {
            deck.create_card_group(cards, context).appendTo(section);

        } else if (cards.constructor !== Array){
            $.each(cards, function (index, group_cards) {
                if (group_cards.constructor === Array){
                    $(header_tpl({code: index, name: index === "undefined" ? "Null" : index, quantity: group_cards.reduce(function(a,b){ return a + b.indeck}, 0) })).appendTo(section);
                    deck.create_card_group(group_cards, context).appendTo(section);
                }
            });
        }
        return section;
    };

    /**
     * @param {boolean} sortByName set to TRUE for sorting by name within sets, or FALSE to sort by card number.
     */
    deck.get_layout_section_for_cards_sorted_by_set = function(sortByName) {
        sortByName = !!sortByName;

        var section = $('<div>');
        var context = sortByName ? "" : "number";
        var sort = sortByName ? {"name": 1} : {"code": 1};
        var packs = deck.get_included_packs({"cycle_position": 1, "position": 1});
        var cards = deck.get_cards(sort, {}, {"pack_name": 1});

        packs.forEach(function(pack){
            $(header_tpl({code: pack.code, name: pack.name, quantity: cards[pack.name].reduce(function(a,b){ return a + b.indeck}, 0) })).appendTo(section);
            deck.create_card_group(cards[pack.name], context).appendTo(section);
        });
        return section;
    };

    deck.create_card_group = function(cards, context){
        var section = $('<div>');
        cards.forEach(function (card) {
            var $div = $('<div>').addClass(deck.can_include_card(card) ? '' : 'invalid-card');

            $div.append($(card_line_tpl({card:card})));
            $div.prepend(card.indeck+'x ');
            if (context && context === "number"){
                $div.append(" | "+card.pack_name+" #"+card.position);
            }

            $div.appendTo(section);
        });
        return section;
    };

    deck.get_layout_data_one_section = function get_layout_data_one_section(sortKey, sortValue, displayLabel)
    {
        var section = $('<div>');
        var query = {};
        query[sortKey] = sortValue;
        var cards = deck.get_cards({name: 1}, query);
        if(cards.length) {
            $(header_tpl({code: sortValue, name: cards[0][displayLabel], quantity: deck.get_nb_cards(cards)})).appendTo(section);
            cards.forEach(function (card)
            {
                var $div = $('<div>').addClass(deck.can_include_card(card) ? '' : 'invalid-card');
                $div.append($(card_line_tpl({card: card})));
                $div.prepend(card.indeck + 'x ');
                $div.appendTo(section);
            });
        }
        return section;
    };

    /**
     * @memberOf deck
     * @return boolean true if at least one other card quantity was updated
     */
    deck.set_card_copies = function set_card_copies(card_code, nb_copies)
    {
        var card = app.data.cards.findById(card_code);
        if(!card)
            return false;

        var updated_other_card = false;
        if(nb_copies > 0) {
            // card-specific rules
            switch(card.type_code) {
                case 'agenda':
                    // is deck alliance before the change
                    var is_alliance = deck.is_alliance();
                    // is deck alliance with the new card
                    if(card.traits.indexOf(Translator.trans('card.traits.banner')) === -1) {
                        is_alliance = false;
                    } else {
                        var nb_banners = deck.get_nb_cards(deck.get_cards(null, {type_code: 'agenda', traits: new RegExp(Translator.trans('card.traits.banner') + '\\.')}));
                        if(nb_banners >= 2)
                            is_alliance = false;
                    }
                    if(card.code === '06018')
                        is_alliance = true;
                    if(is_alliance) {
                        deck.get_agendas().forEach(function (agenda)
                        {
                            if(agenda.code !== '06018' && agenda.traits.indexOf(Translator.trans('card.traits.banner')) === -1) {
                                app.data.cards.update({
                                    code: agenda.code
                                }, {
                                    indeck: 0
                                });
                                updated_other_card = true;
                            }
                        });
                    } else {
                        app.data.cards.update({
                            type_code: 'agenda'
                        }, {
                            indeck: 0
                        });
                        updated_other_card = true;
                    }
                    break;
            }
        }
        app.data.cards.updateById(card_code, {
            indeck: nb_copies
        });
        app.deck_history && app.deck_history.notify_change();

        return updated_other_card;
    };

    /**
     * @memberOf deck
     */
    deck.get_content = function get_content()
    {
        var cards = deck.get_cards();
        var content = {};
        cards.forEach(function (card)
        {
            content[card.code] = card.indeck;
        });
        return content;
    };

    /**
     * @memberOf deck
     */
    deck.get_json = function get_json()
    {
        return JSON.stringify(deck.get_content());
    };

    /**
     * @memberOf deck
     */
    deck.get_export = function get_export(format)
    {

    };

    /**
     * @memberOf deck
     */
    deck.get_copies_and_deck_limit = function get_copies_and_deck_limit()
    {
        var copies_and_deck_limit = {};
        deck.get_draw_deck().forEach(function (card)
        {
            var value = copies_and_deck_limit[card.name];
            if(!value) {
                copies_and_deck_limit[card.name] = {
                    nb_copies: card.indeck,
                    deck_limit: card.deck_limit
                };
            } else {
                value.nb_copies += card.indeck;
                value.deck_limit = Math.min(card.deck_limit, value.deck_limit);
            }
        })
        return copies_and_deck_limit;
    };

    /**
     * @memberOf deck
     */
    deck.get_problem = function get_problem()
    {
        var agendas = deck.get_agendas();
        var expectedPlotDeckSize = 7;
        var expectedMaxDoublePlot = 1;
        var expectedMaxAgendaCount = 1;
        var expectedMinCardCount = 60;
        agendas.forEach(function (agenda) {
            if(agenda && agenda.code === '05045') {
                expectedPlotDeckSize = 12;
            } else if(agenda && agenda.code === '10045') {
                expectedPlotDeckSize = 10;
                expectedMaxDoublePlot = 2;
            }
        });
        // exactly 7 plots
        if(deck.get_plot_deck_size() > expectedPlotDeckSize) {
            return 'too_many_plots';
        }
        if(deck.get_plot_deck_size() < expectedPlotDeckSize) {
            return 'too_few_plots';
        }

        var expectedPlotDeckSpread = expectedPlotDeckSize - expectedMaxDoublePlot;
        // at least 6 different plots
        if(deck.get_plot_deck_variety() < expectedPlotDeckSpread) {
            return 'too_many_different_plots';
        }

        // no more than 1 agenda, unless Alliance
        if(deck.is_alliance()) {
            expectedMaxAgendaCount = 3;
            expectedMinCardCount = 75;
            var unwanted = _.find(deck.get_agendas(), function (agenda)
            {
                return agenda.code !== '06018' && agenda.traits.indexOf(Translator.trans('card.traits.banner')) === -1;
            });
            if(unwanted) {
                return 'too_many_agendas';
            }
        }
        
        // no more than 1 agenda
        if(deck.get_nb_cards(deck.get_agendas()) > expectedMaxAgendaCount) {
            return 'too_many_agendas';
        }

        // at least 60 others cards
        if(deck.get_draw_deck_size() < expectedMinCardCount) {
            return 'too_few_cards';
        }

        // too many copies of one card
        if(!_.isUndefined(_.findKey(deck.get_copies_and_deck_limit(), function (value)
        {
            return value.nb_copies > value.deck_limit;
        }))) {
            return 'too_many_copies';
        }            

        // no invalid card
        if(deck.get_invalid_cards().length > 0) {
            return 'invalid_cards';
        }

        // the condition(s) of the agendas must be fulfilled
        var agendas = deck.get_agendas();
        for(var i=0; i<agendas.length; i++) {
            if(!deck.validate_agenda(agendas[i])) {
                return 'agenda';
            }
        }
    };

    deck.validate_agenda = function validate_agenda(agenda)
    {
        switch(agenda.code) {
            case '01027':
                if(deck.get_nb_cards(deck.get_cards(null, {type_code: {$in: ['character', 'attachment', 'location', 'event']}, faction_code: 'neutral'})) > 15) {
                    return false;
                }
                break;
            case '01198':
            case '01199':
            case '01200':
            case '01201':
            case '01202':
            case '01203':
            case '01204':
            case '01205':
                var minor_faction_code = deck.get_minor_faction_code(agenda);
                if(deck.get_nb_cards(deck.get_cards(null, {type_code: {$in: ['character', 'attachment', 'location', 'event']}, faction_code: minor_faction_code})) < 12) {
                    return false;
                }
                break;
            case '04037':
                if(deck.get_nb_cards(deck.get_cards(null, {type_code: 'plot', traits: new RegExp(Translator.trans('card.traits.winter') + '\\.')})) > 0) {
                    return false;
                }
                break;
            case '04038':
                if(deck.get_nb_cards(deck.get_cards(null, {type_code: 'plot', traits: new RegExp(Translator.trans('card.traits.summer') + '\\.')})) > 0) {
                    return false;
                }
                break;
            case '05045':
                var schemeCards = deck.get_cards(null, {type_code: 'plot', traits: new RegExp(Translator.trans('card.traits.scheme') + '\\.')});
                var totalSchemes = deck.get_nb_cards(schemeCards);
                var uniqueSchemes = schemeCards.length;
                if(totalSchemes !== 5 || uniqueSchemes !== 5) {
                    return false;
                }
                break;
            case '06018':
                var agendas = deck.get_nb_cards(deck.get_cards(null, {type_code: 'agenda'}));
                var banners = deck.get_nb_cards(deck.get_cards(null, {type_code: 'agenda', traits: new RegExp(Translator.trans('card.traits.banner') + '\\.')}));
                if(agendas - banners !== 1) {
                    return false;
                }
                break;
            case '06119':
                var loyalCharacters = deck.get_nb_cards(deck.get_cards(null, {type_code: 'character', is_loyal: true}));
                if(loyalCharacters > 0) {
                    return false;
                }
                break;
            case '09045':
                var maesters = deck.get_nb_cards(deck.get_cards(null, {type_code: 'character', traits: new RegExp(Translator.trans('card.traits.maester') + '\\.')}));
                if(maesters < 12) {
                    return false;
                }
                break;
            case '11079':
                var nonNeutralCards = deck.get_nb_cards(deck.get_cards(null, {faction_code: { $ne: 'neutral' }}));
                if(nonNeutralCards > 0) {
                    return false;
                }
                break;
        }
        return true;
    };

    /**
     * @memberOf deck
     * @returns {array}
     */
    deck.get_minor_faction_codes = function get_minor_faction_codes()
    {
        return deck.get_agendas().map(function (agenda)
        {
            return deck.get_minor_faction_code(agenda);
        });
    };

    /**
     * @memberOf deck
     * @param {object} agenda
     * @returns {string}
     */
    deck.get_minor_faction_code = function get_minor_faction_code(agenda)
    {
        // special case for the Core Set Banners
        var banners_core_set = {
            '01198': 'baratheon',
            '01199': 'greyjoy',
            '01200': 'lannister',
            '01201': 'martell',
            '01202': 'thenightswatch',
            '01203': 'stark',
            '01204': 'targaryen',
            '01205': 'tyrell'
        };

        return banners_core_set[agenda.code];
    };

    deck.get_invalid_cards = function get_invalid_cards()
    {
        return _.filter(deck.get_cards(), function (card)
        {
            return !deck.can_include_card(card);
        });
    };

    /**
     * returns true if the deck can include the card as parameter
     * @memberOf deck
     */
    deck.can_include_card = function can_include_card(card)
    {
        // neutral card => yes
        if(card.faction_code === 'neutral')
            return true;

        // in-house card => yes
        if(card.faction_code === faction_code)
            return true;

        // out-of-house and loyal => no
        if(card.is_loyal)
            return false;

        // agenda => yes
        var agendas = deck.get_agendas();
        for(var i = 0; i < agendas.length; i++) {
            if(deck.card_allowed_by_agenda(agendas[i], card)) {
                return true;
            }
        }

        // if none above => no
        return false;
    };

    /**
     * returns true if the agenda for the deck allows the passed card
     * @memberOfdeck
     */
    deck.card_allowed_by_agenda = function card_allowed_by_agenda(agenda, card) {
        switch(agenda.code) {
            case '01198':
            case '01199':
            case '01200':
            case '01201':
            case '01202':
            case '01203':
            case '01204':
            case '01205':
                return card.faction_code === deck.get_minor_faction_code(agenda);
            case '09045':
                return card.type_code === 'character' && card.traits.indexOf(Translator.trans('card.traits.maester')) !== -1;
        }
    }

})(app.deck = {}, jQuery);
