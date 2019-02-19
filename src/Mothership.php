<?php 
namespace KeriganSolutions\KMARealtor;

use GuzzleHttp\Client;

class Mothership
{
    protected $base_url;

    public function __construct()
    {
        $this->base_url = 'https://navica.kerigan.com/api/v1/';
    }

    protected function getEndpoint()
    {
        return $this->endpoint;
    }

    protected function callApi($endpoint, $method = 'GET')
    {
        $client = new Client([
            'base_uri' => $this->base_url,
            'http_errors' => false
        ]);
        
        try {
            $data = $client->request($method, $endpoint);

        }catch(GuzzleHttp\Exception\BadResponseException $e){
            echo 'Error: ', $e->getMessage(), "\n";
            echo 'ouch!';
            $data = '';

        }

        return $data;
    }
}