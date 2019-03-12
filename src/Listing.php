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
        add_filter('the_posts',array($this,'createTemplate'));
    }

    public function get()
    {
        if($this->getMlsNumber()){
            $this->listing = $this->getListing();
        }
        return $this->listing;
    }

    public function getMlsNumber()
    {
        $pathFragments = explode('listing/',$_SERVER['REQUEST_URI']);
        $this->mlsNumber = str_replace('/','',end($pathFragments));

        if(strlen($this->mlsNumber) > 3 && is_numeric($this->mlsNumber)){
            return true;
        }

        return false;
    }

    public function createTemplate($posts)
    {
        global $wp,$wp_query;

        //check if user is requesting our listings page
        if($this->getMlsNumber()){

            $this->getListing();

            $post = new \stdClass;
            $post->post_author = 1;
            $post->post_name = $this->slug;
            $post->guid = get_bloginfo($this->slug);
            $post->post_title = $this->listing->full_address;
            $post->post_content = null;
            $post->ID = -42;
            $post->post_status = 'static';
            $post->comment_status = 'closed';
            $post->ping_status = 'closed';
            $post->comment_count = 0;
            $post->post_date = current_time('mysql');
            $post->post_date_gmt = current_time('mysql',1);
            $posts = NULL;
            $posts[] = $post;
            $wp_query->is_page = true;
            $wp_query->is_singular = true;
            $wp_query->is_home = false;
            $wp_query->is_archive = false;
            $wp_query->is_category = false;
            unset($wp_query->query["error"]);
            $wp_query->query_vars["error"]="";
            $wp_query->is_404 = false;
        }

        return $posts;
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
        return WP_HOME . parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH) . $this->listing->mls_account;
    }

    public function metaDescription()
    {
        $metaLength = 130;
        $break = ' ';
        $pad = '...';
        $text = $this->listing->remarks;

        if(false !== ($breakpoint = strpos($text, $break, $metaLength))) { 
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
}