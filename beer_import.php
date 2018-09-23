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
      // Prepare the Beer WordPress data
      $beer = $checkin['beer'];
      $beer__name = (string)$beer['beer_name'];
      $beer__beer_style = (string)$beer['beer_style'];
      $beer__bid = (string)$beer['bid'];
      $beer__rating_score = (string)$checkin['rating_score'];
      $beer__created_at = (string)$checkin['created_at'];
      $beer__checkin_id = (string)$checkin['checkin_id'];

      // Look for the Checking ID in the database, see if it exists
      $args = array(
        'post_type' => 'beer',
        'meta_query' => array(
          array(
            'key' => 'checkin_id',
            'value' => $beer__checkin_id,
            'compare' => 'LIKE'
          ),
        'posts_per_page' => 1
        )
      );

      $post_list = get_posts($args);

      if (!$post_list) {
        // Insert new
        $post_to_be_inserted = array(
          'post_author'    => 1,
          'post_status'    => 'publish',
          'post_title'     => 'Checkin',
          'post_type'      => 'beer'
        );

        $post_id = wp_insert_post( $post_to_be_inserted);

        wp_set_post_terms( $post_id, $beer__name, 'brew');

        update_post_meta($post_id, 'bid', $beer__bid);
        update_post_meta($post_id, 'beer_style', $beer__beer_style);
        update_post_meta($post_id, 'rating_score', $beer__rating_score);
        update_post_meta($post_id, 'recent_created_at', $beer__created_at);
        update_post_meta($post_id, 'checkin_id', $beer__checkin_id);
      }
    } // end foreach
  } // if json
} // if data
