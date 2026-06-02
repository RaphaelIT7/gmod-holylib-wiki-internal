<title>LuaJIT</title>

# Dealing with JIT when compiling
This is a lot of pain.<br>
HolyLib has to work with 3 different JIT versions at once, these are `2.0.4`, `2.1.0-beta3` or HolyLib's custom JIT build.<br>
By default, if you include the `lua.h` in HolyLib, it will use the proper JIT version from GMod for 32x or 64x builds BUT if you want to specifically use HolyLib's own JIT version, you must define <page>DISABLE_GMODJIT</page> before the include.<br>

# Dealing with JIT at runtime
At runtime things are different.<br>
HolyLib must be able to deal with two different JIT versions at once, one being GMod's JIT version, the other being HolyLib's JIT version.<br>
An example check at runtime to determine if a `ILuaInterface` is using GMod's JIT version is to do `L->dummy_ffid == FF_C` (if true it's a GMod state!) where we check if the JIT module is NOT active and that the interface is the GMod global interface.<br>
This is because HolyLib can create its own interfaces too, an example would be <page>HolyLua</page>

## Namespaces
There are three different Lua namespaces, which all have different behavior!<br>
\- `Lua`: In here any function should be usable for any Lua state, as all functions here should be independent if a lua_State is from HolyLib or GMod.<br>
\- `RawLua`: All of these functions are specific to HolyLib's JIT version.<br>
\- `GModLua`: All of these functions are specific to GMod's JIT version.<br>

# Assumption
We currently assume that the GCHeader is the same for ALL JIT versions!<br>
If that ever changes the access for `dummy_ffid` will break!<br>
The old method was: `if (!g_bUsingLuaJIT && (g_Lua && L == g_Lua->GetState()))`<br>
But it didn't work on Windows clients as we do also interact with both server and client state<br>
yet g_Lua is only the state HolyLib was loaded in effectively breaking the other state<br>
So now we simply mark our own states.<br>
In HolyLib we set the `dummy_ffid` field to the value `3` currently.<br>