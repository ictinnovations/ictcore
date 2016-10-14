CHANGELOG
=========

## Version 1.2 (work in progress)


## Version 1.1 (28.05.2013)
- **MINOR:** Small fixes and improvements (amal)
- **FEATURE:** Support for max single read/write size in `EventBuffer` - 4 new methods (amal)
- **FEATURE:** `Event::setPriority` (amal)

26.05.2013
- **MINOR:** More complex examples (amal)
- **IMPROVED:** Better support for fork in the newest libevent versions (amal)
- **FEATURE:** Full fork control with `EventBase::fork()` (amal)
- **FEATURE:** Event loops manager moved from `CliBase` and improved (amal)

25.05.2013
- **FEATURE:** Reentrant loop invocation protection (needed for the newest libevent versions) (amal)
- **IMPROVED:** Tests added (amal)

09.05.2013
- **FEATURE:** `EventBuffer` reinitialization on the fly (amal)
- **FEATURE:** `EventBuffer::readAll()` and `EventBuffer::readAllClean()` helper methods (amal)

03.05.2013
- **IMPROVED:** Buffered events disabling before fork and enabling after fork (amal)
- **FEATURE:** Event base before/after fork hooks (amal)

30.04.2013
- **MINOR:** Small typo fix (amal)
- **MINOR:** Some speed optimizations (amal)

27.04.2013
- **IMPROVED:** Better cleanup on reinitializing (amal)
- **MINOR:** PhpDoc improvements (amal)

20.04.2013
- **MINOR:** Small PhpDoc fix (amal)

17.04.2013
- **MINOR:** Fix to support fractional intervals (amal)


## Version 1.0.1 (07.04.2013)
- **MINOR:** Massive README and CHANGELOG improvements (amal)


## Version 1.0 (27.02.2013)
- First public release
