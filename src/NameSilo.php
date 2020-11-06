<?php

namespace CosmicNames\NameSilo;

class NameSilo 
{

    const API_URL = 'https://www.namesilo.com/api/';
    const API_SANDBOX_URL = 'http://sandbox.namesilo.com/api/';
	const API_BATCH = 'https://www.namesilo.com/apibatch/';

    /**
     * Authentication info needed for every request
     * @var array
     */
    private $authentication = [];

	/**
	 * @var
	 */
    private $base_uri;

    public function __construct($apiKey, $sandbox = false, $batch = true)
    {
        $this->authentication = [
            'version' => 1,
            'type' => 'xml',
            'key' => $apiKey
        ];

        $this->base_uri = $sandbox ? self::API_SANDBOX_URL : ($batch ? self::API_BATCH : self::API_URL);

    }

     /**
     * Magic method who will call the NameSilo Api.
     *
     * @param string $operation operation name that will be called
     * @param array $arguments parameters that should be passed when calling API function
     *
     * @return mixed|\SimpleXMLElement result of called functions
     *
     * @since v1.0.0
     * @throws \Exception
     */
    public function __call($operation, $arguments = []) {
    	if (count($arguments) > 0) {
    		$arguments = $arguments[0];
	    }

       return $this->runQuery($operation, $arguments);
    }

    /**
     * The executor. It will run API operation and get the data.
     *
     * @param string $operation operation name that will be called.
     * @param array $arguments list of parameters that will be attached.
     *
     * @return mixed|\SimpleXMLElement results of API call
     *
     * @since v1.0.0
     * @throws \Exception
     */
    protected function runQuery($operation, $arguments = []) {
	    //  Initiate curl
	    $ch = curl_init();

	    // Disable SSL verification
	    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

	    // Will return the response, if false it print the response
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

	    // Set the url
	    curl_setopt($ch, CURLOPT_URL, $this->base_uri . $operation .'?'. http_build_query(array_merge($arguments, $this->authentication)));

	    // Execute
	    $result = curl_exec($ch);

	    // Close curl session
	    curl_close($ch);

	    //decode our xml data
	    return $this->parse($result);
    }

    /**
     * @param $result
     * @param string $type
     * @return mixed|\SimpleXMLElement
     * @throws \Exception
     */
    protected function parse($result, $type = 'xml')
    {
        switch ($type) {
            case 'xml':
                return simplexml_load_string($result);
            default:
                throw new \Exception("Invalid response type");
        }
    }
}