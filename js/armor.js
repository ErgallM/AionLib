var ItemList = new Class({
    Implements: [Options],
    options: {
        // Куда шлем запрос
        uri: '/armor.php',

        container: null,

        itemList: null,
        filter: null,
        loader: null,

        itemsElement: null,

        // Загрузчик
        spy: {
            spy: null,
            start: 0,
            request: null,
            formValues: null
        }
    },

    items: {},


    initialize: function(options) {
        this.setOptions(options);

        if (!this.options.container) {
            throw new Error("Can't select container");
            return false;
        }

        if (!this.options.filter) {
            this.options.filter = this.options.container.getElement('form');
        }

        if (!this.options.itemList) {
            this.options.itemList = this.options.container.getElementById('findItem-list');
        }

        if (!this.options.loader) {
            this.options.loader = this.options.container.getElementById('findItem-loading');
        }

        this.initShowFilterEvent();

        var that = this;

        this.options.container.set('tween', {
            onComplete: function() {that.onShowComplate(); }
        });

        var request = new Request.JSON({
            url: that.options.uri,
            method: 'get',
            noCache: true,
            onRequest: function() {
                // показываем загрузку
                that.options.loader.removeClass('hide');
            },
            onSuccess: function(responseJSON) {
                // показываем итемы
                that.options.spy.start += 100;
                that.displayItemList(responseJSON);
                
                that.spyAct();

                that.options.loader.addClass('hide');
            },
            onFailure: function() {
                //reset the message
                that.options.loader.addClass('hide');
            },
            onComplete: function() {
                //remove the spinner
                that.options.loader.addClass('hide');
            },
            onError: function() {
                that.options.loader.addClass('hide');
            }
        });
        this.options.spy.request = request;

        this.initFilter();
    },

    spyAct: function() {
        var spyContainer = this.options.itemList;
        var filter = this.options.filter;

        var min = spyContainer.getScrollSize().y - spyContainer.getSize().y;

        this.options.spy.spy = new ScrollSpy({
            container: spyContainer,
            min: min,
            onEnter: function() {
                filter.fireEvent('submit');
            }
        });
    },

    // Показать/скрыть парель поиска
    show: function() {
        if (350 != this.options.container.getStyle('marginLeft').toInt()) {
            this.options.container.tween('marginLeft', 350);
        } else {
            this.onShowComplate();
        }
    },

    // Событие после показа формы фильтра
    onShowComplate: function() {
        if (Object.toQueryString(this.options.filter.serialize(true))) {
            this.options.filter.fireEvent('submit');
        }
    },

    // Скрыть парель поиска
    hide: function() {
        this.options.container.tween('marginLeft', 0);
    },

    // Показываем поступившие итемы в списке
    displayItemList: function(postsJSON) {
        var that = this;
        postsJSON.each(function(post, i) {
            that.items[post['id']] = new Item(post);

            var postDiv = new Element('div', {
                'class': 'post' + ((post['q']) ? ' q' + post['q'] : ''),
                'events': {
                    click: function(e) {
                        //that.armor.man.setItem(post);
                        that.items[post['id']].createHtml().inject($('item'));
                    },
                    // Правый клик (сравнение)
                    contextmenu: function() {
                        //that.armor.compare.compare(post);

                        return false;
                    }
                },
                id: 'post-' + post['id'],
                html: '<img src="' + post['smallimage'] + '" /> <span>' + post['name'] + '</span>'
            });
            postDiv.inject(that.options.itemList);
        });
    },

    // Инициализация фильтра
    initFilter: function() {
        var filter = this.options.filter;
        var that = this;

        filter.addEvent('submit', function(event) {
            var data = this.serialize(true);
            if (Object.toQueryString(data) != Object.toQueryString(that.options.spy.formValues)) {
                that.options.itemList.set('html', '');
                that.options.spy.start = 0;
                that.options.spy.formValues = data;
            }

            that.options.spy.request.send({
                'data': {
                    'start': that.options.spy.start,
                    'data': that.options.spy.formValues
                }
            });
            
            return false;
        });
    },

    initShowFilterEvent: function() {
        var that = this;
        
        if (this.options.itemsElement) {
            Array.each(this.options.itemsElement, function(item) {
                item.addEvent('click', function() {
                    that.show();
                });
            });
        }
    }
});

var Item = new Class({
    Implements: [Options],
    options: {
        id: 0,
        aion_id: 0,
        name: '',
        type: 0,
        lvl: 0,
        slot: 0,
        q: 1,
        skills: {
            main: {},
            other: {},
            stoun: {}
        },
        pvp_atack: 0,
        pvp_protect: 0,
        ap_price: {
            ap: 0,
            medal: 0,
            medal_name: ''
        },
        stoun: {
            count: 5,
            lvl: 60
        },
        magicstoun: 0,
        longatack: 0,
        complect: {},
        info: '',
        dopinfo: '',
        smallimage: '',
        image: ''
    },

    childItem: null,

    initialize: function(options) {
        this.setOptions(options);
    },

    /**
     * Создание html представления
     * @param int type 1 - all, 2 - only main, 3 - only other
     */
    createHtml: function(type) {
        var type = type || 1;
        var item = this.options;
        var container = new Element('div', {
            class: 'itemHtml'
        });

        if (1 == type || 2 == type) {
            // Название
            new Element('div', {
                class: 'title q' + item.q,
                html: item.name
            }).inject(container);

            // Тип
            new Element('div', {
                class: 'type',
                html: '<span>Тип</span> ' + item.type
            }).inject(container);

            // Инфа и лвл
            new Element('div', {
                html: ((item.info) ? '<p>' + item.info + '</p>' : '') + '<p>Можно использовать с ' + item.lvl + '-го уровня</p>'
            }).inject(container);

            // ПВП статы
            if (item.pvp_atack != 0) {
                item.skills.main['PVP atack'] = item.pvp_atack;
            }
            if (item.pvp_protect != 0) {
                item.skills.main['PVP protect'] = item.pvp_protect;
            }

            // Основные скилы
            if (Object.getLength(item.skills.main)) {
                var mainSkills = new Element('div', {
                    class: 'skills',
                    html: (item.dopinfo) ? '<div>' + item.dopinfo + '</div>' : ''
                });

                Object.each(item.skills.main, function(value, name) {
                    new Element('label', {
                        html: name + ' <span>' + value + '</span>'
                    }).inject(mainSkills);
                });
                mainSkills.inject(container);
            }
        }

        if (2 != type) {
            var dSkills = [];

            // Доп скилы
            if (Object.getLength(item.skills.other)) {
                var otherSkills = new Element('div', {
                    class: 'skills'
                });

                Object.each(item.skills.other, function(value, name) {
                    new Element('label', {
                        html: name + ' <span>' + value + '</span>'
                    }).inject(otherSkills);
                });
                dSkills.append([otherSkills]);
            }

            // Маг камни
            if (item.stoun.count && Object.getLength(item.skills.stoun)) {
                var stounSkills = new Element('div', {
                    class: 'skills'
                });

                for (var i = 1; i <= item.stoun.count; i++) {
                    if (item.skills.stoun[i]) {
                        new Element('label', {
                            html: item.skills.stoun[i].name + ' <span>' + item.skills.stoun[i].value + '</span>'
                        }).inject(stounSkills);
                    }
                }
                dSkills.append([stounSkills]);
            }

            dSkills = new Elements(dSkills);

            if (3 == type) {
                return dSkills;
            } else {
                dSkills.inject(container);
            }
        }

        // Комбинированная пуха
        if (1 == type && null !== this.childItem) {
            var childItemContainer = this.childItem.createHtml(3);
            childItemContainer.inject(container);
        }

        // ГГС
        if ((1 == type || 2 == type) && item.magicstoun) {
            new Element('div', {
                html: 'Можно вставить магический камень'
            }).inject(container);
        }

        return container;
    }
})