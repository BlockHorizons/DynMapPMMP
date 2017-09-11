<?php

declare(strict_types = 1);

namespace BlockHorizons\DynMapPMMP;

use pocketmine\scheduler\PluginTask;

class SocketListenTask extends PluginTask {

	/** @var resource */
	private $socket = null;

	public function __construct(DynMapPMMP $dynMap, $socket) {
		parent::__construct($dynMap);
		if(is_resource($socket)) {
			$this->socket = $socket;
		}
	}

	public function onRun(int $currentTick) {
		// TODO: Implement onRun() method.
	}
}