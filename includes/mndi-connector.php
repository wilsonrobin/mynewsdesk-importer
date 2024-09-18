<?php

//add_action( 'mndi_run_import', 'mndi_import_articles' );
add_action( 'init', 'mndi_import_articles' );
function mndi_import_articles() {

    $mndi_key = get_mndi_option('mndi_api_key');
    $mndi_base_url = 'https://www.mynewsdesk.com/services/pressroom/list/' . $mndi_key . '?format=json';

    $mndi_pressrelease = do_curl($mndi_base_url . '&type_of_media=pressrelease&limit=2');
    $mndi_news = do_curl($mndi_base_url . '&type_of_media=news&limit=2');

    $mndi_articles = array_merge($mndi_pressrelease->items, $mndi_news->items);
    
    foreach ($mndi_articles as $key => $mndi_article) {
        // Check if there is a published post in Wordpress with the same ID
        $existing_id = find_existing_article($mndi_article->id);

        // Setup post object
        $post_object = [ 
            'ID' => $existing_id,
            'post_title'    => $mndi_article->header,
            'post_content'  => $mndi_article->body,
            'post_excerpt'  => $mndi_article->summary ?? '',
            'post_date'     => $mndi_article->published_at->text,
            'post_type'     => 'mndi_news',
            'post_status'   => 'publish',
            'post_author'   => 1
        ];
        
        // Insert or update post
        $wp_post = wp_insert_post($post_object);

        update_post_meta( $wp_post, 'mndi_id', $mndi_article->id ?? '' );
        update_post_meta( $wp_post, 'mndi_url', $mndi_article->url ?? '' );
        update_post_meta( $wp_post, 'mndi_updated', $mndi_article->updated_at->text ?? '' );
        update_post_meta( $wp_post, 'mndi_data', json_encode($mndi_article));

        if ( $mndi_article->image !== get_post_meta($wp_post, 'mndi_image', true ) ) {
            update_post_meta( $wp_post, 'mndi_image', $mndi_article->image ?? '');

            $attachment_id = upload_from_url($mndi_article->image);

            set_post_thumbnail( $wp_post, $attachment_id );
        }

        // Add category
        $category_term = wp_insert_term($mndi_article->type_of_media, 'mndi_category');
        $category_term_id = (!is_wp_error($category_term) ? $category_term['term_id'] : $category_term->error_data['term_exists']);
        wp_set_post_terms($wp_post, $category_term_id, 'mndi_category', false);
    }
}

function find_existing_article($mndi_id) {
    $matched = new WP_Query([
        'post_type' => 'mndi_news',
        'posts_per_page' => 1,
        'meta_query' => [
            [
                'key' => 'mndi_id',
                'value' => $mndi_id,
                'compare' => '=',
            ]
        ]
    ]);
     
    if ( $matched->post_count > 0 ) {
        return $matched->posts[0]->ID;
    } else {
        return '';
    }
}

function do_curl($mndi_url = '', $requestType = 'GET', $log = true)
{
    $result = wp_remote_get($mndi_url, array(
        'method' => 'GET',
        'headers' => 'Content-Type: application/json'
    ));

    $resultDecoded = json_decode($result['body']);

    return $resultDecoded;
}