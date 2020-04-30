<?php

use Phinx\Migration\AbstractMigration;

class AnimeCollectionCleanup extends AbstractMigration
{
	public function up()
	{
		if ($this->hasTable('genre_anime_set_link'))
		{
			$this->table('genre_anime_set_link')
				->rename('anime_set_genre_link')
				->update();
		}
	}

	public function down()
	{
		if ($this->hasTable('anime_set_genre_link'))
		{
			$this->table('anime_set_genre_link')
				->rename('genre_anime_set_link')
				->update();
		}
	}
}
