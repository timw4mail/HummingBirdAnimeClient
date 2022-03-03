<?php declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

class FirstMigration extends AbstractMigration
{
	/**
	 * Migrate up
	 */
	public function change()
	{
		// Create media table
		$this->table('media')
			->addColumn('type', 'string')
			->create();

		// Add items to media table
		if ($this->hasTable('media'))
		{
			foreach (['DVD & Blu-ray', 'Blu-ray', 'DVD', 'Bootleg DVD'] as $type)
			{
				$this->execute('INSERT INTO "media" ("type") VALUES (\'' . $type . '\')');
			}
		}

		// Create anime_set table
		$anime_set = $this->table('anime_set', ['id' => FALSE, 'primary_key' => ['hummingbird_id']]);
		$anime_set->addColumn('hummingbird_id', 'biginteger')
			->addColumn('slug', 'string', ['comment' => 'URL slug used for image caching and generating links'])
			->addColumn('title', 'string')
			->addColumn('alternate_title', 'string', ['null' => TRUE])
			->addColumn('media_id', 'integer', ['default' => 3, 'null' => TRUE])
			->addColumn('show_type', 'string', ['default' => 'TV', 'null' => TRUE, 'comment' => 'TV Series/OVA/etc'])
			->addColumn('age_rating', 'string', ['default' => 'PG13', 'null' => TRUE])
			->addColumn('cover_image', 'string', ['null' => TRUE])
			->addColumn('episode_count', 'integer', ['null' => TRUE])
			->addColumn('episode_length', 'integer', ['null' => TRUE])
			->addColumn('notes', 'text', ['null' => TRUE])
			->addForeignKey('media_id', 'media', 'id')
			->create();

		// Create genres table
		$this->table('genres')
			->addColumn('genre', 'string')
			->addIndex('genre', ['unique' => TRUE])
			->create();

		// Create genre_anime_set_link table
		$genre_anime_set_link = $this->table('genre_anime_set_link', ['id' => FALSE, 'primary_key' => ['hummingbird_id', 'genre_id']]);
		$genre_anime_set_link->addColumn('hummingbird_id', 'biginteger')
			->addColumn('genre_id', 'integer')
			->addForeignKey('hummingbird_id', 'anime_set', 'hummingbird_id')
			->addForeignKey('genre_id', 'genres', 'id')
			->create();
	}
}
