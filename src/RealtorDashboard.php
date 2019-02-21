<?php 
namespace KeriganSolutions\KMARealtor;

class RealtorDashboard
{
    protected $realtorInfo;

    public function __construct()
    {
        $this->realtorInfo = KMARealtor::getRealtorInfo();
        add_action( 'wp_dashboard_setup', array( $this, 'addDashboardWidgets' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueueAdmin') );
    }

    public function enqueueAdmin()
    {
        wp_register_style( 'am_admin_bootstrap', get_template_directory_uri() . '/assets/styles/admin.css' );
        wp_enqueue_style( 'am_admin_bootstrap');
    }

    public function addDashboardWidgets()
    {
        wp_add_dashboard_widget(
            'info_dashboard_widget',      // Widget slug.
            'My Information',                    // Title.
            [ $this, 'addInfoWidget'] // Display function.
        );
        
        wp_add_dashboard_widget(
            'leads_dashboard_widget',      // Widget slug.
            'My Leads',                    // Title.
            [ $this, 'addLeadsWidget'] // Display function.
        );

        wp_add_dashboard_widget(
            'listings_dashboard_widget',      // Widget slug.
            'My Listings',                    // Title.
            [ $this, 'addListingsWidget'] // Display function.
        );
    }

    public function addLeadsWidget()
    {

        echo '<div class="row mt-4">';

        echo '<div class="col-6 text-center"><span class="display-3 text-primary font-weight-bold">'.count($this->getLeads(-1,'publish')).'</span><br><small class="text-muted">Active Leads</small></div>';

        echo '<div class="col-6 text-center"><span class="display-3 text-primary font-weight-bold">'.count($this->getLeads(-1,'trash')).'</span><br><small class="text-muted">Archived Leads</small></div>';

        echo '</div>';

        foreach($this->getLeads(5) as $lead){
            $email = get_post_meta($lead->ID, 'email', true);
            $phone = get_post_meta($lead->ID, 'phone', true);
            echo '<div class="lead"><hr>
                <p><span class="display-5 font-weight-bold">'.$lead->post_title.'</span><br>
                <a href="mailto:'.$email.'" >'.$email.'</a><br><a href="tel:'.$phone.'" >'.$phone.'</a></p>
                <p>'.get_post_meta($lead->ID, 'comments', true).'</p>
                <a href="'.get_edit_post_link($lead->ID).'" class="btn btn-primary btn-sm" >View</a> 
                <a href="'.get_edit_post_link($lead->ID).'" class="btn btn-info btn-sm" >Archive</a>
            </div>';
        }

        echo '<div class="pt-2" ><hr><a class="px-2" href="/wordpress/wp-admin/edit.php?post_status=publish&post_type=contact_request" >All Active Leads</a> &nbsp; <a class="px-2" href="/wordpress/wp-admin/edit.php?post_status=trash&post_type=contact_request" >All Archived Leads</a></div>';
    }

    protected function getLeads($num = -1, $status = 'publish')
    {
        return get_posts([
            'posts_per_page'  => $num,
            'offset'          => 0,
            'order'           => 'DESC',
            'orderby'   	  => 'post_date',
            'post_type'       => 'contact_request',
            'post_status'     => $status
        ]);
    }

    public function addListingsWidget()
    {
        $listings = new RealtorListings($this->realtorInfo);
        $listingStats = $listings->getListingStats(5);
        $statsSection = '';
        $impressions = 0;
        $clicks = 0;

        if(!$listingStats){
            echo '<p class="text-center mt-5">Add your MLS ID to see stats on your properties!</p>
                    <p class="text-center pb-5"><a href="http://wprets.test/wordpress/wp-admin/admin.php?page=contact-info" >Complete your setup</a></p>';
            return;
        }

        foreach($listingStats as $listing){
            $impressions = $impressions + $listing->impressions;
            $clicks = $clicks + $listing->clicks;
            $statsSection .= '<div class="listing text-center">
            <hr>
                <table style="width:100%;">
                <tr>
                <td><span class="font-weight-bold">'.$listing->mls_account.'</span><br><a target="_blank" href="/listing/'.$listing->mls_account.'">view property</a></td>
                <td><span class="display-4 text-secondary">'.number_format($listing->impressions).'</span><br>Impressions</td><td><span class="display-4 text-secondary">'.number_format($listing->clicks).'</span><br>Clicks</td>
                </tr>
                </table>
            </div>';

        }

        echo '<div class="row mt-4">';

        echo '<div class="col-6 pb-4 text-center"><span class="display-3 text-primary font-weight-bold">'.count($listings->getListings(true)).'</span><br><small class="text-muted">Active Listings</small></div>';

        echo '<div class="col-6 pb-4 text-center"><span class="display-3 text-primary font-weight-bold">'.count($listings->getSoldListings()).'</span><br><small class="text-muted">Sold Listings (6 mo.)</small></div>';

        echo '<div class="col-6 pb-4 text-center"><span class="display-3 text-primary font-weight-bold">'.number_format($impressions).'</span><br><small class="text-muted">Listing Impressions</small></div>';

        echo '<div class="col-6 pb-4 text-center"><span class="display-3 text-primary font-weight-bold">'.number_format($clicks).'</span><br><small class="text-muted">Listing Clicks</small></div>';

        echo '</div>';

        echo $statsSection;
        
        echo '<div class="pt-2" ><hr><a class="px-2" href="/wordpress/wp-admin/index.php?page=my-listings.php" >All Active Listings</a></div>';

    }

    public function addInfoWidget()
    {
        if($this->realtorInfo['name'] == '' && 
            $this->realtorInfo['email'] == '' && 
            $this->realtorInfo['phone'] == '' && 
            $this->realtorInfo['broker'] == ''){
            echo '<p class="text-center mt-5">Required information is missing from configuration!</p>
                    <p class="text-center pb-5"><a href="http://wprets.test/wordpress/wp-admin/admin.php?page=contact-info" >Complete your setup</a></p>';
            return;
        }

        echo '<div class="text-center">';

        if($this->realtorInfo['photo'] != ''){
            echo '<img src="'.$this->realtorInfo['photo'].'" class="img-fluid img-thumbnail mt-4" width="250" >';
        }

        if($this->realtorInfo['name'] != ''){
            echo '<p class="display-4 mt-4 font-weight-bold">'.$this->realtorInfo['name'].'</p>';
        }

        if($this->realtorInfo['email'] != ''){
            echo '<p>'.$this->realtorInfo['email'].'</p>';
        }

        if($this->realtorInfo['phone'] != ''){
            echo '<p>'.$this->realtorInfo['phone'].'</p>';
        }

        if($this->realtorInfo['address'] != ''){
            echo '<p>'.$this->realtorInfo['address'].'</p>';
        }

        if($this->realtorInfo['broker'] != ''){
            echo '<p>'.$this->realtorInfo['broker'].'</p>';
        }

        if($this->realtorInfo['broker_logo'] != ''){
            echo '<img src="'.$this->realtorInfo['broker_logo'].'" width="150" >';
        }

        echo '</div>';

        echo '<div class="pt-2" ><hr><a class="px-2" href="/wordpress/wp-admin/admin.php?page=contact-info" >Edit Information</a></div>';

    }

}