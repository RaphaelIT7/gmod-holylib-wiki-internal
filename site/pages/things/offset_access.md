<title>Offset / Variable Access</title>

# Variable Access
The most important rule for this topic: `The SourceSDK is always wrong`<br>
You cannot trust the class layouts in the SDK unless you verify them properly first as they may change, especially in GMod classes like `CBaseEntity` may have `virtual` functions or `member variables` change frequently.<br>
So HolyLib tries to generally avoid calling `virtual` functions or accessing `member variables` of classes which are known to frequently change.<br>
Instead, for anything entity-related, HolyLib has the <page>DTVarByOffset</page> class.<br>
This class will find offsets by searching through the SendProp's of the server dll which the Source Engine usually exposes for the engine dll but in this case we can benefit off this by also finding the offsets to safely access/get variables.<br>

## What is a SendProp
A SendProp is a networked variable that is inside a DataTable.<br>
You can find all DataTables in this repo: https://github.com/callumok2004/gmod-dt-test<br>

## Example usage of DTVarByOffset
So to create a <page>DTVarByOffset</page> you need to know two things:<br>
\- 1) The DataTable the variable is inside of<br>
\- 2) The Variable name<br>
<br>
Once you got both of these, you can setup the <page>DTVarByOffset</page> like in the example below:
<code language="c++">
static DTVarByOffset movetype_Offset("DT_BaseEntity", "movetype");
// pPlayer is the CBaseEntity pointer. (The class, if it's from CBaseEntity, CBasePlayer or whatever, does not matter)
static inline int GetMoveType(void* pPlayer)
{
	void* pMoveType = movetype_Offset.GetPointer(pPlayer);
	if (!pMoveType)
		return MoveType_t::MOVETYPE_NONE;

	// You must know the type- this sadly cannot be made safe so if the type changes with an update, you must update your code.
	return *(int*)pMoveType;
}
</code>

## Accessing a Variable that is a Member of Another Variable
In some cases you may want to access a variable that is part of a struct of another variable.<br>
Example cases would be `DT_Local`<br>
<code language="c++">
static DTVarByOffset m_hLadder_Offset("DT_HL2Local", "m_hLadder");
static DTVarByOffset m_HL2Local_Offset("DT_HL2_Player", "m_HL2Local");
static inline CBaseEntity* GetLadder(void* pPlayer)
{
	void* pHL2Local = m_HL2Local_Offset.GetPointer(pPlayer);
	if (!pHL2Local)
		return nullptr;

	void* pLadder = m_hLadder_Offset.GetPointer(pHL2Local);
	if (!pLadder)
		return nullptr;

	// When working with an EHANDLE we never just use pHandle->GetEntryIndex()! Use Util::GetCBaseEntityFromHandle!
	return Util::GetCBaseEntityFromHandle(*(EHANDLE*)pLadder);
}
</code>