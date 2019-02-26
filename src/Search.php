<?php 
namespace KeriganSolutions\KMARealtor;

class Search extends Mothership
{
    public $request;
    public $searchableParams;
    public $searchParams;
    public $results;

    public function __construct()
    {
        parent::__construct();

        $this->request = (isset($_GET['q']) ? $_GET : null);

        $this->searchableParams = [
            'area',
            'propertyType',
            'minPrice',
            'maxPrice',
            'beds',
            'baths',
            'sqft',
            'acreage',
            'sort',
            'page'
        ];

        $this->searchParams = [
            'omni'   => '',
            'sort' => 'list_date|desc',
            'status' => [
                'active' => 'Active',
                'contingent' => 'Contingent'
            ]
        ];

        $this->searchResults = [];

    }

    public function getSearchResults()
    {
        return $this->results;
    }

    public function getResultMeta()
    {
        return isset($this->results->meta->pagination) ? $this->results->meta->pagination : null;
    }

    public function getCurrentRequest()
    {
        return json_encode($this->searchParams);
    }

    public function getSort()
    {
        return isset($this->searchParams['sort']) ? $this->searchParams['sort'] : 'list_date|desc';
    }

    public function filterRequest()
    {
        if($this->request == null){
            return false;
        }
        
        foreach($this->request as $key => $var){
            if(in_array($key, $this->searchableParams)){
                $this->searchParams[$key] = $var;
            }
        }
    }

    public function makeRequest()
    {
        $this->filterRequest();

        $request = '?q';
        foreach($this->searchParams as $key => $var){
            if(is_array($var)){
                $request .= '&' . $key . '=';
                $i = 1;
                foreach($var as $k => $v){
                    $request .= $v . ($i < count($var) ? '|' : '');
                    $i++;
                }
            }else{
                if($var != '' && $var != 'Any'){
                    $request .= '&' . $key . '=' . $var;
                }
            }
        }

        $request = $request . '&page=' . get_query_var( 'page' );
        
        return $request;
    }

    public function getListings()
    {
        $apiCall = parent::callApi('search' . $this->makeRequest());
        $response = json_decode($apiCall->getBody());
        $this->results = $response;

        if(!isset($this->results->data)){
            return false;
        }

        return $this->results;
    }

    public function buildPagination()
    {
        if(!isset($this->results->data)){
            return false;
        }
        
        $pagination = new Pagination($this->getResultMeta(),$this->searchParams);
        return $pagination->buildPagination();
    }
}