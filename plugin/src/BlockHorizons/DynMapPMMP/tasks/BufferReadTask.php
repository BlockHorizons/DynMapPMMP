<?php

declare(strict_types = 1);

namespace BlockHorizons\DynMapPMMP\tasks;

use BlockHorizons\DynMapPMMP\DynMapPMMP;
use pocketmine\scheduler\PluginTask;

class BufferReadTask extends PluginTask {

	/** @var resource */
	private $socket = null;

	public function __construct(DynMapPMMP $dynMap, $socket) {
		parent::__construct($dynMap);
		if(is_resource($socket)) {
			$this->socket = $socket;
		}
	}

	public function onRun(int $currentTick): void {
		$buffer = socket_read($this->socket, 128);
		/** @var DynMapPMMP $owner */
		$owner = $this->getOwner();
		switch($buffer) {
			default:
				return;
			case "REQUEST_INITIAL_REGION":
				$owner->requestRegion(0, 0);
				return;
		}
	}
}