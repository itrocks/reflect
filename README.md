Reflect
-------

**Current status is: development in progress** \
*Please don't consider this as a stable release.*

Extends PHP Reflection API to add features:

Annotations:
- All annotations have a default instance
- Annotation templates
- Inheritable annotations
- Override property annotations without redefining them
- Short annotation names

Attributes:
- #Always mark attributes with a default instance
- Attribute templates
- Inheritable attributes
- Keep track of declaring and final class and property
- Override property attributes without redefining them

Reflection:
- Filters allow to filter inheritance too, using self::T_EXTENDS, self::T_IMPLEMENTS and self::T_USE
- Get inherited doc-comments
- Interfaces common to PHP native Reflection, extended reflection and PHP token based reflection

Reflection_Class:
- Can consider interface and traits as abstract
- Get all parent class names or reflection objects
- Get properties matching a given attribute value 
- Get properties default values, that can be calculated by #Default_(callable)
- isA allow you to check if an object or class name instantiates or uses another class,
  including from parents
- isClass allow you to check if this has been defined as a class, ie not as an interface or trait

Reflection_Property: 
- Can be constructed using 'property.path'
- is() allows you ton compare two properties, event if instantiated from different paths
- Keeps track of the final class, the root class, the 'property.path'
- The default value can be calculated by #Default_(callable)

Type:
- getElementName() gets the first named type name,
  and solves @param/@var more precise typing for array and object
- getEmptyValue() allows you to know which empty value
- getFinalTypes() allows you to get easily gets all final named types list
- isClass allow you to check if it priorly matches a class

Pre-requisites
--------------

- This works with PHP 8.2+ only. I wanted to make full use of the current PHP version features. \
  To install php 8.2 on a Debian/Ubuntu/Mint system:
  https://php.watch/articles/install-php82-ubuntu-debian.

Installation
------------

To use this from your project:

```bash
composer require itrocks/reflect
```
