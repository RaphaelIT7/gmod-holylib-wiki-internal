<title>Lua Threading</title>

# The only guarantee
There exists only one guarantee.<br>
The main thread can at any time access the `g_Lua` state.<br>
This is the only case where this is guaranteed to be safe, the main thread or any other thread cannot safely access any other state safely without a mutex.<br>

# All the Mutexes

## Lua::LuaMutex
HolyLib has its own custom Mutex, the <page>Lua::LuaMutex</page>.
It internally uses a mix of different things to achieve one goal, a idiot-proof mutex.<br>
The LuaMutex can be locked recursively safely and also has a queue system in place which is needed for HolyLib's usage of it.<br>
It also exposes a few functions like `isOwning`, `hasWaiting`, `isLocked` and `lockWhenDone`.<br>
This mutex is mainly used to lock down an <page>Lua::StateData</page> for safe access.<br>

## Lua::ThreadAccessMutex
The <page>Lua::ThreadAccessMutex</page> is an internal mutex not meant to be used by any code directly!<br>
It basically is a custom implementation for a shared recursive mutex, which, when locked shared (for reads), and can inside a recursive call, switch to a full lock (for writes)<br>
<br>
This mutex is very important, as this will guard globally to ensure that when a Lua state is created or destroyed threads cannot be accessing them.<br>

# The Locks
These are the classes actually used by code, which are scope based and will properly handle all mutex calls.<br>

## Lua::ScopedThreadAccess
The <page>Lua::ScopedThreadAccess</page> class is just a wrapper to lock the <page>Lua::ThreadAccessMutex</page> shared/read only in RAII style (scoped)<br>
This is most commonly used when needing to access an interface, it ensures that when you do the later <page>Lua::StateAccess</page> lock afterwards, no interfaces are being actively created or destroyed, which without this could lead to race conditions.<br>

## Lua::CriticalThreadAccess
The <page>Lua::CriticalThreadAccess</page> class is just a wrapper to lock the <page>Lua::ThreadAccessMutex</page> in RAII style (scoped) BUT this will enforce a write lock, blocking all other threads out completely!<br>
This should only be used when absolutely necessary, it is only to be used when creating or destroying a Lua state.<br>
An example of this would be:<br>
<code language="c++">
static std::atomic<GarrysMod::Lua::ILuaInterface*> g_ExampleLua = nullptr;
GarrysMod::Lua::ILuaInterface* GetExampleInterface() // Exposed for other .cpp files
{
	return g_ExampleLua.load();
}

static void CreateInterface()
{
	GarrysMod::Lua::ILuaInterface* pExampleLua = Lua::CreateInterface();
	// We register all modules from HolyLib, at this point only the currently executing thread knows of the state.
	g_pModuleManager.LuaInit(pExampleLua, false);

	// We critically lock here since we must modify g_ExampleLua and while yes, it itself is an std::atomic
	// I am unsure about all the other stuff in the background, so we want to just be absolutely certain to not encounter any race condition.
	Lua::CriticalThreadAccess pThreadScope;
	g_ExampleLua.store(pExampleLua);
}

static void DestoryInterface()
{
	// We must Critical lock here, or else another thread may use a Lua::ScopedThreadAccess and call GetExampleInterface() receiving a pointer that would be garbage in a race condition as we freed the Lua state
	Lua::CriticalThreadAccess pCriticalThreadScope;
	auto LUA = GetExampleInterface(); // Safe since were blocking everyone

	g_pModuleManager.LuaShutdown(LUA);

	Lua::DestroyInterface(LUA);
	g_ExampleLua.store(nullptr);
}
</code>

## Lua::StateAccess
The <page>Lua::StateAccess</page> is used **after** the ThreadAccess mutex was created.<br>
Now you provide the Lua interface you want to lock for access, this will block all other threads from accessing this specific Lua interface!<br>

# How do threads access a Lua state
This is an example from HolyLib's code:<br>
<code language="c++">
Lua::ScopedThreadAccess pThreadScope; // Thread lock so that we can safely call GetHolyLuaInterface()
Lua::StateAccess pAccess(GetHolyLuaInterface()); // Lock the HolyLua state
if (pAccess.IsValid()) // If we're valid, we can now safely access and do things on the state
	pAccess.GetLua()->RunString("RunString", "", args.ArgS(), true, true);
</code>

# Non-Main Thread -> Main Thread access
So back to the only guarantee.<br>
Since the main thread always owns `g_Lua`, it is almost consistently locked.<br>
HolyLib internally just permanently locks the LuaMutex mutex of the `g_Lua` state, BUT since threads may need to access the state we must unlock it sometimes.<br>
This is done in <page>Lua::ThinkMainInterface</page>, inside there it will check if there are any waiting locks, and if so it will unlock the mutex, and use `lockWhenDone()` meaning if any thread locks for a long time- it will lock the entire main thread of the server.<br>
There also is an issue with locking being slow- if a thread needs to do many main thread calls, it should try to group them as else it is limited to the server tick rate.<br>
If the tick rate is 20 then the thread could only lock 20 times per second, which can be really slow when doing many calls, it is way faster to group the work you want to do and do a single lock and occupy the main thread for a bit to do all your work.<br>