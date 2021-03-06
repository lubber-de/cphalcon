
/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalconphp.com>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace Phalcon\Config;

use Phalcon\Factory as BaseFactory;
use Phalcon\Factory\Exception;
use Phalcon\Config;

/**
 * Loads Config Adapter class using 'adapter' option, if no extension is provided it will be added to filePath
 *
 *<code>
 * use Phalcon\Config\Factory;
 *
 * $options = [
 *     "filePath" => "path/config",
 *     "adapter"  => "php",
 * ];
 * $config = Factory::load($options);
 *</code>
 */
class Factory extends BaseFactory
{
	/**
	 * @param \Phalcon\Config|array config
	 */
	public static function load(var config) -> object
	{
		return self::loadClass("Phalcon\\Config\\Adapter", config);
	}

	protected static function loadClass(string $namespace, var config)
	{
		var adapter, className, mode, callbacks, filePath, extension, oldConfig;

		if typeof config == "string" {
			let oldConfig = config;
			let extension = substr(strrchr(config, "."), 1);

			if empty extension {
				throw new Exception("You need to provide extension in file path");
			}

			let config = [
				"adapter": extension,
				"filePath": oldConfig
			];
		}

		if typeof config == "object" && config instanceof Config {
			let config = config->toArray();
		}

		if typeof config != "array" {
			throw new Exception("Config must be array or Phalcon\\Config object");
		}

		if !fetch filePath, config["filePath"] {
			throw new Exception("You must provide 'filePath' option in factory config parameter.");
		}

		if fetch adapter, config["adapter"] {
			let className = $namespace."\\".camelize(adapter);
			if !strpos(filePath, ".") {
				let filePath = filePath.".".lcfirst(adapter);
			}

			if className == "Phalcon\\Config\\Adapter\\Ini" {
				if fetch mode, config["mode"] {
					return new {className}(filePath, mode);
				}
			} elseif className == "Phalcon\\Config\\Adapter\\Yaml" {
				if fetch callbacks, config["callbacks"] {
					return new {className}(filePath, callbacks);
				}
			}

			return new {className}(filePath);
		}

		throw new Exception("You must provide 'adapter' option in factory config parameter.");
	}
}
