<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Добавление курсов валют в базу");
?>
Добавление курсов валют в базу.

<?

$fill_from_record_number = isset($_GET['fill_from_record_number'])?$_GET['fill_from_record_number']:1;
$currency_add = new CurrencyActions('eurofxref-hist.xml');
//$currency_add->fill_date_from = '2018-05-01'; 

$currency_add->fill_from_record_number = isset($_GET['fill_from_record_number'])?$_GET['fill_from_record_number']:1;
$currency_add->fill_record_count = 15;
$currency_add->addCurrencyRates();
echo '</br>';
echo 'Валюты добавлены!';

?>

<script type="text/javascript">
	$(document).ready(function(){
		var fill_from_record_number = parseInt(<?=$fill_from_record_number;?>);
		fill_from_record_number += 15;
		setTimeout(function(){
			window.location = '/currency/add-currency.php?fill_from_record_number='+fill_from_record_number;
		}, 2000);
		
	});
</script>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>