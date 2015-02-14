<?php

namespace OlesKashchenko\LarOAuth\Providers;

use Illuminate\Support\Facades\Config,
    Illuminate\Support\Facades\URL,
    Illuminate\Support\Facades\Session,
    Illuminate\Support\Facades\Redirect,
    Illuminate\Support\Facades\Input,
    Illuminate\Support\Facades\DB;

use Cartalyst\Sentry\Facades\Laravel\Sentry;

class GoogleProvider extends SocialProvider
{

    public function __construct()
    {
        parent::__construct();

        $this->oauthUrl = Config::get('lar-oauth::google.oauth_url');
        $this->accessTokenUrl = Config::get('lar-oauth::google.oauth_access_token_url');
        $this->profileDataUrl = Config::get('lar-oauth::google.profile_data_url');
        $this->clientId = Config::get('lar-oauth::google.api_id');
        $this->clientKey = Config::get('lar-oauth::google.secret_key');
        $this->handleUrl = Config::get('lar-oauth::google.redirect_handle_url');
        $this->emailPostfix = Config::get('lar-oauth::google.email_postfix');
        $this->rememberMe = Config::get('lar-oauth::google.remember');
        $this->idFieldName = Config::get('lar-oauth::google.id_field_name');
    }

    public function sendLoginRequest()
    {
        $destinationUrl = $this->oauthUrl . "?" . $this->getRequestParams();

        return Redirect::to($destinationUrl);
    }

    public function handleLogin()
    {
        if (Input::get("code")) {
            $accessTokenData = $this->getAccessTokenData(Input::get("code"));

            if (isset($accessTokenData['access_token'])) {
                $profileData = $this->getProfileData($accessTokenData);

                $firstName = $profileData['given_name'];
                $lastName = $profileData['family_name'];
                $email = '';
                if (isset($profileData['email'])) {
                    $email = $profileData['email'];
                }

                $existedUser = DB::table("users")->where('email', 'like', $email)->first();
                if (!$existedUser) {
                    $password = str_random(6);

                    $user = Sentry::register(array(
                        $this->emailFieldName       => $email ? : $password . '@' . $this->emailPostfix,
                        $this->passwordFieldName    => $password,
                        $this->activatedFieldName   => 1,
                        $this->firstNameFieldName   => $firstName,
                        $this->lastNameFieldName    => $lastName
                    ));

                    $userAuth = Sentry::findUserById($user->id);
                } else {
                    $userAuth = Sentry::findUserById($existedUser['id']);
                }

                Sentry::login($userAuth, $this->rememberMe);

                $redirectUrl = Session::get('url_previous', "/");
                Session::forget('url_previous');

                return Redirect::to($redirectUrl);
            }
        }
    }

    protected function getRequestParams()
    {
        $params = array(
            'client_id'     => $this->clientId,
            'scope'         => 'https://www.googleapis.com/auth/userinfo.email https://www.googleapis.com/auth/userinfo.profile',
            'response_type' => 'code',
            'redirect_uri'  => $this->handleUrl
        );

        return http_build_query($params);
    }

    protected function getAccessTokenParams($code)
    {
        $params = array(
            'client_id'     => $this->clientId,
            'client_secret' => $this->clientKey,
            'code'          => $code,
            'redirect_uri'  => $this->handleUrl,
            'grant_type'    => 'authorization_code',
        );

        return http_build_query($params);
    }

    protected function getAccessTokenData($code)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $this->accessTokenUrl);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $this->getAccessTokenParams($code));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        $result = curl_exec($curl);
        curl_close($curl);

        return json_decode($result, true);
    }

    protected function getProfileDataParams(array $accessTokenData)
    {
        $params = array(
            'access_token' => $accessTokenData['access_token']
        );

        return http_build_query($params);
    }

    protected function getProfileData(array $accessTokenData)
    {
        $profileDataUrl = $this->profileDataUrl . '?' . $this->getProfileDataParams($accessTokenData);
        $profileResponse = file_get_contents($profileDataUrl);

        return json_decode($profileResponse, true);
    }
}