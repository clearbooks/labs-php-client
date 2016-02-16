<?php
namespace Clearbooks\Labs\Client\Toggle\Entity;

class UserStub implements User
{
    /**
     * @var string
     */
    private $userId;

    /**
     * UserStub constructor.
     * @param string $userId
     */
    public function __construct($userId)
    {
        $this->userId = $userId;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->userId;
    }
}
//EOF UserStub.php