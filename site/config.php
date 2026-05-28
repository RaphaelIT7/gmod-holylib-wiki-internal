<?php
function GetConfig()
{
	return array(
	'name' => "Internal HolyLib Wiki", 
	'front_page' => 'gmod.md',
	'missing_page' => 'missing.md',
	'cache_page' => 'cache.md',
	'pages_path' => 'pages/',
	'issues_url' => 'https://github.com/RaphaelIT7/gmod-holylib/issues/',
	'code_language' => 'c++', // lua or c++
	'icon' => '',
	'version' => 0.8,
	'next_version' => 0.9,
	'description' => 'Welcome to the Internal HolyLib Wiki.&#xA;Here you will find a lot of documentation about HolyLib.&#xA;',
	'xampp' => str_contains($_SERVER['SERVER_SOFTWARE'], "Apache"),
	'realm' => 'gmod',
	'categories' => array(
		array(
			'name' => 'Things', 
			'categories' => array(
				array(
					'mdi' => 'mdi-book',
					'name' => 'Things',
					'path' => 'things',
				),
				array(
					'mdi' => 'mdi-book',
					'name' => 'Wiki',
					'path' => 'wiki',
				),
			),
		),
		array(
			'name' => 'Types', 
			'categories' => array(
				array(
					'mdi' => 'mdi-language-lua',
					'name' => 'Lua Types',
					'path' => 'types',
				),
			),
		),
		array(
			'name' => 'Macros, Constants, Definitions', 
			'categories' => array(
				array(
					'mdi' => 'mdi-bookshelf',
					'name' => 'Macros',
					'path' => 'macros_consts_defs',
				),
			),
		),
		array(
			'name' => 'Classes', 
			'categories' => array(
				array(
					'mdi' => 'mdi-book',
					'name' => 'Macros',
					'path' => 'classes',
				),
			),
		)
	)
	);
}
?>