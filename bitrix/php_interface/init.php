<?

CModule::IncludeModule("iblock");

class CurrencyActions{
	public $fill_date_from;
	public $fill_date_to;
	public $fill_from_record_number;
	public $fill_record_count;
	public $currency_XML_url;
	public $update_mode;
	private $xml_data;
	private $all_xml_data_parsed;
	private $XML;
	private $created_count;
	public $xml_data_count;
	public $partialPageCount;
	
	function __construct($currency_XML_url="https://www.ecb.europa.eu/stats/eurofxref/eurofxref-hist.xml", $need_parse_xml_data=false){
		if($currency_XML_url && $currency_XML_url != ''){
			$this->currency_XML_url = $currency_XML_url;
		} else {
			$this->currency_XML_url = "https://www.ecb.europa.eu/stats/eurofxref/eurofxref-hist.xml";
		}
		$this->fill_date_from = null;
		$this->fill_date_to = null;
		$this->fill_from_record_number = 1;
		$this->fill_record_count = null;
		$this->update_mode = false;
		$this->created_count = 0;
		
		
		$this->XML = simplexml_load_file($this->currency_XML_url);
		
		
		
		
		
		$this->partialPageCount = 100;
		
		
		if($need_parse_xml_data){
		
			$this->xml_data = array();
		
			foreach($this->XML->Cube->Cube as $time){
				$curr_date = $this->getRightDateString1($time['time']);
				$this->xml_data[$curr_date] = array();
				foreach($time->Cube as $cube){
					$this->xml_data[$curr_date][] = array('CURRENCY'=>$cube['currency'],'VALUE'=>$cube['rate']);
				}
			}
			$this->all_xml_data_parsed = true;
		} else {
			$this->xml_data = null;
			$this->all_xml_data_parsed = false;
		}		
	}
	
	private function parse_xml_data(){
		$this->xml_data = array();
		$this->xml_data_count = 0;
		
		if($this->fill_from_record_number !== null){
			$curr_record_number = 1;
			$curr_count = 0;
		} else {
			$curr_date = null;
		}
		
		$no_parsed_data_exists = false;
		
		
		if($this->fill_from_record_number !== null){
				$itter_count = 0;
				foreach($this->XML->Cube->Cube as $time){
					
						
					
						if(($this->fill_from_record_number !== null && $curr_record_number < $this->fill_from_record_number) || ($this->fill_record_count !== null && $curr_count > $this->fill_record_count)){
							
							if($this->fill_record_count !== null && $curr_count > $this->fill_record_count){
								$no_parsed_data_exists = true;
								break;
							} else {
								$no_parsed_data_exists = true;
								$curr_record_number++;
								continue;
							}
						}	
						
							
							$curr_date = $this->getRightDateString1($time['time']);
							
							
							
							$this->xml_data[$curr_date] = array();
							foreach($time->Cube as $cube){
								
								$this->xml_data[$curr_date][] = array('CURRENCY'=>$cube['currency'],'DATE_UNIX'=>strtotime($this->getRightDateString3($time['time'])),'VALUE'=>$cube['rate']);
								$this->xml_data_count++;
							}
							
							$curr_count++;
							$curr_record_number++;
							$itter_count++;
						
						
						
				}
				
				
		} else if($this->fill_date_from !== null || $this->fill_date_to !== null){
				
				if($this->fill_date_to !== null){
					$date_to = strtotime($this->getRightDateString3($this->fill_date_to));
				} else {
					$date_to = null;
				}
				
				if($this->fill_date_from !== null){
					$date_from = strtotime($this->getRightDateString3($this->fill_date_from));
				} else {
					$date_from = null;
				}
							
				foreach($this->XML->Cube->Cube as $time){
					
						if($this->fill_date_to !== null && strtotime($this->getRightDateString3($time['time']))>$date_to){
							$no_parsed_data_exists = true;
							break;
						}
					
						if(($this->fill_date_from === null || strtotime($this->getRightDateString3($time['time']))>=$date_from) &&
						($this->fill_date_to === null || strtotime($this->getRightDateString3($time['time']))<=$date_to)){
				
							$curr_date = $this->getRightDateString1($time['time']);
							$this->xml_data[$curr_date] = array();
							foreach($time->Cube as $cube){
								$this->xml_data[$curr_date][] = array('CURRENCY'=>$cube['currency'],'DATE_UNIX'=>strtotime($this->getRightDateString3($time['time'])),'VALUE'=>$cube['rate']);
								$this->xml_data_count++;
							}
						
						} else {
							$no_parsed_data_exists = true;
						}
				}
		
		} else {
			//foreach($this->XML->Cube->Cube as $time){
					
						foreach($this->XML->Cube->Cube as $time){
					
							
					
							
				
								$curr_date = $this->getRightDateString1($time['time']);
								$this->xml_data[$curr_date] = array();
								foreach($time->Cube as $cube){
									
									$this->xml_data[$curr_date][] = array('CURRENCY'=>$cube['currency'],'DATE_UNIX'=>strtotime($this->getRightDateString3($time['time'])),'VALUE'=>$cube['rate']);
									$this->xml_data_count++;
								}
						
							
						}
			//}
		}
		
		if(!$no_parsed_data_exists){
			$this->all_xml_data_parsed = true;
		} else {
			$this->all_xml_data_parsed = false;
		}	
	}
	
	private function getDataPage($page_number){
		
		$fromXML = array();
		
		
		
		
		
		$start_number = ($page_number - 1) * $this->partialPageCount + 1;
		$end_number = $start_number + $this->partialPageCount - 1;
		
		
		
		$curr_number = 1;
		
		$min_date = 0;
		$max_date = $min_date;
		
		
		
		$curr_number = 1;
		
		$total_count = 0;
		
		$records_are_over = false;
		
		$first_entering = true;
		
		foreach($this->xml_data as $date=>$date_item){
			
			//$fromXML[$date] = array();
			
			foreach($date_item as $data_item){
			
				if($curr_number > $end_number){
					break;
				}
			
				if($curr_number < $start_number){
					$curr_number++;
					continue;
				}
				
				if($first_entering){
					
					$min_date = $date_item['UNIX_DATE'];
					$max_date = $date_item['UNIX_DATE'];
					$first_entering = false;
					
				} else {	
					if($date_item['UNIX_DATE'] < $min_date){
						$min_date = $date_item['UNIX_DATE'];
					}
			
					if($date_item['UNIX_DATE'] > $max_date){
						$max_date = $date_item['UNIX_DATE'];
					}
				
				}
				
				if(!isset($fromXML[$date])){
					$fromXML[$date] = array();
					$fromXML[$date][] = $data_item;
				} else {
					$fromXML[$date][] = $data_item;
				}
			
				$curr_number++;
				$total_count++;
			}
			
		}
		
		if($curr_number > $this->partialPageCount){
			$records_are_over = true;
		}
		
		
		$max_date += 86399;
		$min_date_str = $this->getRightDateStringFromUnixDate($min_date,'Y-m-d H:i:s');
		$max_date_str = $this->getRightDateStringFromUnixDate($max_date,'Y-m-d H:i:s');
		
		
		$arFilter = Array(
			"IBLOCK_ID"=>6, 
			//">DATE_ACTIVE_FROM"=>date($DB->DateFormatToPHP(CLang::GetDateFormat("SHORT")), mktime(0,0,0,1,1,2003)), 
			"ACTIVE"=>"Y", 
			">=PROPERTY_DATE_VALUE"=>$min_date_str,
			"<=PROPERTY_DATE_VALUE"=>$max_date_str,
			
		);
		
		$res = CIBlockElement::GetList(array(), $arFilter, false, false, Array("IBLOCK_ID",'ID','PROPERTY_DATE'));
		
		$dates = array();
		
		while($ob = $res->GetNextElement())
		{
			$arProps = $ob->GetProperties();
			$dates[$arProps["DATE"]['VALUE']] = true;
		}
		
		$dates = array_keys($dates);
		
		//var_dump('DATES_COUNT:'.count($dates));
		
		$toUpdate = array();
		
		foreach($dates as $date){
			if(isset($fromXML[$date])){
				
				if(!isset($toUpdate[$date])){
					var_dump('to_update'.$date);
					$toUpdate[$date] = $fromXML[$date];
				}
			}
		}
		
		foreach($toUpdate as $k=>$item){
			unset($fromXML[$k]);
		}
		
		return array(
			'ADDED'=>$fromXML,
			'UPDATED'=>$toUpdate,
			'TOTAL_COUNT'=>$total_count,
			'END'=>$records_are_over
		);
		
		
	}
	

	private function getDateArray($date_string){
		$exp = explode('-',$date_string);
		return array(
			'YEAR'=>$exp[0],
			'MONTH'=>$exp[1],
			'DAY'=>$exp[2]
		);
	}
	
	
	private function getDateArray1($date_string){
		$exp = explode('.',$date_string);
		return array(
			'DAY'=>$exp[0],
			'MONTH'=>$exp[1],
			'YEAR'=>$exp[2]
		);
	}
	
	
	
	private function getFromDate($date_string){
		$date_array = $this->getDateArray1($date_string);
		
		$date_unix = strtotime($date_array['YEAR'].'-'.$date_array['MONTH'].'-'.$date_array['DAY']) - 1;
		
		$str = $this->getRightDateStringFromUnixDate($date_unix, 'Y-m-d H:i:s');
		
		return $str;
	}
	
	
	private function getToDate($date_string){
		$date_array = $this->getDateArray1($date_string);
		
		$str = $date_array['YEAR'].'-'.$date_array['MONTH'].'-'.$date_array['DAY'].' 23:59:59';
		
		return $str;
	}
	
	
	
	private function getRightDateStringFromUnixDate($unix_date, $format='d.m.Y'){
		
		$date = date_create();
		

		date_timestamp_set($date, $unix_date);
		return date_format($date, $format);
	}
	
	private function getRightDateString1($date_string){
		$date_array = $this->getDateArray($date_string);
		
		return $date_array['DAY'].'.'.$date_array['MONTH'].'.'.$date_array['YEAR'];
	}
	
	private function getRightDateString2($date_string){
		
		$date_array = $this->getDateArray($date_string);
		
		return $date_array['DAY'].'-'.$date_array['MONTH'].'-'.$date_array['YEAR'];
	}
	
	private function getRightDateString3($date_string){
		
		$date_array = $this->getDateArray($date_string);
		
		return $date_array['MONTH'].'/'.$date_array['DAY'].'/'.$date_array['YEAR'];
	}
	
	private function getRightDateString4($date_string){
		
		$date_array = $this->getDateArray1($date_string);
		
		return $date_array['YEAR'].'-'.$date_array['MONTH'].'-'.$date_array['DAY'];
	}
	
	private function addCurrencyOnDate($date,$currency_name,$currency_value){
		
			$fields = array(
				'IBLOCK_ID' => 6,
				'NAME'=>$currency_name,
				'ACTIVE'=>'Y',
				'CODE'=>$currency_name.'-'.$date
			);
			$fields['PROPERTY_VALUES'] = array(
				'DATE' => $date,
				'VALUE' => $currency_value
			);
			
			$el = new CIBlockElement;
			
			
			if($ELEMENT_ID = $el->Add($fields)){
				$this->created_count++;
				//echo "New ID: ".$ELEMENT_ID.'</br>';
			}else
				echo "Error: ".$el->LAST_ERROR.'</br>';
			
			
		
	}
	
	public function addCurrencyRates(){
		$this->created_count = 0;
		$this->parse_xml_data();
		
		$min_date_unix = 0;
		$max_date_unix = 0;
		
		$first_entering = true;
		
		foreach($this->xml_data as $date=>$items){
			foreach($items as $item){
				if($first_entering){
					
					$min_date_unix = $item['DATE_UNIX'];
					$max_date_unix = $min_date_unix;
					$first_entering = false;
					
				} else {
					if($item['DATE_UNIX'] < $min_date_unix){
						$min_date_unix = $item['DATE_UNIX'];
					}
					
					if($item['DATE_UNIX'] > $max_date_unix){
						$max_date_unix = $item['DATE_UNIX'];
					}
				}
			}
		}
		
		$min_date_str = $this->getRightDateStringFromUnixDate($min_date_unix - 1,'Y-m-d H:i:s');
		$max_date_str = $this->getRightDateStringFromUnixDate($max_date_unix + 86399,'Y-m-d H:i:s');
		
		
		$arFilter = Array(
			"IBLOCK_ID"=>6, 
			//">DATE_ACTIVE_FROM"=>date($DB->DateFormatToPHP(CLang::GetDateFormat("SHORT")), mktime(0,0,0,1,1,2003)), 
			"ACTIVE"=>"Y", 
			">=PROPERTY_DATE"=>$min_date_str,
			"<=PROPERTY_DATE"=>$max_date_str,
			
		);
		
		$res = CIBlockElement::GetList(array(), $arFilter, false, false, Array("IBLOCK_ID",'ID','PROPERTY_DATE'));
		
		$dates = array();
		
		while($ob = $res->GetNextElement())
		{
			$arProps = $ob->GetProperties();
			$dates[$arProps["DATE"]['VALUE']] = true;
		}
		
		$dates = array_keys($dates);
		
		
		
		
		
		
		$result_xml_data = $this->xml_data;
		
		foreach($dates as $date){
			
			if(isset($result_xml_data[$date])){
				
				unset($result_xml_data[$date]);
				//var_dump($date);
			}
			
		}
		
		
		
		
		
		foreach($result_xml_data as $date=>$items){
			
			foreach($items as $item){
				
				$this->addCurrencyOnDate($date,$item['CURRENCY'],$item['VALUE']);
				
			}
		}
		
		
		
		
		
		
		$created_count = $this->created_count;
		echo "</br>СОЗДАНО: $created_count элементов";
	}
	
	public function getCurrencyRateData($date_from, $date_to, $page_number=1, $page_count=200){
		
		
		
		
		$arFilter = Array(
			"IBLOCK_ID"=>6, 
			//">DATE_ACTIVE_FROM"=>date($DB->DateFormatToPHP(CLang::GetDateFormat("SHORT")), mktime(0,0,0,1,1,2003)), 
			"ACTIVE"=>"Y", 
			">=PROPERTY_DATE"=>$this->getFromDate($date_from),
			"<=PROPERTY_DATE"=>$this->getToDate($date_to),
			
		);
		
		$res = CIBlockElement::GetList(array('PROPERTY_DATE'=>'ASC','NAME'=>'ASC'), $arFilter, false, /*array('iNumPage'=>$page_number, 'nPageSize'=>$page_count)*/false, Array("IBLOCK_ID",'ID','NAME','PROPERTY_DATE_VALUE','PROPERTY_VALUE_VALUE'));
		
		$records = array();
		
		$names_array = array();
		
		$dates_array = array();
		
		while($ob = $res->GetNextElement())
		{
			$arFields = $ob->GetFields();
			$arProps = $ob->GetProperties();
			$records[] = array('CURRENCY'=>$arFields['NAME'], 'DATE'=>$arProps['DATE']['VALUE'], 'VALUE'=>$arProps['VALUE']['VALUE']);
			
			$names_array[$arFields['NAME']] = true;
			$dates_array[$arProps['DATE']['VALUE']] = true;
		}
		
		//var_dump($records);
		
		$result_table = array();
		
		foreach($names_array as $name=>$name_value){
			foreach($dates_array as $date=>$date_value){
				
				if(!isset($result_table[$date])){
					$result_table[$date] = array();
					
					$value = null;
					
					foreach($records as $record){
							if($record['CURRENCY'] == $name && $record['DATE'] == $date){
								$value = $record['VALUE'];
							}
					}
					
					$result_table[$date][$name] = $value; 
				} else {
					$value = null;
					
					foreach($records as $record){
							if($record['CURRENCY'] == $name && $record['DATE'] == $date){
								$value = $record['VALUE'];
							}
					}
					
					$result_table[$date][$name] = $value; 
				}
			
			}
		}
		
		return array('COLUMNS'=>array_keys($names_array), 'ROWS'=>array_keys($dates_array), 'RESULT'=>$result_table);
	}
	
	public function displayCurrencyRateData($date_from, $date_to, $page_number=1, $page_count=200){
		$rate_data = $this->getCurrencyRateData($date_from, $date_to, $page_number=1, $page_count=200);
		
		$html_str = '<div class="display-currency-rates" style="display:block;width:100%;">';
		$html_str .= '<div style="display:inline-block;width:10%;height:410px;overflow-y:scroll;">';
		
		$html_str .= '<table style="width:100%;">';
		
		$html_str .= '<tr style="display:inline-block;width:100%;"><td style="font-style:bold;font-size:8px;text-align:center;padding:10px;">'.'ДАТА'.'</td></tr>';
		
		foreach($rate_data['ROWS'] as $k=>$row){
			if($k < count($rate_data['ROWS']) - 1){
				$html_str .= '<tr style="display:inline-block;margin-top:-1px;"><td style="font-style:italic;font-size:8px;text-align:center;width:100%;padding:3px;">'.$row.'</td></tr>';
			} else {
				$html_str .= '<tr style="display:inline-block;margin-top:-1px;"><td style="font-style:italic;font-size:8px;text-align:center;width:100%;padding:3px;">'.$row.'</td></tr>';
			}
		}
		
		$html_str .= '</table>';
		
		
		$html_str .= '</div>';
		
		$html_str .= '<div style="width:90%;max-width:100%;display:inline-block;height:400px;overflow-x:scroll;overflow-y:scroll;"><table style="border-collapse: collapse;"><thead style="display: block; overflow: auto;color: #fff;background: #000;"><tr>';
		
		foreach($rate_data['COLUMNS'] as $column){
			$html_str .= '<th style="font-style:bold;font-size:10px;text-align:center;padding:5px;">'.$column.'</th>';
		}
		
		$html_str .= '</tr>';
		
		$html_str .= '</thead>';
		
		$html_str .= '<tbody style="display: block;height: 400px;background: pink;">';
		
		foreach($rate_data['RESULT'] as $date=>$result_row){
			//$html_str .= '<tr><td style="font-style:bold;font-size:8px;padding:3px;">'.$date.'</td>';
			$html_str .= '<tr>';
			foreach($result_row as $item){
				$html_str .= '<td style="font-style:italic;font-size:8px;padding:4.2px;">'.$item.'</td>';
			}
			$html_str .= '</tr>';
		}
		
		//$html_str .= '</tr>';
		
		$html_str .= '</tbody>';
		
		$html_str .= '</table></div>';
		
		$html_str .= '</div>';
		
		return $html_str;
		
	}
}

?>