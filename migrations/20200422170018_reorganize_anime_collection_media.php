<?php

use Phinx\Migration\AbstractMigration;

class ReorganizeAnimeCollectionMedia extends AbstractMigration
{
	public function up()
	{
		// Create the new link table
		if ( ! $this->hasTable('anime_set_media_link'))
		{
			$newLinkTable = $this->table('anime_set_media_link', [
				'id' => FALSE,
				'primary_key' => ['hummingbird_id', 'media_id']
			]);

			$newLinkTable->addColumn('hummingbird_id', 'biginteger')
				->addColumn('media_id', 'biginteger')
				->addForeignKey('media_id', 'media', 'id')
				->addForeignKey('hummingbird_id', 'anime_set', 'hummingbird_id')
				->create();
		}

		// Get the old link entries
		$insertRows = [];
		$rows = $this->fetchAll('SELECT hummingbird_id, media_id from anime_set');

		// Filter the numeric keys out of the row results
		foreach ($rows as $row)
		{
			$keys = array_keys($row);
			foreach ($keys as $k)
			{
				if (is_numeric($k))
				{
					unset($row[$k]);
				}
			}
			$insertRows[] = $row;
		}


		// And put them in the new table
		$linkTable = $this->table('anime_set_media_link');
		$linkTable->insert($insertRows)->save();

		// Get the rows where you have the combined media type (DVD & Bluray)
		// and replace those rows with the individual entries
		$linkRows = $this->fetchAll('SELECT hummingbird_id FROM anime_set_media_link WHERE media_id=1');
		$insertRows = [];
		foreach ($linkRows as $row)
		{
			$insertRows[] = [
				'hummingbird_id' => $row['hummingbird_id'],
				'media_id' => 2,
			];
			$insertRows[] = [
				'hummingbird_id' => $row['hummingbird_id'],
				'media_id' => 3,
			];
		}
		$linkTable->insert($insertRows)->save();

		// Finally, delete the old combined media type rows
		$this->execute('DELETE FROM anime_set_media_link WHERE media_id=1');
	}

	public function down()
	{
		if ($this->hasTable('anime_set_media_link'))
		{
			$this->table('anime_set_media_link')->drop()->save();
		}
	}
}
