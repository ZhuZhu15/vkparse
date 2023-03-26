<?php

namespace vkParse;

use DateTime;
use MongoDB\Client;
use MongoDB\Collection;

class VKGroupMembersRepository
{
    private Collection $groupMemberCollection;

    public function __construct()
    {
        $this->groupMemberCollection = (new Client())->selectDatabase('vkParse')->selectCollection('vkGroupMembers');
        $this->groupMemberCollection->createIndex(['group_id' => 1, 'user_id' => 1]); //Можно вынести или закомментить, добавил для скорости поиска
    }

    public function addMembers(array $members, $group_id) {
        foreach($members as $member) {
            $this->groupMemberCollection->updateOne(
                [
                    'group_id' => $group_id,
                    'user_id' => $member['id']
                ],
                ['$set' => [
                    'group_id' => $group_id,
                    'user_id' => $member['id'],
                    'first_name' => $member['first_name'],
                    'last_name' => $member['last_name'],
                    'bdate' => $member['bdate'] ?? null,
                    'age' => isset($member['bdate']) && DateTime::createFromFormat('d.m.Y', $member['bdate']) !== false ?
                        DateTime::createFromFormat('d.m.Y', $member['bdate'])->diff(new DateTime())->format('%y') :
                        null,
                    'city' => $member['city']['title'] ?? null,
                    'country' => $member['country']['title'] ?? null
                ]],
                ['upsert' => true]
            );
        }
    }
}