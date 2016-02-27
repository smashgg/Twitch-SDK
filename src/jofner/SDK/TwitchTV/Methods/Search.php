<?php

namespace jofner\SDK\TwitchTV\Methods;

use jofner\SDK\TwitchTV\TwitchException;
use jofner\SDK\TwitchTV\TwitchRequest;

/**
 * Search method class for TwitchTV API SDK for PHP
 *
 * @author Josef Ohnheiser <ritero@ritero.eu>
 * @license https://github.com/jofner/Twitch-SDK/blob/master/LICENSE.md MIT
 * @homepage https://github.com/jofner/Twitch-SDK
 */
class Search
{
    /** @var TwitchRequest */
    protected $request;

    const URI_SEARCH_CHANNELS = 'search/channels';

    /**
     * Search constructor
     * @param TwitchRequest $request
     */
    public function __construct(TwitchRequest $request)
    {
        $this->request = $request;
    }

    /**
     * Search for channel
     * @see https://github.com/justintv/Twitch-API/blob/master/v3_resources/search.md#get-searchchannels
     * @param $queryString
     * @return \stdClass
     * @throws TwitchException
     */
    public function channels($queryString)
    {
        return $this->request->request(self::URI_SEARCH_CHANNELS . $queryString);
    }
}
