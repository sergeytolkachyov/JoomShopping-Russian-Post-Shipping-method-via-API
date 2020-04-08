<?php 
/**
* @version      1.4.0 08.04.2020
* @author       Sergey Tolkachyov
* @copyright    Copyright (C) 2019 Sergey Tolkachyov. All rights reserved.
* @license      GNU/GPL
*/
defined('_JEXEC') or die('Restricted access');?>
<div class="alert alert-success"><h3>Стоимость расчитывается по API Тарификатором Почты России</h3></div>




<?php
include(dirname(__FILE__)."/const.php");
$tariff_json_info = file_get_contents('https://tariff.pochta.ru/tariff/v1/calculate?jsontext');
$tariff_array = json_decode($tariff_json_info, true);
if ($tariff_json_info == false){
			echo "<div class=\"alert alert-danger\"><span class=\"alert-link\">Ошибка рассчета доставки:</span> <br/>Отсутствует соединение с сервером расчета доставки<br/><span class=\"alert-link\">Свяжитесь, пожалуйста, с администратором сайта.</span></div>";
		}
$sm_params = unserialize($template->sh_method_price->params);
?>





		
<table class="admintable">
<tr>
<td>
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
    <p><span class="badge badge-important"><i class="icon-star"></i></span> Не забудьте указать <span class="label label-important">индекс места отправления</span> в <a href="index.php?option=com_jshopping&controller=shippingextprice">Основных настройках</a> расширения для расчета цены.</p>
</div>
</div>


<ul class="nav nav-tabs" id="myTabTabs">
	<li class="active"><a href="#tipOtpravleniya" data-toggle="tab">Тип отправления</a></li>
	<li class=""><a href="#info" data-toggle="tab">Информация</a></li>
    <li class=""><a href="#donate" data-toggle="tab">Сказать "Спасибо"</a></li>
</ul>
<div class="tab-content" id="myTabContent">
	<div id="tipOtpravleniya" class="tab-pane active"><!-- Tab 1 -->
		<div class="row-fluid">
			<div class="span4">
				<h3>Типы отправлений</h3>
					<ul class="unstyled" id="tipOtpravleniya" >
						<?php
						  foreach ($tipOtpravleniya as $value) {
							  
							  $tip_config = $sm_params['tip_otpravleniya'];
							if ($tip_config == $value["code"]){
								$tip_checked = 'checked="checked"';
							} else {
								$tip_checked = "";
							}
							if($value['disabled'] == 1){
								$radioTipDisabled = "disabled='disabled'";
							} else {
								$radioTipDisabled = "";
							}
							
							echo "<li><input type=\"radio\" data-pack=\"".$value['pack']."\" data-sumoc=\"".$value['sumoc']."\" data-sumnp=\"".$value['sumnp']."\" name=\"sm_params[tip_otpravleniya]\" value=\"".$value['code']."\" ".$tip_checked." ".$radioTipDisabled."> ".$value['desc']."</li>";
						  }

						?>
					</ul>
					<script>
						jQuery(document).ready(function(){
							var hasPack = jQuery("ul#tipOtpravleniya li input[type='radio']:checked").attr("data-pack");
							var hasSumoc = jQuery("ul#tipOtpravleniya li input[type='radio']:checked").attr("data-sumoc");
							var hasSumnp = jQuery("ul#tipOtpravleniya li input[type='radio']:checked").attr("data-sumnp");
							if (hasPack == 1){
								jQuery("#no-pack").hide();//Если в выбранном способе доставки предполагается упаковка, то прячем алерт.
								jQuery("#pack").show();
							} else {
								jQuery("#no-pack").show();
								jQuery("#pack").hide();//Иначе показываем алерт и прячем способы доставки.
								jQuery("#pack select option:selected").removeAttr("selected");//Удаляем выбранную упаковку, чтоб не попала в массив
							}

								if(hasSumoc == 1) {
									jQuery("input#sumoc").removeAttr("disabled","disabled");
								} else {
									jQuery("input#sumoc").attr("disabled","disabled");
								}
								if(hasSumnp == 1) {
									jQuery("#sumnp").show();
								} else {
									jQuery("#sumnp").hide();
								}
							
							//То же самое по клику по списку типов отправлений
							
							jQuery("ul#tipOtpravleniya li input[type='radio']").click(function(){
								var hasPack = jQuery(this).attr("data-pack");
								var hasSumoc = jQuery(this).attr("data-sumoc");
								var hasSumnp = jQuery(this).attr("data-sumnp");
								if (hasPack == 1){
									jQuery("#no-pack").hide();//Если в выбранном способе доставки предполагается упаковка, то прячем алерт.
									jQuery("#pack").show();
									jQuery("#pack-required").show();
									
									
									} else {
										jQuery("#no-pack").show();
										jQuery("#pack").hide();//Иначе показываем алерт и прячем способы доставки.
										jQuery("#pack-required").hide();
										jQuery("#pack select option:selected").removeAttr("selected");//Удаляем выбранную упаковку, чтоб не попала в массив
									}
							
								if(hasSumoc == 1) {
									jQuery("input#sumoc").removeAttr("disabled","disabled");
								} else {
									jQuery("input#sumoc").attr("disabled","disabled").attr("value","");
								}
								
								if(hasSumnp == 1) {
									jQuery("#sumnp").show();
								} else {
									jQuery("#sumnp input").attr("value","");
									jQuery("#sumnp").hide();
								}
							
							});//Клик по типам отправления
														
						});
					</script>

			</div><!-- Тип отправления span4 -->
			<div class="span4 well well-sm">
				<h3>Объявленная стоимость</h3>
				<small>Сумма страховой выплаты на случай потери или повреждения отправления. Чем выше объявленная стоимость, тем дороже стоимость самой доставки.</small>
					<p><input type = "text" id="sumoc" class = "inputbox" name = "sm_params[sumoc]" size="55" value = "<?php echo $sm_params['sumoc']?>"/></p>
					
						<div class="accordion" id="accordion1">
						  <div class="accordion-group">
							<div class="accordion-heading">
							  <a class="accordion-toggle btn" data-toggle="collapse" data-parent="#accordion1" href="#collapseOne">
								Комиссия за объявленную ценность
							  </a>
							</div>
							<div id="collapseOne" class="accordion-body collapse">
							  <div class="accordion-inner">
								<p>За объявленную ценность взимается дополнительная плата (указана без учета НДС) в размере:</p>
								<ul>
								<li>для посылок – 3,39% от оценочной стоимости вложений,</li>
								<li>для писем и бандеролей – 0,03 руб. за каждый рубль оценочной стоимости вложений,</li>
								<li>для отправлений 1 класса – 3% от оценочной стоимости вложений,</li>
								<li>для экспресс-отправлений EMS – 0,42% от оценочной стоимости вложений.</li>
								</ul>
								<p><strong>Вы можете указать этот процент в "Основные настройки" - "Наценка за объявленную стоимость".</strong> Подробнее во вкладке "Информация"</p>
							  </div>
							</div>
						  </div>
 						</div>
				<div id="sumnp">
					<h3>Наложенный платеж</h3>
					<small style="font-color: #ff0000;">Для отправления EMS с объявленной ценностью и наложенным платежом. <span style="color: #ff0000; font-weight: 600;">Наложенный платеж не может быть больше 50 000 рублей.</span></small>
					<p><input type = "text" class = "inputbox" name = "sm_params[sumnp]" size="55" value = "<?php echo $sm_params['sumnp'];?>"/></p>
				</div>
				<h3>Упаковка</h3>
				<p>На стоимость доставки большое влияние оказывает тип упаковки, так как это связано напрямую с занимаемым объёмом.</p>
					<div class="alert alert-danger"  id="no-pack" style="display:none;">
						<p style="text-align:center;">Для данного типа отправления в API не существует упаковки.</p>
					</div>
					<div class="alert alert-warning"  id="pack-required" style="display:none;">
						<h4>Не забудьте выбрать тип упаковки.</h4>
						<p>Без указания типа упаковки для данного типа отправления Тарификатор вернет ошибку.</p>
					</div>
					
					<div id="pack">
						<select name="sm_params[pack]">
						<?php 
							  foreach ($pack as $value) {
								  
								  $pack_config = $sm_params['pack'];
								if ($pack_config == $value[code]){
									$pack_checked = 'selected';
								} else {
									$pack_checked = "";
								}
								  if($value[disabled] == 1){
									  $packDisabled = "disabled='disabled'";
								  } else {
									  $packDisabled = "";
								  }
								echo "<option value=\"".$value['code']."\" ".$pack_checked." ".$packDisabled."> ".$value['desc']."</option>";
							  }

							?>
						</select>
						
						<div id="pack-info">
						<h4>Коробки Почты России</h4>
							<ul>
								<li>Гофрокороб S - 260 × 170 × 80</li>
								<li>Гофрокороб M - 300 × 200 × 150</li>
								<li>Гофрокороб L - 400 × 270 × 180 </li>
								<li>Гофрокороб XL - 530 × 360 × 220</li>
								
							</ul>
						<h4>Пластиковые пакеты</h4>
							<ul>
								<li>Пакет почтовый полиэтиленовый S - 345 × 280</li>
								<li>Конверт с прослойкой из воздушно-пузырчатой пленки S - 200 × 270 × 40</li>
								<li>Пакет почтовый полиэтиленовый М - 375 × 500</li>
								<li>Конверт с прослойкой из воздушно-пузырчатой пленки M - 260 × 320 × 40</li>
								<li>Пакет почтовый полиэтиленовый L - 455 × 580</li>
								<li>Пакет почтовый полиэтиленовый XL - 595 × 700</li>
								
							</ul>
							<p>Также существуют и другие типы упаковки. <a target="_blank" href="https://www.pochta.ru/support/post-rules/package-materials">Подробнее на сайте Почты России</a>.</p>
						
						
						
						</div>

					</div>
			</div><!-- Упаковка span4 -->
			<div class="span4">
				<h3>Дополнительные услуги</h3>
				<p>Coming soon...</p>
				<hr/>
				<div class="alert alert-info">
					<h3>Обратите внимание!</h3>
					<p>Не каждое почтовое отделение является <span class="alert-link">Центром выдачи и приема посылок (ЦВПП)</span>. В некоторых случаях (EMS оптимальное, например) указание индекса отделения, не являющегоса ЦВПП вызовет ошибку.</p>
				</div>
			</div><!-- Доп.услуги span4 -->
		</div>
	</div><!-- Tab 1 End -->
	
	<div id="info" class="tab-pane"><!-- Tab 2 -->
		<div class="row-fluid">
				<h2>Типы отправлений</h2>
				<h3>Бандероли</h3>
				<p>Бандероль - почтовое отправление с малоценными печатными изданиями, рукописями и фотографиями. Cрок доставки бандероли 1-го класса на 25-30% ниже, чем для обычной бандероли. <strong>Вес бандероли не более 2,5 кг.</strong></p>
				
				<h3>Посылки</h3>
				<p>Посылка - почтовое отправление с предметами культурно-бытового и иного назначения.</p>
				<table class="table table-striped table-hover table-bordered">
				<thead><tr><th>Вид отправления*</th><th>Макс. размер</th><th>Где отправить</th><th>Наценка</th></tr></thead>
				<tbody>
				<tr><td>Посылка до 10 кг</td><td>53 × 38 × 26,5 см.</td><td>Любое отделение</td><td>Без наценки</td></tr>
				<tr><td>Посылка до 20 кг </td><td>Cумма измерений трех сторон не более 300 см.</td><td>Любое отделение</td><td>С наценкой 40%</td></tr>
				<tr><td>Экспресс-отправление EMS - до 31,5 кг</td><td>Сумма длины и периметра наибольшей стороны – не более 300 см. Длина, ширина, высота – не более 150 см.</td><td>Забор и доставка курьером</td><td> </td></tr>
				</tbody>
				</table>
				<p>Посылки тяжелее 20 кг или при сумме измерений трех сторон превышающей 300 см можно отправить только из специализированных отделений.</p>
				<hr/>
				<h3>«Посылка онлайн»</h3>
				<p>Услуга «посылка онлайн» разработана специально для компаний дистанционной торговли. Клиенты Почты России могут отправлять посылки весом до 5 кг по фиксированным ценам с гарантированными сроками доставки. Отправления принимаются партиями, проходят приоритетную обработку в автоматизированном сортировочном центре и доставляются во все крупные города России, на которые приходится до 70% рынка электронной коммерции. Адресат может забрать интернет-заказ в почтовом отделении. Также можно выбрать доставку посылки адресату на дом, для этого воспользуйтесь услугой «курьер онлайн».</p>
				<hr/>
				<h3>«Курьер онлайн»</h3>
				<p>Услуга «Курьер онлайн» разработана для клиентов Почты России, которые ежемесячно пересылают свыше 50 отправлений. Компании дистанционной торговли могут отправлять посылки весом до 5 кг по фиксированным ценам с гарантированными сроками доставки. Адресат получает посылку на дом в одном из городов назначения. Чтобы отправить посылку более дешевым способом, воспользуйтесь услугой «посылка онлайн», отправление доставят в почтовое отделение адресата.</p>
				<hr/>
				<h3>«Бизнес курьер»</h3>
				<p>Услуга «бизнес курьер» предполагает чёткое соблюдение контрольных сроков, за нарушение которых Почта России несет ответственность за каждый день задержки по рыночным стандартам. Максимальный допустимый вес отправления «Бизнес курьер» — 31,5 кг. Ограничения по габаритам — любая из сторон не должна превышать 150 см, сумма длины и периметра поперечного сечения должна быть не более 300 см. «Бизнес курьер» может включать дополнительные опции в зависимости от потребностей клиента: объявленную ценность отправления или формирование описи вложения.</p>
				<hr/>
				<h3>Ценные отправления</h3>
				<p>При повреждении, утрате или нарушении сроков доставки отправитель или получатель ценного отправления имеет право на компенсацию.</p>
				<p>Ценные отправления оформляются в почтовом отделении. Ценным может быть только регистрируемое отправление.</p>
				<p>Ценность содержимого отправления назначает отправитель. Сумму объявленной ценности нужно написать на упаковке (конверте, коробке, пакете); сумма пишется цифрами и дублируется прописью в скобках. Например: 100 (сто) руб. 00 коп.</p>
				<p>За объявленную ценность взимается дополнительная плата (указана без учета НДС) в размере:</p>
				<ul>
				<li>для посылок – 3,39% от оценочной стоимости вложений,</li>
				<li>для писем и бандеролей – 0,03 руб. за каждый рубль оценочной стоимости вложений,</li>
				<li>для отправлений 1 класса – 3% от оценочной стоимости вложений,</li>
				<li>для экспресс-отправлений EMS – 0,42% от оценочной стоимости вложений.</li>
				</ul>
				<p>Отправления с наложенным платежом и описью вложения можно оформить только ценным отправлением. При этом сумма наложенного платежа не может превышать сумму ценности.</p>		
				<a href="https://www.pochta.ru/support/post-rules/valuable-departure" class="btn">Подробнее</a>
				<hr/>
				<h3>Наложенный платеж</h3>
				<p>Наложенный платеж — сумма, которую адресат должен оплатить при получении письма или посылки в почтовом отделении. После оплаты получателем, сумма наложенного платежа перечисляется отправителю денежным переводом.</p>
				<p>С наложенным платежом можно отправить только регистрируемые отправления с объявленной ценностью. При этом сумма наложенного платежа не может превышать сумму объявленной ценности.</p>
				<p>Получатель может отказаться от получения отправления (тогда оно возвращается отправителю).</p>
				<p>Отправления с наложенным платежом отправляются по России и за рубеж в Азербайджан, Армению, Белоруссию, Грузию, Казахстан, Киргизию, Латвию, Литву, Молдавию и Украину.</p>
				<a href="https://www.pochta.ru/support/post-rules/cash-on-delivery" class="btn">Подробнее</a>
				<hr/>
				<h3>EMS</h3>
				<p>Express Mail Service (EMS) – это услуга по экспресс-доставке отправлений, оказываемая более чем в 190 странах мира. В России эту услугу оказывает Курьерская служба Почты России - служба, которая обеспечивает доставку Ваших срочных документов и грузов в кратчайшие сроки.</p>
					<h4>Ограничения EMS</h4>
					<p><strong>Вес:</strong> до 31,5 кг — по России, до 20 кг — в Австралию, Англию, Аргентину, на Арубу, в Бахрейн, на Бермудские о-ва, в Вануату, Гайану, Гибралтар, на Доминику, в Израиль, Испанию, Казахстан, Малави, Монголию, Мьянму, Новую Каледонию, Польшу, Сирию, Суринам, Тринидад и Тобаго, на Украину, в Экваториальную Гвинею, до 10 кг — в Гамбию, на Каймановы о-ва, Кубу, Теркс и Кайкос, до 30 кг — в остальные страны.</p>
					<p>Сумма длины и периметра наибольшей стороны – не более 300 см. Длина, ширина, высота  – не более 150 см</p>
					<a href="https://www.pochta.ru/support/parcels/ems" class="btn">Подробнее</a>
				
				
				
		</div>
	</div><!-- Tab 2 End -->
    <div id="donate" class="tab-pane"><!-- Tab 3 -->
        <div class="row-fluid">
            <div class="well well-small">
                <h4>Поблагодарить автора</h4>
                <p>Расширение для рассчета доставки Почтой России распространяется <strong>бесплатно</strong>. Однако, Вы можете поблагодарить автора и поддержать дальнейшее развите и разработку новых функций.</p>
                <p>Пожертвования с Вашей стороны абсолютно добровольны и ни коим образом не влияют на работоспособность расширения.</p>

                <p><a href="https://money.yandex.ru/to/4100111697633604" class="btn btn-small btn-primary">Яндекс.Деньги</a> <a href="https://paypal.me/tolkachyovsergey" class="btn btn-small btn-primary">С помощью PayPal</a></p>
            </div>
    </div><!-- Tab 3 End -->
</div>

</td>
</tr>
</table>