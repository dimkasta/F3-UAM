$f3->route('GET|POST /link',
    function($f3) {
		$url = "feed.xml";
		$xmlfile = file_get_contents($url);
		$fileContents = str_replace(array("\n", "\r", "\t"), '', $xmlfile);
		$fileContents = trim(str_replace('"', "'", $fileContents));
		$simpleXml = simplexml_load_string($fileContents, NULL, LIBXML_NOCDATA);
		$json = json_encode($simpleXml);
		$json = str_replace("@", "", $json);
		
		
		//header('Content-type: application/json');
		$object = json_decode($json);
		//echo json_encode($object);
		
		//echo json_encode($object->program->attributes->name) . ":" . count($object->program->product) . "<br />";
		
		//echo json_encode($object->program->product[1]);
		
		foreach($object->program->product as $prod)
		{
			echo "<div style=\"margin:10px; border:1px solid grey; border-radius:5px; width:500px; height:250px; background: url('" . $prod->image_url . "'); background-size: 100% auto; \">";
			echo "<div style='padding:10px;background-color:rgba(255,255,255,0.8); width:480px; position:relative; top:125px;'>";
			echo 	$prod->product_name;
			echo "</div>";
			echo "</div>";
			echo "";

		}
	}
	);
