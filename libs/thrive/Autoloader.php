<?php
// This file is a part of the Thrive Framework, a PHPExperts.pro Project.
//
// Copyright (c) 2012 Theodore R.Smith (theodore@phpexperts.pro)
// DSA-1024 Fingerprint: 10A0 6372 9092 85A2 BB7F  907B CB8B 654B E33B F1ED
// Provided by the PHP University (www.phpu.cc) and PHPExperts.pro (www.phpexperts.pro)
//
// This file is dually licensed under the terms of the following licenses:
// * Primary License: OSSAL v1.0 - Open Source Software Alliance License
//   * Key points:
//       5.Redistributions of source code in any non-textual form (i.e.
//          binary or object form, etc.) must not be linked to software that is
//          released with a license that requires disclosure of source code
//          (ex: the GPL).
//       6.Redistributions of source code must be licensed under more than one
//          license and must not have the terms of the OSSAL removed.
//   * See LICENSE.ossal for complete details.
//
// * Secondary License: Creative Commons Attribution License v3.0
//   * Key Points:
//       * You are free:
//           * to copy, distribute, display, and perform the work
//           * to make non-commercial or commercial use of the work in its original form
//       * Under the following conditions:
//           * Attribution. You must give the original author credit. You must retain all
//             Copyright notices and you must include the sentence, "Based upon work from
//             PHPExperts.pro (www.phpexperts.pro).", wherever you list contributors.
//   * See LICENSE.cc_by for complete details.

require_once 'flourish/classes/fCache.php';

class Thrive_Autoloader
{
	// Singleton :/
	private static $instantiated = false;

	/** @var fCache **/
	protected $cacher;

	/** @var Thrive_ClassLocator **/
	protected $cloc;

	/** @var string[] **/
	protected $classMap;

	public function __construct($cwd = '', fCache $cacher = null, Thrive_ClassLocator $cloc = null)
	{
		if ($cwd == '') { $cwd = getcwd(); }

		if (self::$instantiated !== false) return self::$instantiated;

		if ($cacher === null)
		{
			$cacher = new fCache('file', $this->getAutoMapFilename());
			//echo "Automap file: " . $this->getAutoMapFilename() . "\n";
		}

		if ($cloc === null) { $cloc = new Thrive_ClassLocator($cwd); }

		$this->cacher = $cacher;
		$this->cloc = $cloc;
		$this->classMap = $this->cacher->get('classMap');

		spl_autoload_register(array($this, 'autoload'));
		$this->cwd = $cwd;

		self::$instantiated = $this;
	}

	public function __destruct()
	{
		//echo "Saving class map...\n";
		if ($this->cacher !== null)
		{
			$this->cacher->set('classMap', $this->classMap);
			$this->cacher->save();
		}
	}

	public function autoload($className)
	{
		$filename = $this->findClassInMap($className);
		if (!is_readable($filename))
		{
			// Assume stale cache; remove entry.
			unset($this->classMap[$className]);

			$filename = $this->findClassInMap($className);
			if (!is_readable($filename))
			{
				echo "Error: Could not find file '$filename' for class '$className'.\n";
				die(1);
			}
		}

		include $filename;
	}

	protected function findClassInMap($className)
	{
		if (!empty($this->classMap[$className]))
		{
			//echo "Found $className in internal class map at {$this->classMap[$className]}\n";
			return $this->classMap[$className];
		}

		$filename = $this->cloc->findClassFile($className);
		$this->classMap[$className] = $filename;

		return $filename;
	}

	protected function getAutoMapFilename()
	{
		static $filename;

		if ($filename !== null) { return $filename; }

		$appHash = md5($_SERVER['PHP_SELF']);
		$filename = sys_get_temp_dir() . '/autoload-' . $appHash . '.map';
		return $filename;
	}
}

class Thrive_ClassLocator
{
	protected $cwd;
	public function __construct($cwd)
	{
		$this->cwd = $cwd;
	}

	public function findClassFile($className)
	{
		//echo "Searching for $className's file...\n";
		if (($filename = $this->findClassViaPSR0($className)) !== false)
		{
			return $filename;
		}

		// Search Thrive library first.
		if (($filename = $this->searchFilesystemForClass($className, dirname(__FILE__))) !== false)
		{
			return $filename;
		}

		// Search the current working directory second.
		if (($filename = $this->searchFilesystemForClass($className, $this->cwd)) !== false)
		{
			return $filename;
		}

		return false;
	}

	protected function findClassViaPSR0($className, $ignoredPrefix = 'Thrive_')
	{
		// 1. Remove the ignored prefix from the class name.
		$noSuffix = str_replace($ignoredPrefix, '', $className);

		// 2. See if it exists in the main directory.
		$try1 = $noSuffix . '.php';
		//echo "Try 1: $try1\n";
		if (is_readable($try1))
		{
			return $try1;
		}

		// 3. See if it's in a sub directory.
		$pieces = explode('_', $try1);
		$try2 = join ('/', $pieces);
		//echo "Try 2: $try2\n";
		if (is_readable($try2))
		{
			return $try2;
		}

		return false;
	}

	protected function searchFilesystemForClass($className, $path)
	{
		static $classes = array();

		//echo "Searching directory $path for the class $className...\n";
		$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path),
		                                    RecursiveIteratorIterator::LEAVES_ONLY,
		                                    RecursiveIteratorIterator::CATCH_GET_CHILD);
		/** @var RecursiveDirectoryIterator $it */
		foreach ($it as $fileinfo)
		{
			/** @var DirectoryIterator $fileinfo **/
			//$filename = $it->getFilename();
			$filename = $it->getPathname();
			$basename = $it->getFilename();
			if ($fileinfo->isDir() || $basename[0] == '.') { continue; }

//			if (method_exists($fileinfo, 'getExtension'))
//			{
//				$ext = $fileinfo->getExtension();
//			}
//			else
//			{
				$ext = pathinfo($basename, PATHINFO_EXTENSION);
//			}

			if ($ext == 'php')
			{
				//echo "Searching for class $className in $filename...\n";
				$foundClasses = $this->findClassNamesInFile($filename);
				if (empty($foundClasses)) { continue; }

				$classes = array_merge($classes, $foundClasses);
				//print_r($classes);
				//if (in_array($className, $classes))
				if (!empty($classes[$className]))
				{
					//echo "Found class $className in {$classes[$className]}\n";
					return $classes[$className];
				}
			}
		}

		return false;
	}

	protected function findClassNamesInFile($filename)
	{	
		//echo "Searching $filename...\n";
		$classes = array();
		$source = file_get_contents($filename);
		$tokens = token_get_all($source);

		$count = count($tokens);
		for ($i = 2; $i < $count; $i++)
		{
			if (in_array($tokens[$i - 2][0], array(T_CLASS, T_ABSTRACT, T_INTERFACE))
			    && $tokens[$i - 1][0] == T_WHITESPACE
			    && $tokens[$i][0] == T_STRING)
			{
				$class_name = $tokens[$i][1];
				$classes[$class_name] = $filename;
			}
		}

		return $classes;
	}
}

