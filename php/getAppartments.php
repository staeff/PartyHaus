<?php
class GetAppartments {

	var $jsonObj;

	function __construct() {
	}
		
	public function tryGet($north, $west, $south, $east, $bars,$liquor,$clubs,$atms,$supermarkets, $imbisses, $transport, $second_toilet, $balcony, $price) {		
		
		//include 'constants.php';
		include 'opendb.php';

		$object = array();
		$this->jsonObj = new StdClass();
		try{



		    $query = "SELECT *, 1 * (CASE WHEN atms >= 1 THEN 1 ELSE 1-(0.2/$atms) END) * (CASE WHEN bars >= 1 THEN 1 ELSE 1-(0.2/$bars) END)
				* (CASE WHEN night_clubs >= 1 THEN 1 ELSE 1-(0.2/$clubs) END) * (CASE WHEN liquor_stores >= 1 THEN 1 ELSE 1-(0.2/$liquor) END)
				* (CASE WHEN supermarkets >= 1 THEN 1 ELSE 1-(0.2/$supermarkets) END) * (CASE WHEN imbisses >= 1 THEN 1 ELSE 1-(0.2/$imbisses) END)
				* (CASE WHEN transport >= 1 THEN 1 ELSE 1-(0.2/$transport) END) * (CASE WHEN second_toilet THEN 1 ELSE 1-(0.2/$second_toilet) END)
				* (CASE WHEN balcony THEN 1 ELSE 1-(0.2/$balcony) END)
				* (CASE WHEN price_per_m2 >= 20 THEN 0 WHEN price_per_m2 <= 10 THEN 1 ELSE ((20/(price_per_m2+0.01))-1)*(1-(0.2/$liquor)) END) AS score
				FROM (
				 SELECT a.id, a.coordinates, a.address, a.balcony, a.build_in, a.size, a.total_rent, a.second_toilet, a.checked, a.total_rent/(a.size+0.001) AS price_per_m2, a.number, a.street, a.postcode, a.quarter, a.images, a.title, a.rooms, a.floor, a.base_rent,
				 count(case when t.name='atm' then 1 end) AS atms, count(case when t.name='bar' then 1 end) AS bars, 
				 count(case when t.name='night_club' then 1 end) AS night_clubs, count(case when t.name='liquor_store' then 1 end) AS liquor_stores
				 , count(case when t.name='grocery_or_supermarket' then 1 end) AS supermarkets, count(case when t.name='meal_takeaway' then 1 end) AS imbisses
				 , count(case when t.name='bus_station' or t.name='subway_station' or t.name='train_station' then 1 end) AS transport
				 FROM appartments a 
				 LEFT JOIN app_poi ap ON (ap.app_id = a.id)
				 LEFT JOIN poi p ON (p.id = ap.poi_id)
				 LEFT JOIN poi_type pt ON (p.id = pt.poi_id)
				 LEFT JOIN type t ON (pt.type_id = t.id)
				 WHERE '($north,$west) , ($north,$east) , ($south,$east) , ($south,$west)'::polygon @> a.coordinates AND 
				 a.checked IS NOT NULL
				 GROUP BY a.id) foo
				ORDER BY score DESC
				LIMIT 20;

				";
			$result = pg_query($query);
			if (!$result) {
				echo "Problem with query " . $query . "<br/>";
				echo pg_last_error();
				exit();
			}
			// fetch for every appartment from the db google api results
			while($myrow = pg_fetch_assoc($result)) {

				$images = explode('|', $myrow['images']);
				$coordinates = explode(',',str_replace('(','',str_replace(')','',$myrow['coordinates'])));
				$object[] = array(
					'type' => 'Feature',
					'properties' => array(
						'id' => $myrow['id'],
						'houseNumber' => $myrow['number'],
						'postcode' => $myrow['postcode'],
						'quarter' => $myrow['quarter'],
						'street' =>$myrow['street'],
						'title' => $myrow['title'],
						'base_rent' => $myrow['base_rent'],
						'total_rent' => $myrow['total_rent'],
						'total_size' => $myrow['size'],
						'floor' => $myrow['floor'],
						'score' => $myrow['score'],
						'rooms' => $myrow['rooms'],
						'second_toilet' => $myrow['second_toilet'],
						'balcony' => $myrow['balcony'],
						'images' => $images
						),
					'geometry' => array(
						'type' => 'Point',
						'lat' => $coordinates[0],
						'lon' => $coordinates[1]
						)
					);
				$id = $myrow['id'];
				$number = $myrow['number'];
				$app_coordinates = explode(',', str_replace('(','',str_replace(')','',$myrow['coordinates'])));
				$lat = $app_coordinates[0];
				$long = $app_coordinates[1];
				}
			
			include 'closedb.php';

			$this->jsonObj = $object;

		} catch (Exception $e) {
			$this->jsonObj->result = $e->getMessage();
		}		
		
		return $this->jsonObj;		
	}
	
}
?>