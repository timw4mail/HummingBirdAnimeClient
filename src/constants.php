<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 7
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2018  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     4.0
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient;

const DEFAULT_CONTROLLER = Controller\Index::class;
const DEFAULT_CONTROLLER_METHOD = 'index';
const DEFAULT_CONTROLLER_NAMESPACE = Controller::class;
const DEFAULT_LIST_CONTROLLER = Controller\Anime::class;
const ERROR_MESSAGE_METHOD = 'errorPage';
const NOT_FOUND_METHOD = 'notFound';
const SESSION_SEGMENT = 'Aviat\AnimeClient\Auth';
const SRC_DIR = __DIR__;
const USER_AGENT = "Tim's Anime Client/4.0";

// Why doesn't this already exist?
const MILLI_FROM_NANO = 1000 * 1000;