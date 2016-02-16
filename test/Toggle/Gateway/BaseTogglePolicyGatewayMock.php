<?php
namespace Clearbooks\Labs\Client\Toggle\Gateway;

use Clearbooks\Labs\Client\Toggle\Entity\Identity;
use Clearbooks\Labs\Client\Toggle\TogglePolicyResponseStub;
use Clearbooks\Labs\Client\Toggle\UseCase\Response\TogglePolicyResponse;

class BaseTogglePolicyGatewayMock implements TogglePolicyGateway
{
    /**
     * @var array
     */
    private $responses = [ ];

    /**
     * @param string $toggleName
     * @param Identity $idHolder
     * @return TogglePolicyResponse
     */
    public function getTogglePolicy( $toggleName, Identity $idHolder )
    {
        if ( !isset( $this->responses[$toggleName] ) || !isset( $this->responses[$toggleName][$idHolder->getId()] ) ) {
            return new TogglePolicyResponseStub();
        }

        return new TogglePolicyResponseStub( $this->responses[$toggleName][$idHolder->getId()] );
    }

    /**
     * @param string $toggleName
     * @param Identity $idHolder
     * @param bool|null $togglePolicyStatus
     */
    public function setTogglePolicy( $toggleName, Identity $idHolder, $togglePolicyStatus )
    {
        if ( !isset( $this->responses[$toggleName] ) ) {
            $this->responses[$toggleName] = [ ];
        }

        if ( $togglePolicyStatus === null ) {
            unset( $this->responses[$toggleName][$idHolder->getId()] );
            return;
        }

        $this->responses[$toggleName][$idHolder->getId()] = $togglePolicyStatus;
    }

    /**
     * @param string $toggleName
     * @param Identity $idHolder
     */
    public function setTogglePolicyDisabled( $toggleName, Identity $idHolder )
    {
        $this->setTogglePolicy( $toggleName, $idHolder, false );
    }

    /**
     * @param string $toggleName
     * @param Identity $idHolder
     */
    public function setTogglePolicyEnabled( $toggleName, Identity $idHolder )
    {
        $this->setTogglePolicy( $toggleName, $idHolder, true );
    }
}
