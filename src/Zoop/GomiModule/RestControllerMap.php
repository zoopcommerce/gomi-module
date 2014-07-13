<?php

/**
 * @link       http://zoopcommerce.github.io/gomi
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\GomiModule;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zoop\ShardModule\RestControllerMap as ShardRestControllerMap;

/**
 *
 * @since   1.0
 * @author  Josh Stuart <josh.stuart@zoopcommerce.com>
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class RestControllerMap extends ShardRestControllerMap implements ServiceLocatorAwareInterface
{
    /**
     * Merges the gomi and shard module configs
     *
     * @return array
     */
    protected function getConfig()
    {
        if (!isset($this->config)) {
            $gomiOptions = $this->serviceLocator
                ->get('config')['zoop']['gomi']['recover_password_token_controller_options'];

            $shardOptions = $this->serviceLocator
                ->get('config')['zoop']['shard']['rest'];

            $this->config = array_merge($gomiOptions, $shardOptions);
        }

        return $this->config;
    }
}
