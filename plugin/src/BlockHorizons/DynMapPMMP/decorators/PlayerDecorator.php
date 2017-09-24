<?php

declare(strict_types = 1);

namespace BlockHorizons\DynMapPMMP\decorators;

use BlockHorizons\DynMapPMMP\DynMapPMMP;
use pocketmine\Server;

class PlayerDecorator extends MapDecorator {

	public function saveDecorations(Server $server): array {
		$players = $server->getOnlinePlayers();
	}
}