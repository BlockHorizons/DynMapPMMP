<?php

declare(strict_types = 1);

namespace BlockHorizons\DynMapPMMP\tasks;

use BlockHorizons\DynMapPMMP\DynMapPMMP;
use BlockHorizons\DynMapPMMP\format\FakeRegionLoader;
use BlockHorizons\DynMapPMMP\format\Generated2DChunk;
use BlockHorizons\DynMapPMMP\format\GeneratedMap;
use BlockHorizons\DynMapPMMP\format\ImageTable;
use pocketmine\level\Level;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;

class ImageRegionFetchThread extends AsyncTask {

	/** @var string */
	private $worldsDir = "";
	/** @var string */
	private $pluginDir = "";
	/** @var int */
	private $regionX = 0;
	/** @var int */
	private $regionZ = 0;
	/** @var string */
	private $levelName = "";

	public function __construct(string $serverDir, int $regionX, int $regionZ, Level $level) {
		$this->worldsDir = $serverDir . "worlds/";
		$this->pluginDir = $serverDir . "plugins/DynMapPMMP/";
		$this->regionX = $regionX;
		$this->regionZ = $regionZ;
		$this->levelName = $level->getFolderName();
	}

	public function onRun(): void {
		$chunks = [];
		$imageTable = new ImageTable($this->pluginDir);
		$level = $this->levelName;
		foreach(scandir($this->worldsDir . $level . "/region", SCANDIR_SORT_NONE) as $regionFile) {
			if($regionFile === "." || $regionFile === "..") {
				continue;
			}
			$index = explode(".", $regionFile);
			if((int) $index[1] !== $this->regionX || (int) $index[2] !== $this->regionZ) {
				continue;
			}
			$loader = new FakeRegionLoader($this->worldsDir  . $level . "\\", (int) $index[1], (int) $index[2], $index[3]);
			$loader->open();
			for($x = 0; $x < 32; $x++) {
				for($z = 0; $z < 32; $z++) {
					if(($chunk = $loader->readChunk($x, $z)) !== null) {
						$chunks[] = new Generated2DChunk($chunk, $imageTable);
					}
				}
			}
		}
		$generatedMap = (new GeneratedMap($chunks))->getImage();

		ob_start();
		imagepng($generatedMap);
		$imageData = ob_get_contents();
		ob_end_clean();
		$this->setResult($imageData);
	}

	public function onCompletion(Server $server): void {
		$plugin = $server->getPluginManager()->getPlugin("DynMapPMMP");
		if(!($plugin instanceof DynMapPMMP)) {
			return;
		}
		if(!$plugin->isEnabled()) {
			return;
		}
		$plugin->submitImage($this->getResult());
	}
}