<?php
class GetBuildingNearbys {

	var $jsonObj;

	function __construct() {
	}
		
	public function tryGet() {		
		
		include 'constants.php';
		include 'opendb.php';

		$this->jsonObj = new StdClass();
		try{

			$response = "";


		    /*$query = "SELECT a.id, a.coordinates, a.address, a.balcony, a.build_in, a.size, a.total_rent, a.second_toilet, a.checked
				FROM appartments a
				WHERE --'(52.5008987,13.4265401) , (52.5036417,13.4369256) , (52.4974498,13.4583827) , (52.4938703,13.4346076)'::polygon @> a.coordinates AND
				 a.checked IS NULL;

				";*/
		    $query = "SELECT a.id, a.coordinates, a.address, a.balcony, a.build_in, a.size, a.total_rent, a.second_toilet, a.checked
				FROM appartments a
				WHERE a.checked IS NULL;

				";
			$result = pg_query($query);
			if (!$result) {
				echo "Problem with query " . $query . "<br/>";
				echo pg_last_error();
				exit();
			}
			// fetch for every appartment from the db google api results
			while($myrow = pg_fetch_assoc($result)) {
				$app_id = $myrow['id'];
				$app_coordinates = split(',', str_replace('(','',str_replace(')','',$myrow['coordinates'])));
				$lat = $app_coordinates[0];
				$long = $app_coordinates[1];

				for($i = 0; $i < 9; $i++){

					$response = $this->getInfo($lat,$long,$googlePlacesTypes[$i],$googlePlacesDistances[$i]);
					$store = $this->insertInfo($response,$app_id);
				}
			}
			include 'closedb.php';
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

	public function insertInfo($response,$app_id){

		$json = json_decode($response);
		foreach($json->results as $item)
		{
		    $lat = pg_escape_string($item->geometry->location->lat);
		    $lng = pg_escape_string($item->geometry->location->lng);
		    $icon = pg_escape_string($item->icon);
		    $id = pg_escape_string($item->id);
		    $name = pg_escape_string($item->name);
		    $vicinity = pg_escape_string($item->vicinity);
		    
		    // store the poi in db

		    $query = "WITH s AS (
		    	SELECT id FROM poi WHERE foreign_id = '$id'
		    	), i AS (
		    		INSERT INTO poi (source, source_id, coordinates, NAME, address, foreign_id)
					SELECT 'api', 'google', '$lat,$lng', '$name', '$vicinity', '$id'
					WHERE NOT EXISTS (SELECT foreign_id FROM poi WHERE foreign_id = '$id')
					RETURNING id
				)
				SELECT id FROM s
				UNION ALL
				SELECT id FROM i
		    		;
				";
			$result = pg_query($query);
			if (!$result) {
				echo "Problem with query " . $query . "<br/>";
				echo pg_last_error();
				exit();
			}

			while($myrow = pg_fetch_assoc($result)) {
				$poi_id = $myrow['id'];
			}

			// store app_poi into db

		    $query = "INSERT INTO app_poi (app_id, poi_id)
					SELECT '$app_id', '$poi_id'
					WHERE NOT EXISTS (SELECT app_id FROM app_poi WHERE app_id = '$app_id' AND poi_id = '$poi_id');
				";
			$result = pg_query($query);
			if (!$result) {
				echo "Problem with query " . $query . "<br/>";
				echo pg_last_error();
				exit();
			}

		    // store the types of poi in db
		    

		    foreach ($item->types as $type) {
		    	if(in_array($type, array ('atm','bar','bus_station', 'grocery_or_supermarket','liquor_store','meal_takeaway','night_club','subway_station','train_station'))){
			    	$type = pg_escape_string($type);

			    	$query = "WITH s AS (
			    	SELECT id FROM type WHERE name = '$type'
			    	), i AS (
			    		INSERT INTO type (name)
						SELECT '$type'
						WHERE NOT EXISTS (SELECT id FROM type WHERE name = '$type')
						RETURNING id
					)
					SELECT id FROM s
					UNION ALL
					SELECT id FROM i;
					";
					$result = pg_query($query);
					if (!$result) {
						echo "Problem with query " . $query . "<br/>";
						echo pg_last_error();
						exit();
					}

					while($myrow = pg_fetch_assoc($result)) {
						$type_id = $myrow['id'];
					}
				    $query = "INSERT INTO poi_type (poi_id, type_id)
							SELECT '$poi_id', '$type_id'
							WHERE NOT EXISTS (SELECT poi_id FROM poi_type WHERE poi_id = '$poi_id' AND type_id = '$type_id');
						";
					$editions = pg_query($query);
					if (!$editions) {
						echo "Problem with query " . $query . "<br/>";
						echo pg_last_error();
						exit();
					}
				}
		    }
		}

		// set the appartment as checked

	    $query = "UPDATE appartments 
	    SET checked = NOW()
	    WHERE id = '$app_id';";
		$editions = pg_query($query);
		if (!$editions) {
			echo "Problem with query " . $query . "<br/>";
			echo pg_last_error();
			exit();
		}
	}
}
?>