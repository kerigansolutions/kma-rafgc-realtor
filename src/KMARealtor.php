<?php
namespace KeriganSolutions\KMARealtor;

class KMARealtor
{
    public static function getRealtorInfo()
    {
        return [
            'name'        => get_field('agent_name','option'),
            'email'       => get_field('email','option'),
            'id'          => get_field('agent_id','option'),
            'address'     => get_field('address','option'),
            'phone'       => get_field('phone','option'),
            'photo'       => get_field('image','option'),
            'broker'      => get_field('broker_name','option'),
            'broker_logo' => get_field('broker_logo','option')
        ];
    }
}