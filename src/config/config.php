<?php

return array(

    //create account https://vk.com/editapp?act=create
    'vk' => array(
        "api_id"                    => "4766979",
        "secret_key"                => "2DKiMgJEMmfmmTb9Ty4L",
        "oauth_url"                 => "http://api.vk.com/oauth/authorize",
        "oauth_access_token_url"    => "https://oauth.vk.com/access_token",
        "profile_data_url"          => "https://api.vkontakte.ru/method/getProfiles",
        "redirect_request_url"      => URL::to('/') . "/oauth/vk/request",
        "redirect_handle_url"       => URL::to('/') . "/oauth/vk/handle",
        "remember"                  => false,
        'id_field_name'             => 'id_vk',
        'email_postfix'             => 'vk.com'
    ),

    //create account https://developers.facebook.com/quickstarts/?platform=web
    'fb' => array(
        "api_id"                    => "335391563335704",
        "secret_key"                => "7722b31de8bb4aa48518844da7b9ba12",
        "oauth_url"                 => "http://www.facebook.com/dialog/oauth",
        "oauth_access_token_url"    => "https://graph.facebook.com/oauth/access_token",
        "profile_data_url"          => "https://graph.facebook.com/me",
        "redirect_handle_url"       => URL::to('/') . "/oauth/fb/handle",
        "remember"                  => false,
        'id_field_name'             => 'id_fb',
        'email_postfix'             => 'facebook.com'
    ),

    //create account https://console.developers.google.com/project

    'google'  => array(
        "api_id"                    => "75531091597-eb5k91755caf527a7cmj2ru4ckrb1qhg.apps.googleusercontent.com",
        "secret_key"                => "HvnuOTHWtgGgKxgqDSIGBgXa",
        "oauth_url"                 => "https://accounts.google.com/o/oauth2/auth",
        "oauth_access_token_url"    => "https://accounts.google.com/o/oauth2/token",
        "profile_data_url"          => "https://www.googleapis.com/oauth2/v1/userinfo",
        "redirect_request_url"      => URL::to('/') . "/oauth/google/request",
        "redirect_handle_url"       => URL::to('/') . "/oauth/google/handle",
        "remember"                  => false,
        'id_field_name'             => 'id_google',
        'email_postfix'             => 'google.com'
    ),

    'fieldNames' => array(
        'email_field_name'         => 'email',
        'last_name_field_name'     => 'last_name',
        'first_name_field_name'    => 'first_name',
        'password_field_name'      => 'password',
        'activated_field_name'     => 'activated'
    ),
    
    'callback' => function($user) {
        
    },
);
