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

            if(!isset( $this->listing->mls_account )) {
                return false;
            }

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
        $pathFragments = explode('?', $_SERVER['REQUEST_URI'], 2);
        $pathFragments = explode('listing/', $pathFragments[0]);
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

    /**
     * Checks whether Yoast is active
     * Returns Boolean
     */
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
        if(!isset($this->listing->mls_account)) {
            return null;
        }

        // override Yoast so we can use dynamic data
        if($this->yoastActive()){
            add_filter('wpseo_title', [$this, 'seoTitle']);
            add_filter('wpseo_metadesc', function () { return false; });
            add_filter('wpseo_canonical', function () { return false; });
            add_filter('wpseo_robots', function () { return false; });
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
        echo '<meta property="og:street-address" content="'.$this->listing->full_address.'"/>';
        echo '<meta property="og:locality" content="'.$this->listing->city.'"/>';
        echo '<meta property="og:region" content="'.$this->listing->state.'"/>';
        echo '<meta property="og:postal-code" content="'.$this->listing->zip.'"/>';
        echo '<meta property="og:country-name" content="USA"/>';
        echo '<meta property="place:location:latitude" content="'.$this->listing->location->lat.'"/>';
        echo '<meta property="place:location:longitude" content="'.$this->listing->location->long.'"/>';

        $this->ogImage();
    }

    /**
     * Breaks down the primary image from the mothership and pops out the url, height and width required for Facebook
     * Echos output directly
     */
    public function ogImage($url = null)
    {
        if(!isset($this->listing->media_objects->data[0]->url)) {
            return null;
        }

        $photoParts = getimagesize ( $this->listing->media_objects->data[0]->url );
        echo '<meta property="og:image" content="' .  $this->listing->media_objects->data[0]->url . '" />' . "\n";
        echo '<meta property="og:image:secure_url" content="' .  str_replace('http://','https://', $this->listing->media_objects->data[0]->url ) . '" />' . "\n";
        echo '<meta property="og:image:width" content="' .  $photoParts['0'] . '" />' . "\n";
        echo '<meta property="og:image:height" content="' .  $photoParts['1'] . '" />' . "\n";
    }

    /**
     * Sets the schema type to place. This may change in the future, but for now it's what Realtor.com uses.
     * @return String
     */
    public function ogType($type = null)
    {
        return "place";
    }

    /**
     * Returns the correct canonical URL
     * @return String
     */
    public function canonicalUrl($data = null)
    {
        return trailingslashit($_SERVER["REQUEST_URI"]);
    }

    /**
     * Returns a formatted page title with listing data
     * @return String $title
     */
    public function seoTitle($data = null)
    {

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

    /**
     * Returns a truncated meta description
     * @return String $text
     */
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

    /**
     * Returns a simplified string based on what property type is returned from the listing data. We do this for our SEO content.
     * @return String $type
     */
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
