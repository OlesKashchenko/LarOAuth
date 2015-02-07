<?php

namespace OlesKashchenko\LarOAuth\Providers;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;
use Cartalyst\Sentry\Facades\Laravel\Sentry;

class VkProvider
{
    private $oauthUrl;
    private $accessTokenUrl;
    private $profileDataUrl;
    private $clientId;
    private $clientKey;
    private $redirectUrl;
    private $redirectAccessTokenUrl;
    private $handleUrl;
    private $idFieldName;

    public function __construct()
    {
        $this->oauthUrl = Config::get('lar-oauth::vk.oauth_url');
        $this->accessTokenUrl = Config::get('lar-oauth::vk.oauth_access_token_url');
        $this->profileDataUrl = Config::get('lar-oauth::vk.profile_data_url');
        $this->clientId = Config::get('lar-oauth::vk.api_id');
        $this->clientKey = Config::get('lar-oauth::vk.secret_key');
        $this->redirectUrl = Config::get('lar-oauth::vk.redirect_request_url');
        $this->handleUrl = Config::get('lar-oauth::vk.redirect_handle_url');
        $this->redirectAccessTokenUrl = Config::get('lar-oauth::vk.redirect_access_token_url');
        $this->idFieldName = Config::get('lar-oauth::vk.id_field_name');

        Session::put('url_previous', URL::previous());
    }

    public function sendLoginRequest()
    {
        $destinationUrl = $this->oauthUrl . "?" . $this->getRequestParams();

        return Redirect::to($destinationUrl);
    }

    public function handleLogin()
    {
        if (Input::get("code")) {
            $accessTokenUrl = $this->accessTokenUrl . '?' . $this->getAccessTokenParams(Input::get("code"));
            $accessTokenData = $this->doCurlRequest($accessTokenUrl);

            if (isset($data['access_token'])) {
                $profileDataUrl = $this->profileDataUrl . '?' . $this->getProfileDataParams($data);
                $profileData = $this->getProfileData($profileDataUrl);

                $firstName = $profileData['response'][0]['first_name'];
                $lastName = $profileData['response'][0]['last_name'];
                $idUser = $profileData['response'][0]['uid'];
                $email = '';
                if (isset($profileData['response'][0]['email'])) {
                    $email = $profileData['response'][0]['email'];
                }

                $existedUser = DB::table("users")->where($this->idFieldName, $idUser)->first();
                if (!$existedUser) {
                    $password = str_random(6);

                    $user = Sentry::register(array(
                        'email'         => $email ? : $idUser . '@vk.com',
                        'password'      => $password,
                        'id_vk'         => $idUser,
                        'activated'     => 1,
                        'first_name'    => $firstName,
                        'last_name'     => $lastName
                    ));

                    $userAuth = Sentry::findUserById($user->id);
                } else {
                    $userAuth = Sentry::findUserById($existedUser['id']);
                }

                Sentry::login($userAuth, Config::get('auth-soc::config.vk.remember'));

                $redirect = Session::get('url_previous', "/");
                Session::forget('url_previous');

                return Redirect::to($redirect);
            }
        }
    }

    private function getRequestParams()
    {
        $params = array(
            'client_id'     => $this->clientId,
            'scope'         => 'friends,photos,offline',
            'display'       => 'popup',
            'redirect_uri'  => $this->redirectUrl
        );

        return http_build_query($params);
    }

    private function getAccessTokenParams($code)
    {
        $params = array(
            'client_id'     => $this->clientId,
            'client_secret' => $this->clientKey,
            'code'          => $code,
            'redirect_uri'  => $this->redirectAccessTokenUrl
        );

        return http_build_query($params);
    }

    private function doCurlRequest($url)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $result = curl_exec($ch);

        curl_close($ch);

        return json_decode($result, true);
    }

    private function getProfileDataParams($data)
    {
        $params = array(
            'uid'           => $data['user_id'],
            'fields'        => 'photo_big',
            'access_token'  => $data['access_token']
        );

        return http_build_query($params);
    }

    private function getProfileData($profileDataUrl)
    {
        $profileResponse = file_get_contents($profileDataUrl);

        return json_decode($profileResponse, true);
    }
}