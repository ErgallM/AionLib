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

        'buttons': {
            'calcPeople': null
        },

        /**
         * Цена итемов
         */
        itemsAp: {
            'icon'  : [300, 600, 900, 1200],
            'seal'  : [600, 1200, 1800, 2400],
            'cup'   : [1200, 2400, 3600, 4800],
            'crown' : [2400, 4800, 7200, 9600]
        },

        /**
         * Название итемов
         */
        itemsName: {
            'icon'  : {
                '300'   : 'Простая древняя иконка',
                '600'   : 'Обычная древняя икона',
                '900'   : 'Дорогая древняя икона',
                '1200'  : 'Бесценная древняя икона'
            },
            'seal'  : {
                '600'   : 'Простая древняя печать',
                '1200'  : 'Обычная древняя печать',
                '1800'  : 'Дорогая древняя печать',
                '2400'  : 'Бесценная древняя печать'
            },
            'cup'   : {
                '1200'  : 'Простая древняя чаша',
                '2400'  : 'Обычная древняя чаша',
                '3600'  : 'Дорогая древняя чаша',
                '4800'  : 'Бесценная древняя чаша'
            },
            'crown' : {
                '2400'  : 'Простая древняя корона',
                '4800'  : 'Обычная древняя корона',
                '7200'  : 'Дорогая древняя корона',
                '9600'  : 'Бесценная древняя корона'
            }
        },

        users: [],
        usersItems: [],
        usersGroupLegend: null,
        iterationN: 1,

        groupNowApId: null,

        shablon: {
            iterationBlock: '<div><a href="#iteration-{iterationN}" name="iteration-{iterationN}" onclick="$(\'iteration-{iterationN}\').toggleClass(\'hide\');">Распределение #{iterationN}</a>' +
                    '<div id="iteration-{iterationN}" class="iteration-conteiner">{block}</div>' +
                    '</div>',

            block:  '<div class="group-block"><div class="group-block-title left">{userId}<span>{userAp}</span><small>+{userAddAp}</small></div>' +
                    '<div class="group-block-items right">{items}</div><div class="clear"></div>' +
                    '</div><hr class="space" />',

            items:  '<div class="group-block-item"><img src="/images/ap/{itemName}_{itemN}.png" />{itemFullName} ' +
                    '<span>{itemAp} APs</span><div class="clear"></div>' +
                    '</div>',

            groupNowAp: '<div>{userId} &mdash; {userAp} Aps</div>'

        }
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

        if (this.options.buttons.calcPeople) {
            this.options.buttons.calcPeople.addEvent('click', function(e) {
                that.calcPersone();
                e.stop();
                return false;
            });
        }

        $('clear').addEvent('click', function(e) {
            that.clear();
            e.stop();
            return false;
        })

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
                    stek = Array.append([{
                        ap: itemAp,
                        name: key,
                        n: valueKey
                    }], stek);
                    allPrice += itemAp;
                }
            });
        });

        var users = this.options.users;
        var srez = allPrice / userCound;

        var usersItems = Array.clone(this.options.usersItems);

        for (var userId = 0; userId < userCound; userId++) {
            if (undefined == users[userId]) users[userId] = 0;
            if (undefined == usersItems[userId]) usersItems[userId] = [];

            if (users[userId] >= srez) continue;

            for (var itemId in stek) {
                if (stek[itemId]) {
                    if ((users[userId] + stek[itemId].ap) <= srez) {
                        users[userId] += stek[itemId].ap;

                        usersItems[userId].append([stek[itemId]]);

                        //console.log(stek[itemId], itemId);
                        stek[itemId] = null;
                    }
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
            if (value) {
                var minUser = getMinUser(users);
                users[minUser.key] += value.ap;
                if (undefined == usersItems[minUser.key]) usersItems[minUser.key] = [];
                usersItems[minUser.key].append([value]);
            }
        });

        //console.log(users, usersItems);
        this.addPerconeResultLine(users, usersItems);
    },
    clear: function() {
        this.setOptions(this.startOptions);

        $(this.options.resultId).set('text', '0');
        $$('input[type=text]').set('value', '0');
        $(this.options.usersGroupLegend).set('html', '');
        $(this.options.groupNowApId).set('html', '');
    },
    clearToNext: function() {
        $(this.options.resultId).set('text', '0');
        $$('input[type=text]').set('value', '0');
    },
    addPerconeResultLine: function(users, usersItems) {
        var that = this;

        if (!that.options.usersGroupLegend) throw new Error('usersGroupLegend is null');

        var blockHtml = '';
        
        users.each(function(userAp, userId) {
            var itemsHtml = '';
            var userAddAp = 0;

            usersItems[userId].each(function(value) {
                var item = String.from(that.options.shablon.items);
                item = item.substitute({
                    'itemName': value.name,
                    'itemN': value.n,
                    'itemAp': value.ap,
                    'itemFullName': (that.options.itemsName[value.name][value.ap]) ? that.options.itemsName[value.name][value.ap] : 'Ошибочное имя'
                });

                userAddAp += value.ap;
                itemsHtml += item;
            });

            var block = String.from(that.options.shablon.block);

            block = block.substitute({
                'userId': userId + 1,
                'userAp': userAp,
                'userAddAp': userAddAp,
                'iterationN': that.options.iterationN,
                'items': itemsHtml
            });

            blockHtml += block;
        });

        var iterationHtml = String.from(that.options.shablon.iterationBlock);
            iterationHtml = iterationHtml.substitute({
                'iterationN': that.options.iterationN,
                'block': blockHtml
            });

        that.options.iterationN++;

        $$('.iteration-conteiner').addClass('hide');

        var el = $(that.options.usersGroupLegend);
            el.set('html', el.get('html') + iterationHtml);

        this.clearToNext();
        this.showUsersNowAp();
    },
    showUsersNowAp: function() {
        var users = this.options.users;
        var that = this;

        var html = '';
        var allAp = 0;
        users.each(function(userAp, userId) {
            html += String.from(that.options.shablon.groupNowAp).substitute({
                'userId': (userId + 1),
                'userAp': userAp
            });
            allAp += userAp;
        });

        html += '<span style="border-top: 1px solid #000;">Всего AP: ' + allAp + '</span>';

        $(that.options.groupNowApId).set('html', html);
    }
});