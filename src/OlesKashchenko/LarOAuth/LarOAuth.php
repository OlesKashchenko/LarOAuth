<?php

namespace OlesKashchenko\LarOAuth;


class LarOAuth
{

    public function vk()
    {
        return new Providers\VkProvider();
    } // end vk

    public function fb()
    {
        return new Providers\FbProvider();
    } // end fb

    public function google()
    {
       return new Providers\GoogleProvider();
    } // end google
}

