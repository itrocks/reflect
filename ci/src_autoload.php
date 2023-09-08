<?php
namespace ITRocks\Reflect;

use Composer\Autoload\ClassLoader;

$class_loader = new ClassLoader();
$class_loader->addPsr4(__NAMESPACE__ . '\\', substr(__DIR__, 0, -2) . 'src', true);
$class_loader->register();
