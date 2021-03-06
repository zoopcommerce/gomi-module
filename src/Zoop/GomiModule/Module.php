<?php

/**
 * @package    Zoop
 * @license    MIT
 */

namespace Zoop\GomiModule;

/**
 *
 * @license MIT
 * @link    http://www.doctrine-project.org/
 * @since   0.1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class Module
{
    public function getConfig()
    {
        return include __DIR__ . '/../../../config/module.config.php';
    }
}
