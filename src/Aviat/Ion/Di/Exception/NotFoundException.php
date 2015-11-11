<?php

namespace Aviat\Ion\Di\Exception;

/**
 * Exception for Di Container when trying to access a
 * key that doesn't exist in the container
 */
class NotFoundException extends ContainerException implements \Interop\Container\Exception\NotFoundException {

}
// End of NotFoundException.php