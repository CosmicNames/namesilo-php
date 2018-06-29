<?php

namespace CosmicNames\NameSilo;

use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;

class NameSilo 
{

    const API_URL = 'https://www.namesilo.com/api/';
    const API_SANDBOX_URL = 'http://sandbox.namesilo.com/api/';

    /*
     * @var GuzzleHttp\Client
     */
    private $client;

    /**
     * List of API classes
     * @var array
     */
    private $operations = [];

    /**
     * Authentication info needed for every request
     * @var array
     */
    private $authentication = [];

    public function __construct($apiKey, $sandbox = false) 
    {
        $this->authentication = [
            'version' => 1,
            'type' => 'xml',
            'key' => $apiKey
        ];

        $this->client = new Client([
            'base_uri' => $sandbox ? self::API_SANDBOX_URL : self::API_URL,
            'defaults' => [
                'query' => $this->authentication
            ]
        ]);
    }

    /*private function _getOperation($operation) {
        if (empty($this->operations[$operation])) {
            $class = 'CosmicNames\\NameSilo\\Operation\\'. $operation;
            $this->operations[$operation] = new $class($this->client);
        }

        return $this->operations[$operation];
    }*/

     /**
     * Magic method who will call the NameSilo Api.
     *
     * @param string $operation operation name that will be called
     * @param array $arguments parameters that should be passed when calling API function
     *
     * @return mixed|\SimpleXMLElement result of called functions
     *
     * @since v1.0.0
     */
    public function __call($operation, $arguments) {
       if (count($arguments) > 0) {
           $arguments = $arguments[0];
       } 

       return $this->runQuery($operation, $arguments);
    }

    /**
     * The executor. It will run API operation and get the data.
     *
     * @param string $operation operation name that will be called.
     * @param string $arguments list of parameters that will be attached.
     *
     * @return mixed|\SimpleXMLElement results of API call
     *
     * @since v1.0.0
     */
    protected function runQuery($operation, $arguments) {
        try {
            $response = $this->client->post($operation, http_build_query($arguments, $this->authentication));
            return $this->parse($response);
        } catch(\Exception $error) {
            return $error;
        }
    }

    /**
     * @param ResponseInterface $response
     * @param string $type
     * @return mixed|\SimpleXMLElement
     * @throws \Exception
     */
    protected function parse(ResponseInterface $response, $type = 'xml')
    {
        switch ($type) {
            case 'xml':
                return simplexml_load_file((string)$response->getBody());
            default:
                throw new \Exception("Invalid response type");
        }
    }
}