<?php
namespace Clearbooks\LabsClient\Toggle\Gateway;

use Clearbooks\LabsClient\Toggle\Entity\Identity;
use Clearbooks\LabsClient\Toggle\UseCase\Response\TogglePolicyResponse;

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
     * @param string $toggleId
     * @param Identity $idHolder
     * @param TogglePolicyResponse $response
     */
    public function __construct($toggleId, Identity $idHolder,TogglePolicyResponse $response)
    {
        parent::__construct($toggleId);
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
     * @param string $toggleId
     * @param Identity $idHolder
     * @return TogglePolicyResponse
     */
    public function getTogglePolicy($toggleId,Identity $idHolder) {
        $this->testToggle($toggleId);
        $this->idHolderMatched = $idHolder->getId() == $this->idHolder->getId();
        return $this->response;
    }
}
//EOF BaseTogglePolicyGatewayMock.php