var Ap = new Class({
    Implements: [Options],
    options: {
        /**
         * Форма
         */
        formId: null,

        /**
         * Начальное количество АП
         */
        startApId: null,

        /**
         * Куда записывать результат
         */
        resultId: null,

        usersCoundId: null,

        /**
         * Цена итемов
         */
        itemsAp: {
            'icon'  : [300, 600, 900, 1200],
            'seal'  : [600, 1200, 1800, 2400],
            'cup'   : [1200, 2400, 3600, 4800],
            'crown' : [2400, 4800, 7200, 9600]
        },

        users: []
    },
    startOptions: {},

    initialize: function (options) {
        this.setOptions(options);
        this.startOptions = Object.clone(this.options);

        var that = this;
        $$('#' + this.options.formId + " input[type='text'], #"+ this.options.startApId + ', #' + this.options.usersCoundId).addEvents({
            'focus': function() {
                var value = Number.from($(this).get('value'));
                if (!value) $(this).set('value', '');
            },
            'blur': function() {
                var value = Number.from($(this).get('value'));
                if (!value) $(this).set('value', '0');
            },
            'keyup': function() {
                $(that.options.resultId).set('text', that.calc());
            },
            'keypress': function(e) {
                var e = new Event(e);
                if(e.code <= 31 || (e.code >= 48 && e.code <= 57) ) {
                    return true;
                } else {
                    e.stopPropagation();
                    e.stop();
                    return false;
                }
            }
        });

        /*
        $('calcPeople').addEvent('click', function(e) {
            that.calcPersone();
            e.stop();
            return false;
        });

        $('clear').addEvent('click', function(e) {
            that.clear();
            e.stop();
            return false;
        })
        */

        return this;
    },

    calc: function() {
        if (!$(this.options.formId)) throw new Error("Can't found form with id '" + this.options.formId + "'");
        
        var formItems = $(this.options.formId).serialize(true);
        var allAp = 0;
        var startAp = ($(this.options.startApId)) ? Number.from($(this.options.startApId).get('value')) : 0;

        if (undefined == formItems.items) return 0;

        formItems = formItems.items;
        var itemsAp = this.options.itemsAp;

        for (var itemType in formItems) {
            for (var itemName in formItems[itemType]) {
                if (undefined != itemsAp[itemType][itemName]) {
                    var itemCound = formItems[itemType][itemName];
                    var value = Number.from(itemsAp[itemType][itemName]);

                    allAp += (null != value) ? (itemCound * value) : 0;
                }
            }
        }

        return (allAp + startAp);
    },
    calcPersone: function() {
        var userCound = Number.from($(this.options.usersCoundId).get('value'));
        if (!userCound) throw new Error("userCound is empty");

        var items = $(this.options.formId).serialize(true);
        if (!items.items) throw new Error("items is empty");

        var itemsAp = this.options.itemsAp;


        var stek = [];
        var allPrice = 0;


        // Заполняем стек
        Object.each(items.items, function(value, key) {
            Object.each(value, function(valueItem, valueKey) {
                var itemAp = itemsAp[key][valueKey];
                for (var i = 0; i < valueItem; i++) {
                    stek = Array.append([itemAp], stek);
                    allPrice += itemAp;
                }
            });
        });

        var users = this.options.users;
        var srez = allPrice / userCound;

        for (var userId = 0; userId < userCound; userId++) {
            if (undefined == users[userId]) users[userId] = 0;

            if (users[userId] >= srez) continue;

            for (var itemId in stek) {
                if ((users[userId] + stek[itemId]) <= srez) {
                    users[userId] += stek[itemId];
                    stek[itemId] = null;
                }
                if (users[userId] >= srez) break;
            }
        }

        var getMinUser = function(users) {
            var minId = null, minValue = null;
            users.each(function(value, key) {
                if (null == minId) {
                    minId = key;
                    minValue = value;
                    return;
                }

                if (value < minValue) {
                    minId = key;
                    minValue = value;
                }
            });

            return {key: minId, value: minValue};
        }

        stek.each(function(value) {
            if (!value) return;

            var minUser = getMinUser(users);
            users[minUser.key] += value;
        });

        console.log(users);
    },
    clear: function() {
        this.setOptions(this.startOptions);
    }
});