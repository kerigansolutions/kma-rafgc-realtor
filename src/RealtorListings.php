<?php 
namespace KeriganSolutions\KMARealtor;

class RealtorListings extends Mothership
{
    protected $realtorInfo;
    public $realtorListings;
    protected $dir;
    protected $searchParams;
    protected $request;
    protected $searchableParams;

    public function __construct()
    {
        parent::__construct();
        $this->realtorInfo = KMARealtor::getRealtorInfo();
        $this->dir = dirname(__FILE__);
        add_action( 'admin_menu', [$this, 'createListingsPage'] );

        $this->searchParams = [
            'sort' => 'list_date|desc',
            'status' => [
                'active' => 'Active',
                'contingent' => 'Contingent'
            ]
        ];

        $this->request = (isset($_GET['q']) ? $_GET : null);

        $this->searchableParams = [
            'sort',
            'page'
        ];
    }

    public function getSort()
    {
        return isset($this->searchParams['sort']) ? $this->searchParams['sort'] : 'list_date|desc';
    }

    public function getCurrentRequest()
    {
        return json_encode($this->searchParams);
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

        $request = '';
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

        // $request = $request . '&page=' . get_query_var( 'page' );
        
        return $request;
    }

    public function createListingsPage()
    {
        add_submenu_page( 
            'index.php', 
            'My Listings', 
            'My Listings',        
            'manage_options', 
            'my-listings.php', 
            [$this, 'listingsPage'] ); 
    }

    public function listingsPage()
    {
        $listings = $this->getListingStats();
        include(wp_normalize_path($this->dir . '/templates/my-listings.php'));
    }

    public function getListings($hidestats = false)
    {
        if(!isset($this->realtorInfo['id'])){
            return false;
        }

        $apiCall = parent::callApi('agent-listings/' . $this->realtorInfo['id'] . '?q=' . ($hidestats ? '&nostats=true' : null) . ($this->request ? $this->makeRequest() : null));

        $response = json_decode($apiCall->getBody());

        return $response->data;
    }
    
    public function getSoldListings()
    {
        if(!isset($this->realtorInfo['id'])){
            return false;
        }

        $apiCall = parent::callApi('agent-sold/' . $this->realtorInfo['id']);
        $response = json_decode($apiCall->getBody());

        return $response->data;
    }

    public function getListingStats($limit = -1)
    {
        if(!isset($this->realtorInfo['id'])){
            return false;
        }

        $apiCall = parent::callApi('agent-listings/' . $this->realtorInfo['id'] . '?analytics=true&nostats=true');
        $response = json_decode($apiCall->getBody());

        $listings = (count($response->data) > $limit && $limit !== -1 ? array_slice($response->data,0,$limit) : $response->data); 

        return $listings;
    }

}