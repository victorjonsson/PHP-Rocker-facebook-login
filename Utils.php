<?php
namespace Rocker\FacebookLogin;

use Rocker\Cache\Cache;
use Rocker\Object\User\UserFactory;
use Rocker\Server;


/**
 * @package Rocker\FacebookLogin
 * @author Victor Jonsson (http://victorjonsson.se)
 * @license MIT license (http://opensource.org/licenses/MIT)
 */
class Utils {

    /**
     * @param Server $server
     * @param string $accessToken
     * @return array
     */
    public static function loadFacebookData(Server $server, $accessToken)
    {
        $config = $server->config('facebook');
        $fb = new \Facebook(array(
            'appId' => $config['id'],
            'secret' => $config['secret']
        ));

        $fb->setAccessToken($accessToken);
        return $fb->api('/me');
    }

    /**
     * @param int $facebookID
     * @param \Rocker\Object\User\UserFactory $userFactory
     * @return \Rocker\Object\User\UserInterface|null
     */
    public static function loadUserByFacebookID($facebookID, UserFactory $userFactory)
    {
        $user = null;
        $cache = Cache::instance();
        if( $userID = $cache->fetch('fb_user_'.$facebookID) ) {
            $user = $userFactory->load($userID);
        } else {
            $users = $userFactory->metaSearch(array(Connect::USER_META_KEY=>$facebookID), 0, 1);
            if( $users->getNumMatching() == 1 ) {
                $user = $users[0];
                $cache->store('fb_user_'.$facebookID, $user->getId(), 3600);
            }
        }
        return $user;
    }
}