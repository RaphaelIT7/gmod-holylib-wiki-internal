<title>Goals</title>

The goal of HolyLib is to expose many new functions from the engine or even implement new ones for new behavior.<br>
It also targets high performance and optimizes the engine when possible.<br>
HolyLib may go quite far to achieve that high performance and touch things that usually no one should touch- if it's JIT internals, deep engine functions or other potentially unsafe behavior, any unstable optimizations should be disabled for release builds **BUT** may be left enabled in dev builds.<br>

For exposed APIs it should put any unsafe API behind <page>Util::DoUnsafeCodeCheck</page> and block any behavior that may break a server when unsafe code is disabled.<br>
While HolyLib may expose all kinds of functions, it is acknowledged that a server may use HolyLib in an environment where Lua may also be executed from third party addons- especially when HolyLib is used on a client, the API should be restricted to avoid exploitation.<br>

Dev builds are the builds created from each push- they are experimental, may be unstable, may be broken, may be used to test different things, they are **NOT** known for stability.<br>