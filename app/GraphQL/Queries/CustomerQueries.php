<?php


namespace App\GraphQL\Queries;


use App\Customer;

class CustomerQueries
{
    public function cheaps_customers ($root, array $args) {
        return Customer::all();
    }

}
