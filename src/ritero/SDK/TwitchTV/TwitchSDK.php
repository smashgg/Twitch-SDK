<?php

namespace ritero\SDK\TwitchTV;

/**
 * TwitchTV API SDK for PHP
 * 
 * PHP SDK for interacting with the TwitchTV API
 * 
 * @author Josef Ohnheiser <ritero@ritero.eu>
 * @license https://github.com/jofner/Twitch-SDK/blob/master/LICENSE.md MIT
 * @homepage https://github.com/jofner/Twitch-SDK
 * @version 0.3.7
 */
class TwitchSDK
{
    /** @var array */
    protected $auth_config = false;

    /** @var integer Set timeout default. */
    public $timeout = 30;

    /** @var integer Set connect timeout */
    public $connect_timeout = 30;

    /** @var boolean Verify SSL Cert */
    public $ssl_verifypeer = false;

    /** @var integer Contains the last HTTP status code returned */
    public $http_code = 0;

    /** @var array Contains the last Server headers returned */
    public $http_header = array();

    /** @var array Contains the last HTTP headers returned */
    public $http_info = array();

    /** @var boolean Throw cURL errors */
    public $throw_curl_errors = true;

    /** @var string Set the useragnet */
    private $useragent = 'ritero TwitchSDK dev-0.3.*';

    /**
     * TwitchAPI URI's
     */
    const URL_TWITCH = 'https://api.twitch.tv/kraken/';
    const URI_USER = 'users/';
    const URI_USER_FOLLOWS_CHANNEL = '/users/%s/follows/channels';
    const URI_USER_FOLLOW_RELATION = '/users/%s/follows/channels/%s';
    const URI_CHANNEL = 'channels/';
    const URI_CHANNEL_FOLLOWS = 'channels/%s/follows';
    const URI_STREAM = 'streams/';
    const URI_STREAM_SUMMARY = 'streams/summary/';
    const URI_STREAMS_FEATURED = 'streams/featured/';
    const URI_STREAMS_SEARCH = 'search/streams/';
    const URI_VIDEO = 'videos/';
    const URI_CHAT = 'chat/';
    const URI_CHAT_EMOTICONS = 'chat/emoticons';
    const URI_GAMES_TOP = 'games/top/';
    const URI_AUTH = 'oauth2/authorize';
    const URI_AUTH_TOKEN = 'oauth2/token';
    const URI_USER_AUTH = 'user';
    const URI_CHANNEL_AUTH = 'channel';
    const URI_CHANNEL_EDITORS_AUTH = 'channels/%s/editors';
    const URI_STREAMS_FOLLOWED_AUTH = 'streams/followed';

    /**
     * SDK constructor
     * @param   array
     * @throws  \ritero\SDK\TwitchTV\TwitchException
     */
    public function __construct($config = array())
    {
        if (!in_array('curl', get_loaded_extensions())) {
            throw new TwitchException('cURL extension is not installed and is required');
        }

        if (!empty($config)) {
            if ($this->configValidate($config) === true) {
                $this->auth_config = $config;
            } else {
                throw new TwitchException('Wrong Twitch API config parameters');
            }
        }
    }

    /**
     * Basic information about the API and authentication status
     * @param   string
     * @return  stdClass
     */
    public function status($token = null)
    {
        $auth = null;

        if (!is_null($token)) {
            if ($this->auth_config === false) {
                $this->authConfigException();
            } else {
                $auth = $this->buildQueryString(array('oauth_token' => $token));
            }
        }

        return $this->request($auth);
    }

    /**
     * Get the specified user
     * @param   string
     * @return  stdClass
     */
    public function userGet($username)
    {
        return $this->request(self::URI_USER . $username);
    }

    /**
     * Get a user's list of followed channels
     * @param   integer
     * @param   integer
     * @param   integer
     * @return  stdClass
     */
    public function userFollowChannels($user, $limit = null, $offset = null)
    {
        $query_string = $this->buildQueryString(array(
            'limit' => $limit,
            'offset' => $offset,
            ));

        return $this->request(sprintf(self::URI_USER_FOLLOWS_CHANNEL, $user) . $query_string);
    }

    /**
     * Get the status of a follow relationship
     * @param   string
     * @param   string
     * @return  stdClass
     */
    public function userFollowRelationship($user, $channel)
    {
        return $this->request(sprintf(self::URI_USER_FOLLOW_RELATION, $user, $channel));
    }

    /**
     * Get the specified channel
     * @param   string
     * @return  stdClass
     */
    public function channelGet($channel)
    {
        return $this->request(self::URI_CHANNEL . $channel);
    }

    /**
     * Returns an array of users who follow the specified channel
     * @param   string
     * @param   integer
     * @param   integer
     * @return  stdClass
     */
    public function channelFollows($channel, $limit = null, $offset = null)
    {
        $query_string = $this->buildQueryString(array(
            'limit' => $limit,
            'offset' => $offset,
            ));

        return $this->request(sprintf(self::URI_CHANNEL_FOLLOWS, $channel) . $query_string);
    }

    /**
     * Get the specified channel's stream
     * @param   string
     * @return  stdClass
     */
    public function streamGet($channel)
    {
        return $this->request(self::URI_STREAM . $channel);
    }

    /**
     * Search live streams
     * @param   string
     * @param   integer
     * @param   integer
     * @return  stdClass
     */
    public function streamSearch($query, $limit = null, $offset = null)
    {
        $query_string = $this->buildQueryString(array(
            'query' => $query,
            'limit' => $limit,
            'offset' => $offset,
            ));

        return $this->request(self::URI_STREAMS_SEARCH . $query_string);
    }

    /**
     * Summarize streams
     * @param   string
     * @param   array
     * @param   boolean
     * @return  stdClass
     */
    public function streamsSummarize($game = null, array $channels = null, $hls = null)
    {
        if (!empty($channels)) {
            $channels = implode(',', $channels);
        }

        $query_string = $this->buildQueryString(array(
            'game' => $game,
            'channel' => $channels,
            'hls' => $hls,
            ));

        return $this->request(self::URI_STREAM_SUMMARY . $query_string);
    }

    /**
     * Get featured streams
     * @param   integer
     * @param   integer
     * @param   boolean
     * @return  stdClass
     */
    public function streamsFeatured($limit = null, $offset = null, $hls = null)
    {
        $query_string = $this->buildQueryString(array(
            'limit' => $limit,
            'offset' => $offset,
            'hls' => $hls,
            ));

        return $this->request(self::URI_STREAMS_FEATURED . $query_string);
    }

    /**
     * Get streams by channel
     * @param   array
     * @param   integer
     * @param   integer
     * @param   boolean
     * @param   boolean
     * @return  stdClass
     */
    public function streamsByChannels($channels, $limit = null, $offset = null, $embeddable = null, $hls = null)
    {
        $channels_string = implode(',', $channels);

        return $this->getStreams(null, $limit, $offset, $channels_string, $embeddable, $hls);
    }

    /**
     * Get streams by game
     * @param   string
     * @param   integer
     * @param   integer
     * @param   boolean
     * @param   boolean
     * @return  stdClass
     */
    public function streamsByGame($game, $limit = null, $offset = null, $embeddable = null, $hls = null)
    {
        return $this->getStreams($game, $limit, $offset, null, $embeddable, $hls);
    }

    /**
     * Get video
     * @param   integer
     * @return  stdClass
     */
    public function videoGet($video)
    {
        return $this->request(self::URI_VIDEO . $video);
    }

    /**
     * Get videos for a channel
     * @param   string
     * @param   integer
     * @param   integer
     * @return  stdClass
     */
    public function videosByChannel($channel, $limit = null, $offset = null)
    {
        $query_string = $this->buildQueryString(array(
            'limit' => $limit,
            'offset' => $offset,
            ));

        return $this->request(self::URI_CHANNEL . $channel . '/' . self::URI_VIDEO . $query_string);
    }

    /**
     * Get the specified channel's chat
     * @param   string
     * @return  stdClass
     */
    public function chatGet($channel)
    {
        return $this->request(self::URI_CHAT . $channel);
    }

    /**
     * Get a chat's emoticons
     * @return  stdClass
     */
    public function chatEmoticons()
    {
        return $this->request(self::URI_CHAT_EMOTICONS);
    }

    /**
     * Get top games
     * @param   integer
     * @param   integer
     * @return  stdClass
     */
    public function gamesTop($limit = null, $offset = null)
    {
        $query_string = $this->buildQueryString(array(
            'limit' => $limit,
            'offset' => $offset,
            ));

        return $this->request(self::URI_GAMES_TOP . $query_string);
    }

    /**
     * Get HTML code for stream embedding
     * @param   string
     * @param   integer
     * @param   integer
     * @param   integer
     * @return  string
     */
    public function embedStream($channel, $width = 620, $height = 378, $volume = 25)
    {
        return '<object type="application/x-shockwave-flash" 
                height="' . $height . '" 
                width="' . $width . '" 
                id="live_embed_player_flash" 
                data="http://www.twitch.tv/widgets/live_embed_player.swf?channel=' . $channel . '" 
                bgcolor="#000000">
                <param  name="allowFullScreen" 
                    value="true" />
                <param  name="allowScriptAccess" 
                    value="always" />
                <param  name="allowNetworking" 
                    value="all" />
                <param  name="movie" 
                    value="http://www.twitch.tv/widgets/live_embed_player.swf" />
                <param  name="flashvars" 
                    value="hostname=www.twitch.tv&channel=' . $channel . '&auto_play=true&start_volume=' . $volume . '" />
                </object>';
    }

    /**
     * Get HTML code for video embedding
     * @param   string
     * @param   integer
     * @param   integer
     * @param   integer
     * @param   integer
     * @return  string
     */
    public function embedVideo($channel, $chapterid, $width = 400, $height = 300, $volume = 25)
    {
        return '<object bgcolor="#000000" 
                    data="http://www.twitch.tv/widgets/archive_embed_player.swf" 
                    width="' . $width . '" 
                    height="' . $height . '" 
                    id="clip_embed_player_flash" 
                    type="application/x-shockwave-flash"> 
                <param  name="movie" 
                    value="http://www.twitch.tv/widgets/archive_embed_player.swf" /> 
                <param  name="allowScriptAccess" 
                    value="always" /> 
                <param  name="allowNetworking" 
                    value="all" /> 
                <param name="allowFullScreen" 
                    value="true" /> 
                <param  name="flashvars" 
                    value="channel=' . $channel . '&start_volume=' . $volume . '&auto_play=false&chapter_id=' . $chapterid . '" />
                </object>';
    }

    /**
     * Get HTML code for chat embedding
     * @param   string
     * @param   integer
     * @param   integer
     * @return  string
     */
    public function embedChat($channel, $width = 400, $height = 300)
    {
        return '<iframe frameborder="0" 
                    scrolling="no" 
                    id="chat_embed" 
                    src="http://twitch.tv/chat/embed?channel=' . $channel . '&amp;popout_chat=true" 
                    height="' . $height . '" 
                    width="' . $width . '">
                </iframe>';
    }

    /**
     * Get login URL for authentication
     * @param   string $scope Specify which permissions your app requires (space separated list)
     * @return  string
     */
    public function authLoginURL($scope)
    {
        if ($this->auth_config === false) {
            $this->authConfigException();
        }

        $query_string = $this->buildQueryString(array(
            'response_type' => 'code',
            'client_id' => $this->auth_config['client_id'],
            'redirect_uri' => $this->auth_config['redirect_uri'],
            'scope' => $scope,
            ));

        return self::URL_TWITCH . self::URI_AUTH . $query_string;
    }

    /**
     * Get authentication access token
     * @param   string code returned after app authorization by user
     * @return  stdClass
     */
    public function authAccessTokenGet($code)
    {
        if ($this->auth_config === false) {
            $this->authConfigException();
        }

        $query_string = $this->buildQueryString(array(
            'client_id' => $this->auth_config['client_id'],
            'client_secret' => $this->auth_config['client_secret'],
            'grant_type' => 'authorization_code',
            'redirect_uri' => $this->auth_config['redirect_uri'],
            'code' => $code,
            ));

        return $this->request(self::URI_AUTH_TOKEN, 'POST', $query_string);
    }

    /**
     * Get the authenticated user
     *  - requires scope 'user_read'
     * @param   string
     * @return  stdClass
     */
    public function authUserGet($token)
    {
        if ($this->auth_config === false) {
            $this->authConfigException();
        }

        $query_string = $this->buildQueryString(array(
            'oauth_token' => $token,
            'client_id' => $this->auth_config['client_id'],
            ));

        return $this->request(self::URI_USER_AUTH . $query_string);
    }

    /**
     * Get the authenticated channel
     *  - requires scope 'channel_read'
     * @param   string
     * @return  stdClass
     */
    public function authChannelGet($token)
    {
        if ($this->auth_config === false) {
            $this->authConfigException();
        }

        $query_string = $this->buildQueryString(array(
            'oauth_token' => $token,
            'client_id' => $this->auth_config['client_id'],
            ));

        return $this->request(self::URI_CHANNEL_AUTH . $query_string);
    }

    /**
     * Returns an array of users who are editors of specified channel
     *  - requires scope 'channel_read'
     * @param   string
     * @param   string
     * @return  stdClass
     */
    public function authChannelEditors($token, $channel)
    {
        if ($this->auth_config === false) {
            $this->authConfigException();
        }

        $query_string = $this->buildQueryString(array(
            'oauth_token' => $token,
            'client_id' => $this->auth_config['client_id'],
            ));

        return $this->request(sprintf(self::URI_CHANNEL_EDITORS_AUTH, $channel) . $query_string);
    }

    /**
     * List the live streams that the authenticated user is following
     *  - requires scope 'user_read'
     * @param   string
     * @return  stdClass
     */
    public function authStreamsFollowed($token)
    {
        if ($this->auth_config === false) {
            $this->authConfigException();
        }

        $query_string = $this->buildQueryString(array(
            'oauth_token' => $token,
            'client_id' => $this->auth_config['client_id'],
            ));

        return $this->request(self::URI_STREAMS_FOLLOWED_AUTH . $query_string);
    }

    /**
     * Get streams helper
     * @param   string
     * @param   integer
     * @param   integer
     * @param   string
     * @param   boolean
     * @param   boolean
     * @return  stdClass
     */
    private function getStreams($game = null, $limit = null, $offset = null, $channels = null, $embeddable = null, $hls = null)
    {
        $params = array(
            'game' => $game,
            'limit' => $limit,
            'offset' => $offset,
            'channel' => !empty($channels) ? $channels : null,
            'embeddable' => $embeddable,
            'hls' => $hls,
        );

        $query_string = $this->buildQueryString($params);

        return $this->request(self::URI_STREAM . $query_string);
    }

    /**
     * Validate parameters for authentication
     * @param   array
     * @return  boolean
     */
    private function configValidate($config)
    {
        $check = array('client_id', 'client_secret', 'redirect_uri');

        foreach ($check AS $val) {
            if (!array_key_exists($val, $config) ||
                (empty($config[$val]) ||
                !is_string($config[$val]))
            ) {
                return false;
            }
        }

        return true;
    }

    /**
     * Build query string
     * @param   array
     * @return  string
     */
    private function buildQueryString($params)
    {
        $param = array();
        $query_string = null;

        foreach ($params as $key => $value) {
            if (!empty($value)) {
                $param[$key] = $value;
            }
        }

        if (!empty($param)) {
            $query_string = '?' . http_build_query($param);
        }

        return $query_string;
    }

    /**
     * TwitchAPI request
     * @param   string
     * @param   string
     * @param   string
     * @return  stdClass
     * @throws  \ritero\SDK\TwitchTV\TwitchException
     */
    private function request($uri, $method = 'GET', $postfields = null)
    {
        $this->http_info = array();

        $crl = curl_init();
        curl_setopt($crl, CURLOPT_USERAGENT, $this->useragent);
        curl_setopt($crl, CURLOPT_CONNECTTIMEOUT, $this->connect_timeout);
        curl_setopt($crl, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($crl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($crl, CURLOPT_HTTPHEADER, array('Expect:'));
        curl_setopt($crl, CURLOPT_SSL_VERIFYPEER, $this->ssl_verifypeer);
        curl_setopt($crl, CURLOPT_HEADERFUNCTION, array($this, 'getHeader'));
        curl_setopt($crl, CURLOPT_HEADER, false);

        switch ($method) {
            case 'POST':
                curl_setopt($crl, CURLOPT_POST, true);
                if (!is_null($postfields)) {
                    curl_setopt($crl, CURLOPT_POSTFIELDS, ltrim($postfields, '?'));
                }
                break;
            case 'DELETE':
                curl_setopt($crl, CURLOPT_CUSTOMREQUEST, 'DELETE');
                if (!is_null($postfields)) {
                    $uri = self::URL_TWITCH . $uri . $postfields;
                }
        }

        curl_setopt($crl, CURLOPT_URL, self::URL_TWITCH . $uri);

        $response = curl_exec($crl);

        $this->http_code = curl_getinfo($crl, CURLINFO_HTTP_CODE);
        $this->http_info = array_merge($this->http_info, curl_getinfo($crl));

        if (curl_errno($crl) && $this->throw_curl_errors === true) {
            throw new TwitchException(curl_error($crl), curl_errno($crl));
        }

        curl_close($crl);

        return json_decode($response);
    }

    /**
     * Get the header info to store
     */
    private function getHeader($ch, $header)
    {
        $i = strpos($header, ':');
        if (!empty($i)) {
            $key = str_replace('-', '_', strtolower(substr($header, 0, $i)));
            $value = trim(substr($header, $i + 2));
            $this->http_header[$key] = $value;
        }

        return strlen($header);
    }

    /**
     * Configuration exception
     * @throws  \ritero\SDK\TwitchTV\TwitchException
     */
    private function authConfigException()
    {
        throw new TwitchException('Cannot call authenticate functions without valid API configuration');
    }
}
