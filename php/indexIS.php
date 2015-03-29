<?
// Load SDK 
require_once ('restapi-php-sdk-master/Immocaster/Sdk.php');

// Verbinden 
$sImmobilienScout24Key  =  'hackathonKey' ; 
$sImmobilienScout24Secret  =  'cyqg0uAVZbgDhXWXC4aa' ; 

$oImmocaster  =  Immocaster_Sdk :: getInstance( ' is24 ' , $sImmobilienScout24Key , $sImmobilienScout24Secret );

//Get live data
$oImmocaster->setRequestUrl('live');
//$oImmocaster->setRequestUrl('sandbox');*/

$oImmocaster->setContentResultType('json');
$oImmocaster->setStrictMode(true);

//Check if is enough
//$oImmocaster->authenticateWithoutDB(true);

//Get the info of the quarters of berlin
$aParameter = array('q'=>'Berlin'); 
$res = $oImmocaster->getRegions($aParameter);
$res = json_decode($res);

$geoCodesId = array();

//Get the geoCodeIds of every quarter
foreach ($res as $value) {
	foreach ($value as $regions) {	
		foreach ($regions->region as $item) {
			 array_push($geoCodesId,$item->geoCodeId);
		}
	}
}

//Get the exposeIds of every apartment of every Quarter
$exposeIds = array();
for($j = 0; $j < count($geoCodesId); $j++){
	$aParameter = array ( 
	  'geocodes'=>$geoCodesId[$j], 
	  'realestatetype' =>'flatshareroom'
	);
	$res2 = json_decode($oImmocaster->regionSearch($aParameter));
	foreach ($res2 as $value) {
		if($value->paging->numberOfHits > 0){
			if($value->paging->numberOfHits == 1){
				array_push($exposeIds,$value->strictEntry->{'@id'});
			}else{
				foreach ($value->strictEntry as $value2) {
					array_push($exposeIds,$value2->{'@id'});
				}
			}
		}
	}
}

//echo count($exposeIds);

//Get The needed information for each expose and store the apartments information in an array of apartments
$exposesArray = array();
for($k = 0; $k < count($exposeIds); $k++){
	$aParameter = array ('exposeid'=>$exposeIds[11]); 
	$res3 = json_decode($oImmocaster->getExpose($aParameter));
	//print_r($res3);
	$exposeInfo = array();

	foreach ($res3 as $value) {
		if(property_exists($value->realEstate->address, "wgs84Coordinate")){
			$exposeInfo["id"] = $value->{"@id"};
			$exposeInfo["title"] = $value->realEstate->title;
			$exposeInfo["address"] = new StdClass();
			$exposeInfo["address"]->street = $value->realEstate->address->street;
			$exposeInfo["address"]->houseNumber = $value->realEstate->address->houseNumber;
			$exposeInfo["address"]->postcode = $value->realEstate->address->postcode;
			$exposeInfo["address"]->quarter = $value->realEstate->address->quarter;
			$exposeInfo["address"]->latitude = $value->realEstate->address->wgs84Coordinate->latitude;
			$exposeInfo["address"]->longitude = $value->realEstate->address->wgs84Coordinate->longitude;
			$exposeInfo["balcony"] = $value->realEstate->balcony;
			$exposeInfo["rooms"] = $value->realEstate->flatShareSize;
			
			if(property_exists($value->realEstate, "floor")){
				$exposeInfo["floor"] = $value->realEstate->floor;
			}else{
				$exposeInfo["floor"] = 999;
			}

			$exposeInfo["guestToilet"] = $value->realEstate->guestToilet;
			$exposeInfo["size"] = $value->realEstate->totalSpace;
			$exposeInfo["baseRent"] = $value->realEstate->baseRent;
			$exposeInfo["totalRent"] = $value->realEstate->totalRent;
			$exposeInfo["pictures"] = array();
			
			if(property_exists($value->realEstate, "attachments")){
				if(count($value->realEstate->attachments[0]->attachment) > 1){
					foreach ($value->realEstate->attachments[0]->attachment as $image) {
						array_push($exposeInfo["pictures"], $image->urls[0]->url[1]->{"@href"});
					}	
				}else{
					array_push($exposeInfo["pictures"], $value->realEstate->attachments[0]->attachment->urls[0]->url[1]->{"@href"});
				}
			}
			array_push($exposesArray, $exposeInfo);
		}
	}
}

print_r($exposesArray);

?>