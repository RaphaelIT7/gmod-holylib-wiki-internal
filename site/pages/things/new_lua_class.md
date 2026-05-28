<title>Defining a new Lua class</title>

To add a new Lua class that HolyLib can push, you need to do a few things.<br>

# Defining the Class
As an example, we can define
<code language="c++">
struct ExampleStruct
{
	int field1 = 123;
	int field2 = 456;
};
</code>

Then we must add the class name to the <page>LuaTypes</page> enum which in this case would be `ExampleStruct`<br>
<br>
Now, to be able to push and get the class from Lua we can use the <page>Push_LuaClass</page> and <page>Get_LuaClass</page> macros.<br>
<code language="c++">
Push_LuaClass(ExampleStruct)
Get_LuaClass(ExampleStruct, "ExampleStruct")
</code>

And we can define the default `__index`, `__newindex`, `__GetTable`, `__gc` methods using the `Default__` macros.<br>
<code language="c++">
Default__index(ExampleStruct);
Default__newindex(ExampleStruct);
Default__GetTable(ExampleStruct);
Default__gc(ExampleStruct, 
	ExampleStruct* pData = (ExampleStruct*)pStoredData;
	if (pData)
		delete pData;
)
</code>

Now that everything is defined, we only need to push it to Lua now, that would look something like this.<br>
<code language="c++">
Lua::GetLuaData(pLua)->RegisterMetaTable(Lua::ExampleStruct, pLua->CreateMetaTable("ExampleStruct"));
	Util::AddFunc(pLua, ExampleStruct__index, "__index");
	Util::AddFunc(pLua, ExampleStruct__newindex, "__newindex");
	Util::AddFunc(pLua, ExampleStruct__gc, "__gc");
	LUA_REGISTER_JIT(pLua, ExampleStruct_GetTable, "GetTable");
pLua->Pop(1);
</code>

And now you've got your fully functional `ExampleStruct` that you can push and get from lua.<br>
An example of how to push & get them<br>
<code language="c++">
LUA_FUNCTION_STATIC(CreateExampleStruct)
{
	Push_ExampleStruct(LUA, new ExampleStruct);
	return 1;
}

LUA_FUNCTION_STATIC(GetExampleStruct)
{
	ExampleStruct* pExample = Get_ExampleStruct(LUA, 1, true);
	Msg("Got field1: %i & field2: %i\n", pExample->field1, pExample->field2);
	return 0;
}
</code>

# Different kinds of pushed Userdata
There are different kinds of `Push` functions<br>

## Push_LuaClass
This is the most commonly used one<br>
This will always push a newly created userdata with the given class.<br>

## PushInlined functions
This is part of the <page>Push_LuaClass</page> macro BUT it will inline the data into the Userdata itself.<br>
This means that instead of the data being a pointer to a `ExampleStruct*` it instead will be inlined into the Userdata and is part of the GC Object.<br>
This means that you **do not allocate or delete** the `ExampleStruct`.<br>
An example of this would be:<br>
<code language="c++">
LUA_FUNCTION_STATIC(CreateExampleStruct)
{
	LuaUserData* pUserData = PushInlined_ExampleStruct(LUA, 0);
	ExampleStruct* pExample = (ExampleStruct*)pUserData->GetData();
	pExample->field1 = 789;

	return 1; // PushInlined_ExampleStruct pushed the userdata onto the stack already!
}

Default__gc(ExampleStruct, 
	ExampleStruct* pData = (ExampleStruct*)pStoredData;
	if (pData && !bIsInlined) // We must check that bIsInlined is false, or else we would attempt to free an invalid pointer!
		delete pData;
)
</code>

The benefits of this are- no manual memory management and faster creation, as instead of doing two allocations (the lua userdata and the `new ExampleStruct` call) it now only does one allocation (the lua userdata)<br>
But the downside of this is, that **you do not own the pointer**- it could be deleted by Lua at any point when the garbage collector decides to free it.<br>

## PushReferenced_LuaClass
This will store a reference for the pushed data and reuse the userdata, it will also prevent the userdata from being removed by the garbage collector until the `Delete_` function is called for the data.<br>