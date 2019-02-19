<?php

?>
<div id="kmarealtor" >
    <div class="kma-header border-bottom shadow">
        <div class="header p-4 text-secondary">
            <h1>My Listings</h1>
        </div>
    </div>
    <div class="content px-4 pb-4">
        <div class="row" >
        <?php foreach($listings as $listing){ ?>
            <div class="col-sm-6 col-md-4 col-lg-3 col-xl-2">
                <div class="card listing bg-white p-0 shadow" >
                    <div 
                        class="embed-responsive embed-responsive-16by9 main-image"
                        style="background: url(<?php echo $listing->media_objects->data[0]->url; ?>) center no-repeat"
                        ></div>
                    <div class="p-4 text-center text-dark flex-grow-1">
                        <p><?php echo $listing->full_address; ?><br>
                        <?php echo $listing->city . ', ' . $listing->state; ?></p>
                        <p class="display-4 text-primary font-weight-bold">$<?php echo number_format($listing->price); ?></p>

                        <div class="row">
                            <div class="col">
                                <span class="display-4 text-secondary"><?php echo number_format($listing->impressions); ?></span><br>Impressions
                            </div>
                            <div class="col">
                                <span class="display-4 text-secondary"><?php echo number_format($listing->clicks); ?></span><br>Clicks
                            </div>
                        </div>
                    </div>
                    <div class="p-2 text-center"><span class="mls-number">MLS# <?php echo $listing->mls_account; ?></span><br>
                    <a class="listing-link" target="_blank" href="/listing/<?php echo $listing->mls_account; ?>/" >view property</a></div>
                </div>
            </div>
        <?php } ?>
        </div>
    </div>
</div>