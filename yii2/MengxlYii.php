<?php

namespace mengxl;

use yii\base\InvalidParamException;
use yii\base\UnknownClassException;

/**
 * This constant defines the framework installation directory.
 */
defined('YII2_PATH') or define('YII2_PATH', __DIR__);
/**
 * This constant defines whether the application should be in debug mode or not. Defaults to false.
 */
defined('YII_DEBUG') or define('YII_DEBUG', false);

class MengxlYii
{
	/**
	 * @var MengxlApp
	 */
	public static $app;

	/**
	 * @var array class map used by the Yii autoloading mechanism.
	 * The array keys are the class names (without leading backslashes), and the array values
	 * are the corresponding class file paths (or path aliases). This property mainly affects
	 * how [[autoload()]] works.
	 * @see autoload()
	 */
	public static $classMap = [];
	/**
	 * @var array registered path aliases
	 * @see getAlias()
	 * @see setAlias()
	 */
	public static $aliases = ['@yii' => __DIR__];
	/**
	 * @var Container the dependency injection (DI) container used by [[createObject()]].
	 * You may use [[Container::set()]] to set up the needed dependencies of classes and
	 * their initial property values.
	 * @see createObject()
	 * @see Container
	 */
	public static $container;


	/**
	 * Translates a path alias into an actual path.
	 *
	 * The translation is done according to the following procedure:
	 *
	 * 1. If the given alias does not start with '@', it is returned back without change;
	 * 2. Otherwise, look for the longest registered alias that matches the beginning part
	 *    of the given alias. If it exists, replace the matching part of the given alias with
	 *    the corresponding registered path.
	 * 3. Throw an exception or return false, depending on the `$throwException` parameter.
	 *
	 * For example, by default '@yii' is registered as the alias to the Yii framework directory,
	 * say '/path/to/yii'. The alias '@yii/web' would then be translated into '/path/to/yii/web'.
	 *
	 * If you have registered two aliases '@foo' and '@foo/bar'. Then translating '@foo/bar/config'
	 * would replace the part '@foo/bar' (instead of '@foo') with the corresponding registered path.
	 * This is because the longest alias takes precedence.
	 *
	 * However, if the alias to be translated is '@foo/barbar/config', then '@foo' will be replaced
	 * instead of '@foo/bar', because '/' serves as the boundary character.
	 *
	 * Note, this method does not check if the returned path exists or not.
	 *
	 * @param string $alias the alias to be translated.
	 * @param boolean $throwException whether to throw an exception if the given alias is invalid.
	 * If this is false and an invalid alias is given, false will be returned by this method.
	 * @return string|boolean the path corresponding to the alias, false if the root alias is not previously registered.
	 * @throws InvalidParamException if the alias is invalid while $throwException is true.
	 * @see setAlias()
	 */
	public static function getAlias($alias, $throwException = true)
	{
		if (strncmp($alias, '@', 1)) {
			// not an alias
			return $alias;
		}

		$pos = strpos($alias, '/');
		$root = $pos === false ? $alias : substr($alias, 0, $pos);

		if (isset(static::$aliases[$root])) {
			if (is_string(static::$aliases[$root])) {
				return $pos === false ? static::$aliases[$root] : static::$aliases[$root] . substr($alias, $pos);
			} else {
				foreach (static::$aliases[$root] as $name => $path) {
					if (strpos($alias . '/', $name . '/') === 0) {
						return $path . substr($alias, strlen($name));
					}
				}
			}
		}

		if ($throwException) {
			throw new InvalidParamException("Invalid path alias: $alias");
		} else {
			return false;
		}
	}

	/**
	 * Returns the root alias part of a given alias.
	 * A root alias is an alias that has been registered via [[setAlias()]] previously.
	 * If a given alias matches multiple root aliases, the longest one will be returned.
	 * @param string $alias the alias
	 * @return string|boolean the root alias, or false if no root alias is found
	 */
	public static function getRootAlias($alias)
	{
		$pos = strpos($alias, '/');
		$root = $pos === false ? $alias : substr($alias, 0, $pos);

		if (isset(static::$aliases[$root])) {
			if (is_string(static::$aliases[$root])) {
				return $root;
			} else {
				foreach (static::$aliases[$root] as $name => $path) {
					if (strpos($alias . '/', $name . '/') === 0) {
						return $name;
					}
				}
			}
		}

		return false;
	}


	/**
	 * Class autoload loader.
	 * This method is invoked automatically when PHP sees an unknown class.
	 * The method will attempt to include the class file according to the following procedure:
	 *
	 * 1. Search in [[classMap]];
	 * 2. If the class is namespaced (e.g. `yii\base\Component`), it will attempt
	 *    to include the file associated with the corresponding path alias
	 *    (e.g. `@yii/base/Component.php`);
	 *
	 * This autoloader allows loading classes that follow the [PSR-4 standard](http://www.php-fig.org/psr/psr-4/)
	 * and have its top-level namespace or sub-namespaces defined as path aliases.
	 *
	 * Example: When aliases `@yii` and `@yii/bootstrap` are defined, classes in the `yii\bootstrap` namespace
	 * will be loaded using the `@yii/bootstrap` alias which points to the directory where bootstrap extension
	 * files are installed and all classes from other `yii` namespaces will be loaded from the yii framework directory.
	 *
	 * Also the [guide section on autoloading](guide:concept-autoloading).
	 *
	 * @param string $className the fully qualified class name without a leading backslash "\"
	 * @throws UnknownClassException if the class does not exist in the class file
	 */
	public static function autoload($className)
	{
		if (isset(static::$classMap[$className])) {
			$classFile = static::$classMap[$className];
			if ($classFile[0] === '@') {
				$classFile = static::getAlias($classFile);
			}
		} elseif (strpos($className, '\\') !== false) {
			$classFile = static::getAlias('@' . str_replace('\\', '/', $className) . '.php', false);
			if ($classFile === false || !is_file($classFile)) {
				return;
			}
		} else {
			return;
		}

		// mengxl copy yii2 files
		if (defined('MENGXL_AUTO_COPY') && !file_exists($classFile)) {
			$srcFile = str_replace(__DIR__, __DIR__ . "/../../../yii2/basic/vendor/yiisoft/yii2", $classFile);
			if ($srcFile) {
				echo "\n\n copy: " . $classFile . "\n\n";
				if(!file_exists(dirname($classFile))){
					mkdir(dirname($classFile), 0777, true);
				}
				copy($srcFile, $classFile);
			}
		}
		if (defined('SDLI_AUTO_COPY') && !file_exists($classFile)) {
			$srcFile = str_replace(__DIR__, __DIR__ . "/../../../../../libadmin2_yii2/vendor/yiisoft/yii2", $classFile);
			if ($srcFile) {
				echo "\n\n copy: " . $classFile . "\n\n";
				if(!file_exists(dirname($classFile))){
					mkdir(dirname($classFile), 0777, true);
				}
				copy($srcFile, $classFile);
			}
		}

		include($classFile);

		if (YII_DEBUG && !class_exists($className, false) && !interface_exists($className, false) && !trait_exists($className, false)) {
			throw new UnknownClassException("Unable to find '$className' in file: $classFile. Namespace missing?");
		}
	}

	/**
	 * Creates a new object using the given configuration.
	 *
	 * You may view this method as an enhanced version of the `new` operator.
	 * The method supports creating an object based on a class name, a configuration array or
	 * an anonymous function.
	 *
	 * Below are some usage examples:
	 *
	 * ```php
	 * // create an object using a class name
	 * $object = Yii::createObject('yii\db\Connection');
	 *
	 * // create an object using a configuration array
	 * $object = Yii::createObject([
	 *     'class' => 'yii\db\Connection',
	 *     'dsn' => 'mysql:host=127.0.0.1;dbname=demo',
	 *     'username' => 'root',
	 *     'password' => '',
	 *     'charset' => 'utf8',
	 * ]);
	 *
	 * // create an object with two constructor parameters
	 * $object = \Yii::createObject('MyClass', [$param1, $param2]);
	 * ```
	 *
	 * Using [[\yii\di\Container|dependency injection container]], this method can also identify
	 * dependent objects, instantiate them and inject them into the newly created object.
	 *
	 * @param string|array|callable $type the object type. This can be specified in one of the following forms:
	 *
	 * - a string: representing the class name of the object to be created
	 * - a configuration array: the array must contain a `class` element which is treated as the object class,
	 *   and the rest of the name-value pairs will be used to initialize the corresponding object properties
	 * - a PHP callable: either an anonymous function or an array representing a class method (`[$class or $object, $method]`).
	 *   The callable should return a new instance of the object being created.
	 *
	 * @param array $params the constructor parameters
	 * @return object the created object
	 * @throws InvalidConfigException if the configuration is invalid.
	 * @see \yii\di\Container
	 */
	public static function createObject($type, array $params = [])
	{
		if (is_string($type)) {
			return static::$container->get($type, $params);
		} elseif (is_array($type) && isset($type['class'])) {
			$class = $type['class'];
			unset($type['class']);
			return static::$container->get($class, $params, $type);
		} elseif (is_callable($type, true)) {
			return static::$container->invoke($type, $params);
		} elseif (is_array($type)) {
			throw new InvalidConfigException('Object configuration must be an array containing a "class" element.');
		} else {
			throw new InvalidConfigException('Unsupported configuration type: ' . gettype($type));
		}
	}

	/**
	 * Logs a trace message.
	 * Trace messages are logged mainly for development purpose to see
	 * the execution work flow of some code.
	 * @param string $message the message to be logged.
	 * @param string $category the category of the message.
	 */
	public static function trace($message, $category = 'application')
	{
		if (YII_DEBUG) {
			//static::getLogger()->log($message, Logger::LEVEL_TRACE, $category);
		}
	}

	/**
	 * Logs an error message.
	 * An error message is typically logged when an unrecoverable error occurs
	 * during the execution of an application.
	 * @param string $message the message to be logged.
	 * @param string $category the category of the message.
	 */
	public static function error($message, $category = 'application')
	{
		//static::getLogger()->log($message, Logger::LEVEL_ERROR, $category);
	}

	/**
	 * Logs a warning message.
	 * A warning message is typically logged when an error occurs while the execution
	 * can still continue.
	 * @param string $message the message to be logged.
	 * @param string $category the category of the message.
	 */
	public static function warning($message, $category = 'application')
	{
		//static::getLogger()->log($message, Logger::LEVEL_WARNING, $category);
	}

	/**
	 * Logs an informative message.
	 * An informative message is typically logged by an application to keep record of
	 * something important (e.g. an administrator logs in).
	 * @param string $message the message to be logged.
	 * @param string $category the category of the message.
	 */
	public static function info($message, $category = 'application')
	{
		//static::getLogger()->log($message, Logger::LEVEL_INFO, $category);
	}

	/**
	 * Marks the beginning of a code block for profiling.
	 * This has to be matched with a call to [[endProfile]] with the same category name.
	 * The begin- and end- calls must also be properly nested. For example,
	 *
	 * ```php
	 * \Yii::beginProfile('block1');
	 * // some code to be profiled
	 *     \Yii::beginProfile('block2');
	 *     // some other code to be profiled
	 *     \Yii::endProfile('block2');
	 * \Yii::endProfile('block1');
	 * ```
	 * @param string $token token for the code block
	 * @param string $category the category of this log message
	 * @see endProfile()
	 */
	public static function beginProfile($token, $category = 'application')
	{
		//static::getLogger()->log($token, Logger::LEVEL_PROFILE_BEGIN, $category);
	}

	/**
	 * Marks the end of a code block for profiling.
	 * This has to be matched with a previous call to [[beginProfile]] with the same category name.
	 * @param string $token token for the code block
	 * @param string $category the category of this log message
	 * @see beginProfile()
	 */
	public static function endProfile($token, $category = 'application')
	{
		//static::getLogger()->log($token, Logger::LEVEL_PROFILE_END, $category);
	}

	/**
	 * Configures an object with the initial property values.
	 * @param object $object the object to be configured
	 * @param array $properties the property initial values given in terms of name-value pairs.
	 * @return object the object itself
	 */
	public static function configure($object, $properties)
	{
		foreach ($properties as $name => $value) {
			$object->$name = $value;
		}

		return $object;
	}

	/**
	 * Returns the public member variables of an object.
	 * This method is provided such that we can get the public member variables of an object.
	 * It is different from "get_object_vars()" because the latter will return private
	 * and protected variables if it is called within the object itself.
	 * @param object $object the object to be handled
	 * @return array the public member variables of the object
	 */
	public static function getObjectVars($object)
	{
		return get_object_vars($object);
	}

	public static function initDb($config){
		static::$app->db = new \yii\db\Connection($config);
	}
}
