<?php

declare(strict_types = 1);

namespace BlockHorizons\DynMapPMMP\format;

use pocketmine\level\format\Chunk;

class DynMap2DChunk extends Chunk {

	/** @var int */
	protected $chunkX = 0;
	/** @var int */
	protected $chunkZ = 0;
	/** @var array */
	protected $blockStore = [];

	public function __construct(Chunk $chunk) {
		$this->chunkX = $chunk->getX();
		$this->chunkZ = $chunk->getZ();
		for($x = 0; $x < 16; $x++) {
			for($z = 0; $z < 16; $z++) {
				$this->blockStore[($z << 4) | $x] = ($chunk->getBlockId($x, $chunk->getHighestBlockAt($x, $z), $z) << 4) | $chunk->getBlockData($x, $chunk->getHighestBlockAt($x, $z), $z);
			}
		}
	}

	/**
	 * @return int
	 */
	public function getX(): int {
		return $this->chunkX;
	}

	/**
	 * @return int
	 */
	public function getZ(): int {
		return $this->chunkZ;
	}

	/**
	 * @param int $x
	 * @param int $z
	 *
	 * @return int
	 */
	public function getBlockIdAt(int $x, int $z): int {
		return $this->blockStore[($z << 4) | $x] >> 4;
	}

	/**
	 * @param int $index
	 *
	 * @return int
	 */
	public function getBlockIdAtIndex(int $index): int {
		return $this->blockStore[$index] >> 4;
	}

	/**
	 * @param int $x
	 * @param int $z
	 *
	 * @return int
	 */
	public function getBlockDataAt(int $x, int $z): int {
		return $this->blockStore[($z << 4) | $x] & 0x0f;
	}

	/**
	 * @param int $index
	 *
	 * @return int
	 */
	public function getBlockDataAtIndex(int $index): int {
		return $this->blockStore[$index];
	}

	/**
	 * @return array
	 */
	public function getAll(): array {
		return $this->blockStore;
	}
}