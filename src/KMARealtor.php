<?php
namespace KeriganSolutions\KMARealtor;

class KMARealtor
{
    public $realtorName;
    public $realtorEmail;
    public $realtorID;
    public $realtorAddress;
    public $realtorPhone;
    public $realtorPhoto;

    public function __construct()
    {
        $this->realtorName    = get_field('agent_name','option');
        $this->realtorEmail   = get_field('email','option');
        $this->realtorID      = get_field('agent_id','option');
        $this->realtorAddress = get_field('address','option');
        $this->realtorPhone   = get_field('phone','option');
        $this->realtorPhoto   = get_field('image','option');
        $this->brokerName     = get_field('broker_name','option');
        $this->brokerLogo     = get_field('broker_logo','option');

        new RealtorDashboard($this->getRealtorInfo());
        new RealtorListings($this->getRealtorInfo());
        (new FeaturedListings())->use();
        (new Listing())->use();
    }

    public function getRealtorInfo()
    {
        return [
            'name'        => $this->realtorName,
            'email'       => $this->realtorEmail,
            'id'          => $this->realtorID,
            'address'     => $this->realtorAddress,
            'phone'       => $this->realtorPhone,
            'photo'       => $this->realtorPhoto,
            'broker'      => $this->brokerName,
            'broker_logo' => $this->brokerLogo
        ];
    }


}