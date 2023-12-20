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
        // New system doesnt support stats, and nobody uses it nayway, so remove.
        // add_action( 'admin_menu', [$this, 'createListingsPage'] );

        $this->searchParams = [
            'sort' => 'date_listed|desc',
            'agent' => isset($this->realtorInfo['id']) ? $this->realtorInfo['id'] : '',
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
        return isset($this->searchParams['sort']) ? $this->searchParams['sort'] : 'date_listed|desc';
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

        $params = $this->searchParams;
		// print_r($params); die();

		if(isset($params['status']) && is_array($params['status'])) {
			$newStatus = [];
			foreach($params['status'] as $status){
				switch ($status) {
					case 'Active':
						$newStatus[] = 'Active';
						break;
					case 'Contingent':
						$newStatus[] = 'Contingent|ActiveContingent|Active Contingent|Under Contract';
						break;
					case 'Sold':
						$newStatus[] = 'Sold|Sold/Closed|Closed/Sold|Closed';
						break;
					case 'Pending':
						$newStatus[] = 'Pending';
						break;
					case '':
						$newStatus[] = '';
					default:
						$newStatus[] = $status;
				}
			}
			$params['status'] = $newStatus;
		}

		if(isset($params['property_type'])) {
			switch ($params['property_type']) {
                case 'Detached Single Family':
                    $params['property_type'] = 'House';
                    break;
				case 'AllHomes':
                    $params['property_type'] = 'House|Condo|Multi|Manufactured';
                    break;
                case 'AllLand':
                    $params['property_type'] = 'Land';
                    break;
                case 'MultiUnit':
                    $params['property_type'] = 'Multi|Condo';
					break;
                case 'Commercial':
                    $params['property_type'] = 'Commercial';
                    break;
				case '':
					$params['property_type'] = '';
					break;
				default:
					$params['property_type'] = $params['property_type'];
			}
		}

        $request = [];
        foreach($params as $key => $var){
			$request[$key] = (is_array($var) ? implode('|',$var) : $var);
        }

        return '?' .  http_build_query($request);
    }

    // V2 Deprecated
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

    // V2 Deprecated
    public function listingsPage()
    {
        $listings = $this->getListingStats();
        include(wp_normalize_path($this->dir . '/templates/my-listings.php'));
    }

    public function getListings()
    {
        if(!isset($this->realtorInfo['id']) || $this->realtorInfo['id'] == ''){
            return false;
        }

        $apiCall = parent::callApi('listings' . $this->makeRequest());
        $response = json_decode($apiCall->getBody());

        return $response->data;
    }

    public function getSoldListings()
    {
        if(!isset($this->realtorInfo['id']) || $this->realtorInfo['id'] == ''){
            return false;
        }

        $this->searchParams['status'] = ['Sold'];
        $apiCall = parent::callApi('listings' . $this->makeRequest());
        $response = json_decode($apiCall->getBody());

        return $response->data;
    }

    // Deprecated
    public function getListingStats($limit = -1)
    {
        return [];
    }

}
