<?php

use Phinx\Migration\AbstractMigration;

class AddMangaCollectionTables extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     * The following commands can be used in this method and Phinx will
     * automatically reverse them when rolling back:
     *
     *    createTable
     *    renameTable
     *    addColumn
     *    renameColumn
     *    addIndex
     *    addForeignKey
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
	public function change()
	{
		// Create manga_set table
		$manga_set = $this->table('manga_set', ['id' => FALSE, 'primary_key' => ['hummingbird_id']]);
		$manga_set->addColumn('hummingbird_id', 'biginteger')
			->addColumn('slug', 'string', ['comment' => "URL slug used for image caching and generating links"])
			->addColumn('title', 'string')
			->addColumn('alternate_title', 'string', ['null' => TRUE])
			->addColumn('media_id', 'integer', ['default' => 3, 'null' => TRUE])
			->addColumn('show_type', 'string', ['default' => 'TV', 'null' => TRUE, 'comment' => "TV Series/OVA/etc"])
			->addColumn('age_rating', 'string', ['default' => 'PG13', 'null' => TRUE])
			->addColumn('cover_image', 'string', ['null' => TRUE])
			->addColumn('episode_count', 'integer', ['null' => TRUE])
			->addColumn('episode_length', 'integer', ['null' => TRUE])
			->addColumn('notes', 'text', ['null' => TRUE])
			->addForeignKey('media_id', 'media', 'id')
			->create();

		// Create genre_manga_set_link table
		$genre_manga_set_link = $this->table('genre_manga_set_link', ['id' => FALSE, 'primary_key' => ['hummingbird_id', 'genre_id']]);
		$genre_manga_set_link->addColumn('hummingbird_id', 'biginteger')
			->addColumn('genre_id', 'integer')
			->addForeignKey('hummingbird_id', 'manga_set', 'hummingbird_id')
			->addForeignKey('genre_id', 'genres', 'id')
			->create();
	}
}
