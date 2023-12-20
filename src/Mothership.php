<?php
namespace KeriganSolutions\KMARealtor;

use GuzzleHttp\Client;

class Mothership
{
    protected $base_url;
    public $endpoint;

    public function __construct()
    {
        $this->base_url = env('RETS_BASE', '');
    }

    protected function getEndpoint()
    {
        return $this->endpoint;
    }

    protected function callApi($endpoint, $method = 'GET')
    {
        $client = new Client([
            'base_uri' => $this->base_url,
            'http_errors' => false,
            'headers' => [
                'X-AUTH-TOKEN' => env('RETS_API_KEY', ''),
            ]
        ]);

        try {
            $data = $client->request($method, $endpoint);

        }catch(GuzzleHttp\Exception\BadResponseException $e){
            echo 'Error: ', $e->getMessage(), "\n";
            $data = false;

        }

        return $data;
    }
}
