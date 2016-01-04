<?php
/**
 * Ion
 *
 * Building blocks for web development
 *
 * @package     Ion
 * @author      Timothy J. Warren
 * @copyright   Copyright (c) 2015 - 2016
 * @license     MIT
 */

namespace Aviat\Ion\Di\Exception;

/**
 * Exception for Di Container when trying to access a
 * key that doesn't exist in the container
 */
class NotFoundException extends ContainerException implements \Interop\Container\Exception\NotFoundException {

}
// End of NotFoundException.php