<?php
namespace Clearbooks\Labs\Client\Toggle\Entity;

class GroupStub implements Group
{
    /**
     * @var string
     */
    private $groupId;

    /**
     * GroupStub constructor.
     * @param string $groupId
     */
    public function __construct($groupId)
    {
        $this->groupId = $groupId;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->groupId;
    }
}
//EOF GroupStub.php