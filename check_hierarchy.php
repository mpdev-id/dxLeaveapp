<?php

use App\Models\User;

// Get user with all relationships
$user = User::with([
    'plant.team.department',
    'plant.team.leader',
    'plant.team.sl',
    'plant.team.asmen',
    'plant.supervisor',
    'department.head'
])->find(1);

if (!$user) {
    echo "User not found!\n";
    exit;
}

echo "=== User Info ===\n";
echo "User: {$user->name}\n";
echo "Plant: " . ($user->plant ? $user->plant->name : 'NULL') . "\n";
echo "Team: " . ($user->plant?->team ? $user->plant->team->name : 'NULL') . "\n";
echo "Department: " . ($user->department ? $user->department->name : 'NULL') . "\n";

echo "\n=== Potential Approvers ===\n";
echo "SPV: " . ($user->plant?->supervisor ? $user->plant->supervisor->name : 'NULL') . "\n";
echo "SL: " . ($user->plant?->team?->sl ? $user->plant->team->sl->name : 'NULL') . "\n";
echo "ASMEN: " . ($user->plant?->team?->asmen ? $user->plant->team->asmen->name : 'NULL') . "\n";
echo "TL: " . ($user->plant?->team?->leader ? $user->plant->team->leader->name : 'NULL') . "\n";
echo "Manager: " . ($user->department?->head ? $user->department->head->name : 'NULL') . "\n";
