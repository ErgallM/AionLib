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
            //that.options.items[post['id']] = post;

            var postDiv = new Element('div', {
                'class': 'post' + ((post['q']) ? ' q' + post['q'] : ''),
                'events': {
                    click: function(e) {
                        //that.armor.man.setItem(post);
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