<?php
namespace Rocker\FacebookLogin;

use Rocker\Server;
use Rocker\Cache\Cache;
use Rocker\Object\User\UserFactory;
use Rocker\Object\User\UserInterface;
use Rocker\REST\Authenticator as RockerAuthenticator;


/**
 * Authentication class for PHP-Rocker that can authorize client using
 * Facebook access token. This authentication method will be executed
 * when requesting the API with a authorization header
 * like "Authorization: facebook [id]:[access-token]"
 *
 *  Authorization: facebook 19402398:aod3lkn24l2wkm32kl13mfl2342mf2k34
 *
 * @package Rocker\FacebookLogin
 * @author Victor Jonsson (http://victorjonsson.se)
 * @license MIT license (http://opensource.org/licenses/MIT)
 */
class Authenticator extends RockerAuthenticator {

    /**
     * @param $data
     * @param \Rocker\Server $server
     * @return \Rocker\Object\User\UserInterface|null
     */
    public function facebookAuth($data, Server $server)
    {
        list($facebookID, $accessToken) = explode(':', $data);
        $user = Utils::loadUserByFacebookID($facebookID, $this->userFactory);

        if( $user && !$this->isValidToken($accessToken, $user) ) {

            $facebookUser = Utils::loadFacebookData($server, $accessToken);

            if( $facebookID != $facebookUser['id'] ) {
                $user = null;
            } else {
                $user->meta()->set('fb_token_expires', time()+1800);
                $user->meta()->set('fb_access_token', $accessToken);

                $this->userFactory->update($user);
            }
        }

        return $user;
    }

    /**
     * @param string $accessToken
     * @param UserInterface $user
     * @return bool
     */
    private function isValidToken($accessToken, $user)
    {
        return $user->meta()->get('fb_access_token') == $accessToken &&
                $user->meta()->get('fb_token_expires') < time();
    }

    /**
     * @inheritdoc
     */
    public function rc4Auth($data, $server)
    {
        $conf = $server->config('facebook');
        if( empty($conf['disabled_auth_mechanisms']) || !in_array('rc4', explode(',', $conf['disabled_auth_mechanisms']))) {
            return parent::rc4Auth($data, $server);
        }
        return null;
    }

    /**
     * @inheritdoc
     */
    public function basicAuth($data, $server)
    {
        $conf = $server->config('facebook');
        if( empty($conf['disabled_auth_mechanisms']) || !in_array('basic', explode(',', $conf['disabled_auth_mechanisms']))) {
            return parent::rc4Auth($data, $server);
        }
        return null;
    }
}