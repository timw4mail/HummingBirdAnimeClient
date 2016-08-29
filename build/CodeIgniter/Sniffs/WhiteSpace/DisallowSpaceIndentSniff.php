<?php
/**
 * CodeIgniter_Sniffs_WhiteSpace_DisallowSpaceIndentSniff.
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PHP_CodeSniffer
 * @author    Thomas Ernest <thomas.ernest@gmail.com>
 * @copyright 2011 Thomas ERNEST
 * @license   http://thomas.ernest.fr/developement/php_cs/licence GNU General Public License
 * @link      http://pear.php.net/package/PHP_CodeSniffer
 */

/**
 * CodeIgniter_Sniffs_WhiteSpace_DisallowSpaceIndentSniff.
 *
 * Ensures the use of tabs for indentation.
 *
 * @category  PHP
 * @package   PHP_CodeSniffer
 * @author    Thomas Ernest <thomas.ernest@gmail.com>
 * @copyright 2011 Thomas ERNEST
 * @license   http://thomas.ernest.fr/developement/php_cs/licence GNU General Public License
 * @link      http://pear.php.net/package/PHP_CodeSniffer
 */

namespace CodeIgniter\Sniffs\WhiteSpace;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

class DisallowSpaceIndentSniff implements Sniff
{

    /**
     * A list of tokenizers this sniff supports.
     *
     * @var array
     */
    public $supportedTokenizers = array(
                                   'PHP',
                                   'JS',
                                   'CSS',
                                  );


    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register()
    {
        return array(T_WHITESPACE);
    }//end register()


    /**
     * Processes this test, when one of its tokens is encountered.
     *
     * @param File $phpcsFile All the tokens found in the document.
     * @param int                  $stackPtr  The position of the current token
     *                                        in the stack passed in $tokens.
     *
     * @return void
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        // Make sure this is whitespace used for indentation.
        $line = $tokens[$stackPtr]['line'];
        if ($stackPtr > 0 && $tokens[($stackPtr - 1)]['line'] === $line) {
            return;
        }

        if (strpos($tokens[$stackPtr]['content'], " ") !== false) {
            $error = 'Tabs must be used to indent lines; spaces are not allowed for code indentation';
            $phpcsFile->addError($error, $stackPtr);
        }
    }//end process()


}//end class

?>
