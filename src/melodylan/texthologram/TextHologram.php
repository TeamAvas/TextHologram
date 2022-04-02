<?php

declare(strict_types=1);

namespace melodylan\texthologram;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;

final class TextHologram extends PluginBase{
	use SingletonTrait;

	public static function getInstance() : TextHologram{
		return self::$instance;
	}

	protected function onLoad() : void{
		self::$instance = $this;
	}

	protected function onEnable() : void{
	}
}