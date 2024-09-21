<?php

function mndi_import_articles($limit = 10) {
    $mndi_validation = get_mndi_option('mndi_key_is_valid');
    
    if ( $mndi_validation !== 'true' ) {
        return;
    }

    $mndi_key = get_mndi_option('mndi_api_key');
    $mndi_base_url = 'https://www.mynewsdesk.com/services/pressroom/list/' . $mndi_key . '?format=json';

    $mndi_pressrelease = do_curl($mndi_base_url . '&type_of_media=pressrelease&limit=' . $limit);
    $mndi_news = do_curl($mndi_base_url . '&type_of_media=news&limit=' . $limit);
    $mndi_posts = do_curl($mndi_base_url . '&type_of_media=blog_post&limit=' . $limit);

    $mndi_articles = array_merge($mndi_pressrelease->items, $mndi_news->items, $mndi_posts->items);
    
    foreach ($mndi_articles as $key => $mndi_article) {
        // Check if there is a published post in Wordpress with the same ID
        $existing_post = find_existing_article($mndi_article);

        if ($existing_post->skip) {
            continue;
        }

        // Setup post object
        $post_object = [ 
            'ID' => $existing_post->id,
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
        
        if ( !$wp_post ) {
            // Skip if insert post failed.
            continue;
        }

        // Add category
        $category_term = wp_insert_term($mndi_article->type_of_media, 'mndi_category');
        $category_term_id = (!is_wp_error($category_term) ? $category_term['term_id'] : $category_term->error_data['term_exists']);
        wp_set_post_terms($wp_post, $category_term_id, 'mndi_category', false);

        update_post_meta( $wp_post, 'mndi_id', $mndi_article->id ?? '' );
        update_post_meta( $wp_post, 'mndi_url', $mndi_article->url ?? '' );
        update_post_meta( $wp_post, 'mndi_updated', $mndi_article->updated_at->text ?? '' );
        update_post_meta( $wp_post, 'mndi_data', $mndi_article);

        // Check if featured image have changed, if so... upload new featured image
        if ( $mndi_article->image !== get_post_meta($wp_post, 'mndi_image', true ) ) {
            update_post_meta( $wp_post, 'mndi_image', $mndi_article->image ?? '');
            $attachment_id = upload_from_url($mndi_article->image);
            set_post_thumbnail( $wp_post, $attachment_id );
        }
    }
}

add_action( 'wp_ajax_nopriv_mdni_manual_import_action', 'mndi_manual_import' );
add_action( 'wp_ajax_mdni_manual_import_action', 'mndi_manual_import' );
function mndi_manual_import() {

    if ( !wp_verify_nonce( $_REQUEST['nonce'], "mndi_ajax_nonce")) {
        exit("No naughty business please");
    }

    mndi_import_articles();

    header("Location: ".$_SERVER["HTTP_REFERER"]);

    die();
}

function find_existing_article($mndi_article) {
    $matched = new WP_Query([
        'post_type' => 'mndi_news',
        'posts_per_page' => 1,
        'meta_query' => [
            [
                'key' => 'mndi_id',
                'value' => $mndi_article->id,
                'compare' => '=',
            ]
        ]
    ]);
     
    if ( $matched->post_count > 0 ) {
        
        $updated = strtotime(get_post_meta($matched->posts[0]->ID, 'mndi_updated', true)); // Get last updated date
        $skip = ($updated >= strtotime($mndi_article->updated_at->text)); // Check if new post has a new updates
        
        return (object)[
            'id' => $matched->posts[0]->ID,
            'skip' => $skip,
        ];
    } else {
        // No exisisting post found
        return (object)[
            'id' => '',
            'skip' => false
        ];
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