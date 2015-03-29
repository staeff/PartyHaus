<?php
	// crea las variables recibidas por POST y les asigna el valor 
	$countPosts = count($_POST);
	$tags = array_keys($_POST); 
	$values = array_values($_POST);
	for($i=0;$i<$countPosts;$i++){ 
		$$tags[$i]=$values[$i]; 
	}
	
	// crea las variables recibidas por GET y les asigna el valor 
	$countGets = count($_GET);
	$tags = array_keys($_GET); 
	$values = array_values($_GET);
	for($i=0;$i<$countGets;$i++){ 
		$$tags[$i]=$values[$i]; 
	}
	
	include 'constants.php';
	include 'getBuildingNearbys.php';
	include 'getAppartments.php';
	include 'getImmoScout.php';


	$jsonObj = new StdClass();
	
	$jsonObj->result = ERROR;
	
	switch($action) {	
		case GET_IMMO_SCOUT:	
			$getImmoScout = new GetImmoScout();
			$response = $getImmoScout->tryGet();
			break;
		case GET_BUILDING_NEARBYS:
			$gbNearbys = new GetBuildingNearbys();
			$response = $gbNearbys->tryGet();
			break;		
		case GET_APPARTMENTS:
			$getAppartments = new GetAppartments();
			$response = $getAppartments->tryGet($north=52.52,$east=13.45,$south=52.40,$west=13.40, $bars=2, $liquor=3, $clubs=1,$atms=7,$supermarkets=8, $imbisses=10, $transport=4, $second_toilet=10, $balcony=5, $price=6);
		default:
			break;
	}
	
	echo json_encode($response);
	
?>