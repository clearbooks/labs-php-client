<?php
namespace Clearbooks\LabsPhpClient\Toggle\Entity;

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

    public function getId()
    {
        return $this->userId;
    }
}
//EOF UserStub.php