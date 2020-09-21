<?php
namespace Clearbooks\Labs\Client\Toggle\Segment;

use Clearbooks\Labs\Client\Toggle\Entity\Segment;
use Clearbooks\Labs\Client\Toggle\Entity\SegmentStub;
use Clearbooks\Labs\Client\Toggle\Gateway\SegmentTogglePolicyGatewayMock;
use PHPUnit\Framework\TestCase;

class SegmentPolicyEvaluatorTest extends TestCase
{
    const TOGGLE_NAME = "Test toggle";

    /**
     * @var SegmentTogglePolicyGatewayMock
     */
    private $segmentTogglePolicyGatewayMock;

    /**
     * @var SegmentPolicyEvaluator
     */
    private $segmentPolicyEvaluator;

    public function setUp(): void
    {
        parent::setUp();
        $this->segmentTogglePolicyGatewayMock = new SegmentTogglePolicyGatewayMock();
        $this->segmentPolicyEvaluator = new SegmentPolicyEvaluator(
                new SegmentPriorityArranger(),
                $this->segmentTogglePolicyGatewayMock
        );
    }

    /**
     * @param Segment[] $segments
     */
    private function assertPolicyNotSet( array $segments )
    {
        $this->assertNull( $this->segmentPolicyEvaluator->evaluateSegmentPoliciesForToggle( self::TOGGLE_NAME, $segments ) );
    }

    /**
     * @param Segment[] $segments
     */
    private function assertPolicyEnabled( array $segments )
    {
        $this->assertTrue( $this->segmentPolicyEvaluator->evaluateSegmentPoliciesForToggle( self::TOGGLE_NAME, $segments ) );
    }

    /**
     * @param Segment[] $segments
     */
    private function assertPolicyDisabled( array $segments )
    {
        $this->assertFalse( $this->segmentPolicyEvaluator->evaluateSegmentPoliciesForToggle( self::TOGGLE_NAME, $segments ) );
    }

    /**
     * @test
     */
    public function GivenNoSegments_ExpectPolicyNotSet()
    {
        $this->assertPolicyNotSet( [ ] );
    }

    /**
     * @test
     */
    public function GivenOneSegmentWithoutPolicySet_ExpectPolicyNotSet()
    {
        $segments = [ new SegmentStub( 1, 10 ) ];
        $this->assertPolicyNotSet( $segments );
    }

    /**
     * @test
     */
    public function GivenOneSegmentWithPolicyEnabled_ExpectPolicyEnabled()
    {
        $segments = [ new SegmentStub( 1, 10 ) ];
        $this->segmentTogglePolicyGatewayMock->setTogglePolicyEnabled( self::TOGGLE_NAME, $segments[0] );
        $this->assertPolicyEnabled( $segments );
    }

    /**
     * @test
     */
    public function GivenOneSegmentWithPolicyDisabled_ExpectPolicyDisabled()
    {
        $segments = [ new SegmentStub( 1, 10 ) ];
        $this->segmentTogglePolicyGatewayMock->setTogglePolicyDisabled( self::TOGGLE_NAME, $segments[0] );
        $this->assertPolicyDisabled( $segments );
    }

    /**
     * @return array
     */
    public function getSegmentTestData()
    {
        return [
            [
                    [
                            new SegmentStub( 1, 2 ),
                            new SegmentStub( 2, 1 )
                    ],
                    [ ],
                    [ ],
                    null
            ],

            [
                    [
                            new SegmentStub( 1, 2 ),
                            new SegmentStub( 2, 10 ),
                            new SegmentStub( 3, 5 ),
                            new SegmentStub( 4, 4 ),
                    ],
                    [ 1 ],
                    [ 0 ],
                    true
            ],

            [
                    [
                            new SegmentStub( 1, 2 ),
                            new SegmentStub( 2, 100 ),
                            new SegmentStub( 3, 8 ),
                            new SegmentStub( 4, 4 ),
                    ],
                    [ 3 ],
                    [ ],
                    true
            ],

            [
                    [
                            new SegmentStub( 1, 2 ),
                            new SegmentStub( 2, 100 ),
                            new SegmentStub( 3, 8 ),
                            new SegmentStub( 4, 4 ),
                    ],
                    [ 0, 3 ],
                    [ 2 ],
                    false
            ],
        ];
    }

    /**
     * @test
     * @dataProvider getSegmentTestData
     *
     * @param Segment[] $segments
     * @param int[] $enabledSegmentKeys
     * @param int[] $disabledSegmentKeys
     * @param bool|null $expectedResult
     */
    public function GivenMultipleSegments_ExpectCorrectResult( array $segments, array $enabledSegmentKeys,
                                                               array $disabledSegmentKeys, $expectedResult )
    {
        foreach ( $segments as $key => $segment ) {
            if ( in_array( $key, $enabledSegmentKeys ) ) {
                $this->segmentTogglePolicyGatewayMock->setTogglePolicyEnabled( self::TOGGLE_NAME, $segment );
            }
            else if ( in_array( $key, $disabledSegmentKeys ) ) {
                $this->segmentTogglePolicyGatewayMock->setTogglePolicyDisabled( self::TOGGLE_NAME, $segment );
            }
        }

        $this->assertEquals( $expectedResult, $this->segmentPolicyEvaluator->evaluateSegmentPoliciesForToggle( self::TOGGLE_NAME, $segments ) );
    }
}
