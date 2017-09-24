<?php

declare(strict_types = 1);

namespace BlockHorizons\DynMapPMMP\decorators\decoration;

use BlockHorizons\DynMapPMMP\DynMapPMMP;
use pocketmine\Server;

class PlayerDecoration extends Decoration {

	public function __construct(DynMapPMMP $dynMap) {
		parent::__construct($dynMap);
	}

	public function getImage(Server $server): string {
		// TODO: Implement getImage() method.
	}
}