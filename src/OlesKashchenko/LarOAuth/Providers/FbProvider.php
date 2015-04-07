<?php

namespace OlesKashchenko\LarOAuth\Providers;

use Illuminate\Support\Facades\Config,
    Illuminate\Support\Facades\URL,
    Illuminate\Support\Facades\Session,
    Illuminate\Support\Facades\Redirect,
    Illuminate\Support\Facades\Input,
    Illuminate\Support\Facades\DB;

use Cartalyst\Sentry\Facades\Laravel\Sentry;

class FbProvider extends SocialProvider
{

    public function __construct()
    {
        parent::__construct();

        $this->oauthUrl = Config::get('lar-oauth::fb.oauth_url');
        $this->accessTokenUrl = Config::get('lar-oauth::fb.oauth_access_token_url');
        $this->profileDataUrl = Config::get('lar-oauth::fb.profile_data_url');
        $this->clientId = Config::get('lar-oauth::fb.api_id');
        $this->clientKey = Config::get('lar-oauth::fb.secret_key');
        $this->handleUrl = Config::get('lar-oauth::fb.redirect_handle_url');
        $this->emailPostfix = Config::get('lar-oauth::fb.email_postfix');
        $this->rememberMe = Config::get('lar-oauth::fb.remember');
        $this->idFieldName = Config::get('lar-oauth::fb.id_field_name');
    }

    public function sendLoginRequest()
    {
        Session::put('state', md5(uniqid(rand(), true)));

        $destinationUrl = $this->oauthUrl . "?" . $this->getRequestParams();

        return Redirect::to($destinationUrl);
    }

    public function handleLogin()
    {
        $code = Input::get("code");
        $state = Input::get("state");

        if ($state == Session::get('state')) {
            $accessTokenData = $this->getAccessTokenData($code);

            if (isset($accessTokenData['access_token'])) {
                $profileData = $this->getProfileData($accessTokenData);

                $firstName = $profileData['first_name'];
                $lastName = $profileData['last_name'];
                $idUser = $profileData['id'];
                $email = '';
                if (isset($profileData['email'])) {
                    $email = $profileData['email'];
                }

                if ($email) {
                    $existedUser = DB::table("users")->where($this->emailFieldName, $email)->first();
                } else {
                    $existedUser = DB::table("users")
                        ->where($this->emailFieldName, $idUser . '@' . $this->emailPostfix)
                        ->where($this->idFieldName, $idUser)
                        ->first();
                }

                if (!$existedUser) {
                    $password = str_random(6);

                    $user = Sentry::register(array(
                        $this->emailFieldName       => $email ? : $idUser . '@' . $this->emailPostfix,
                        $this->passwordFieldName    => $password,
                        $this->idFieldName          => $idUser,
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
            } else {
                return Redirect::to('/');
            }

        } else {
            return Redirect::to('/');
        }
    }

    protected function getRequestParams()
    {
        $params = array(
            'client_id'     => $this->clientId,
            'scope'         => 'email,user_photos,read_stream',
            'state'         => Session::get('state'),
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
            'redirect_uri'  => $this->handleUrl
        );

        return http_build_query($params);
    }

    protected function getAccessTokenData($code)
    {
        $accessTokenUrl = $this->accessTokenUrl . '?' . $this->getAccessTokenParams($code);

        $accessTokenResponse = file_get_contents($accessTokenUrl);
        $accessTokenData = array();
        parse_str($accessTokenResponse, $accessTokenData);

        return $accessTokenData;
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