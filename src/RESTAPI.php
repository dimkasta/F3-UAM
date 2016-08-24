<?php
	//REST API Helper class
	class RESTAPI {
		
		public static function getObject() {
			$jsonObject =  new stdClass();
			$jsonObject->errors =  new stdClass();
			$jsonObject->messages =  new stdClass();
			$jsonObject->data =  new stdClass();
			$jsonObject->success = false;
			return $jsonObject;
		}
		
		public static function returnJSON($jsonObject) {
			header('Content-Type: application/json');
			echo json_encode($jsonObject);
		}
	}
?>
