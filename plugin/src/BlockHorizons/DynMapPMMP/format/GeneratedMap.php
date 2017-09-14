<?php

declare(strict_types = 1);

namespace BlockHorizons\DynMapPMMP\format;

class GeneratedMap {

	/** @var Generated2DChunk[] */
	private $chunks = [];
	/** @var int */
	private $maxWidth = 0;
	/** @var int */
	private $minWidth = 0;
	/** @var int */
	private $maxHeight = 0;
	/** @var int */
	private $minHeight = 0;

	public function __construct(array $chunks = []) {
		foreach($chunks as $chunk) {
			$this->addChunk($chunk);
		}
	}

	/**
	 * @param Generated2DChunk $chunk
	 */
	public function addChunk(Generated2DChunk $chunk) {
		$this->chunks[] = $chunk;
		if($chunk->getX() > $this->maxWidth) {
			$this->maxWidth = $chunk->getX();
		} elseif($chunk->getX() < $this->minWidth) {
			$this->minWidth = $chunk->getX();
		}
		if($chunk->getZ() > $this->maxHeight) {
			$this->maxHeight = $chunk->getZ();
		} elseif($chunk->getZ() < $this->minHeight) {
			$this->minHeight = $chunk->getZ();
		}
	}

	/**
	 * @return resource
	 */
	public function getImage() {
		$processedChunks = 0;
		$totalChunks = count($this->chunks);
		$totalWidth = ($this->maxWidth - $this->minWidth) * Generated2DChunk::getWidth();
		$totalHeight = ($this->maxHeight - $this->minHeight) * Generated2DChunk::getWidth();
		$newImage = imagecreatetruecolor($totalWidth, $totalHeight);
		foreach($this->chunks as $chunk) {
			imagecopy($newImage, $chunk->getImage(), $chunk::getWidth() * $chunk->getX(), $chunk::getWidth() * $chunk->getZ(), 0, 0, $chunk::getWidth(), $chunk::getWidth());
			var_dump($processedChunks++ . "/" . $totalChunks);
		}
		return $newImage;
	}
}