/**
 * Used to retrieve data from the XML API in a SimpleXMLElement object.
 *
 * @param string $url
 *        	Example: https://api.eveonline.com/account/APIKeyInfo.xml.aspx
 * @param array $data
 *        	Example: array ( "keyID" => 1234, "vCode" => "asdfasdfasdfasdfasdfasdfasdf", "characterID" => 123456789)
 * @throws Exception
 */
function callAPI($url, array $data = array()) {

	// Certain aspects of the API key require a keyID and vCode.
	// First we validate that such section has been called,
	// then make sure that the keyID and vCode have been provided before populating the query.
	if (preg_match ( '/(\/account\/)|(\/char\/)|(\/corp\/)/', $url )) {
		if (empty ( $data )) {
			throw new Exception ( "No API keyID or verification code have been provided", 0, NULL );
		} else if (empty ( $data ['keyID'] )) {
			throw new Exception ( "No API keyID has been provided", 0, NULL );
		} else if (empty ( $data ['vCode'] )) {
			throw new Exception ( "No API verification code has been provided", 0, NULL );
		}
			
		// Build the URL query string.
		$url = sprintf ( "%s?%s", $url, http_build_query ( $data ) );
	}

	// Initialize curl
	$ch = curl_init ();

	// To please the API gods we contain a user agent
	// You should change this info..
	curl_setopt ( $ch, CURLOPT_USERAGENT, "YourWebsite Reader v0.1 / Contact mail: your@mail.address" );

	// Set the URL we're going to use
	curl_setopt ( $ch, CURLOPT_URL, $url );
	curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );

	// While using this is optional, it may be required if you encounter
	// the "SSL certificate problem: unable to get local issuer certificate"
	// error when calling the API using curl.
	// curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, 0 );

	$response = curl_exec ( $ch );

	// If there's a failure or an error, the function returns false.
	if (! $response) {
		// Could add some 404 headers here.
		$errorMessage = curl_error($ch);
		curl_close($ch);
		
		throw new Exception ( $errorMessage, - 1, NULL );
	}

	// Close curl resource to free up system resources.
	curl_close ( $ch );

	// Return the data as a SimpleXMLElement object.
	return new SimpleXMLElement($response);
}



/* Usage example */
// Obviously fake information, you should change this.
$keyData = array (
		"keyID" => 1234,
		"vCode" => "asdfasdfasdfasdfasdfasdfasdf" 
);

try {
	$xmlData = callAPI("https://api.eveonline.com/account/APIKeyInfo.xml.aspx", $keyData);
} catch (Exception $e) {
	echo $e->__toString();
}

echo "<pre>";
print_r ( $xmlData );
echo "</pre>";