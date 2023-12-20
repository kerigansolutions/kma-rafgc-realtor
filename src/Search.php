<?php
namespace KeriganSolutions\KMARealtor;

class Search extends Mothership
{
    public $request;
    public $searchableParams;
    public $searchParams = [];
    public $results;
    public $listing;
    public $searchResults = [];

    public function __construct()
    {
        parent::__construct();

        $this->request = (isset($_GET['q']) ? $_GET : null);

        $this->searchableParams = [
            'association', //association (bcar, cpar, or bcar|cpar)
            'omni', //omni
            'area',
            'sub_area',
            'region',
            'subdivision',
            'property_type',
            'status',
            'office',
            'agent',
            'co_agent',
            'construction',
            'waterfront',
            'waterview',
            'sale_type',
            'exclude_areas',
			'omni',
            'minPrice',
            'maxPrice',
            'beds',
			'baths',
			'built',
			'built_ago',
			'sqft',
			'acreage',
			'days',
			'is_waterfront',
			'is_waterview',
			'pool',
			'open_houses',
			'date_modified',
			'date_listed',
			'sold_date',
			'date_price_changed',
			'sort',
			'sortBy',
			'orderBy',
			'latitude',
			'longitude',
        ];

        $this->searchParams = [
            'association' => 'rafgc',
            'status' => ['Active','Contingent'],
			'sort'        => 'date_modified|desc',
        ];

        $this->searchResults = [];

    }

    public function setRequest($searchParams)
    {
        $this->searchParams = $searchParams;
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
        return isset($this->searchParams['sort']) ? $this->searchParams['sort'] : 'date_listed|desc';
    }

    public function enhanceTitle()
    {
        if(isset($this->searchParams['area']) && $this->searchParams['area'] != '' && $this->searchParams['area'] != 'Any'){
            $title = 'Searching properties in ' . $this->searchParams['area'];
        }else{
            $title = get_the_title();
        }

        return $title;
    }

    protected function yoastActive()
    {
        $active_plugins = get_option('active_plugins');
        foreach($active_plugins as $plugin){
            if(strpos($plugin, 'wp-seo')){
                return true;
            }
        }
        return false;
    }

    /**
     * Hook meta tags into WP lifecycle
     * Removes Yoast tags since they don't work well in this use case.
     */
    public function setSeo()
    {
        // override Yoast so we can use dynamic data
        if($this->yoastActive()){
            add_filter('wpseo_title', [$this, 'seoTitle']);
            add_filter('wpseo_metadesc', function () { return false; });
            add_filter('wpseo_canonical', function () { return false; });
            add_filter('wpseo_robots', function () {
				return "max-image-preview:large,max-snippet:-1,max-video-preview:-1";
			});
            add_filter('wpseo_opengraph_url', function () { return false; });
            add_filter('wpseo_opengraph_type', function () { return false; });
            add_filter('wpseo_opengraph_image', function () { return false; });
            add_filter('wpseo_opengraph_title', function () { return false; });
            add_filter('wpseo_opengraph_site_name', function () { return false; });
            add_filter('wpseo_opengraph_admin', function () { return false; });
            add_filter('wpseo_opengraph_author_facebook', function () { return false; });
            add_filter('wpseo_opengraph_show_publish_date', function () { return false; });
            add_filter('wpseo_twitter_description', function () { return false; });
            add_filter('wpseo_twitter_card_type', function () { return false; });
            add_filter('wpseo_twitter_site', function () { return false; });
            add_filter('wpseo_twitter_image', function () { return false; });
            add_filter('wpseo_twitter_creator_account', function () { return false; });
            add_filter('wpseo_json_ld_output', function () { return false; });
        }

        add_filter('pre_get_document_title', [$this, 'seoTitle']);
        add_action( 'wp_head', [$this, 'setStandardMeta']);
        add_action( 'wp_head', [$this, 'setTwitterCard']);
        add_action( 'wp_head', [$this, 'setOpenGraph']);

    }

    public function fixArea()
    {
        $area = (
            isset($this->searchParams['region']) &&
            $this->searchParams['region'] != '' &&
            $this->searchParams['region'] != 'Any' ?
            $this->searchParams['region'] : 'Gulf & Franklin County');

        return $area;
    }

    public function fixType()
    {
        $type = (isset($this->searchParams['property_type']) && $this->searchParams['property_type'] != '' ?
            $this->searchParams['property_type'] : 'Listings');

            switch ($type) {
                case 'AllHomes':
                    $type = 'Homes';
                    break;
                case 'AllLand':
                    $type = 'Lots & Land';
                    break;
                case 'MultiUnit':
                    $type = 'Muli-unit properties';
                    break;
                case 'Commercial':
                    $type = 'Commercial properties';
                    break;
                case 'Any':
                    $type = 'Properties';
                    break;
            }

        return $type;
    }

    /**
     * Returns a formatted page title with listing data
     * @return String $title
     */
    public function seoTitle(){
        $area = $this->fixArea();
        $type = $this->fixType();

        return $type . ' for sale in ' . $area;
    }

    /**
     * Returns a truncated meta description
     * @return String $text
     */
    public function metaDescription()
    {
        $area = $this->fixArea();
        $type = $this->fixType();

        return 'Browse all ' . strtolower($type) . ' for sale in ' . $area . '. Contact '.get_bloginfo().' to schedule a showing today!';
    }

    /**
     * Publishes standard meta description and canonical tags
     * Echos output directly
     */
    public function setStandardMeta()
    {
        echo '<meta name="description" content="'.$this->metaDescription().'" />';
        echo '<link rel="canonical" href="'.$this->canonicalUrl().'"/>';
    }

    /**
     * Publishes twitter meta tags for nice looking twitter cards
     * Echos output directly
     */
    public function setTwitterCard()
    {
        echo '<meta name="twitter:title" content="'.$this->seoTitle().'" />';
        echo '<meta name="twitter:card" content="summary_large_image" />';
        echo '<meta name="description" content="'.$this->metaDescription().'"/>';
        echo '<meta name="twitter:site" content="'.get_bloginfo('name').'"/>';
        echo '<meta name="twitter:creator" content="@BeachyBeachPCB">';
    }

    /**
     * Publishes Open Graph tags for nice looking Facebook snippets
     * Echos output directly
     */
    public function setOpenGraph()
    {
        echo '<meta property="og:site_name" content="'.get_bloginfo('name').'" />';
        echo '<meta property="og:title" content="'.$this->seoTitle().'" />';
        echo '<meta property="og:description" content="'.$this->metaDescription().'" />';
        echo '<meta property="og:url" content="'.$this->canonicalUrl().'" />';

        echo '<meta property="og:type" content="'.$this->ogType().'"/>';

        if(isset($this->searchParams['city']) && $this->searchParams['city'] != ''){
            echo '<meta property="og:locality" content="'.$this->searchParams['city'].'"/>';
        }

        echo '<meta property="og:region" content="FL"/>';
        echo '<meta property="og:country-name" content="USA"/>';

        // $this->ogImage();
    }

    /**
     * Breaks down the primary image from the mothership and pops out the url, height and width required for Facebook
     * Echos output directly
     */
    public function ogImage($url = null)
    {
        if(!isset($this->listing) || $this->listing->preferred_image == '' ) {
            return null;
        }

        $photoParts = getimagesize ( $this->listing->preferred_image );
        echo '<meta property="og:image" content="' .  $this->listing->preferred_image . '" />' . "\n";
        echo '<meta property="og:image:secure_url" content="' .  str_replace('http://','https://' ,$this->listing->preferred_image) . '" />' . "\n";
        echo '<meta property="og:image:width" content="' .  $photoParts['0'] . '" />' . "\n";
        echo '<meta property="og:image:height" content="' .  $photoParts['1'] . '" />' . "\n";
    }

    /**
     * Sets the schema type to place. This may change in the future, but for now it's what Realtor.com uses.
     * @return String
     */
    public function ogType($type = null)
    {
        return "website";
    }

    /**
     * Returns the correct canonical URL
     * @return String
     */
    public function canonicalUrl()
    {
        return trailingslashit($_SERVER["REQUEST_URI"]);
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
            if($key == 'pg'){
                $this->searchParams['page'] = $var;
            }
        }
    }

    public function makeRequest()
    {
        $this->filterRequest();
        $this->setSeo();

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

    public function rewriteStatus($statusArray)
	{
		if(!is_array($statusArray)) {
			return $statusArray;
		}

		$rewrite = [];

		foreach($statusArray as $status) {
			switch($status){
				case 'Under Contract':
					$rewrite[] = 'ActiveUnderContract|Active Under Contract|Contingent';
					break;
				case 'Contingent':
					$rewrite[] = 'ActiveUnderContract|Active Under Contract|Contingent';
					break;
				case 'Sold':
					$rewrite[] = 'Sold/Closed|Closed|Sold';
					break;
				case 'Closed':
					$rewrite[] = 'Sold/Closed|Closed|Sold';
					break;
				default:
					$rewrite[] = $status;
			}
		}

		return $rewrite;
	}

    public function setConstructionStatus($array)
	{
		if(!is_array($array)){
			$array = explode('|',$array);
		}

		$inputArray = [
			'Under Construction'  => 'Under Construction|UnderConstruction',
			'To Be Built'         => 'To Be Built|ToBeBuilt',
			'Updated/Remodeled'   => 'Updated/Remodeled|UpdatedRemodeled',
			'Fixer'               => 'Fixer',
		];

		$value = [];

		foreach($array as $item){
			if(isset($inputArray[$item])){
				$value[] = $inputArray[$item];
			}
		}

		return implode('|',$value);
	}

    public function getListings()
    {
        $apiCall = parent::callApi('listings' . $this->makeRequest());
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
