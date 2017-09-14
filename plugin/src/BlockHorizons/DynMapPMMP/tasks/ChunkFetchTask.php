<?php

declare(strict_types = 1);

namespace BlockHorizons\DynMapPMMP\tasks;

use BlockHorizons\DynMapPMMP\format\FakeRegionLoader;
use BlockHorizons\DynMapPMMP\format\Generated2DChunk;
use BlockHorizons\DynMapPMMP\format\GeneratedMap;
use BlockHorizons\DynMapPMMP\format\ImageTable;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;

class ChunkFetchTask extends AsyncTask {

	/** @var string */
	private $worldsDir = "";
	/** @var null|resource */
	private $image = null;
	/** @var string */
	private $pluginDir = "";

	public function __construct(string $serverDir) {
		$this->worldsDir = $serverDir . "worlds/";
		$this->pluginDir = $serverDir . "plugins/DynMapPMMP/";
	}

	public function onRun() {
		$chunks = [];
		$imageTable = new ImageTable($this->pluginDir);
		foreach(scandir($this->worldsDir, SCANDIR_SORT_NONE) as $level) {
			if($level === "." || $level === "..") {
				continue;
			}
			foreach(scandir($this->worldsDir . $level . "/region", SCANDIR_SORT_NONE) as $regionFile) {
				if($regionFile === "." || $regionFile === "..") {
					continue;
				}
				$index = explode(".", $regionFile);
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
		}
		$generatedMap = (new GeneratedMap($chunks))->getImage();
		$this->image = $generatedMap;
	}

	public function onCompletion(Server $server) {
		$plugin = $server->getPluginManager()->getPlugin("DynMapPMMP");
		if(!($plugin instanceof PluginBase)) {
			return;
		}
		imagepng($this->image, $plugin->getDataFolder() . "map" . random_int(0, 1000) . ".png");
	}
}