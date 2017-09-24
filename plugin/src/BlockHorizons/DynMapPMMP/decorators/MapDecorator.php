<?php

declare(strict_types = 1);

namespace BlockHorizons\DynMapPMMP\decorators;

use BlockHorizons\DynMapPMMP\decorators\decoration\Decoration;
use BlockHorizons\DynMapPMMP\DynMapPMMP;
use pocketmine\Server;

abstract class MapDecorator {

	/** @var array */
	private $data = [];
	/** @var MapDecorator[] */
	private static $knownDecorators = [];

	public final function __construct(DynMapPMMP $dynMap) {
		$this->storeDecorations($dynMap->getServer());
	}

	/**
	 * @param MapDecorator $decoration
	 */
	public static function register(MapDecorator $decoration): void {
		self::$knownDecorators[] = $decoration;
	}

	/**
	 * @param Server $server
	 */
	private function storeDecorations(Server $server): void {
		$this->data = $this->saveDecorations($server);
	}

	/**
	 * @return array
	 */
	public function getStoredData(): array {
		return $this->data;
	}

	/**
	 * @param Server $server
	 *
	 * @return array
	 */
	public abstract function saveDecorations(Server $server): array;
}