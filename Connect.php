<?php
namespace Rocker\FacebookLogin;

use Rocker\Object\DuplicationException;
use Rocker\Object\User\UserInterface;
use Rocker\REST\OperationResponse;
use Rocker\Server;
use Rocker\Cache\CacheInterface;
use Rocker\Object\User\UserFactory;
use Rocker\REST\AbstractOperation;
use Fridge\DBAL\Connection\ConnectionInterface;


/**
 * API Operation that can connect Rocker users with Facebook users
 *
 * @package Rocker\FacebookLogin
 * @author Victor Jonsson (http://victorjonsson.se)
 * @license MIT license (http://opensource.org/licenses/MIT)
 */
class Connect extends AbstractOperation {

    const USER_META_KEY = 'fb_id';

    /**
     * Execute the operation and return response to client
     * @param \Rocker\Server $server
     * @param \Fridge\DBAL\Connection\ConnectionInterface $db
     * @param \Rocker\Cache\CacheInterface $cache
     * @internal param \Slim\Slim $app
     * @return \Rocker\REST\OperationResponse
     */
    public function exec(\Rocker\Server $server, ConnectionInterface $db, CacheInterface $cache)
    {
        $userFactory = new UserFactory($db, $cache);
        $config = $server->config('facebook');

        // Connect exiting user
        if( isset($_REQUEST['user']) ) {
            $user = $userFactory->load($_REQUEST['user']);
            if( !$user ) {
                return new OperationResponse(400, array('error'=>'User does not exist'));
            } else {
                $userData = Utils::loadFacebookData($server, $_REQUEST['access_token']);
                $this->connectUser($user, $userData, $config, $userFactory);
                $userArray = $server->applyFilter('user.array', $user->toArray(), $db, $cache);
                return new OperationResponse(200, $userArray);
            }
        }

        // Create new user and connect
        else {

            $facebookData = Utils::loadFacebookData($server, $_REQUEST['access_token']);
            $user = Utils::loadUserByFacebookID($facebookData['id'], $userFactory);

            // FB user is already connected
            if( $user ) {
                return new OperationResponse(409, array('error'=>'This facebook user is already connected to user #'.$user->getId()));
            }

            try {

                // Create user
                $user = $userFactory->createUser(
                    $facebookData['email'],
                    $facebookData['name'],
                    $this->makeSuperHardPassword()
                );

                $this->connectUser($user, $facebookData, $config, $userFactory);

                // Return user
                $userArray = $server->applyFilter('user.array', $user->toArray(), $db, $cache);
                return new OperationResponse(200, $userArray);

            } catch(DuplicationException $e) {
                return new OperationResponse(400, array('error'=>'E-mail of facebook user is already registered'));
            }
        }
    }

    /**
     * @param UserInterface $user
     * @param array $facebookData
     * @param array $config
     * @param UserFactory $userFactory
     */
    protected function connectUser(UserInterface $user, $facebookData, $config, UserFactory $userFactory)
    {
        if( !empty($config['connect_data']) && $fields = explode(',', $config['connect_data'])) {
            foreach($fields as $field) {
                if( isset($facebookData[$field]) ) {
                    $user->meta()->set($field, $facebookData[$field]);
                }
            }
        }
        $user->meta()->set(self::USER_META_KEY, $facebookData['id']);
        $userFactory->update($user);
    }

    /**
     * @return string
     */
    private function makeSuperHardPassword()
    {
        $str = 'qwertyuiopasdfghjklzxcvbnm.,0987654321!#%&';
        $len = strlen($str);
        $pass = '';
        for($i=0;$i<26;$i++) {
            $pass .= substr($str, mt_rand(0,$len), 1);
        }
        return $pass;
    }

    /**
     * @inheritdoc
     */
    public function allowedMethods()
    {
        return array('POST');
    }

    /**
     * @return array
     */
    public function requiredArgs()
    {
        return array('access_token');
    }
}