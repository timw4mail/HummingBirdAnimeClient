<?php

use Aviat\Ion\Config;
use Aviat\Ion\Friend;

use Aviat\AnimeClient\Model\AnimeCollection as AnimeCollectionModel;

class AnimeCollectionModelTest extends AnimeClient_TestCase {

	public function setUp()
	{
		parent::setUp();
		$this->container->setInstance('config', new Config([
			'database' => [
				'collection' => [
					'type' => 'sqlite',
					'host' => '',
					'user' => '',
					'pass' => '',
					'port' => '',
					'name' => 'default',
					'database'   => '',
					'file' => ':memory:',
				]
			]
		]));
		$this->config = $this->container->get('config');
		$this->collectionModel = new AnimeCollectionModel($this->container);
	}

	public function testSanity()
	{
		$friend = new Friend($this->collectionModel);
		$this->assertInstanceOf('Aviat\Ion\Model\DB', $this->collectionModel);
		$this->assertInstanceOf('Aviat\AnimeClient\Model\Anime', $friend->anime_model);
	}

	public function testInvalidDatabase()
	{
		$this->container->setInstance('config', new Config([
			'database' => [
				'collection' => [
					'type' => 'sqlite',
					'host' => '',
					'user' => '',
					'pass' => '',
					'port' => '',
					'name' => 'default',
					'database'   => '',
					'file' => __FILE__,
				]
			]
		]));
		$collectionModel = new Friend(new AnimeCollectionModel($this->container));
		$this->assertFalse($collectionModel->valid_database);
	}

	public function testNonExistentDatabase()
	{
		$this->container->setInstance('config', new Config([
			'database' => [
				'collection' => [
					'type' => 'sqlite',
					'host' => '',
					'user' => '',
					'pass' => '',
					'port' => '',
					'name' => 'default',
					'database'   => '',
					'file' => '/foo/bar/baz/foobazbar.db',
				]
			]
		]));
		$collectionModel = new Friend(new AnimeCollectionModel($this->container));
		$this->assertFalse($collectionModel->valid_database);
	}
}