<?php

declare(strict_types=1);

namespace melodylan\texthologram\hologram;

use JsonSerializable;
use melodylan\texthologram\utils\PositionHash;
use pocketmine\entity\Entity;
use pocketmine\network\mcpe\protocol\AddPlayerPacket;
use pocketmine\network\mcpe\protocol\AdventureSettingsPacket;
use pocketmine\network\mcpe\protocol\RemoveActorPacket;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataFlags;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataProperties;
use pocketmine\network\mcpe\protocol\types\entity\FloatMetadataProperty;
use pocketmine\network\mcpe\protocol\types\entity\LongMetadataProperty;
use pocketmine\network\mcpe\protocol\types\inventory\ItemStack;
use pocketmine\network\mcpe\protocol\types\inventory\ItemStackWrapper;
use pocketmine\player\Player;
use pocketmine\world\Position;
use Ramsey\Uuid\Nonstandard\Uuid;

abstract class Hologram implements JsonSerializable{

	protected int $id;

	protected AddPlayerPacket $addPlayerPacket;

	protected RemoveActorPacket $removeActorPacket;

	protected array $viewers = [];

	public function __construct(
		protected Position $position,
		protected string $text,
		protected float $radius
	){
		$this->id = Entity::nextRuntimeId();

		$this->addPlayerPacket = new AddPlayerPacket();
		$this->addPlayerPacket->uuid = Uuid::uuid4();
		$this->addPlayerPacket->username = str_replace('(n)', PHP_EOL, $this->text);
		$this->addPlayerPacket->actorRuntimeId = $this->addPlayerPacket->actorUniqueId = $this->id;
		$this->addPlayerPacket->position = $this->position->add(0.5, 0, 0.5);
		$this->addPlayerPacket->item = ItemStackWrapper::legacy(ItemStack::null());
		$this->addPlayerPacket->metadata = [
			EntityMetadataProperties::FLAGS => new LongMetadataProperty(1 << EntityMetadataFlags::IMMOBILE),
			EntityMetadataProperties::SCALE => new FloatMetadataProperty(0.01)
		];
		$this->addPlayerPacket->adventureSettingsPacket = AdventureSettingsPacket::create(0, 0, 0, 0, 0, $this->addPlayerPacket->actorRuntimeId);

		$this->removeActorPacket = RemoveActorPacket::create($this->addPlayerPacket->actorRuntimeId);
	}

	public function jsonSerialize(): array{
		return [
			PositionHash::toHash($this->position),
			$this->text,
			$this->radius
		];
	}

	public function getViewers(): array{
		return $this->viewers;
	}

	public function isViewer(Player $player): bool{
		return isset($this->viewers[$player->getUniqueId()->getBytes()]);
	}

	public function spawnTo(Player $player): bool{
		return !(!$this->canSpawn($player) || $this->position->distance($player->getPosition()) > $this->radius);
	}

	public function despawnTo(Player $player): bool{
		return !$this->canSpawn($player);
	}

	public function getRadius(): float{
		return $this->radius;
	}

	public function canSpawn(Player $player) : bool{
		return $this->position->isValid() && !$this->isViewer($player);
	}

	public function getText() : string{
		return $this->text;
	}

	abstract public function canSave(): bool;
}