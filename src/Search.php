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
            'omni',
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

    public function setSeo()
    {
        
        add_filter('wpseo_title', [$this, 'seoTitle']);
        add_filter('wpseo_metadesc', [$this, 'metaDescription']);
        add_filter('wpseo_canonical', [$this, 'setCanonical']);
        
        if(!$this->yoastActive()){
            add_filter('pre_get_document_title', [$this, 'seoTitle']);
            add_action( 'wp_head', [$this, 'setMeta']);
        }
    }

    public function fixArea()
    {
        $area = (isset($this->searchParams['area']) && $this->searchParams['area'] != '' ?
            $this->searchParams['area'] : 'Gulf & Franklin County');

        return $area;
    }

    public function fixType()
    {
        $type = (isset($this->searchParams['propertyType']) && $this->searchParams['propertyType'] != '' ?
            $this->searchParams['propertyType'] : 'Listings');
        
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

    public function seoTitle($data){
        $area = $this->fixArea();
        $type = $this->fixType();

        return $type . ' for sale in ' . $area;
    }

    public function setMeta()
    {
        echo '<meta name="description" content="'.$this->metaDescription().'" />';
    }

    public function setCanonical()
    {
        $area = (isset($this->searchParams['area']) && $this->searchParams['area'] != '' ?
            $this->searchParams['area'] : 'Any');

        $type = (isset($this->searchParams['propertyType']) && $this->searchParams['propertyType'] != '' ?
            $this->searchParams['propertyType'] : 'Any');

        return WP_HOME . parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH) . '?q=search&area=' . $area . '&propertyType=' . $type;
    }

    public function metaDescription()
    {
        $area = $this->fixArea();
        $type = $this->fixType();

        return 'Browse all ' . $type . ' for sale in ' . $area . '. Contact '.get_bloginfo().' to schedule a showing today!';
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
        $this->setSeo();

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