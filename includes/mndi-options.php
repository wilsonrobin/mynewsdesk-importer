<?php

use Carbon_Fields\Container;
use Carbon_Fields\Field;

$status = 'empty';

add_action( 'plugins_loaded', function(){
    add_action( 'carbon_fields_register_fields', __NAMESPACE__ . 'create_options_page' );
    add_action( 'after_setup_theme', __NAMESPACE__ . '\load_carbon_fields' );
});

function load_carbon_fields() {
    \Carbon_Fields\Carbon_Fields::boot();
}

function create_options_page() {
    $key_is_invalid = array(array(
        'field' => 'mndi_key_is_valid',
        'value' => 'false',
        'compare' => '='
    ));
    $invalid_html = '<div style="display: inline-block; background: #cc4f4f; border-radius: 4px; color: white; padding: 7px 12px 8px; line-height: 1;">Api key is invalid</div>';

    $key_is_valid = array(array(
        'field' => 'mndi_key_is_valid',
        'value' => 'true',
        'compare' => '='
    ));
    $valid_html = '<div style="display: inline-block; background: #4fcc6b; border-radius: 4px; color: white; padding: 7px 12px 8px; line-height: 1;">Api key is valid</div>';

    Container::make( 'theme_options', __( 'MyNewDesk Importer', 'mndi' ) )
    ->set_page_parent( 'options-general.php' )
    ->add_fields( array(
        Field::make( 'html', 'mndi_invalid_message' )->set_html($invalid_html)->set_conditional_logic($key_is_invalid),
        Field::make( 'html', 'mndi_valid_message' )->set_html($valid_html)->set_conditional_logic($key_is_valid),
        Field::make( 'text', 'mndi_api_key', __( 'MyNewsDesk API Key', 'mndi' ) ),
        Field::make( 'select', 'mndi_recurrence', __( 'Select cronjob recurrence', 'mndi') )->set_options( array(
            'hourly' => 'Hourly',
            'twicedaily' => 'Twice daily',
            'daily' => 'Daily',
            'weekly' => 'Weekly',
            'none' => 'Disable cronjob'
        ))->set_conditional_logic($key_is_valid),
        Field::make( 'select', 'mndi_archive', __( 'Enable archive', 'mndi') )->set_options( array(
            'true' => 'Yes',
            'false' => 'No'
        ))->set_conditional_logic($key_is_valid),
        Field::make( 'html', 'mndi_manual' )->set_html(get_manual_button())->set_conditional_logic($key_is_valid),
        Field::make( 'hidden', 'mndi_key_is_valid', '' ),
    ));
}

function get_manual_button() {
    $nonce = wp_create_nonce("mndi_ajax_nonce");

    return '<a class="button button-primary button-large" href="'.admin_url('admin-ajax.php?action=mdni_manual_import_action&nonce='.$nonce).'">Run import now</a>';
}


add_action( 'carbon_fields_before_field_save', 'validate_api_key');
function validate_api_key($field)
{
    global $status;

    $name = $field->get_base_name();

    if ( $name === 'mndi_api_key') {
        $key = $field->get_value();

        if ( $key !== '' ) {
            $request = wp_remote_get('https://www.mynewsdesk.com/services/pressroom/pressroom_info/' . $key . '?format=json');

            if ( isset($request['response']['code']) && $request['response']['code'] === 200 ) {
                $status = 'valid';
            } else {
                $status = 'invalid';
            }
        }

        return $field;
    } elseif ($name === 'mndi_key_is_valid') {
        if ( $status === 'valid' ) {
            $field->set_value('true');
        } elseif ( $status === 'invalid' ) {
            $field->set_value('false');
        } else {
            $field->set_value('');
        }

        return $field;
    } else {
        return $field;
    }
}


// dce3ef5a947a675125adcc52ccb5c5cc