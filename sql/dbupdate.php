<#1>
<?php
//Previous Version
global $DIC;
$db = $DIC->database();
if (!$db->tableExists('rep_robj_xlvo_cat')) {
    $fields = [
        'id' => [
            'type' => 'integer',
            'length' => 8,
            'notnull' => true
        ],
        'title' => [
            'type' => 'text',
            'length' => 256,
            'notnull' => false
        ],
        'voting_id' => [
            'type' => 'integer',
            'length' => 8,
            'notnull' => false
        ],
        'round_id' => [
            'type' => 'integer',
            'length' => 8,
            'notnull' => false
        ]
    ];

    $db->createTable('rep_robj_xlvo_cat', $fields);
    $db->addPrimaryKey('rep_robj_xlvo_cat', ['id']);
    $db->createSequence('rep_robj_xlvo_cat');
}
if (!$db->tableExists('rep_robj_xlvo_config_n')) {
    $fields = [
        'obj_id' => [
            'type' => 'integer',
            'length' => 8,
            'notnull' => true
        ],
        'pin' => [
            'type' => 'text',
            'length' => 256,
            'notnull' => false
        ],
        'obj_online' => [
            'type' => 'integer',
            'length' => 4,
            'notnull' => false
        ],
        'anonymous' => [
            'type' => 'integer',
            'length' => 4,
            'notnull' => false
        ],
        'terminable' => [
            'type' => 'integer',
            'length' => 4,
            'notnull' => false
        ],
        'start_date' => [
            'type' => 'timestamp',
            'notnull' => false
        ],
        'end_date' => [
            'type' => 'timestamp',
            'notnull' => false
        ],
        'reuse_status' => [
            'type' => 'integer',
            'length' => 4,
            'notnull' => false
        ],
        'last_access' => [
            'type' => 'timestamp',
            'notnull' => false
        ],
        'frozen_behaviour' => [
            'type' => 'integer',
            'length' => 4,
            'notnull' => false
        ],
        'results_behaviour' => [
            'type' => 'integer',
            'length' => 4,
            'notnull' => false
        ],
        'voting_history' => [
            'type' => 'integer',
            'length' => 4,
            'notnull' => false
        ],
        'show_attendees' => [
            'type' => 'integer',
            'length' => 4,
            'notnull' => false
        ],
        'puk' => [
            'type' => 'text',
            'length' => 256,
            'notnull' => false
        ]
    ];

    $db->createTable('rep_robj_xlvo_config_n', $fields);
    $db->addPrimaryKey('rep_robj_xlvo_config_n', ['obj_id']);
}
if (!$db->tableExists('rep_robj_xlvo_option_n')) {
    $fields = [
        'text' => [
            'type' => 'text',
            'length' => 256,
            'notnull' => false
        ],
        'id' => [
            'type' => 'integer',
            'length' => 8,
            'notnull' => true
        ],
        'voting_id' => [
            'type' => 'integer',
            'length' => 8,
            'notnull' => false
        ],
        'type' => [
            'type' => 'integer',
            'length' => 8,
            'notnull' => false
        ],
        'status' => [
            'type' => 'integer',
            'length' => 8,
            'notnull' => false
        ],
        'position' => [
            'type' => 'integer',
            'length' => 8,
            'notnull' => false
        ],
        'correct_position' => [
            'type' => 'integer',
            'length' => 8,
            'notnull' => false
        ]
    ];

    $db->createTable('rep_robj_xlvo_option_n', $fields);
    $db->addPrimaryKey('rep_robj_xlvo_option_n', ['id']);
    $db->createSequence('rep_robj_xlvo_option_n');
}
if (!$db->tableExists('rep_robj_xlvo_player_n')) {
    $fields = [
        'id' => [
            'type' => 'integer',
            'length' => 8,
            'notnull' => true
        ],
        'obj_id' => [
            'type' => 'integer',
            'length' => 8,
            'notnull' => false
        ],
        'active_voting' => [
            'type' => 'integer',
            'length' => 8,
            'notnull' => false
        ],
        'status' => [
            'type' => 'integer',
            'length' => 8,
            'notnull' => false
        ],
        'frozen' => [
            'type' => 'integer',
            'length' => 4,
            'notnull' => false
        ],
        'timestamp_refresh' => [
            'type' => 'integer',
            'length' => 8,
            'notnull' => false
        ],
        'show_results' => [
            'type' => 'integer',
            'length' => 4,
            'notnull' => false
        ],
        'button_states' => [
            'type' => 'text',
            'length' => 1024,
            'notnull' => false
        ],
        'countdown' => [
            'type' => 'integer',
            'length' => 6,
            'notnull' => false
        ],
        'countdown_start' => [
            'type' => 'integer',
            'length' => 8,
            'notnull' => false
        ],
        'force_reload' => [
            'type' => 'integer',
            'length' => 4,
            'notnull' => false
        ],
        'round_id' => [
            'type' => 'integer',
            'length' => 8,
            'notnull' => false
        ]
    ];

    $db->createTable('rep_robj_xlvo_player_n', $fields);
    $db->addPrimaryKey('rep_robj_xlvo_player_n', ['id']);
    $db->createSequence('rep_robj_xlvo_player_n');
}
// Pendiente: rep_robj_xlvo_round_n
// Pendiente: rep_robj_xlvo_votehist
// Pendiente: rep_robj_xlvo_vote_n
// Pendiente: rep_robj_xlvo_voting_n
// Pendiente: xlvo_config
// Pendiente: xlvo_voter
?>
<#2>
<?php
//Previous Version
?>
<#3>
<?php
//Previous Version
?>
<#4>
<?php
//Previous Version
?>
<#5>
<?php
//Previous Version
?>
<#6>
<?php
//Previous Version
?>
<#7>
<?php
//Previous Version
?>
<#8>
<?php
//Previous Version
?>
<#9>
<?php
//Previous Version
?>
<#10>
<?php
//Previous Version
?>
<#11>
<?php
//Previous Version
?>
<#12>
<?php
//Previous Version
?>
<#13>
<?php
//Previous Version
?>
<#14>
<?php
//Previous Version
?>
<#15>
<?php
//Previous Version
?>
<#16>
<?php
//Previous Version
?>
<#17>
<?php
//Previous Version
?>
<#18>
<?php
//Previous Version
?>
<#19>
<?php
//Previous Version
?>
<#20>
<?php
//Previous Version
?>
<#21>
<?php
//Previous Version
?>
<#22>
<?php
//Previous Version
?>
<#23>
<?php
//Previous Version
?>
<#24>
<?php
//Previous Version
?>
<#25>
<?php
//Previous Version
?>
<#26>
<?php
//Previous Version
?>
<#27>
<?php
//Previous Version
?>
<#28>
<?php
//Previous Version
?>
<#29>
<?php
//Previous Version
?>
<#30>
<?php
//Previous Version
?>
<#31>
<?php
//Previous Version
?>
<#32>
<?php
//Previous Version
?>
<#33>
<?php
//Previous Version
?>
<#34>
<?php
//Previous Version
?>
<#35>
<?php
//Previous Version
?>
<#36>
<?php
//Previous Version
?>
<#37>
<?php
//Previous Version
?>
<#38>
<?php
//Previous Version
?>
<#39>
<?php
//Previous Version
?>
<#40>
<?php
//Previous Version
?>
<#41>
<?php
//Previous Version
?>
<#42>
<?php
//Current Version
?>
