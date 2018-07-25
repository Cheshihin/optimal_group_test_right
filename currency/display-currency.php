<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Отображение курсов валют");
?>
Отображение курсов валют

<?

$date_from = isset($_GET['date_from'])?$_GET['date_from']:'24.06.2018';
$date_to = isset($_GET['date_to'])?$_GET['date_to']:'24.07.2018';



$currency_display = new CurrencyActions('eurofxref-hist.xml');
//$currency_add->fill_date_from = '2018-05-01'; 

echo '</br>'.$currency_display->displayCurrencyRateData($date_from, $date_to, 1,5000);


?>

<script type="text/javascript">
	$('.display-currency-rates>div').eq(1).scroll(function(){
		var self = this;
		$('.display-currency-rates>div').eq(0).scrollTop($(self).scrollTop());
	});
	
	$('.display-currency-rates>div').eq(0).scroll(function(){
		var self = this;
		$('.display-currency-rates>div').eq(1).scrollTop($(self).scrollTop());
	});
	
	//$('.display-currency-rates thead').eq(0).css('position','absolute').css('overflow-x','hidden').css('width',$('.display-currency-rates>div').eq(1).width());
</script>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>