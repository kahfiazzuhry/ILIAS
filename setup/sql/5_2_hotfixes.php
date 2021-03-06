<?php
// This is the hotfix file for ILIAS 5.0.x DB fixes
// This file should be used, if bugfixes need DB changes, but the
// main db update script cannot be used anymore, since it is
// impossible to merge the changes with the trunk.
//
// IMPORTANT: The fixes done here must ALSO BE reflected in the trunk.
// The trunk needs to work in both cases !!!
// 1. If the hotfixes have been applied.
// 2. If the hotfixes have not been applied.
?>
<#1>
<?php
	$ilCtrlStructureReader->getStructure();
?>
<#2>
<?php
$ilDB->modifyTableColumn(
	'wiki_stat_page',
	'num_ratings',
	array(
		'type' => 'integer',
		'length' => 4,
		'notnull' => true,
		'default' => 0
	)
);
?>
<#3>
<?php
$ilDB->modifyTableColumn(
	'wiki_stat_page',
	'avg_rating',
	array(
		'type' => 'integer',
		'length' => 4,
		'notnull' => true,
		'default' => 0
	)
);
?>
<#4>
<?php
$query = "SELECT value FROM settings WHERE module = %s AND keyword = %s";
$res = $ilDB->queryF($query, array('text', 'text'), array("mobs", "black_list_file_types"));
if (!$ilDB->fetchAssoc($res))
{
	$mset = new ilSetting("mobs");
	$mset->set("black_list_file_types", "html");
}
?>
<#5>
<?php
// #0020342
$query = $ilDB->query('SELECT 
    stloc.*
FROM
    il_dcl_stloc2_value stloc
        INNER JOIN
    il_dcl_record_field rf ON stloc.record_field_id = rf.id
        INNER JOIN
    il_dcl_field f ON rf.field_id = f.id
WHERE
    f.datatype_id = 3
ORDER BY stloc.id ASC');

while ($row = $query->fetchAssoc()) {
	$query2 = $ilDB->query('SELECT * FROM il_dcl_stloc1_value WHERE record_field_id = ' . $ilDB->quote($row['record_field_id'], 'integer'));
	if ($ilDB->numRows($query2)) {
		$rec = $ilDB->fetchAssoc($query2);
		if ($rec['value'] != null) {
			continue;
		}
	}

	$id = $ilDB->nextId('il_dcl_stloc1_value');
	$ilDB->insert('il_dcl_stloc1_value', array(
		'id' => array('integer', $id),
		'record_field_id' => array('integer', $row['record_field_id']),
		'value' => array('text', $row['value']),
	));
	$ilDB->manipulate('DELETE FROM il_dcl_stloc2_value WHERE id = ' . $ilDB->quote($row['id'], 'integer'));
}
?>
<#6>
<?php

$ilDB->manipulate('update grp_settings set registration_start = '. $ilDB->quote(null, 'integer').', '.
	'registration_end = '.$ilDB->quote(null, 'integer') .' '.
	'where registration_unlimited = '.$ilDB->quote(1,'integer')
);
?>

<#7>
<?php
$ilDB->manipulate('update crs_settings set '
	.'sub_start = ' . $ilDB->quote(null,'integer').', '
	.'sub_end = '.$ilDB->quote(null,'integer').' '
	.'WHERE sub_limitation_type != '.$ilDB->quote(2,'integer')
);
	
?>
<#8>
<?php
if(!$ilDB->tableColumnExists('frm_posts', 'pos_activation_date'))
{
	$ilDB->addTableColumn('frm_posts', 'pos_activation_date',
		array('type' => 'timestamp', 'notnull' => false));
}

if($ilDB->tableColumnExists('frm_posts', 'pos_activation_date'))
{
	$ilDB->manipulate('
	UPDATE frm_posts SET pos_activation_date = pos_date 
	WHERE pos_status = '. $ilDB->quote(1, 'integer')
	.' AND pos_activation_date is NULL'
	);
}
?>
<#9>
<?php
// #0020342
$query = $ilDB->query('SELECT 
    stloc.*,
	fp.value as fp_value,
	fp.name as fp_name
FROM
    il_dcl_stloc1_value stloc
        INNER JOIN
    il_dcl_record_field rf ON stloc.record_field_id = rf.id
        INNER JOIN
    il_dcl_field f ON rf.field_id = f.id
		INNER JOIN
	il_dcl_field_prop fp ON rf.field_id = fp.field_id
WHERE
    f.datatype_id = 3
	AND fp.name = "multiple_selection"
	AND fp.value = 1
ORDER BY stloc.id ASC');

while ($row = $query->fetchAssoc()) {
	if (!is_numeric($row['value'])) {
		continue;
	}

	$value_array = array($row['value']);

	$query2 = $ilDB->query('SELECT * FROM il_dcl_stloc2_value WHERE record_field_id = ' . $ilDB->quote($row['record_field_id'], 'integer'));
	while ($row2 = $ilDB->fetchAssoc($query2)) {
		$value_array[] = $row2['value'];
	}

	$ilDB->update('il_dcl_stloc1_value', array(
		'id' => array('integer', $row['id']),
		'record_field_id' => array('integer', $row['record_field_id']),
		'value' => array('text', json_encode($value_array)),
	), array('id' => array('integer', $row['id'])));
	$ilDB->manipulate('DELETE FROM il_dcl_stloc2_value WHERE record_field_id = ' . $ilDB->quote($row['record_field_id'], 'integer'));
}
?>
<#10>
<?php
$set = $ilDB->query("SELECT * FROM mep_item JOIN mep_tree ON (mep_item.obj_id = mep_tree.child) ".
	" WHERE mep_item.type = ".$ilDB->quote("pg", "text")
);
while ($rec = $ilDB->fetchAssoc($set))
{
	$q = "UPDATE page_object SET ".
		" parent_id = ".$ilDB->quote($rec["mep_id"], "integer").
		" WHERE parent_type = ".$ilDB->quote("mep", "text").
		" AND page_id = ".$ilDB->quote($rec["obj_id"], "integer");
	//echo "<br>".$q;
	$ilDB->manipulate($q);
}
?>
<#11>
<?php
	// fix 20706
	$ilDB->dropPrimaryKey('page_question');
	$ilDB->addPrimaryKey('page_question', array('page_parent_type', 'page_id', 'question_id', 'page_lang'));
?>
<#12>
<?php
	$ilCtrlStructureReader->getStructure();
?>
<#13>
<?php
    // fix 20409 and 20638
    $old = 'http://cdn.mathjax.org/mathjax/latest/MathJax.js?config=TeX-AMS-MML_HTMLorMML';
    $new = 'https://cdnjs.cloudflare.com/ajax/libs/mathjax/2.7.1/MathJax.js?config=TeX-AMS-MML_HTMLorMML';

    $ilDB->manipulateF("UPDATE settings SET value=%s WHERE module='MathJax' AND keyword='path_to_mathjax' AND value=%s",
        array('text','text'), array($new, $old)
    );
?>