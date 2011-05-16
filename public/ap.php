<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
        "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
    <title>Aion Library</title>

    <link rel="stylesheet" href="/css/blueprint/screen.css" type="text/css" media="screen, projection">
    <link rel="stylesheet" href="/css/blueprint/print.css" type="text/css" media="print">
    <!--[if lt IE 8]>
        <link rel="stylesheet" href="/css/blueprint/ie.css" type="text/css" media="screen, projection">
    <![endif]-->

    <link rel="stylesheet" href="/css/ap.css" type="text/css" media="screen, projection">

    <script language="javascript" src="/js/mootools-core-1.3.2-full-nocompat-yc.js"></script>
    <script language="javascript" src="/js/Element.serialize.js"></script>

    <script language="javascript" src="/js/main.js"></script>
</head>
<body>
    <div class="container">
        <h2 align="center">Aion Library &mdash; Калькулятор АП</h2>

        <ul>
            <li><a href="/">На главную</a></li>
        </ul>

        <p><label>Текущее количество очков бездны <input type="text" id="now-ap" name="now-ap" value="0" /></label></p>

        <form id="items" method="post">
        <div class="span-24 last">
            <div class="span-10">
                <div class="items">
                    <img src="/images/ap/icon_1.png" />
                    Простая древняя икона
                    <span>(300 ап)</span>
                    <input type="text" name="items[icon][0]" value="0" />
                </div>
                <div class="items">
                    <img src="/images/ap/icon_2.png" />
                    Обычная древняя икона
                    <span>(600 ап)</span>
                    <input type="text" name="items[icon][1]" value="0" />
                </div>
                <div class="items">
                    <img src="/images/ap/icon_3.png" />
                    Дорогая древняя икона
                    <span>(900 ап)</span>
                    <input type="text" name="items[icon][2]" value="0" />
                </div>
                <div class="items">
                    <img src="/images/ap/icon_4.png" />
                    Бесценная древняя икона
                    <span>(1200 ап)</span>
                    <input type="text" name="items[icon][3]" value="0" />
                </div>
            </div>
            <div class="span-2">&nbsp;</div>
            <div class="span-10 last">
                <div class="items">
                    <img src="/images/ap/seal_1.png" />
                    Простая древняя печать
                    <span>(600 ап)</span>
                    <input type="text" name="items[seal][0]" value="0" />
                </div>
                <div class="items">
                    <img src="/images/ap/seal_2.png" />
                    Обычная древняя печать
                    <span>(1200 ап)</span>
                    <input type="text" name="items[seal][1]" value="0" />
                </div>
                <div class="items">
                    <img src="/images/ap/seal_3.png" />
                    Дорогая древняя печать
                    <span>(1800 ап)</span>
                    <input type="text" name="items[seal][2]" value="0" />
                </div>
                <div class="items">
                    <img src="/images/ap/seal_4.png" />
                    Бесценная древняя печать
                    <span>(2400 ап)</span>
                    <input type="text" name="items[seal][3]" value="0" />
                </div>
            </div>

            <hr class="space" />

            <div class="span-10">
                <div class="items">
                    <img src="/images/ap/cup_1.png" />
                    Простая древняя чаша
                    <span>(1200 ап)</span>
                    <input type="text" name="items[cup][0]" value="0" />
                </div>

                <div class="items">
                    <img src="/images/ap/cup_2.png" />
                    Обычная древняя чаша
                    <span>(2400 ап)</span>
                    <input type="text" name="items[cup][1]" value="0" />
                </div>
                <div class="items">
                    <img src="/images/ap/cup_3.png" />
                    Дорогая древняя чаша
                    <span>(3600 ап)</span>
                    <input type="text" name="items[cup][2]" value="0" />
                </div>
                <div class="items">
                    <img src="/images/ap/cup_4.png" />
                    Бесценная древняя чаша
                    <span>(4800 ап)</span>
                    <input type="text" name="items[cup][3]" value="0" />
                </div>
            </div>
            <div class="span-2">&nbsp;</div>
            <div class="span-10 last">
                <div class="items">
                    <img src="/images/ap/crown_1.png" />
                    Простая древняя корона
                    <span>(2400 ап)</span>
                    <input type="text" name="items[crown][0]" value="0" />
                </div>
                <div class="items">
                    <img src="/images/ap/crown_2.png" />
                    Обычная древняя корона
                    <span>(4800 ап)</span>
                    <input type="text" name="items[crown][1]" value="0" />
                </div>
                <div class="items">
                    <img src="/images/ap/crown_3.png" />
                    Дорогая древняя корона
                    <span>(7200 ап)</span>
                    <input type="text" name="items[crown][2]" value="0" />
                </div>
                <div class="items">
                    <img src="/images/ap/crown_4.png" />
                    Бесценная древняя корона
                    <span>(9600 ап)</span>
                    <input type="text" name="items[crown][3]" value="0" />
                </div>
            </div>
        </div>
        </form>

        <hr class="space" />

        <div><div class="end">Итогове количество очков бездны после обмена:</div> <span id="end">0</span></div>
    </div>

    <script language="javascript">
        window.addEvent('domready', function() {
            var a = new Ap({'formId':'items', 'resultId': 'end', 'startApId': 'now-ap'});
        })
    </script>
</body>
</html>