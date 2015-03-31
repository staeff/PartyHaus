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
			$response = $getAppartments->tryGet($north,$east,$south,$west, $bars, $liquor, $clubs,$atms,$supermarkets, $imbisses, $transport, $second_toilet, $balcony, $price);
		default:
			break;
	}
	
	echo json_encode($response);
	
?>