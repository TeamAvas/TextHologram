<?php

declare(strict_types=1);

namespace melodylan\texthologram;

use melodylan\texthologram\hologram\DefaultHologram;
use melodylan\texthologram\hologram\Hologram;
use melodylan\texthologram\lang\PluginLang;
use melodylan\texthologram\utils\PositionHash;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Filesystem;
use pocketmine\utils\SingletonTrait;
use Webmozart\PathUtil\Path;
use function is_dir;
use function mkdir;
use function array_diff;
use function scandir;
use function is_file;
use function pathinfo;

final class TextHologram extends PluginBase{
	use SingletonTrait;

	public static function getInstance() : TextHologram{
		return self::$instance;
	}

	protected function onLoad() : void{
		self::$instance = $this;
	}

	/**
	 * @phpstan-var array<string, Hologram>
	 * @var Hologram[]
	 */
	private array $holograms = [];

	protected function onEnable() : void{
		if(!is_dir($path = Path::join($this->getDataFolder(), "holograms"))){
			mkdir($path);
		}
		$this->saveDefaultConfig();
		$this->saveResource('lang/eng.yml');
		$this->saveResource('lang/kor.yml');

		if(($lang = $this->getConfig()->get("plugin-lang", $this->getServer()->getLanguage()->getLang())) !== ''){
			PluginLang::getInstance()->initialize($lang, Path::join($this->getDataFolder(), "lang"));
		}

		foreach(array_diff(scandir(Path::join($this->getDataFolder(), "holograms")), ['.', '..']) as $value){
			$path = Path::join($this->getDataFolder(), "holograms", $value);
			if(!is_file($path)){
				continue;
			}
			$extension = pathinfo($value, PATHINFO_EXTENSION);
			if($extension !== "yml"){
				continue;
			}
			$data = yaml_parse(file_get_contents($path));
			$hash = $data[0];
			$this->holograms[$hash] = new DefaultHologram(PositionHash::toPosition($hash), $data[1], (float)$data[2]);
		}
	}

	protected function onDisable() : void{
		$path = Path::join($this->getDataFolder(), "holograms");
		foreach($this->holograms as $hologram){
			if(!$hologram->canSave()){
				continue;
			}
			$data = $hologram->jsonSerialize();
			Filesystem::safeFilePutContents(Path::join($path, $data[0] . ".yml"), yaml_emit($data, YAML_UTF8_ENCODING));
		}
	}
}