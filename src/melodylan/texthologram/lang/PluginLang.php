<?php

declare(strict_types=1);

namespace melodylan\texthologram\lang;

use InvalidArgumentException;
use pocketmine\utils\SingletonTrait;
use Webmozart\PathUtil\Path;
use function file_exists;
use function file_get_contents;
use function yaml_parse;

final class PluginLang{
	use SingletonTrait;

	public static function getInstance() : PluginLang{
		return self::$instance ??= new self;
	}

	private string $lang;

	/**
	 * @phpstan-var array<string, string>
	 * @var string[]
	 */
	private array $translates;

	public function initialize(string $lang, string $path): void{
		$filePath = Path::join($path, $lang . '.yml');
		if(!file_exists($filePath)){
			throw new InvalidArgumentException("The language file could not be found. [Path: $filePath]");
		}
		$content = file_get_contents($filePath);
		if($content === false){
			throw new InvalidArgumentException("The language file could not be opened.");
		}
		$parse = yaml_parse($content);
		if(!is_array($parse)){
			throw new InvalidArgumentException("The language file could not be parsed.");
		}
		$this->lang = $lang;
		$this->translates = $parse;
	}

	/**
	 * @param string $key
	 * @param string[][]  $replaced
	 * @param bool   $pushPrefix
	 *
	 * @return string
	 */
	public function translate(string $key, array $replaced = [], bool $pushPrefix = true): string{
		$text = $pushPrefix ? $this->translates['prefix'] . ' ' ?? '' : '';
		if(!isset($this->translates[$key])){
			throw new InvalidArgumentException("No translation found for $key.");
		}
		$translate = str_replace($replaced[0], $replaced[1], $this->translates[$key]);
		return $text . $translate;
	}
}