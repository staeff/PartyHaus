<?php
class GetBuildingNearbys {

	var $jsonObj;

	function __construct() {
	}
		
	public function tryGet($lat, $long) {		
		
		include 'constants.php';

		$this->jsonObj = new StdClass();
		try{

			$response = "";

			for($i = 0; $i < 1; $i++){

				$response .= $this->getInfo($lat,$long,$googlePlacesTypes[$i],$googlePlacesDistances[$i]);
			}

			$this->jsonObj = $response;

		} catch (Exception $e) {
			$this->jsonObj->result = $e->getMessage();
		}		
		
		return $this->jsonObj;		
	}

	public function getInfo($lat,$long,$type,$distance){
		$response = file_get_contents('https://maps.googleapis.com/maps/api/place/nearbysearch/json?location='
 		. $lat .',' . $long .'&radius='. $distance .'&types=' . $type .'&sensor=false&key=AIzaSyDjXm320eJ13lKiFVJ-THrIUqBzZaf1OTU', false, $context);

 		return $response;
	}
}
?>