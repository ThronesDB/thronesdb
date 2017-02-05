(function app_data(data, $) {

    data.isLoaded = false;

    var force_update = false;
    var force_refresh = false;
    var database_changed = false;
    var locale_changed = false;
    var fdb = new ForerunnerDB();
    var database = fdb.db('thronesdb');

    var masters;

    var dfd = {
        packs: new $.Deferred(),
        cards: new $.Deferred()
    };
    $.when(dfd.packs, dfd.cards).done(update_done).fail(update_fail);

    /**
     * loads the database from local
     * sets up a Promise on all data loading/updating
     * @memberOf data
     */
    function load() {

        masters = {
            packs: database.collection('master_pack', {primaryKey: 'code', changeTimestamp: true}),
            cards: database.collection('master_card', {primaryKey: 'code', changeTimestamp: true})
        };

        masters.packs.load(function (err) {
            if (err) {
                console.log('error when loading packs', err);
                force_update = true;
            }
            masters.cards.load(function (err) {
                if (err) {
                    console.log('error when loading cards', err);
                    force_update = true;
                }

                /*
                 * data has been fetched from local store
                 */

                /*
                 * if database is older than 10 days, we assume it's obsolete and delete it
                 */
                var age_of_database = new Date() - new Date(masters.cards.metaData().lastChange);
                if (age_of_database > 864000000) {
                    console.log('database is older than 10 days => refresh it');
                    masters.packs.setData([]);
                    masters.cards.setData([]);
                }

                /*
                 * if database is empty, we will wait for the new data
                 */
                if (masters.packs.count() === 0 || masters.cards.count() === 0) {
                    console.log('database is empty => load it');
                    force_update = true;
                }

                /*
                 * triggering event that data is loaded
                 */
                if (!force_update) {
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
     * triggers a forced update of the database
     * @memberOf data
     */
    function update() {
        _.each(masters, function (collection) {
            collection.drop();
        });
        force_refresh = true;
        load();
    }

    /**
     * queries the server to update data
     * @memberOf data
     */
    function query() {
        $.ajax({
            url: Routing.generate('api_packs'),
            cache: !force_refresh,
            success: parse_packs,
            error: function (jqXHR, textStatus, errorThrown) {
                console.log('error when requesting packs', errorThrown);
                dfd.packs.reject(false);
            }
        });

        $.ajax({
            url: Routing.generate('api_cards'),
            cache: !force_refresh,
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
        if (force_update) {
            release();
            return;
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
        collection.on("setData", function (newData, oldData) {
            if (JSON.stringify(newData) !== JSON.stringify(oldData)) {
                console.log("data has changed for " + collection.name());
                database_changed = true;
            }
        }).setData(data);

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

    data.update = update;

})(app.data = {}, jQuery);
