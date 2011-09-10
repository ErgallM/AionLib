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

var ArmorItems = new Class({
    Implements: [Options],
    options: {
        urls: {
            items: '/armor.php'
        },
        
        spy: null,
        start: 0,
        request: null,

        filterLoading: null,

        items: {},

        searchName: ''
    },
    armor: {},

    initRequest: function() {
        var that = this;

        var loading = this.options.filterLoading;

        var request = new Request.JSON({
            url: that.options.urls.items,
            method: 'get',
            link: 'cancel',
            noCache: true,
            onRequest: function() {
                loading.removeClass('hide');
            },
            onSuccess: function(responseJSON) {
                loading.addClass('hide');

                 that.options.start += 100;
                 that.displayItemList(responseJSON);

                 that.spyAct();
            },
            onFailure: function() {
                //reset the message
                loading.addClass('hide');
            },
            onComplete: function() {
                //remove the spinner
                loading.addClass('hide');
            },
            onError: function() {
                loading.addClass('hide');
            }
        });
        this.options.request = request;
    },

    spyAct: function() {
        var spyContainer = this.options.itemsList;
        var filter = this.options.filter;

        var min = spyContainer.getScrollSize().y - spyContainer.getSize().y - 300;

        this.spy = new ScrollSpy({
            container: spyContainer,
            min: min,
            onEnter: function() {
                filter.fireEvent('submit');
            }
        })
    },

    displayItemList: function(postsJSON) {
        var that = this;
        postsJSON.each(function(post, i) {
            that.options.items[post['id']] = post;
            
            var postDiv = new Element('div', {
                'class': 'post',
                'events': {
                    click: function() {
                        that.armor.man.setItem(post);
                    }
                },
                id: 'post-' + post['id'],
                html: '<img src="' + post['smallimage'] + '" /> <span>' + post['name'] + '</span>'
            });
            postDiv.inject(that.options.itemsList);
        });
    },

    initFilter: function() {
        var filter = this.options.filter;
        var that = this;
        filter.addEvent('submit', function(e) {
            var e = new Event(e);
            e.stop();
            e.stopPropagation();

            var name = this.serialize(true)['name'];
            if (name != that.options.searchName) {
                that.options.itemsList.set('html', '');
                that.options.start = 0;
                that.options.searchName = name;
            }

            that.options.request.send({
                data: {
                    'start': that.options.start,
                    'name': name
                }
            });
            return false;
        });
    },

    initialize: function (options) {
        this.setOptions(options);

        this.initRequest();
        this.initFilter();
    }
});

var Man = new Class({
    Implements: [Options],
    options: {
        man: $('man'),
        selectItem: null,
        items: {}
    },
    armor: {},

    initSelectItem: function() {
        var that = this;
        $$('.item').each(function(item) {
            if (item.hasAttribute('slot')) {
                var slot = item.get('slot');
                item.addEvent('click', function() {
                    $$('.item').removeClass('selectedItem');
                    this.addClass('selectedItem');
                    that.options.selectItem = this;
                });
            }
        });
    },

    initialize: function (options) {
        this.setOptions(options);
        this.initSelectItem();
    },

    setItem: function(item) {
        var slot = item.slot;
        var that = this;

        this.options.items[slot] = item;
        var img = new Element('img', {
            src: item['smallimage']
        });

        var div;

        //1 Вставка по выбраному полю
        div = this.options.selectItem;

        if (div) img.inject(div.empty());

    }

});

var Armor = new Class({
    Implements: [Options],
    options: {
        types: {
            itemsType: {
                '1': 'Тканые доспехи',
                '2': 'Кожаные доспехи',
                '3': 'Кольчужные доспехи',
                '4': 'Латные доспехи',
                '5': 'Щиты',
                '6': 'Головной убор',

                '7': 'Копья',
                '8': 'Двуручные мечи',
                '9': 'Мечи',
                '10':'Кинжалы',
                '11':'Булавы',
                '12':'Посохи',
                '13':'Луки',
                '14':'Орбы',
                '15':'Гримуары',

                '16':'Серьги',
                '17':'Ожерелья',
                '18':'Кольца',
                '19':'Пояса'
            },
            skills: {
                '1': 'Атака',
                '2': 'Физическая атака',
                '3': 'Маг. атака',
                '4': 'Скор. атаки',
                '5': 'Скор. магии',
                '6': 'Точность',
                '7': 'Точн. магии',
                '8': 'Ф. крит.',
                '9': 'М. крит.',
                '10':'Сила магии',
                '11':'Сила исцелен.',

                '12':'Парир.',
                '13':'Уклонение',
                '14':'Концентрац.',
                '15':'Блок урона',
                '16':'Блок щитом',
                '17':'Блок ф. крит.',
                '18':'Блок м. крит.',

                '19':'Физ. защита',
                '20':'Маг. защита',
                '21':'Защ. от земли',
                '22':'Защ. от возд.',
                '23':'Защ. от воды',
                '24':'Защ. от огня',
                '25':'Защита от ф. крит.',

                '26':'Сопротивление оглушению',
                '27':'Сопротивление опрокидыванию',
                '28':'Сопротивление отталкиванию',

                '29':'Макс. HP',
                '30':'Макс. MP',

                '31':'Скор. полета',
                '32':'Время полета',
                '33':'Скор. движ.',

                '34':'Агрессия',

                '35':'ЛВК'
            },
            slots: {
                '1': 'Голова',
                '2': 'Торс',
                '3': 'Штаны',
                '4': 'Ботинки',
                '5': 'Наплечники',
                '6': 'Перчатки',

                '7': 'Ожерелья',
                '8': 'Серьги',
                '9': 'Кольца',
                '10':'Пояс',

                '11':'Крыло',

                '12':'Главная или Вторая Рука',
                '13':'Главная Рука'
            }
        }
    },

    armorItems: null,
    man: null,

    initialize: function (options) {
        this.setOptions(options);

        if (options.items) {
            this.armorItems = new ArmorItems(options.items);
            this.armorItems.armor = this;
        }

        if (options.man) {
            this.man = new Man(options.man);
            this.man.armor = this;
        }
    }

});