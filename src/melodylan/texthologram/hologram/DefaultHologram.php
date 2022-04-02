<?php

declare(strict_types=1);

namespace melodylan\texthologram\hologram;

use pocketmine\player\Player;

class DefaultHologram extends Hologram{

	public function canSave() : bool{
		return true;
	}

	public function spawnTo(Player $player) : bool{
		if(!parent::spawnTo($player)){
			return false;
		}
		$player->getNetworkSession()->sendDataPacket($this->addPlayerPacket);
		$this->viewers[$player->getUniqueId()->getBytes()] = true;
		return true;
	}

	public function despawnTo(Player $player) : bool{
		if(!parent::despawnTo($player)){
			return false;
		}
		$player->getNetworkSession()->sendDataPacket($this->removeActorPacket);
		unset($this->viewers[$player->getUniqueId()->getBytes()]);
		return true;
	}
}
