<?php
namespace Clearbooks\Labs\Client\Toggle\Gateway;

use Clearbooks\Labs\Client\Toggle\Entity\Identity;
use Clearbooks\Labs\Client\Toggle\UseCase\Response\TogglePolicyResponse;

class BaseTogglePolicyGatewayMock extends BaseToggleMock implements TogglePolicyGateway
{
    /** @var bool */
    private $idHolderMatched;
    /** @var TogglePolicyResponse */
    private $response;
    /** @var Identity */
    private $idHolder;

    /**
     * GroupTogglePolicyGatewayMock constructor.
     * @param string $toggleName
     * @param Identity $idHolder
     * @param TogglePolicyResponse $response
     */
    public function __construct($toggleName, Identity $idHolder,TogglePolicyResponse $response)
    {
        parent::__construct($toggleName);
        $this->response = $response;
        $this->idHolder = $idHolder;
    }

    /**
     * @return bool
     */
    public function isCalledProperly()
    {
        return parent::isCalledProperly() && $this->idHolderMatched;
    }

    /**
     * @param string $toggleName
     * @param Identity $idHolder
     * @return TogglePolicyResponse
     */
    public function getTogglePolicy($toggleName,Identity $idHolder) {
        $this->testToggle($toggleName);
        $this->idHolderMatched = $idHolder->getId() == $this->idHolder->getId();
        return $this->response;
    }
}
//EOF BaseTogglePolicyGatewayMock.php