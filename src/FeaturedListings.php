<?php
namespace KeriganSolutions\KMARealtor;

class FeaturedListings extends Mothership
{
    public $dir;
    public $featuredListings;

    public function __construct()
    {
        parent::__construct();
    }

    public function use()
    {
        $this->includePostType();
    }

    protected function includePostType()
    {
        $this->dir = dirname(__FILE__);
        include(wp_normalize_path($this->dir . '/post-types/featured-listing.php'));
    }

    protected function getFeaturedList($limit)
    {
        foreach(get_posts(['post_type' => 'featured-listing', 'posts_per_page' => $limit]) as $post){
            $this->featuredListings[] = $post->post_title;
        }
    }

    public function getListings($limit = -1)
    {
        $this->getFeaturedList($limit);

        if(!is_array($this->featuredListings)){
            return [];
        }

        $apiCall = parent::callApi('listings?mls=' . implode('|',$this->featuredListings));
        $response = json_decode($apiCall->getBody());
        return $response->data;
    }

}
