<?php
/**
* @version      1.3.1 29.02.2020
* @author       Sergey Tolkachyov
* @copyright    Copyright (C) 2019 Sergey Tolkachyov. All rights reserved.
* @license      GNU/GPL
*/

defined('_JEXEC') or die('Restricted access');

class sm_pochta_ru extends shippingextRoot{
	function showShippingPriceForm($params, &$shipping_ext_row, &$template){
        include(dirname(__FILE__)."/shippingpriceform.php");
	}
   
   
   
    function showConfigForm($config, &$shipping_ext, &$template){ 
        $checkedNo = $checkedYes = '';
		$checkeNDSdNo = $checkedNDSYes = '';
        if($config['debug']){
            $checkedYes = 'checked="checked"';
        }
        else{
            $checkedNo = 'checked="checked"';
        }
		
        include(dirname(__FILE__)."/configform.php");
    }
    



    function getPrice($cart, $params, $price, &$shipping_ext_row, &$shipping_method_price){


//параметры способа доставки
        $sm_params = unserialize($shipping_ext_row->params);
        //загружаем пользователя
        $user = &JFactory::getUser();
        if ($user->id){
            $user_info = &JSFactory::getUserShop();
			
			}else{
            $user_info = &JSFactory::getUserShopGuest();
        }

            //вычисляем стоимость доставки
            $price_shipping = $this->calculatePrice($user_info, $cart, $params, $sm_params, $shipping_method_price);

            //если стоимость доставки не определена
            if(!$price_shipping){
                $price_shipping = '0';
            }
			
			

            return $price_shipping;



    }

     private function calculatePrice($user_info, $cart, $params, $sm_params,$shipping_method_price)
    {

		$debug = (!empty($sm_params['debug'])) ? (int)$sm_params['debug'] : 0;
        $price_tax = (!empty($sm_params['price_tax'])) ? (int)$sm_params['price_tax'] : 0;
        $weight_factor = (float)str_replace(',', '.', $sm_params['weight_factor']);
        $weight_factor = ($weight_factor == 0) ? $weight_factor = 1 : $weight_factor;
        if (!$weight_factor){$weight_factor = 1;} //Если не указан коэффициент поправки веса, то умножаем вес на 1
        $general_factor = (float)str_replace(',', '.', $sm_params['general_factor']);
        $general_factor = ($general_factor == 0) ? $general_factor = 1 : $general_factor;
        //сумма счета
        $summ = $cart->price_product_brutto;
        //вес
        $weight = $cart->getWeightProducts();
		//Коэффициент поправки веса
		$weight = $weight*$weight_factor;
      
		include(dirname(__FILE__)."/const.php");
		
      //Собираем информацию для формирования url запроса
		$index_fromForDebug = $sm_params['index_from'];
		$index_from = "&from=".$sm_params['index_from'];//Индекс отправителя
		$index_toForDebug = $user_info->zip;
		$index_to = "&to=".$user_info->zip;// Индекс получателя
		$weight_measure = $sm_params['weight_measure'];// в чем единицы измерения веса товара
		if ($weight_measure == 1) {// Если в килограммах, то умножаем на 1000. Тарификатор считает все в граммах
			$weight = $weight * 1000;
			}
		
		if ($weight < $minweight) {
			$weight = $minweight;//Минимальный вес отправления 100 грамм. Если меньше, то увеличиваем до 100 грамм. Иначе ошибка.
		}
		$weightForDebug = $weight;

		if($weight > 31500){
			$how_many_orders = ceil($weight/31500);
			
			echo "<div class='alert alert-danger'>
			<h3>Общий вес заказа (".($weight/1000).") превышает допустимое значение</h3>
			<p>Максимально допустимое значение - 31,5кг.</p>
			<p><strong>Ваш заказ необходимо разделить на ".$how_many_orders."</strong></p>
			</div>
			<script>
			jQuery(document).ready(function(){
				jQuery('input#shipping_method_".$shipping_method_price->shipping_method_id."').attr('disabled','disabled');
				jQuery('[for=shipping_method_".$shipping_method_price->shipping_method_id."]').css('color','red').append('<br/><strong>Превышен максимально допустимый вес</strong>');
			});
			</script>
			";
			$weight_warning = 0;
			return $weight_warning;
					
		} else {
				$weight = "&weight=".$weight;
				$object_type = "&object=".$params['tip_otpravleniya'];
				foreach ($tipOtpravleniya as $value) {
							if ($value["code"] == $params['tip_otpravleniya']){
								if ($value["pack"] == 1) {
									$pack = "&pack=".$params["pack"];
								} else {
									$pack = "";
								}
							}
					  }
				$sumoc = $params["sumoc"];//Объявленная ценность
				if(!$sumoc){
					$sumoc = "";
				} else {
					$sumoc = $params["sumoc"]*100;//Сумма в копейках
					$sumoc = "&sumoc=".$sumoc;
				}
				
				$sumnp = $params["sumnp"];//Сумма наложенного платежа
				if(!$sumnp){
					$sumnp = "";
				} else {
					$sumnp = $params["sumnp"]*100;//Сумма в копейках
					$sumnp = "&sumnp=".$sumnp;
				}
				//запрашиваем стоимость перевозки
				$url = "https://tariff.pochta.ru/tariff/v1/calculate?jsontext".$object_type.$index_from.$index_to.$weight.$pack.$sumoc.$sumnp;
				$url_json = file_get_contents($url);
				$tariff_array = json_decode($url_json, true);
				if ($tariff_array["error"]){
					echo "<div class=\"alert alert-danger\"><span class=\"alert-link\">Ошибка рассчета доставки:</span> <br/>".$tariff_array["error"][0]."<br/><span class=\"alert-link\">Свяжитесь, пожалуйста, с администратором сайта.</span></div>";
				}
				if ($url_json == false){
					echo "<div class=\"alert alert-danger\"><span class=\"alert-link\">Ошибка рассчета доставки:</span> <br/>Отсутствует соединение с сервером расчета доставки<br/><span class=\"alert-link\">Свяжитесь, пожалуйста, с администратором сайта.</span></div>";
				}

				
				

				$showNDS = $sm_params['nds'];// Тарификатор отдает 2 цены: с НДС и без. Выбираем, что показывать
				if ($showNDS == 0) {
					$price = $tariff_array["pay"] / 100;
				} elseif ($showNDS == 1) {
					$price = $tariff_array["paynds"] / 100;
					$nds = $tariff_array["nds"] / 100;
					$ndsrate = $tariff_array["ndsrate"];
					$name = $tariff_array["name"];
				} 

			   if ($price_tax != 0){$price_tax = ($summ/100)*$price_tax;}//Если наценка не указана, то коэффициент = 0.
			   if (!$general_factor){$general_factor = 1;}
			   
				$prices = ($price+$price_tax)*$general_factor;
				if ($debug){
						echo '<div class="well well-sm">';
								echo '<br/>Минимальный вес отправления = <span class="label label-info">'.$minweight." грамм</span>";
								echo '<br/>Размер НДС = <span class="label label-info">';
								if ($ndsrate){echo $ndsrate;} else{echo "Показ НДС выключен";}
								echo '</span>';
								
						echo '<br/>Общая стоимость доставки Почтой России с учетом всех коэффициентов = '.$prices;
						echo '
								<table class="table table-bordered table-striped">
								<thead>
								<caption><h4>Итоговая формула</h4></caption>
								<tr><th>Параметр</th><th>Значение</th><th>Формула</th><th>Промежуточное значение</th></tr>
								</thead>
								<tr><td>Вес корзины</td><td>'.$cart->getWeightProducts().'</td><td></td><td>-</td></tr>
								<tr><td>Коэффициент поправки веса<br/><small>Например 1,1 - вес упаковки, тары. Умножает вес на этот коэффициент. В Тарификатор Почты России отправляется вес с учетом коэффициента.</small></td><td>'.$weight_factor.'</td><td>'.$cart->getWeightProducts().' * '.$weight_factor.' </td><td>'.$cart->getWeightProducts()*$weight_factor.'</td></tr>
								<tr><td colspan="4">Отправляем в Почту России:<br/>
								<ul>
								<li>Код типа отправления - '.$object_type.'</li>
								<li>Индекс отправителя - '.$index_from.'</li>
								<li>Индекс получателя - '.$index_to.'</li>
								<li>Объявленная стоимость  - '.$sumoc.' (в копейках)</li>
								<li>Сумма наложенного платежа - '.$sumnp.' (в копейках)</li>
								<li>Код типа упаковки - '.$pack.'</li>
								<li>Вес с учетом коэффициента - '.$weight.' (в граммах)</li>
								</ul>
								</td></tr>
								<tr><td colspan="4"><span class="label label-info">url:</span> '.$url.'</td></tr>
								<tr><td>Цена Почты России</td><td>'.$price.'</td><td>-</td><td>'.$price.'</td></tr>
								<tr><td>Наценка за объявленную стоимость<br/><small><strong>(процент от суммы заказа из <span style="color:red">корзины</span>)</strong></small></td><td>'.$sm_params['price_tax'].'% <br/><small>от '.$summ.'</small></td><td>'.$price.' + '.$price_tax.'</td><td>'.($price+$price_tax).'</td></tr>
								<tr><td>Общая наценка на способ доставки<br/><small><strong>(Коэффициент, например, 1,1. Умножает общую стоимость доставки (число, полученное из Тарификатора) на этот коэффициент).</strong></small></td><td>'.$general_factor.'</td><td>'.($price+$price_tax).' * '.$general_factor.'</td><td>'.$prices.'</td></tr>
								
								
								</table>
								
								';
					
					
						echo "<h4>Массив,полученный из Тарификатора</h4><pre>";
							print_r($tariff_array);
							echo "</pre>";
							
							echo "</div>";
					}
							return $prices;
		}
	}


}

?>