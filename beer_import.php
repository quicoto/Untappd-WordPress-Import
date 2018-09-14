<?php

include_once("../wp-load.php");

include_once ("./config.php");

$url = 'https://api.untappd.com/v4/user/beers/quicoto?client_id=' . $client_id .  '&client_secret=' . $client_secret . '&sort=date&limit=50';
// $url = 'http://localhost:3000/response'; // Fake the call with https://github.com/typicode/json-server

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

    $index = 0;
    foreach($json['response']['beers']['items'] as $beer){
      // if ($index >= $beers_to_check) break;

      // Prepare the WordPress data
      $beer__post_title = (string)$beer['beer']['beer_name'];
      $beer__post_content = (string)$beer['beer']['beer_description'];
      $beer__beer_style = (string)$beer['beer']['beer_style'];
      $beer__bid = (string)$beer['beer']['bid'];
      $beer__count = (string)$beer['count'];
      $beer__rating_score = (string)$beer['rating_score'];
      $beer__recent_created_at = (string)$beer['recent_created_at'];

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

      $query = new WP_Query( $args );

      if( $query->have_posts() ) {
        the_post();

        // Update it
        $post = array(
          'ID'             => get_the_ID(),
          'post_title'     => $beer__post_title,
          'post_content'   => $beer__post_content,
        );

        wp_update_post($post);
      } else {
        // Insert new
        $post = array(
          'post_author'    => 1,
          'post_status'    => 'publish',
          'post_title'     => $beer__post_title,
          'post_content'   => $beer__post_content,
          'post_type'      => 'beer'
        );

        $post_id = wp_insert_post( $post);
      }

      // Update meta fields
      update_post_meta($post_id, 'bid', $beer__bid);
      update_post_meta($post_id, 'beer_style', $beer__beer_style);
      update_post_meta($post_id, 'count', $beer__count);
      update_post_meta($post_id, 'rating_score', $beer__rating_score);
      update_post_meta($post_id, 'recent_created_at', $beer__recent_created_at);

      $index++;
    } // end foreach
  } // if json
} // if data