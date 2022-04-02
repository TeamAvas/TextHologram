<?php

declare(strict_types=1);

namespace melodylan\texthologram\utils;

use pocketmine\Server;
use pocketmine\world\Position;

final class PositionHash{

	public static function toHash(Position $position): string{
		$floor = $position->floor();
		return $floor->x . ":" . $floor->y . ':' . $floor->z . ':' . $position->world->getFolderName();
	}

	public static function toPosition(string $hash): Position{
		[$x, $y, $z, $world] = explode(':', $hash);
		return new Position((int)$x, (int)$y, (int)$z, Server::getInstance()->getWorldManager()->getWorldByName($world));
	}
}