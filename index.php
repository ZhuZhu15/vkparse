<?php
require_once __DIR__ . '/vendor/autoload.php';
ini_set('max_execution_time', '3600');

use vkParse\VKParse;
use vkParse\VKGroupMembersRepository;

//на бою нужно сделать джобу, которая обновляет токен, и репозиторий, который забирает его из хранилища
$accessToken = 'vk1.a.lcYJbSRgImobsbBb6PctLB2DTX-QtBXTc4d9DwLQv84toKIUhr-AwNnyeSGyvq4oynM59ILnX8XlVTg8G3_ure5zzwmDJaBrppLQSIZmmcjLjLGyCbz1xisTeBZ-4MmdhX7wqjT9ifUXHiAdlDiLH9B3RueEsEtW_OA1GTRScYU3h93ncXRvBYDwPXybqZb9';
if(empty($argv[1])) {
    echo 'Please write ID group number!';
    die();
}

$groupId = $argv[1];
$vk = new VKParse();
$offset = 0;
$members = $vk->groups()->getMembers($accessToken, ['group_id' => $groupId]);
$fullcount = $members['count'];
$groupRealMembers = 0;
$iterationStep = 25000;

try {
    $groupMembersRepository = new VKGroupMembersRepository();
    $mongoDBConnect = true;
    echo 'Uploading data...';
} catch (Exception $exception) {
    $mongoDBConnect = false;
    echo "Can't find MongoDB, just getting Members from VK, without saving...";
}

do {
    $code =  'var members = [];'
        .'var offset = '.$offset.';'
        .'var membersInExecute = 0;'
        .'while (offset < '.$fullcount.' && membersInExecute < ' . $iterationStep . ')'
        .'{members.push(API.groups.getMembers({"group_id": '.$groupId.', "sort": "id_asc", "offset": offset, "fields": "city, bdate, city, country"}).items);offset = offset + 1000;membersInExecute = membersInExecute + 1000;}'
        .'return members;';
    $membersChunks = $vk->execute($accessToken, $code);
    foreach($membersChunks as $members) {
        $groupRealMembers += count($members);
        if($mongoDBConnect !== false) {
            $groupMembersRepository->addMembers($members, $groupId);
        }
    }
    $offset += $iterationStep;
    unset($membersChunks);
} while($fullcount > $offset);

echo 'Processed users - ' . $groupRealMembers;
