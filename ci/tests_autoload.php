<?php
namespace ITRocks\Reflect\Tests;

use Composer\Autoload\ClassLoader;

include __DIR__ . '/src_autoload.php';

$class_loader = new ClassLoader();
$class_loader->addPsr4(__NAMESPACE__ . '\\', substr(__DIR__, 0, -2) . 'tests', true);
$class_loader->register();
