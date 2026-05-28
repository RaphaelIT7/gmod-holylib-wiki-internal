<title>LuaJIT</title>

# Dealing with JIT when compiling
This is a lot of pain.<br>
HolyLib has to work with 3 different JIT versions at once, these are `2.0.4`, `2.1.0-beta3` or HolyLib's custom JIT build.<br>
By default, if you include the `lua.h` in HolyLib, it will use the proper JIT version from GMod for 32x or 64x builds BUT if you want to specifically use HolyLib's own JIT version, you must define <page>DISABLE_GMODJIT</page> before the include.<br>

# Dealing with JIT at runtime
At runtime things are different.<br>
HolyLib must be able to deal with two different JIT versions at once, one being GMod's JIT version, the other being HolyLib's JIT version.<br>
An example check at runtime to determine if a `ILuaInterface` is using GMod's JIT version is to do `!Lua::g_bUsingLuaJIT && LUA == g_Lua` where we check if the JIT module is NOT active and that the interface is the GMod global interface.<br>
This is because HolyLib can create its own interfaces too, an example would be <page>HolyLua</page>