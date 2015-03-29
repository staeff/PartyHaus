<?php
class GetImmoScout {

	var $jsonObj;

	function __construct() {
	}
		
	public function tryGet() {		
		
		//include 'constants.php';

		$this->jsonObj = new StdClass();

		try{
			// Load SDK 
			require_once ('restapi-php-sdk-master/Immocaster/Sdk.php');

			$oImmocaster  =  Immocaster_Sdk :: getInstance( ' is24 ' , IS_KEY , IS_SECRET );

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
			for($j = 0; $j < count($geoCodesId); $j++){ // //////////////////////////////////////////////
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
				$aParameter = array ('exposeid'=>$exposeIds[$k]); 
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
			//echo '<pre>'.print_r($exposesArray, TRUE).'</pre>';
			$this->jsonObj->result = "goood!";
			$this->inserISData($exposesArray);
		}catch (Exception $e) {
			$this->jsonObj->result = $e->getMessage();
		}

		return $this->jsonObj;	
	}

	private function inserISData($data){

		include 'opendb.php';



		foreach ($data as $apt) {
			$images = '';
			foreach ($apt['pictures'] as $img) {
				$images .= $img.'|';
			}
			$images = substr($images, 0, -1);
			if(!isset($apt['size'])||$apt['size']>!0)
				$apt['size'] = 0;

	    	$query = "INSERT INTO appartments (id, coordinates, balcony, size, total_rent, base_rent, second_toilet, number, street, postcode, quarter, title, rooms, floor, images)";
			$query .= "SELECT '".$apt['id']."', '".$apt['address']->latitude.",".$apt['address']->longitude."', CASE WHEN '".$apt['balcony']."' = 'YES' THEN true ELSE false END, ".$apt['size'].", ".$apt['totalRent'].", ".$apt['baseRent'].", CASE WHEN '".$apt['guestToilet']."' = 'NOT_APPLICABLE' OR '".$apt['guestToilet']."' = 'NO' THEN false ELSE true END, '".$apt['address']->houseNumber."', '".pg_escape_string($apt['address']->street)."', ".$apt['address']->postcode.", '".pg_escape_string($apt['address']->quarter)."', '".pg_escape_string($apt['title'])."', ".$apt['rooms'].", ".$apt['floor'].", '".pg_escape_string($images)."'
			WHERE NOT EXISTS (SELECT id FROM appartments WHERE id = '".$apt['id']."'),";
			$query = substr($query, 0, -1);
			$query .= ";";
		$result = pg_query($query);
		if (!$result) {
			echo "Problem with query " . $query . "<br/>";
			echo pg_last_error();
			exit();
		}
		}

	    /*$query = "INSERT INTO appartments (id, coordinates, address, balcony, build_in, size, total_rent, second_toilet, number, street, postcode, quarter, title, rooms, floor) VALUES
			(SELECT '09809098', '52.49705,13.43266', 'Wiener Straße 25, 10999 Berlin', true, 1930, 89.3, 985.78, true, '25', 'Wiener Straße', 10999, 'Kreuzberg', 'Die schönste WG Berlins', 3, 2)
			WHERE NOT EXISTS (SELECT id FROM appartments WHERE id = '09809098')
			RETURNING id;

			";*/
		// fetch for every appartment from the db google api results
		while($myrow = pg_fetch_assoc($result)) {
			$app_id = $myrow['id'];
		}
		include 'closedb.php';
	}
}
?>