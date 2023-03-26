<?php

namespace vkParse;

use VK\Client\VKApiClient;

class VKParse extends VKApiClient
{
    public function execute($accessToken, $code)
    {
        return $this->getRequest()->post('execute', $accessToken, ['code' => $code]);
    }
}