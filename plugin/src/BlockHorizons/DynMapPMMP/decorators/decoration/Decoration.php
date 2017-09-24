<?php

declare(strict_types = 1);

namespace BlockHorizons\DynMapPMMP\decorators\decoration;

use BlockHorizons\DynMapPMMP\DynMapPMMP;
use pocketmine\Server;

abstract class Decoration {

	public function __construct(DynMapPMMP $dynMap) {
		$this->getImage($dynMap->getServer());
	}

	/**
	 * @param Server $server
	 *
	 * @return string obtained using image
	 */
	public abstract function getImage(Server $server): string;
}