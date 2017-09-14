<?php

declare(strict_types = 1);

namespace BlockHorizons\DynMapPMMP\format;

use pocketmine\block\BlockIds;

class ImageTable {

	/** @var resource[] */
	private $imageCache = [];

	public function __construct(string $pluginFolder) {
		$reflection = new \ReflectionClass(BlockIds::class);
		foreach($reflection->getConstants() as $name => $id) {
			if(!file_exists($pluginFolder . "images/" . strtolower($name) . ".png")) {
				continue;
			}
			$this->imageCache[$id] = imagecreatefrompng($pluginFolder . "images/" . strtolower($name) . ".png");
		}
	}

	/**
	 * @param int $id
	 * @param int $data
	 *
	 * @return resource
	 */
	public function getImageContentFor(int $id, int $data = 0) {
		if(!isset($this->imageCache[$id])) {
			return $this->imageCache[1];
		}
		return $this->imageCache[$id];
	}
}