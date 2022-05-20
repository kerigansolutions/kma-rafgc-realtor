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
        $this->setHooks();
    }

    protected function includePostType()
    {
        $this->dir = dirname(__FILE__);
        include(wp_normalize_path($this->dir . '/post-types/top-homes.php'));
        include(wp_normalize_path($this->dir . '/post-types/top-lots.php'));
    }

    protected function getWPData($postType = 'top-home', $limit = -1)
    {
        return get_posts(['post_type' => $postType, 'posts_per_page' => $limit, 'orderby' => 'menu_order', 'order' => 'ASC']);
    }

    protected function getFeaturedList($postType = 'top-home', $limit = -1)
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

    public function setHooks()
    {
        add_action( 'rest_api_init', [$this, 'setEndpoints']);
    }

    public function setEndpoints()
    {
        register_rest_route( 'kerigansolutions/v1', '/list', array(
            'methods' => 'GET',
            'callback' => [$this, 'getAPIList'],
            'permission_callback' => '__return_true'
        ) );
    }

    public function getAPIList($request)
    {
        $limit = ($request->get_param( 'limit' ) !== null ? $request->get_param( 'limit' ) : -1);
        $postType = ($request->get_param( 'type' ) !== null ? $request->get_param( 'type' ) : 'top-home');

        $output = [];

        $i = 0;
        foreach($this->getWPData($postType, $limit) as $item){
            if(get_post_meta($item->ID, 'mls_number', true)){
                $output[$i]['mls_number'] = get_post_meta($item->ID, 'mls_number', true);
                $output[$i]['menu_order'] = $item->menu_order;
                $output[$i]['post_title'] = $item->post_title;
                $i++;
            }
        }
        
        return rest_ensure_response($output);
    }

}