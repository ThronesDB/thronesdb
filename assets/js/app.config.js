/* global app */
(function app_tip(config, $)
{
    var _config;

    function load()
    {
        var config = {};
        if (localStorage) {
            var stored = localStorage.getItem('app.config');
            if (stored) {
                config = JSON.parse(stored);
            }
        }
        return _.extend({
            'show-unusable': false,
            'show-only-deck': false,
            'hide-banned-melee': false,
            'hide-banned-joust': false,
            'display-column': 1,
            'core-set': 3,
            'buttons-behavior': 'cumulative'
        }, config || {});
    }

    function save(config)
    {
        if (localStorage) {
            localStorage.setItem('app.config', JSON.stringify(config));
        }
    }

    function getConfig()
    {
        if (! _config) {
            _config = load();
        }
        return _config;
    }

    config.set = function (name, value) {
        var config = getConfig();
        config[name] = value;
        save(config);
    };

    config.get = function ( name) {
        var config = getConfig();
        return config[name];
    }


})(app.config = {}, jQuery);
