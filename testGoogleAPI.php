<?php

    const GOOGLE_AUTH_KEY = 'AIzaSyD2w16IUheJDZNxc6EAzILtlEatAfIcFsQ'; //new
    const GOOGLE_SENG_KEY = '010804632401755585359:qdcdqjahzkq';     // new


    $query = 'Goldsmith went travelling instead of heading to university';

    $url = 'https://www.googleapis.com/customsearch/v1?key='.GOOGLE_AUTH_KEY.'&cx='.GOOGLE_SENG_KEY .'&q="'.urlencode($query).'"&num=10';

    $data = file_get_contents($url);

    $json = json_decode($data, true);

    print print_r( $data , true );

         

