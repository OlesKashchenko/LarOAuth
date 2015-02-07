<?php

return array(

    //create account https://vk.com/editapp?act=create

    'vk' => array(
        "status"                    => true,

        "api_id"                    => "4764874",
        "secret_key"                => "Evj3X6QJBtQkgbYfCnth",

        "oauth_url"                 => "http://api.vk.com/oauth/authorize",
        "oauth_access_token_url"    => "https://oauth.vk.com/access_token",
        "profile_data_url"          => "https://api.vkontakte.ru/method/getProfiles",

        "redirect_request_url"      => URL::to('/') . "/oauth/request",
        "redirect_handle_url"       => URL::to('/') . "/oauth/handle",
        "redirect_access_token_url" => URL::to('/') . "/oauth/access-token-handle",

        "remember"                  => false,

        'id_field_name'             => 'id_vk',
    ),

    //create account https://developers.facebook.com/quickstarts/?platform=web

    'fb' => array(

    ),

    //create account https://console.developers.google.com/project

    'google'  => array(

    ),
);