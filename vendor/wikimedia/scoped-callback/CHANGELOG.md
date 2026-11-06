# Changelog

## 6.0.0 (2026-10-27)

* [BREAKING CHANGE] Drop support for PHP < 8.3 (James D. Forrester, Alexander Vorwerk)
* [BREAKING CHANGE] Add void return type declarations (Umherirrender)
* [BREAKING CHANGE] Use native `callable` type (Bartosz Dziewoński)
* [BREAKING CHANGE] Use PHP8 constructor property promotion and readonly property (Umherirrender)
* Add #[\NoDiscard] to functions that return ScopedCallback (Bartosz Dziewoński)
* Add public visibility to php magic functions (Umherirrender)
* Declare strict types on all php files (Umherirrender)
* Enable Phan PHPDocRedundantPlugin (Bartosz Dziewoński)
* Loosen symfony/polyfill-php85 requirements (Alexander Vorwerk)
* Replace call_user_func_array with dynamic function call (Umherirrender)
* Replace __sleep()/__wakeup() with __serialize()/__unserialize() (Alexander Vorwerk)
* Update copy of GPL license and licensing headers in files (James D. Forrester)
* Upgrade `@return never` PHPDoc to `never` return type (Bartosz Dziewoński)
* Use self, static::class and strict comparison in ScopedCallback (Alexander Vorwerk)

## 5.0.0 (2024-10-29)

* [BREAKING CHANGE] Drop PHP 7.2 and PHP 7.3 support (James D. Forrester)
* composer.json: Add changelog command (Reedy)
* Make argument for consume/cancel function no longer optional (Umherirrender)

## 4.0.0 (2022-07-08)

* Drop PHP 7.0, 7.1, and HHVM support. Require PHP 7.2+. (James D. Forrester)

## 3.0.0 (2018-10-30)

* Drop PHP 5.5 and 5.6 support. Require PHP 7.0+ or HHVM. (Kunal Mehta)
* Add `ScopedCallback::newScopedIgnoreUserAbort`. (Stibba) [T184044](https://phabricator.wikimedia.org/T184044)

## 2.0.0 (2018-05-09)

* Protect against insecure unserialization. (Tim Starling) [T169328](https://phabricator.wikimedia.org/T169328)

## 1.0.0 (2016-09-29)

* Initial release.
