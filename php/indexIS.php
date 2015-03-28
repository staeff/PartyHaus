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

/*$aParameter = array('q'=>'Ber'); 
$res =  $oImmocaster->getRegions($aParameter);

echo $res;*/

$aParameter = array( 'country-id'=>276,'region-id'=>3,'list'=>true ); 
$res = $oImmocaster->geoService($aParameter);

echo $res;

?>