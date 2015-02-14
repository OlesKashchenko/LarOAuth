<?php

namespace OlesKashchenko\LarOAuth\Providers;

use Illuminate\Support\Facades\Config,
    Illuminate\Support\Facades\URL,
    Illuminate\Support\Facades\Session,
    Illuminate\Support\Facades\Redirect,
    Illuminate\Support\Facades\Input,
    Illuminate\Support\Facades\DB;

use Cartalyst\Sentry\Facades\Laravel\Sentry;

abstract class SocialProvider
{
    protected $oauthUrl;
    protected $accessTokenUrl;
    protected $profileDataUrl;
    protected $clientId;
    protected $clientKey;
    protected $handleUrl;
    protected $emailPostfix;
    protected $rememberMe;
    protected $idFieldName;
    protected $emailFieldName;
    protected $passwordFieldName;
    protected $lastNameFieldName;
    protected $firstNameFieldName;
    protected $activatedFieldName;

    public function __construct()
    {
        $this->emailFieldName = Config::get('lar-oauth::fieldNames.email_field_name');
        $this->passwordFieldName = Config::get('lar-oauth::fieldNames.password_field_name');
        $this->lastNameFieldName = Config::get('lar-oauth::fieldNames.last_name_field_name');
        $this->firstNameFieldName = Config::get('lar-oauth::fieldNames.first_name_field_name');
        $this->activatedFieldName = Config::get('lar-oauth::fieldNames.activated_field_name');

        Session::put('url_previous', URL::previous());
    }

    abstract public function sendLoginRequest();
    abstract public function handleLogin();
    abstract protected function getRequestParams();
    abstract protected function getAccessTokenParams($code);
    abstract protected function getAccessTokenData($code);
    abstract protected function getProfileDataParams(array $accessTokenData);
    abstract protected function getProfileData(array $accessTokenData);
}