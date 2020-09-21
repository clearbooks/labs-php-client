<?php
namespace Clearbooks\Labs\Client\Toggle\Segment;

use Clearbooks\Labs\Client\Toggle\Entity\SegmentStub;
use PHPUnit\Framework\TestCase;

class SegmentPriorityArrangerTest extends TestCase
{
    /**
     * @var SegmentPriorityArranger
     */
    private $segmentPriorityArranger;

    public function setUp(): void
    {
        parent::setUp();
        $this->segmentPriorityArranger = new SegmentPriorityArranger();
    }

    /**
     * @test
     */
    public function GivenEmptyArray_ExpectEmptyArray()
    {
        $segments = [ ];
        $orderedSegments = $this->segmentPriorityArranger->orderSegmentsByPriority( $segments );
        $this->assertEquals( $segments, $orderedSegments );
    }

    /**
     * @test
     */
    public function GivenUnorderedSegments_ExpectOrderedListByPriority()
    {
        $segments = [
            new SegmentStub( 5, 5 ),
            new SegmentStub( 4, 4 ),
            new SegmentStub( 3, 3 ),
            new SegmentStub( 2, 2 ),
            new SegmentStub( 1, 1 )
        ];

        $shuffledSegments = $segments;
        shuffle( $shuffledSegments );

        $orderedSegments = $this->segmentPriorityArranger->orderSegmentsByPriority( $shuffledSegments );
        $this->assertEquals( $segments, $orderedSegments );
    }

    /**
     * @test
     */
    public function GivenSegmentsWithSamePriority_ThenExpectOrderingToBeDeterminedById()
    {
        $segments = [
            new SegmentStub( 10, 10 ),
            new SegmentStub( 9, 10 ),
            new SegmentStub( 1, 10 )
        ];

        $shuffledSegments = $segments;
        shuffle( $shuffledSegments );

        $orderedSegments = $this->segmentPriorityArranger->orderSegmentsByPriority( $shuffledSegments );
        $this->assertEquals( $segments, $orderedSegments );
    }


}
