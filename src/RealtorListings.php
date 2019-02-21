<?php 
namespace KeriganSolutions\KMARealtor;

class RealtorListings extends Mothership
{
    protected $realtorInfo;
    public $realtorListings;
    protected $dir;

    public function __construct()
    {
        parent::__construct();
        $this->realtorInfo = KMARealtor::getRealtorInfo();
        $this->dir = dirname(__FILE__);
        add_action( 'admin_menu', [$this, 'createListingsPage'] );

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

        $apiCall = parent::callApi('agent-listings/' . $this->realtorInfo['id'] . ($hidestats ? '?nostats=true' : null));

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