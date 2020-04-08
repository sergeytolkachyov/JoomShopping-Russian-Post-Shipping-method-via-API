<?php 
/**
* @version      1.4.0 08.04.2020
* @author       Sergey Tolkachyov
* @copyright    Copyright (C) 2019 Sergey Tolkachyov. All rights reserved.
* @license      GNU/GPL
*/
defined('_JEXEC') or die('Restricted access');?>


<table class="admintable">
    <tr>
        <td>



    <?php
    include(dirname(__FILE__)."/const.php");
    $tariff_json_info = file_get_contents('https://tariff.pochta.ru/tariff/v1/calculate?jsontext');
    $tariff_array = json_decode($tariff_json_info, true);
    if ($tariff_json_info == false){
        echo "<div class=\"alert alert-danger\"><span class=\"alert-link\">Ошибка рассчета доставки:</span> <br/>Отсутствует соединение с сервером расчета доставки<br/><span class=\"alert-link\">Свяжитесь, пожалуйста, с администратором сайта.</span></div>";
    }
    ?>
    <div class="row-fluid">
        <div class="span6 well well-sm">
            <h3>Расчет доставки с помощью Тарификатора Почты России<br/><small>Версия <span class="label label-info">1.3.1</span></small></h3>
            <p><a href="https://tariff.pochta.ru/">Тарификатор</a> - сервис расчета стоимости доставки Почты России. В нем представлены почти все тарифы и дополнительные услуги.</p>
            <p>Расширение разрабатывалось с учетом версии тарификатора <span class="label label-success">1.12.15.376</span>. Текущая версия Тарификатора: <span class="label label-info"><?php echo $tariff_array["version"];?></span></p>
            <p>Вопросы, поддержка: Сергей Толкачев, <a href="mailto:info@web-tolk.ru">info@web-tolk.ru</a> +7(906)304-97-83 (Viber/WhatsApp)</p>
        </div>
        <div class="span6 alert alert-danger">Для корректного рассчета цены у пользователя обязательно нужно запрашивать почтовый индекс. Именно по нему идет рассчет доставки.</div>
        <div class="span6">
            <h3>Формула рассчета</h3>
            <ol>
                <li>Вес товара из корзины умножаем на <strong>коэффициент поправки веса</strong> (если указан).</li>
                <li>Отправляем в Тарификатор тип отправления, вес, тип упаковки (если есть), индексы отправителя и получателя, суммы объявленной стоимости и наложенного платежа.</li>
                <li>Тарификатор возвращает стоимость доставки</li>
                <li>К стоимости доставки прибавляем <u>процент от суммы заказа</u> из корзины - <strong>Наценка за объявленную стоимость</strong></li>
                <li>Полученную цифру умножаем на <strong>Общую наценку на способ доставки</strong></li>
            </ol>
            <p><span class="badge badge-info"><i class="icon-star"></i></span> Тип отправления указывается в <a href="index.php?option=com_jshopping&controller=shippingsprices">Ценах на на доставку</a></p>
        </div>
    </div>


</td></tr></table>
<ul class="nav nav-tabs" id="myTabTabs">
    <li class="active"><a href="#settings" data-toggle="tab">Основные настройки</a></li>
    <li class=""><a href="#donate" data-toggle="tab">Сказать "Спасибо"</a></li>
</ul>
<div class="tab-content" id="myTabContent">
    <div id="settings" class="tab-pane active"><!-- Tab 1 -->
        <div class="row-fluid">
            <div class="span3">

                <p><strong>Индекс отправителя</strong><br/></p>
                <input class="inputbox" type = "text" class = "inputbox" name = "params[index_from]" size="45" value = "<?php echo $config['index_from']?>" />
                <p><small>С какого почтового отделения Вы будете отправлять заказы?</small></p>



            </div>
            <div class="span3">
                <p><strong>Наценка за объявленную стоимость</strong><br/></p>
                <input class="inputbox" type = "text" class = "inputbox" name = "params[price_tax]" size="45" value = "<?php echo $config['price_tax']?>" />
                <p><small><strong>В процентах, только число.</strong> Процент считается от суммы заказа без учета доставки.</small></p>
            </div>

            <div class="span3">
                <p><strong>Коэффициент поправки веса</strong></p>
                <input class="inputbox" type = "text" class = "inputbox" name = "params[weight_factor]" size="45" value = "<?php echo $config['weight_factor']?>" />
                <p><small><Strong>Например 1,1 - вес упаковки, тары.</strong> Умножает вес на этот коэффициент. В Тарификатор Почты России отправляется вес с учетом коэффициента.</small></p>
            </div>




            <div class="span3">
                <p><strong>Общая наценка на способ доставки</strong></p>
                <input type = "text" class = "inputbox" name = "params[general_factor]" size="45" value = "<?php echo $config['general_factor']?>" />
                <p><small><strong>Коэффициент, например, 1,1.</strong> Умножает общую стоимость доставки (число, полученное из Тарификатора) на этот коэффициент.</small></p>
            </div>
        </div>
        <hr/>
        <div class="row-fluid">
            <div class="span3">
                <p><strong>Показывать цену с учетом НДС:</strong><br><small>Тарификатор возвращает 2 цены: с НДС и без. Какую цену использовать для рассчета доставки?</p><p>Для справки: калькулятор доставки на <a href="http://pochta.ru">http://pochta.ru</a> показывает цену с учетом НДС.</small></p>
            </div>
            <div class="span3">
                <?php
                $nds_checked = $config['nds'];
                if ($nds_checked == 1){
                    $nds_checked_yes = 'checked="checked"';
                } else {
                    $nds_checked_no = 'checked="checked"';
                }
                ?>

                <ul class="unstyled">
                    <li><input type="radio" name="params[nds]" value="1" id="jform_params_nds1" <?php echo $nds_checked_yes;?>> Да</li>
                    <li><input type="radio" name="params[nds]" value="0" id="jform_params_nds0" <?php echo $nds_checked_no;?>> Нет</li>

                </ul>
            </div>

            <div class="span3">
                <p><strong>Единицы измерения веса:</strong><br/><small>Единицы измерения веса из настроек Joomshopping</small></p>
            </div>
            <div class="span3">
                <?php
                $weight_measure_checked = $config['weight_measure'];
                if ($weight_measure_checked == 1){
                    $weight_measure_kg = 'checked="checked"';
                } elseif ($weight_measure_checked == 2) {
                    $weight_measure_g = 'checked="checked"';
                }
                ?>
                <ul class="unstyled">
                    <li><input type="radio" name="params[weight_measure]" value="1" <?php echo $weight_measure_kg;?>> Килограммы</li>
                    <li><input type="radio" name="params[weight_measure]" value="2" <?php echo $weight_measure_g;?>> Граммы</li>
                </ul>
            </div>

        </div>

        <hr/>
        <div class="row-fluid">
            <div class="span2">
                <p><strong>Отладка</strong><br/><small>Показывать отладочную информацию во фронтэнде.</small></p>
            </div>
            <div class="span2">
                <ul class="unstyled">
                    <li><input type="radio" id="params_debug0" name="params[debug]" value="1" <?php echo $checkedYes; ?>> Показать</li>
                    <li><input type="radio" id="params_debug1" name="params[debug]" value="0" <?php echo $checkedNo; ?>> Скрыть</li>
                </ul>
            </div>
			<div class="span2">
                <p><strong>Ошибки расчета доставки</strong><br/><small>Показывать ошибки расчета доставки на шаге выбора способа доставки (фронтенд)?</small></p>
            </div>
            <div class="span2">
			  <?php
                $display_errors_checked = $config['display_errors'];
                if ($display_errors_checked == 1){
                    $display_errors_checked_yes = 'checked="checked"';
                } else {
                    $display_errors_checked_no = 'checked="checked"';
                }
                ?>
			
                <ul class="unstyled">
                    <li><input type="radio" id="params_debug0" name="params[display_errors]" value="1" <?php echo $display_errors_checked_yes; ?>> Показать</li>
                    <li><input type="radio" id="params_debug1" name="params[display_errors]" value="0" <?php echo $display_errors_checked_no; ?>> Скрыть</li>
                </ul>
            </div>
			<div class="span2">
                <p><strong>При стоимости доставки "0" способ доставки сделать...</strong><br/>
				<small><strong>Без изменений</strong> - способ доставки с нулевой ценой можно выбрать</small><br/>
				<small><strong>Сделать неактивным</strong> - способ доставки с нулевой ценой будет виден, но его нельзя будет выбрать</small><br/>
				<small><strong>Скрыть</strong> - способ доставки с нулевой ценой будет сделан неактивным и скрыт от пользователя, его нельзя будет выбрать</small></p>
				<p>Как правило нулевая цена возникает из-за того, что сервер Почты России не получает всю необходимую информацию, пользователем заполнены не все поля.</p>
            </div>
            <div class="span2">
			  <?php
                $zero_cost_checked = $config['zero_cost'];
                if ($zero_cost_checked == 0){
                    $display_errors_checked_0 = 'checked="checked"';
                } elseif($zero_cost_checked == 1) {
                    $display_errors_checked_1 = 'checked="checked"';
                } elseif($zero_cost_checked == 2) {
                    $display_errors_checked_2 = 'checked="checked"';
                }
                ?>
			
                <ul class="unstyled">
					<li><input type="radio" id="params_debug1" name="params[zero_cost]" value="0" <?php echo $display_errors_checked_0; ?>> Оставить без изменений</li>
					<li><input type="radio" id="params_debug0" name="params[zero_cost]" value="1" <?php echo $display_errors_checked_1; ?>> Сделать неактивным</li>
                    <li><input type="radio" id="params_debug0" name="params[zero_cost]" value="2" <?php echo $display_errors_checked_2; ?>> Скрыть</li>
                </ul>
            </div>
        </div>
    </div><!-- Tab 1 End -->
    <div id="donate" class="tab-pane"><!-- Tab 2 -->
        <div class="row-fluid">
            <div class="well well-small">
                <h4>Поблагодарить автора</h4>
                <p>Расширение для рассчета доставки Почтой России распространяется <strong>бесплатно</strong>. Однако, Вы можете поблагодарить автора и поддержать дальнейшее развите и разработку новых функций.</p>
                <p>Пожертвования с Вашей стороны абсолютно добровольны и ни коим образом не влияют на работоспособность расширения.</p>

                <p><a href="https://money.yandex.ru/to/4100111697633604" class="btn btn-small btn-primary">Яндекс.Деньги</a> <a href="https://paypal.me/tolkachyovsergey" class="btn btn-small btn-primary">С помощью PayPal</a></p>
            </div>
        </div>
    </div><!-- Tab 2 End -->

<div class="clr"></div>