<?php

declare(strict_types = 1);

namespace BlockHorizons\DynMapPMMP\format;

use pocketmine\level\format\Chunk;
use pocketmine\level\format\ChunkException;
use pocketmine\level\format\io\ChunkUtils;
use pocketmine\level\format\io\region\CorruptedRegionException;
use pocketmine\level\format\io\region\McRegion;
use pocketmine\level\format\io\region\RegionLoader;
use pocketmine\level\format\SubChunk;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\ByteArrayTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntArrayTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\utils\Binary;
use pocketmine\utils\MainLogger;

class FakeRegionLoader extends RegionLoader {

	/** @var string */
	private $worldDir = "";
	/** @var string */
	private $fileExtension = "";

	public function __construct(string $worldDir, int $regionX, int $regionZ, string $fileExtension = McRegion::REGION_FILE_EXTENSION) {
		$this->worldDir = $worldDir;
		$this->x = $regionX;
		$this->z = $regionZ;
		$this->filePath = $worldDir . "region\\r.$regionX.$regionZ.$fileExtension";
		$this->fileExtension = $fileExtension;
	}

	public function open(){
		$exists = file_exists($this->filePath);
		if(!$exists){
			touch($this->filePath);
		}else{
			$fileSize = filesize($this->filePath);
			if($fileSize > self::MAX_REGION_FILE_SIZE){
				throw new CorruptedRegionException("Corrupted oversized region file found, should be a maximum of " . self::MAX_REGION_FILE_SIZE . " bytes, got " . $fileSize . " bytes");
			}elseif($fileSize % 4096 !== 0){
				throw new CorruptedRegionException("Region file should be padded to a multiple of 4KiB");
			}
		}
		$this->filePointer = fopen($this->filePath, "r+b");
		stream_set_read_buffer($this->filePointer, 1024 * 16); //16KB
		stream_set_write_buffer($this->filePointer, 1024 * 16); //16KB
		if(!$exists){
			$this->createBlank();
		}else{
			$this->loadLocationTable();
		}
		$this->lastUsed = time();
	}

	/**
	 * @param string $data
	 *
	 * @return Chunk|null
	 */
	public function nbtDeserialize(string $data){
		switch($this->fileExtension) {
			case "mcr":
				$nbt = new NBT(NBT::BIG_ENDIAN);
				try{
					$nbt->readCompressed($data);
					$chunk = $nbt->getData();
					if(!isset($chunk->Level) or !($chunk->Level instanceof CompoundTag)){
						throw new ChunkException("Invalid NBT format");
					}
					$subChunks = [];
					$chunk = $chunk->Level;
					$fullIds = isset($chunk->Blocks) ? $chunk->Blocks->getValue() : str_repeat("\x00", 32768);
					$fullData = isset($chunk->Data) ? $chunk->Data->getValue() : str_repeat("\x00", 16384);

					for($y = 0; $y < 8; ++$y){
						$offset = ($y << 4);
						$ids = "";
						for($i = 0; $i < 256; ++$i){
							$ids .= substr($fullIds, $offset, 16);
							$offset += 128;
						}
						$data = "";
						$offset = ($y << 3);
						for($i = 0; $i < 256; ++$i){
							$data .= substr($fullData, $offset, 8);
							$offset += 64;
						}
						$subChunks[$y] = new SubChunk($ids, $data);
					}
					if(isset($chunk->BiomeColors)){
						$biomeIds = ChunkUtils::convertBiomeColors($chunk->BiomeColors->getValue());
					}elseif(isset($chunk->Biomes)){
						$biomeIds = $chunk->Biomes->getValue();
					}else{
						$biomeIds = "";
					}
					$heightMap = [];
					if(isset($chunk->HeightMap)){
						if($chunk->HeightMap instanceof ByteArrayTag){
							$heightMap = array_values(unpack("C*", $chunk->HeightMap->getValue()));
						}elseif($chunk->HeightMap instanceof IntArrayTag){
							$heightMap = $chunk->HeightMap->getValue();
						}
					}
					$result = new Chunk(
						$chunk["xPos"],
						$chunk["zPos"],
						$subChunks,
						isset($chunk->Entities) ? $chunk->Entities->getValue() : [],
						isset($chunk->TileEntities) ? $chunk->TileEntities->getValue() : [],
						$biomeIds,
						$heightMap
					);
					$result->setLightPopulated(isset($chunk->LightPopulated) ? ((bool) $chunk->LightPopulated->getValue()) : false);
					$result->setPopulated(isset($chunk->TerrainPopulated) ? ((bool) $chunk->TerrainPopulated->getValue()) : false);
					$result->setGenerated(true);
					return $result;
				}catch(\Throwable $e){
					MainLogger::getLogger()->logException($e);
					return null;
				}
				break;
			default:
			case "mca":
				$nbt = new NBT(NBT::BIG_ENDIAN);
				try{
					$nbt->readCompressed($data);
					$chunk = $nbt->getData();
					if(!isset($chunk->Level) or !($chunk->Level instanceof CompoundTag)){
						throw new ChunkException("Invalid NBT format");
					}
					$chunk = $chunk->Level;
					$subChunks = [];
					if($chunk->Sections instanceof ListTag){
						foreach($chunk->Sections as $subChunk){
							if($subChunk instanceof CompoundTag){
								$subChunks[$subChunk->Y->getValue()] = new SubChunk(
									ChunkUtils::reorderByteArray($subChunk->Blocks->getValue()),
									ChunkUtils::reorderNibbleArray($subChunk->Data->getValue()),
									ChunkUtils::reorderNibbleArray($subChunk->SkyLight->getValue(), "\xff"),
									ChunkUtils::reorderNibbleArray($subChunk->BlockLight->getValue())
								);
							}
						}
					}
					if(isset($chunk->BiomeColors)){
						$biomeIds = ChunkUtils::convertBiomeColors($chunk->BiomeColors->getValue()); //Convert back to original format
					}elseif(isset($chunk->Biomes)){
						$biomeIds = $chunk->Biomes->getValue();
					}else{
						$biomeIds = "";
					}
					$result = new Chunk(
						$chunk["xPos"],
						$chunk["zPos"],
						$subChunks,
						isset($chunk->Entities) ? $chunk->Entities->getValue() : [],
						isset($chunk->TileEntities) ? $chunk->TileEntities->getValue() : [],
						$biomeIds,
						isset($chunk->HeightMap) ? $chunk->HeightMap->getValue() : []
					);
					$result->setLightPopulated(isset($chunk->LightPopulated) ? ((bool) $chunk->LightPopulated->getValue()) : false);
					$result->setPopulated(isset($chunk->TerrainPopulated) ? ((bool) $chunk->TerrainPopulated->getValue()) : false);
					$result->setGenerated(true);
					return $result;
				}catch(\Throwable $e){
					MainLogger::getLogger()->logException($e);
					return null;
				}
				break;
			case "mcapm":
				$nbt = new NBT(NBT::BIG_ENDIAN);
				try{
					$nbt->readCompressed($data);
					$chunk = $nbt->getData();
					if(!isset($chunk->Level) or !($chunk->Level instanceof CompoundTag)){
						throw new ChunkException("Invalid NBT format");
					}
					$chunk = $chunk->Level;
					$subChunks = [];
					if($chunk->Sections instanceof ListTag){
						foreach($chunk->Sections as $subChunk){
							if($subChunk instanceof CompoundTag){
								$subChunks[$subChunk->Y->getValue()] = new SubChunk(
									$subChunk->Blocks->getValue(),
									$subChunk->Data->getValue(),
									$subChunk->SkyLight->getValue(),
									$subChunk->BlockLight->getValue()
								);
							}
						}
					}
					$result = new Chunk(
						$chunk["xPos"],
						$chunk["zPos"],
						$subChunks,
						isset($chunk->Entities) ? $chunk->Entities->getValue() : [],
						isset($chunk->TileEntities) ? $chunk->TileEntities->getValue() : [],
						isset($chunk->Biomes) ? $chunk->Biomes->getValue() : "",
						isset($chunk->HeightMap) ? $chunk->HeightMap->getValue() : []
					);
					$result->setLightPopulated(isset($chunk->LightPopulated) ? ((bool) $chunk->LightPopulated->getValue()) : false);
					$result->setPopulated(isset($chunk->TerrainPopulated) ? ((bool) $chunk->TerrainPopulated->getValue()) : false);
					$result->setGenerated(true);
					return $result;
				}catch(\Throwable $e){
					MainLogger::getLogger()->logException($e);
					return null;
				}
				break;
		}

	}

	public function readChunk(int $x, int $z){
		$index = self::getChunkOffset($x, $z);
		if($index < 0 or $index >= 4096){
			return null;
		}
		$this->lastUsed = time();
		if(!$this->isChunkGenerated($index)){
			return null;
		}
		fseek($this->filePointer, $this->locationTable[$index][0] << 12);
		$length = Binary::readInt(fread($this->filePointer, 4));
		$compression = ord(fgetc($this->filePointer));
		if($length <= 0 or $length > self::MAX_SECTOR_LENGTH){ //Not yet generated / corrupted
			if($length >= self::MAX_SECTOR_LENGTH){
				$this->locationTable[$index][0] = ++$this->lastSector;
				$this->locationTable[$index][1] = 1;
				MainLogger::getLogger()->error("Corrupted chunk header detected");
			}
			return null;
		}
		if($length > ($this->locationTable[$index][1] << 12)){ //Invalid chunk, bigger than defined number of sectors
			MainLogger::getLogger()->error("Corrupted bigger chunk detected");
			$this->locationTable[$index][1] = $length >> 12;
			$this->writeLocationIndex($index);
		}elseif($compression !== self::COMPRESSION_ZLIB and $compression !== self::COMPRESSION_GZIP){
			MainLogger::getLogger()->error("Invalid compression type");
			return null;
		}
		$chunk = $this->nbtDeserialize(fread($this->filePointer, $length - 1));
		if($chunk instanceof Chunk){
			return new DynMap2DChunk($chunk);
		}
		MainLogger::getLogger()->error("Corrupted chunk detected");
		return null;
	}
}