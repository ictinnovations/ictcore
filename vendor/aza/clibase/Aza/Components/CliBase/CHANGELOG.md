CHANGELOG
=========

## Version 1.2 (work in progress)


## Version 1.1 (28.05.2013)
26.05.2013
- **FIXED:** Removed usage of not public `Aza\Kernel\Exceptions\Exception` (Issue #1, amal)
- **CHANGE:** Event loops management removed (amal)

03.05.2013
- **IMPROVED:** Event base before/after fork hooks support (amal)

27.04.2013
- **CHANGE:** `Base::$isMaster` refactored to `Base::$hasParent` with inverse value (amal)

25.04.2013
- **MINOR:** `Base::getTimeForLog` improved (amal)
- **FEATURE:** `Base::setProcessTitle` now support PHP 5.5 `cli_set_process_title` function (amal)
- **MINOR:** POSIX (Unix) signals reference improved (amal)

14.04.2013
- **IMPROVED:** `Base::getEventBase` improvements (now it can be called without creating new event base) (amal)


## Version 1.0.1 (07.04.2013)
- **MINOR:** Massive README and CHANGELOG improvements (amal)


## Version 1.0 (27.02.2013)
- First public release
