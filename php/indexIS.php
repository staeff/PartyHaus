<?
// Load SDK 
require_once ('restapi-php-sdk-master/Immocaster/Sdk.php');

// Verbinden 
$sImmobilienScout24Key  =  'hackathonKey' ; 
$sImmobilienScout24Secret  =  'cyqg0uAVZbgDhXWXC4aa' ; 

//moyanotomasi account
//$sImmobilienScout24Key  =  'PartyHaus2Key' ; 
//$sImmobilienScout24Secret  =  'hgE1dh8WN1kbV0TE' ; 


$oImmocaster  =  Immocaster_Sdk :: getInstance( ' is24 ' , $sImmobilienScout24Key , $sImmobilienScout24Secret );

//Get live data
$oImmocaster->setRequestUrl('live');
//$oImmocaster->setRequestUrl('sandbox');*/

$oImmocaster->setContentResultType('json');
$oImmocaster->setStrictMode(true);

//Check if is enough
//$oImmocaster->authenticateWithoutDB(true);

$aParameter = array('q'=>'Berlin'); 
$res = $oImmocaster->getRegions($aParameter);
$res = json_decode($res);

$geoCodesId = array();

foreach ($res as $value) {
	foreach ($value as $regions) {	
		foreach ($regions->region as $item) {
			 array_push($geoCodesId,$item->geoCodeId);
		}
	}
}
//for($j = 0; $j < count($geoCodesId); $j++){
	$aParameter = array ( 
	  'geocodes'=>$geoCodesId[0], 
	  'realestatetype' =>'flatshareroom'
	);

	$res2 = json_decode($oImmocaster->regionSearch($aParameter));
	$exposeIds = array();

	foreach ($res2 as $value) {
		foreach ($value->strictEntry as $value2) {
			array_push($exposeIds,$value2->{'@id'});
		}
	}
//}
$aParameter = array ('exposeid'=>$exposeIds[0]); 
$res3 = json_decode($oImmocaster->getExpose($aParameter));
$exposeInfo = array();

foreach ($res3 as $value) {
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
	$exposeInfo["floor"] = $value->realEstate->floor;
	$exposeInfo["guestToilet"] = $value->realEstate->guestToilet;
	$exposeInfo["size"] = $value->realEstate->totalSpace;
	$exposeInfo["baseRent"] = $value->realEstate->baseRent;
	$exposeInfo["totalRent"] = $value->realEstate->totalRent;
	$exposeInfo["pictures"] = array();
	
	foreach ($value->realEstate->attachments[0]->attachment as $image) {
		array_push($exposeInfo["pictures"], $image->urls[0]->url[1]->{"@href"});
	}
}

?>