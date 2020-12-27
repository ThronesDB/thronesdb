(function app_data(data, $) {

    data.isLoaded = false;

    var database_changed = false;
    var locale_changed = false;

    var fdb = new ForerunnerDB();
    var database = fdb.db('thronesdb');

    data.db = database;
    var masters = {
        packs: database.collection('master_pack', {primaryKey: 'code'}),
        cards: database.collection('master_card', {primaryKey: 'code'})
    };

    var dfd;

    // Restricted/Banned Lists issued by The Conclave (v2.0)
    data.joust_restricted_list = [
    ];
    data.joust_pods = [
    ];
    data.joust_banned_list = [
        "01119", // Doran's Game (Core)
        "02034", // Crown of Gold (TRtW)
        "02065", // Halder (NMG)
        "02091", // Raider from Pyke (CoW)
        "02092", // Iron Mines (CoW)
        "02102", // Ward (TS)
        "03038", // To the Rose Banner! (WotN)
        "04001", // The Dragon's Tail (AtSK)
        "05010", // Taena Merryweather (LoCR)
        "05049", // Littlefinger's Meddling (LoCR)
        "06004", // All Men Are Fools (AMAF)
        "06011", // Drowned Disciple (AMAF)
        "06038", // Great Hall (GtR)
        "06039", // "The Dornishman's Wife" (GtR)
        "06040", // The Annals of Castle Black (GtR)
        "06063", // Oldtown Informer (TRW)
        "06098", // Flea Bottom (OR)
        "07018", // Abandoned Stronghold (WotW)
        "08019", // The Iron Bank (TAK)
        "08080", // The King in the North (FotOG)
        "08082", // I Am No One (TFM)
        "09001", // Mace Tyrell (HoT)
        "09037", // Qotho (HoT)
        "09051", // Trade Routes (HoT)
        "10017", // Dorne (SoD)
        "10045", // The Wars To Come (SoD)
        "10048", // Forced March (SoD)
        "10050", // Breaking Ties (SoD)
        "11012", // Nighttime Marauders (TSC)
        "11021", // Wyman Manderly (TMoW)
        "11033", // Hizdahr zo Loraq (TMoW)
        "11034", // Meereen (TMoW)
        "11051", // Drowned God Fanatic (SoKL)
        "11061", // Meera Reed (MoD)
        "11071", // Victarion Greyjoy (MoD)
        "11076", // A Mission in Essos (MoD)
        "11079", // The Free Folk (MoD)
        "11081", // Bear Island Scout (IDP)
        "11082", // Skagos (IDP)
        "11085", // Three-Finger Hobb (IDP)
        "11093", // Drogon (IDP)
        "11114", // Gifts for the Widow (DitD)
        "12002", // Euron Crow's Eye (KotI)
        "12029", // Desert Raider (KotI)
        "12045", // Sea of Blood (KotI)
        "12046", // We Take Westeros! (KotI)
        "12047", // Return to the Fields (KotI)
        "13044", // Unexpected Guile (PoS)
        "13079", // Kingdom of Shadows (BtRK)
        "13085", // Yoren (TB)
        "13086", // Bound for the Wall (TB)
        "13103", // The Queen's Retinue (LMHR)
        "13118", // Valyrian Steel (LMHR)
        "14008", // Selyse Baratheon (FotS)
        "15001", // Daenerys Targaryen (DotE)
        "15017", // Womb of the World (DotE)
        "15030", // The Red Keep (DotE)
        "15033", // Clydas (DotE)
        "15045", // Bribery (DotE)
        "15050", // At the Palace of Sorrows (DotE)
        "16001", // Ser Davos Seaworth (TTWDFL)
        "16002", // Melisandre's Favor (TTWDFL)
        "16003", // Wintertime Marauders (TTWDFL)
        "16004", // Conquer (TTWDFL)
        "16005", // Spider's Whisperer (TTWDFL)
        "16006", // Wheels Within Wheels (TTWDFL)
        "16007", // Prince's Loyalist (TTWDFL)
        "16008", // You Murdered Her Children (TTWDFL)
        "16009", // Samwell Tarly (TTWDFL)
        "16010", // Old Bear Mormont (TTWDFL)
        "16011", // Catelyn Stark (TTWDFL)
        "16012", // Snow Castle (TTWDFL)
        "16013", // Mad King Aerys (TTWDFL)
        "16014", // The Hatchlings' Feast (TTWDFL)
        "16015", // The Queen of Thorns (TTWDFL)
        "16016", // Olenna's Study (TTWDFL)
        "16017", // Littlefinger (TTWDFL)
        "16018", // Vale Refugee (TTWDFL)
        "16019", // High Ground (TTWDFL)
        "16020", // King's Landing (TTWDFL)
        "16021", // Harrenhal (TTWDFL)
        "16022", // Sky Cell (TTWDFL)
        "16023", // Heads on Pikes (TTWDFL)
        "16024", // Narrow Escape (TTWDFL)
        "16025", // Seductive Promise (TTWDFL)
        "16026", // Westeros Bleeds (TTWDFL)
        "16031", // Benjen's Cache (TTWDFL)
        "16032", // Rioting (TTWDFL)
        "16033", // Rule By Decree (TTWDFL)
        "16034", // Search and Detain (TTWDFL)
        "16035", // The Art of Seduction (TTWDFL)
        "16036", // The Gathering Storm (TTWDFL)
        "17147", // The Dragon's Tail (R)
    ];
    data.melee_restricted_list = [
        "01001", // A Clash of Kings (Core)
        "01013", // Heads on Spikes (Core)
        "01043", // Superior Claim (Core)
        "01078", // Great Kraken (Core)
        "01146", // Robb Stark (Core)
        "01162", // Khal Drogo (Core)
        "02012", // Rise of the Kraken (TtB)
        "02024", // Lady Sansa's Rose (TRtW)
        "02060", // The Lord of the Crossing (TKP)
        "03003", // Eddard Stark (WotN)
        "04003", // Riverrun (AtSK)
        "04118", // Relentless Assault (TC)
        "05001", // Cersei Lannister (LoCR)
        "07036", // Plaza of Pride (WotW)
        "08013", // Nagga's Ribs (TAK)
        "08014", // Daario Naharis (TAK)
        "08098", // "The Song of the Seven" (TFM)
        "08120", // You Win Or You Die (SAT)
        "09028", // Corpse Lake (HoT)
        "11039", // Trading With Qohor (TMoW)
        "11054", // Queensguard (SoKL)
        "13107", // Robert Baratheon (LMHR)
        "17114", // Doran's Game (R)
    ];
    data.melee_banned_list = [
        "01119", // Doran's Game (Core)
        "02034", // Crown of Gold (TRtW)
        "02065", // Halder (NMG)
        "02091", // Raider from Pyke (CoW)
        "02092", // Iron Mines (CoW)
        "02102", // Ward (TS)
        "03038", // To the Rose Banner! (WotN)
        "04001", // The Dragon's Tail (AtSK)
        "05010", // Taena Merryweather (LoCR)
        "05049", // Littlefinger's Meddling (LoCR)
        "06004", // All Men Are Fools (AMAF)
        "06011", // Drowned Disciple (AMAF)
        "06038", // Great Hall (GtR)
        "06039", // "The Dornishman's Wife" (GtR)
        "06040", // The Annals of Castle Black (GtR)
        "06063", // Oldtown Informer (TRW)
        "06098", // Flea Bottom (OR)
        "07018", // Abandoned Stronghold (WotW)
        "08019", // The Iron Bank (TAK)
        "08080", // The King in the North (FotOG)
        "08082", // I Am No One (TFM)
        "09001", // Mace Tyrell (HoT)
        "09037", // Qotho (HoT)
        "09051", // Trade Routes (HoT)
        "10017", // Dorne (SoD)
        "10045", // The Wars To Come (SoD)
        "10048", // Forced March (SoD)
        "10050", // Breaking Ties (SoD)
        "11012", // Nighttime Marauders (TSC)
        "11021", // Wyman Manderly (TMoW)
        "11033", // Hizdahr zo Loraq (TMoW)
        "11034", // Meereen (TMoW)
        "11051", // Drowned God Fanatic (SoKL)
        "11061", // Meera Reed (MoD)
        "11071", // Victarion Greyjoy (MoD)
        "11076", // A Mission in Essos (MoD)
        "11079", // The Free Folk (MoD)
        "11081", // Bear Island Scout (IDP)
        "11082", // Skagos (IDP)
        "11085", // Three-Finger Hobb (IDP)
        "11093", // Drogon (IDP)
        "11114", // Gifts for the Widow (DitD)
        "12002", // Euron Crow's Eye (KotI)
        "12029", // Desert Raider (KotI)
        "12045", // Sea of Blood (KotI)
        "12046", // We Take Westeros! (KotI)
        "12047", // Return to the Fields (KotI)
        "13044", // Unexpected Guile (PoS)
        "13079", // Kingdom of Shadows (BtRK)
        "13085", // Yoren (TB)
        "13086", // Bound for the Wall (TB)
        "13103", // The Queen's Retinue (LMHR)
        "13118", // Valyrian Steel (LMHR)
        "14008", // Selyse Baratheon (FotS)
        "15001", // Daenerys Targaryen (DotE)
        "15017", // Womb of the World (DotE)
        "15030", // The Red Keep (DotE)
        "15033", // Clydas (DotE)
        "15045", // Bribery (DotE)
        "15050", // At the Palace of Sorrows (DotE)
        "16001", // Ser Davos Seaworth (TTWDFL)
        "16002", // Melisandre's Favor (TTWDFL)
        "16003", // Wintertime Marauders (TTWDFL)
        "16004", // Conquer (TTWDFL)
        "16005", // Spider's Whisperer (TTWDFL)
        "16006", // Wheels Within Wheels (TTWDFL)
        "16007", // Prince's Loyalist (TTWDFL)
        "16008", // You Murdered Her Children (TTWDFL)
        "16009", // Samwell Tarly (TTWDFL)
        "16010", // Old Bear Mormont (TTWDFL)
        "16011", // Catelyn Stark (TTWDFL)
        "16012", // Snow Castle (TTWDFL)
        "16013", // Mad King Aerys (TTWDFL)
        "16014", // The Hatchlings' Feast (TTWDFL)
        "16015", // The Queen of Thorns (TTWDFL)
        "16016", // Olenna's Study (TTWDFL)
        "16017", // Littlefinger (TTWDFL)
        "16018", // Vale Refugee (TTWDFL)
        "16019", // High Ground (TTWDFL)
        "16020", // King's Landing (TTWDFL)
        "16021", // Harrenhal (TTWDFL)
        "16022", // Sky Cell (TTWDFL)
        "16023", // Heads on Pikes (TTWDFL)
        "16024", // Narrow Escape (TTWDFL)
        "16025", // Seductive Promise (TTWDFL)
        "16026", // Westeros Bleeds (TTWDFL)
        "16031", // Benjen's Cache (TTWDFL)
        "16032", // Rioting (TTWDFL)
        "16033", // Rule By Decree (TTWDFL)
        "16034", // Search and Detain (TTWDFL)
        "16035", // The Art of Seduction (TTWDFL)
        "16036", // The Gathering Storm (TTWDFL)
    ];

    data.restricted_list_title = 'Redesigns FAQ 2.1';

    function onCollectionUpdate(updated) {
        database_changed = true;
    }

    function onCollectionInsert(inserted, failed) {
        database_changed = true;
    }

    /**
     * loads the database from local
     * sets up a Promise on all data loading/updating
     * @memberOf data
     */
    function load() {
        masters.packs.load(function (err) {
            if (err) {
                console.log('error when loading packs', err);
            }
            masters.cards.load(function (err) {
                if (err) {
                    console.log('error when loading cards', err);
                }

                /*
                 * data has been fetched from local store
                 */

                /*
                 * we set up insert and update listeners now
                 * if we did it before, .load() would have called onInsert
                 */
                masters.packs.on("insert", onCollectionInsert).on("update", onCollectionUpdate);
                masters.cards.on("insert", onCollectionInsert).on("update", onCollectionUpdate);

                /*
                 * if database is not empty, use it for now
                 */
                if (masters.packs.count() > 0 && masters.cards.count() > 0) {
                    release();
                }

                /*
                 * then we ask the server if new data is available
                 */
                query();
            });
        });
    }

    /**
     * release the data for consumption by other modules
     * @memberOf data
     */
    function release() {
        data.packs = database.collection('pack', {primaryKey: 'code', changeTimestamp: false});
        data.packs.setData(masters.packs.find());

        data.cards = database.collection('card', {primaryKey: 'code', changeTimestamp: false});
        data.cards.setData(masters.cards.find());

        data.isLoaded = true;

        $(document).trigger('data.app');
    }

    /**
     * queries the server to update data
     * @memberOf data
     */
    function query() {
        dfd = {
            packs: new $.Deferred(),
            cards: new $.Deferred()
        };
        $.when(dfd.packs, dfd.cards).done(update_done).fail(update_fail);

        $.ajax({
            url: Routing.generate('api_packs'),
            success: parse_packs,
            error: function (jqXHR, textStatus, errorThrown) {
                console.log('error when requesting packs', errorThrown);
                dfd.packs.reject(false);
            }
        });

        $.ajax({
            url: Routing.generate('api_cards', {'v': '2.0'}),
            success: parse_cards,
            error: function (jqXHR, textStatus, errorThrown) {
                console.log('error when requesting cards', errorThrown);
                dfd.cards.reject(false);
            }
        });
    }

    /**
     * called if all operations (load+update) succeed (resolve)
     * deferred returns true if data has been updated
     * @memberOf data
     */
    function update_done() {
        if (database_changed && !locale_changed) {
            /*
             * we display a message informing the user that they can reload their page to use the updated data
             * except if we are on the front page, because data is not essential on the front page
             */
            if ($('.site-title').size() === 0) {
                var message = "A new version of the data is available. Click <a href=\"javascript:window.location.reload(true)\">here</a> to reload your page.";
                app.ui.insert_alert_message('warning', message);
            }
        }

        // if it is a force update, we haven't release the data yet
        if (!data.isLoaded) {
            release();
        }
    }

    /**
     * called if an operation (load+update) fails (reject)
     * deferred returns true if data has been loaded
     * @memberOf data
     */
    function update_fail(packs_loaded, cards_loaded) {
        if (packs_loaded === false || cards_loaded === false) {
            var message = Translator.trans('data_load_fail');
            app.ui.insert_alert_message('danger', message);
        } else {
            /*
             * since data hasn't been persisted, we will have to do the query next time as well
             * -- not much we can do about it
             * but since data has been loaded, we call the promise
             */
            release();
        }
    }

    /**
     * updates the database if necessary, from fetched data
     * @memberOf data
     */
    function update_collection(data, collection, locale, deferred) {
        // we update the database and Forerunner will tell us if the data is actually different
        data.forEach(function (row) {
            if(collection.findById(row.code)) {
                collection.update({code: row.code}, row);
            } else {
                collection.insert(row);
            }
        });

        // we update the locale
        if (locale !== collection.metaData().locale) {
            locale_changed = true;
        }
        collection.metaData().locale = locale;

        collection.save(function (err) {
            if (err) {
                console.log('error when saving ' + collection.name(), err);
                deferred.reject(true)
            } else {
                deferred.resolve();
            }
        });
    }

    /**
     * handles the response to the ajax query for packs data
     * @memberOf data
     */
    function parse_packs(response, textStatus, jqXHR) {
        var locale = jqXHR.getResponseHeader('Content-Language');
        update_collection(response, masters.packs, locale, dfd.packs);
    }

    /**
     * handles the response to the ajax query for the cards data
     * @memberOf data
     */
    function parse_cards(response, textStatus, jqXHR) {
        var locale = jqXHR.getResponseHeader('Content-Language');
        update_collection(response, masters.cards, locale, dfd.cards);
    }

    $(function () {
        load();
    });

})(app.data = {}, jQuery);
