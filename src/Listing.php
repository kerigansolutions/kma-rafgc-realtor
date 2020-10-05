<?php 
namespace KeriganSolutions\KMARealtor;

class Listing extends Mothership
{
    public $dir;
    public $slug;
    public $viewingListing;
    public $mlsNumber;
    public $listing;

    public function use()
    {
        $this->slug = 'listing';
        add_action( 'init', [$this,'addRewriteRule'] );
        add_filter( 'query_vars', [$this, 'addQueryVar'] );
        add_filter( 'template_include', [$this,'loadTemplate'] ) ;

        $this->setHooks();
    }

    public function get()
    {
        if( $this->mlsIsGood() ){  
            $this->listing = $this->getListing();  
            return $this->listing;
        }

        return false;
    }
    
    public function set($mlsNumber)
    {
        $this->mlsNumber = $mlsNumber;
    }

    public function setHooks()
    {
        add_action( 'rest_api_init', [$this, 'setEndpoints']);
    }

    public function mlsIsGood()
    {
        $pathFragments = explode('listing/', $_SERVER['REQUEST_URI']);
        $this->mlsNumber = str_replace('/','',end($pathFragments));
        
        if(strlen($this->mlsNumber) > 3 && is_numeric($this->mlsNumber)){            
            return true;
        }

        return false;
    }

    public function addRewriteRule()
    {
        add_rewrite_rule(
            '^listing/([0-9]+)/?',
            'index.php?mls=$matches[1]'
        );
    }

    public function addQueryVar($query_vars)
    {
        $query_vars[] = 'mls';
        return $query_vars;
    }

    public function loadTemplate($template)
    {
        if($this->mlsIsGood()){
            status_header(200, 'OK');
            $template = TEMPLATEPATH . '/page-listing.php';
        }
        return $template;
    }

    public function getListing()
    {
        $apiCall = parent::callApi('listing/' . $this->mlsNumber);
        $response = json_decode($apiCall->getBody());
        $this->listing = $response->data;
        $this->setSeo();

        return $this->listing;
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
        add_filter('wpseo_opengraph_title', [$this, 'seoTitle']); 
        add_filter('wpseo_twitter_title', [$this, 'seoTitle']); 
        add_filter('wpseo_metadesc', [$this, 'metaDescription']);
        add_filter('wpseo_canonical', [$this, 'setCanonical']);
        add_filter('wpseo_opengraph_image', function () { return null; });
        add_action('wpseo_add_opengraph_images', [$this, 'ogPhotos']);

        if(!$this->yoastActive()){
            add_filter('pre_get_document_title', [$this, 'seoTitle']);
            add_action( 'wp_head', [$this, 'setMeta']);
        }
    }

    public function seoTitle($data){
        
        $address = $this->listing->full_address;
        $price = ($this->listing->price != null ? '$' . number_format($this->listing->price) :
            (isset($this->listing->monthly_rent) ? '$' . number_format($this->listing->monthly_rent) . ' / mo.' : ''));
        $type = $this->fixType();
        $status = $this->listing->status;
        $area = $this->listing->area;

        //Default title
        $title = $type . ' for sale in ' . $area .' | ' . $price;

        if($status == 'Sold/Closed'){
            $title = 'SOLD | ' . $price . ' | ' . $address;
        }
        if($status == 'Contingent'){
            $title = 'CONTINGENT | ' . $price . ' | ' . $address;
        }
        if($status == 'Active' && $this->listing->price != null){
            $title = $type . ' for sale in ' . $area .' | ' . $price . ' | MLS# ' . $this->listing->mls_account;
        }elseif(isset($this->listing->monthly_rent)){
            $title = $type . ' for rent in ' . $area .' | ' . $price;
        }

        return $title;
    }

    public function fixType()
    {
        $type = $this->listing->prop_type;

        //Change just the ones that need to be changed
        switch ($type) {
            case 'Residential Lots/Land':
                $type = 'Land';
                break;
            case 'Improved Commercial':
                $type = 'Commercial property';
                break;
            case 'Real Estate & Business':
                $type = 'Property';
                break;
            case 'Unimproved Land':
                $type = 'Land';
                break;
            case 'Dup/Tri/Quad (Multi-Unit)':
                $type = 'Multi-Unit Property';
                break;
            case 'Detached Single Family':
                $type = 'House';
                break;
            case 'Mobile/Manufactured':
                $type = 'Mobile Home';
                break;
            case 'ASF (Attached Single Family)':
                $type = 'Townhome';
                break;
            case 'Apartments/Multi-Family':
                $type = 'Apartment';
                break;
            case 'Condominium':
                $type = 'Condo';
                break;
            case 'Condominium Rental':
                $type = 'Condo';
                break;
            case 'ASF (Attached Single Family) Rental':
                $type = 'Townhome';
                break;
            case 'Detached Single Family Rental':
                $type = 'House';
                break;
        }
        
        return $type;
    }

    public function setMeta()
    {
        echo '<meta name="description" content="'.$this->metaDescription().'" />';
    }

    public function setCanonical()
    {
        return $_SERVER['HTTP_HOST'] . $_SERVER["REQUEST_URI"];
    }

    public function metaDescription()
    {
        $metaLength = 130;
        $break = ' ';
        $pad = '...';
        $text = $this->listing->remarks;

        if($metaLength < strlen($text) && ($breakpoint = strpos($text, $break, $metaLength) !== false)) { 
            if($breakpoint < strlen($text) - 1) { 
                $text = substr($text, 0, $breakpoint) . $pad; 
            } 
        } 

        return $text;
    }

    public function ogPhotos(){
        $photos = $this->listing->media_objects->data;

        $photoParts = getimagesize ( $photos[0]->url );
        echo '<meta property="og:image" content="' .  $photos[0]->url . '" />' . "\n";
        echo '<meta property="og:image:secure_url" content="' .  str_replace('http://','https://' , $photos[0]->url) . '" />' . "\n";
        echo '<meta property="og:image:width" content="' .  $photoParts['0'] . '" />' . "\n";
        echo '<meta property="og:image:height" content="' .  $photoParts['1'] . '" />' . "\n";

        if(is_array($photos)){
            foreach($photos as $photo){
                echo '<meta property="og:image" content="' .  $photo->url . '" />' . "\n";
                echo '<meta property="og:image:secure_url" content="' .  str_replace('http://','https://' , $photo->url) . '" />' . "\n";
            }
        }

    }

    public function setEndpoints()
    {
        register_rest_route( 'kerigansolutions/v1', '/listing', array(
            'methods' => 'GET',
            'callback' => [$this, 'getAPIListing'],
            'permission_callback' => '__return_true'
        ) );
    }

    public function getAPIListing($request)
    {
        $mlsNumber = ($request->get_param( 'mls' ) !== null ? $request->get_param( 'mls' ) : null);

        if($mlsNumber){
            $apiCall = parent::callApi('listing/' . $mlsNumber);
            $data = json_decode($apiCall->getBody())->data;
            return rest_ensure_response($data);
        }
    }
}