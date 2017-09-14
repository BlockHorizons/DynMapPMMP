<?php

declare(strict_types = 1);

namespace BlockHorizons\DynMapPMMP\format;

class Generated2DChunk {

	/** @var int */
	protected $chunkX = 0;
	/** @var int */
	protected $chunkZ = 0;
	/** @var resource */
	private $base = null;
	/** @var array */
	private $blockData = [];

	public function __construct(DynMap2DChunk $chunk, ImageTable $imageTable) {
		$this->chunkX = $chunk->getX();
		$this->chunkZ = $chunk->getZ();

		$this->base = imagecreatetruecolor(self::getWidth(), self::getWidth());

		$data = [];
		foreach($chunk->getAll() as $index => $blockData) {
			$data[$index] = $imageTable->getImageContentFor($blockData >> 4);
		}
		$this->addData($data);
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
	 * @param array $data
	 */
	public function addData(array $data): void {
		$this->blockData = $data;
	}

	/**
	 * @return int
	 */
	public static function getWidth(): int {
		return 273;
	}

	/**
	 * @return resource
	 */
	public function getImage() {
		foreach($this->blockData as $index => $image) {
			imagecopy($this->base, $image, 1 + 17 * ($index & 0x0f), 1 + 17 * ($index >> 4), 0, 0, 16, 16);
		}
		return $this->base;
	}
}