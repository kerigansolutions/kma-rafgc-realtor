<?php 
namespace KeriganSolutions\KMARealtor;

class FeaturedLists extends Mothership
{
    public $dir;
    public $featuredList;

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
        include(wp_normalize_path($this->dir . '/post-types/top-homes.php'));
        include(wp_normalize_path($this->dir . '/post-types/top-lots.php'));
    }

    protected function getFeaturedList($postType = 'top-home', $limit)
    {
        foreach(get_posts(['post_type' => $postType, 'posts_per_page' => $limit, 'orderby' => 'menu_order', 'order' => 'ASC']) as $post){
            $apiCall = parent::callApi('listing/' . get_field('mls_number', $post->ID));
            $data = json_decode($apiCall->getBody())->data;
            if($data){
                $this->featuredList[$post->menu_order] = $data;
                $this->featuredList[$post->menu_order]->post_title = $post->post_title;
                $this->featuredList[$post->menu_order]->menu_order = $post->menu_order;
            }
        }
    }

    public function getListings($postType = 'top-home', $limit = -1)
    {
        $this->getFeaturedList($postType, $limit);

        if(!is_array($this->featuredList)){
            return [];
        }

        return $this->featuredList;
    }

}