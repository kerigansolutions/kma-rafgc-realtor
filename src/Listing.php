<?php 
namespace Includes\Modules\KMARealtor;

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

        return $this->listing;
    }
}