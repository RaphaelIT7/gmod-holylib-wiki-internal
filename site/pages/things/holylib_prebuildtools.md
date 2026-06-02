<title>HolyLib's Pre-Build Tools</title>

# Pre-Build Tools
HolyLib has a custom prebuild setup to provide a few options.<br>

# Lua header Generation
Any `.lua` files that exist in `source/lua/scripts/` have a header generated where the Lua script is made accessible to C++ as a `const char*`.<br>
The variable it is exposed at will be `lua` + the filename without the `.lua` meaning if your Lua file is `TestScript.lua` then you can include the script using `#include "lua/scripts/TestScript.h"` and use the variable `luaTestScript`<br>

# Module List Generation
The file `source/modules/_modules.h` and `_modules.cpp` are generated.<br>
While yes, this file is generated, it won't cause compile cache misses as the contents do not change unless you added/removed/modified(changed priority) of a module<br>

## HOLYLIB_PRIORITY_MODULE
A module can have a comment like `// HOLYLIB_PRIORITY_MODULE` causing it to be prioritized and be put higher in the module list to be loaded earlier.<br>
This for example, is used by the `crashhandler` module.<br>

# Version File Generation
The file `source/_versioninfo.h` is generated and will change contents with every build causing a compile cache miss!<br>
Do **not** include this file yourself!<br>

# Module Macros
Using comments, a module .cpp file can tell the pre-build tool how to generate another file.<br>
This is for example, used in the `luagc` module.<br>

## HOLYLIB_SETUP_FILE
This comment will define the generated file name & path.<br>

## HOLYLIB_SETUP_FILE_DEPENDING_MODULE
Defines on which module the setup file depends on.<br>
Example would be `HOLYLIB_SETUP_FILE_DEPENDING_MODULE=luajit`<br>

## HOLYLIB_SETUP_FILE_REPLACE_PER_LINE
Allows you to define a regex that is performed later on the contents before they are written to disk.<br>

## HOLYLIB_SETUP_FILE_CONTENTS_BEGIN
Marks the start of the content, everything after it will be treated like file content!<br>

## HOLYLIB_SETUP_FILE_SKIPNEXTLINE
Skips the next line in the file from being added to the generated file.<br>
This is useful to escape a comment.<br>

## HOLYLIB_SETUP_FILE_END
Marks the end of the content & file setup, at this point the regex is applied and the contents are written to disk.<br>

# Dependency Manager
Unlike the others, this one applies at project generation, not pre-build.<br>
This one is responsible for stripping out files from being included when the dependency is missing.<br>

## HOLYLIB_REQUIRES_MODULE
This defines the module **this** (the file this is defined in) depends on.<br>
If the module is missing, then this file won't be included in the project.<br>
An example definition would be `HOLYLIB_REQUIRES_MODULE=gameserver`<br>