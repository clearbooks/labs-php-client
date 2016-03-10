<?php
namespace Clearbooks\Labs\Client\Toggle;

use Clearbooks\Labs\Client\Toggle\Entity\SegmentStub;
use Clearbooks\Labs\Client\Toggle\Gateway\SegmentTogglePolicyGatewayMock;
use Clearbooks\Labs\Client\Toggle\Gateway\ToggleGatewayMock;
use Clearbooks\Labs\Client\Toggle\Segment\SegmentLockedPropertyFilter;
use Clearbooks\Labs\Client\Toggle\Segment\SegmentPolicyEvaluator;
use Clearbooks\Labs\Client\Toggle\Segment\SegmentPriorityArranger;

class CanDefaultToggleStatusBeOverruledTest extends \PHPUnit_Framework_TestCase
{
    const TOGGLE_NAME = "Test toggle";

    /**
     * @var ToggleGatewayMock
     */
    private $toggleGatewayMock;

    /**
     * @var SegmentTogglePolicyGatewayMock
     */
    private $segmentTogglePolicyGatewayMock;

    /**
     * @var CanDefaultToggleStatusBeOverruled
     */
    private $canDefaultToggleStatusBeOverruled;

    public function setUp()
    {
        parent::setUp();
        $this->toggleGatewayMock = new ToggleGatewayMock();
        $this->segmentTogglePolicyGatewayMock = new SegmentTogglePolicyGatewayMock();
        $this->canDefaultToggleStatusBeOverruled = new CanDefaultToggleStatusBeOverruled(
            $this->toggleGatewayMock,
            new SegmentLockedPropertyFilter(),
            new SegmentPolicyEvaluator( new SegmentPriorityArranger(), $this->segmentTogglePolicyGatewayMock )
        );
    }

    /**
     * @param $segments
     */
    private function assertCanBeOverruledGivenSegments( $segments )
    {
        $this->assertTrue( $this->canDefaultToggleStatusBeOverruled->canBeOverruled( self::TOGGLE_NAME, $segments ) );
    }

    /**
     * @param $segments
     */
    private function assertCannotBeOverruledGivenSegments( $segments )
    {
        $this->assertFalse( $this->canDefaultToggleStatusBeOverruled->canBeOverruled( self::TOGGLE_NAME, $segments ) );
    }

    /**
     * @test
     */
    public function GivenToggleReleaseDateIsTodayOrInThePast_ExpectFalse()
    {
        $this->toggleGatewayMock->setIsReleaseDateTodayOrInThePast( self::TOGGLE_NAME, true );
        $this->assertCannotBeOverruledGivenSegments( [ ] );
    }

    /**
     * @test
     */
    public function GivenNoSegmentPolicySet_WhenThereAreNoSegments_ExpectTrue()
    {
        $this->assertCanBeOverruledGivenSegments( [ ] );
    }

    /**
     * @test
     */
    public function GivenNoSegmentPolicySet_WhenThereIsALockedSegment_ExpectTrue()
    {
        $segments = [ new SegmentStub( 1, 1, true ) ];
        $this->assertCanBeOverruledGivenSegments( $segments );
    }

    /**
     * @test
     */
    public function GivenSegmentPolicySetForNotLockedSegment_ExpectTrue()
    {
        $segments = [ new SegmentStub( 1, 1 ) ];
        $this->segmentTogglePolicyGatewayMock->setTogglePolicyDisabled( self::TOGGLE_NAME, $segments[0] );
        $this->assertCanBeOverruledGivenSegments( $segments );
    }

    /**
     * @test
     */
    public function GivenSegmentPolicySetForLockedSegment_ExpectFalse()
    {
        $segments = [ new SegmentStub( 1, 1, true ) ];
        $this->segmentTogglePolicyGatewayMock->setTogglePolicyDisabled( self::TOGGLE_NAME, $segments[0] );
        $this->assertCannotBeOverruledGivenSegments( $segments );
    }

    /**
     * @test
     */
    public function GivenGroupToggleAndLockedSegment_ExpectTrue()
    {
        $segments = [ new SegmentStub( 1, 1, true ) ];
        $this->toggleGatewayMock->setIsGroupToggle( self::TOGGLE_NAME, true );
        $this->segmentTogglePolicyGatewayMock->setTogglePolicyDisabled( self::TOGGLE_NAME, $segments[0] );
        $this->assertCanBeOverruledGivenSegments( $segments );
    }

    /**
     * @test
     */
    public function GivenMultipleSegmentsWithoutPolicySetAndSegmentPolicySetForALockedSegment_ExpectFalse()
    {
        $segments = [
                new SegmentStub( 1, 1 ),
                new SegmentStub( 2, 5 ),
                new SegmentStub( 3, 4, true ),
                new SegmentStub( 4, 12 ),
                new SegmentStub( 5, 3, true )
        ];
        $this->segmentTogglePolicyGatewayMock->setTogglePolicyEnabled( self::TOGGLE_NAME, $segments[4] );
        $this->assertCannotBeOverruledGivenSegments( $segments );
    }
}
