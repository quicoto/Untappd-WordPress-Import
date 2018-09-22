<?php

include_once("../wp-load.php");

include_once ("./config.php");

$url = 'https://api.untappd.com/v4/user/checkins/quicoto?client_id=' . $client_id .  '&client_secret=' . $client_secret . '&sort=date&limit=10';
// $url = 'http://localhost:3000/response'; // Fake the call with https://github.com/typicode/json-server

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
    foreach($json['response']['checkins']['items'] as $checkin){
      // Each checkin has a beer.
      // We need to look the entire DB to see if the beer is already created
      // If no, we create it

      // Prepare the Beer WordPress data
      $beer = $checkin['beer'];
      $beer__post_title = (string)$beer['beer_name'];
      $beer__beer_style = (string)$beer['beer_style'];
      $beer__bid = (string)$beer['bid'];
      $beer__count = (string)$checkin['count'];
      $beer__rating_score = (string)$checkin['rating_score'];
      $beer__created_at = (string)$checkin['created_at'];
      $beer__last_checkin_id = (string)$checkin['checkin_id'];

      // Look for the Beer ID in the database, see if it exists
      // If it exists, we want to update the values (in case I've changed the rating)
      // And of course the number of times I've drinken it
      $args = array(
        'post_type' => 'beer',
        'meta_query' => array(
          array(
            'key' => 'bid',
            'value' => (string)$beer['bid'],
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
          'post_title'     => $beer__post_title
        );

        wp_update_post($post);

        $post_id = get_the_ID();
      } else {
        // Insert new
        $post = array(
          'post_author'    => 1,
          'post_status'    => 'publish',
          'post_title'     => $beer__post_title,
          'post_type'      => 'beer'
        );

        $post_id = wp_insert_post( $post);
      }

      // Only update if the last checkin ID is different than the current checking we're looking at
      $last_checkin_id = get_post_meta($post_id, 'last_checkin_id', true);

      if ($last_checkin_id !== $beer__last_checkin_id) {
        update_post_meta($post_id, 'bid', $beer__bid);
        update_post_meta($post_id, 'beer_style', $beer__beer_style);
        $post_beer_count = get_post_meta($post_id, 'count', true);
        update_post_meta($post_id, 'count', $post_beer_count + 1);
        update_post_meta($post_id, 'rating_score', $beer__rating_score);
        update_post_meta($post_id, 'recent_created_at', $beer__created_at);
        update_post_meta($post_id, 'last_checkin_id', $beer__last_checkin_id);
      }
    } // end foreach
  } // if json
} // if data