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


	$jsonObj = new StdClass();
	
	$jsonObj->result = ERROR;
	
	switch($action) {	
		case GET_BUILDING_NEARBYS:
			$gbNearbys = new GetBuildingNearbys();
			$response = $gbNearbys->tryGet($lat,$long);
			break;		
		default:
			break;
	}
	
	echo $response;
	
?>