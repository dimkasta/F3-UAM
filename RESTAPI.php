<?php
	//REST AI Helper class
	class RESTAPI {
		
		public static function getObject() {
			$jsonObject =  new stdClass();//json_decode(json_encode(array(), JSON_FORCE_OBJECT));
			$jsonObject->errors =  new stdClass();//json_decode(json_encode(array(), JSON_FORCE_OBJECT));
			$jsonObject->messages =  new stdClass();//json_decode(json_encode(array(), JSON_FORCE_OBJECT));
			$jsonObject->data =  new stdClass();//json_decode(json_encode(array(), JSON_FORCE_OBJECT));
			$jsonObject->success = false;
			return $jsonObject;
		}
		
		public static function returnJSON($jsonObject) {
			header('Content-Type: application/json');
			echo json_encode($jsonObject);
		}
	}
?>
