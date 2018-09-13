<?php

// include_once("wp-load.php");

include_once ("./config.php");

$url = 'https://api.untappd.com/v4/user/beers/quicoto?client_id=' . $client_id .  '&client_secret=' . $client_secret . '&sort=date&limit=50';
$url = 'http://localhost:3000/response'; // Fake the call with https://github.com/typicode/json-server

// This will loop through the latest 10 beers, should be enough.
// Don't think I'll check in in more than 10 beers in 1 day
$beers_to_check = 10;

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$data = curl_exec($ch);
curl_close($ch);

if ($data) {
  $json = json_decode($data, true);

  if ($json) {
    echo "<pre>";
    // print_r($json);

    $index = 0;
    foreach($json['beers']['items'] as $beer){
      if ($index >= $beers_to_check) break;

      print_r($beer);

      // Prepare the WordPress data
      $beer__post_title = (string)$beer['beer']['beer_name'];
      $beer__post_content = (string)$beer['beer']['beer_description'];
      $beer__beer_style = (string)$beer['beer']['beer_style'];
      $beer__bid = (string)$beer['beer']['bid'];
      $beer__count = (string)$beer['beer']['count'];
      $beer__rating_score = (string)$beer['beer']['rating_score'];
      $beer__recent_created_at = (string)$beer['beer']['recent_created_at'];

      die();

      // Look for the Beer ID in the database, see if it exists
      // If it exists, we want to update the values (in case I've changed the rating)
      // And of course the number of times I've drinken it
      $args = array(
        'post_type' => 'beer',
        'meta_query' => array(
          array(
            'key' => 'bid',
            'value' => (string)$beer['beer']['bid'],
            'compare' => 'LIKE'
          ),
        'posts_per_page' => 1
        )
      );

      /*

      $query = new WP_Query( $args );

      if( !$query->have_posts() ) {
        foreach ($item->children('http://www.georss.org/georss') as $geo) {

          $geo = explode(' ', $geo);

              $item->lat = $geo[0];
              $item->lng = $geo[1];
          }

        $item->description = str_replace('@', '', $item->description);

        $new_title = explode('-', $item->description);

        if(sizeof($new_title) > 1) $new_title = $new_title[1]. " @".$new_title[0];
        else $new_title = $new_title[0];

        $item->title = substr($new_title, 1);

        $item_time = $item->pubDate;

        $dt = new DateTime($item_time);
        $post_date = $dt->format('Y-m-d H:i:s');

        // Insert post to the database

        $post = array(
          'post_author'    => 1,
          'post_category'  => array('2'),
          'post_date'      =>  $post_date,
          'post_date_gmt'  =>  $post_date,
          'post_status'    => 'publish',
          'post_title'     => (string)$item->title,
          'post_content'   => "<a href='" . (string)$item->link . "' target='_blank'>" . (string)$item->link . "</a>",
          'post_type'      => 'post'
        );

        print_r($post);

        $post_id = wp_insert_post( $post , $error);

        print_r($post_id);

        update_post_meta($post_id, 'foursquare_checkin_id', (string)$item->guid);
        update_post_meta($post_id, 'lat', (string)$item->lat);
        update_post_meta($post_id, 'lng', (string)$item->lng);
      }*/

      $index++;
    }
  } // if json
} // if data