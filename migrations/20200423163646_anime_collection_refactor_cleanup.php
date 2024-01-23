<?php declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

class AnimeCollectionRefactorCleanup extends AbstractMigration
{
	protected array $newMediaTypes = [
		'LaserDisc',
		'VHS',
		'Digital',
		'Video CD',
		'Betamax',
		'UMD',
		'Other',
	];

	public function up()
	{
		// Add some new media types
		$moreMediaTypes = [];

		foreach ($this->newMediaTypes as $id => $medium)
		{
			$moreMediaTypes[] = [
				'id' => $id + 5,
				'type' => $medium,
			];
		}
		$this->table('media')->insert($moreMediaTypes)->save();

		// Remove foreign key and media_id column from anime_set
		$animeSet = $this->table('anime_set');
		if ($animeSet->hasColumn('media_id'))
		{
			$animeSet->dropForeignKey('media_id')->save();
			$animeSet->removeColumn('media_id')->save();
		}

		// Cleanup existing media types a bit
		$this->execute("UPDATE media SET type='Bootleg' WHERE id=4");
		$this->execute('DELETE FROM media WHERE id=1');
	}

	public function down()
	{
		// Restore the original values for existing media
		$this->execute("INSERT INTO media (id, type) VALUES (1, 'DVD & Blu-ray')");
		$this->execute("UPDATE media SET type='Bootleg DVD' WHERE id=4");

		// Remove the new media types
		$values = array_map(static fn ($medium) => "'{$medium}'", $this->newMediaTypes);
		$valueList = implode(',', $values);
		$this->execute("DELETE FROM media WHERE type IN ({$valueList})");
	}
}
