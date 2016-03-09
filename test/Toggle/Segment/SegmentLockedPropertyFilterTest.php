<?php
namespace Clearbooks\Labs\Client\Toggle\Segment;

use Clearbooks\Labs\Client\Toggle\Entity\SegmentStub;

class SegmentLockedPropertyFilterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SegmentLockedPropertyFilter
     */
    private $segmentLockedPropertyFilter;

    public function setUp()
    {
        parent::setUp();
        $this->segmentLockedPropertyFilter = new SegmentLockedPropertyFilter();
    }

    /**
     * @test
     */
    public function GivenEmptySegmentArray_WhenCallingFilterLockedSegments_ExpectEmptyArray()
    {
        $segments = [ ];
        $filteredSegments = $this->segmentLockedPropertyFilter->filterLockedSegments( $segments );
        $this->assertEquals( $segments, $filteredSegments );
    }

    /**
     * @test
     */
    public function GivenEmptySegmentArray_WhenCallingFilterNotLockedSegments_ExpectEmptyArray()
    {
        $segments = [ ];
        $filteredSegments = $this->segmentLockedPropertyFilter->filterNotLockedSegments( $segments );
        $this->assertEquals( $segments, $filteredSegments );
    }

    /**
     * @test
     */
    public function GivenLockedAndNotLockedSegments_WhenCallingFilterLockedSegments_ExpectLockedSegmentsOnly()
    {
        $lockedSegments = [
            new SegmentStub( 1, 10, true ),
            new SegmentStub( 8, 4, true )
        ];

        $segments = [
            new SegmentStub( 2, 2 ),
            $lockedSegments[0],
            new SegmentStub( 10, 20 ),
            $lockedSegments[1]
        ];

        $filteredSegments = $this->segmentLockedPropertyFilter->filterLockedSegments( $segments );
        $this->assertEquals( $lockedSegments, array_values( $filteredSegments ) );
    }

    /**
     * @test
     */
    public function GivenLockedAndNotLockedSegments_WhenCallingFilterNotLockedSegments_ExpectNotLockedSegmentsOnly()
    {
        $notLockedSegments = [
                new SegmentStub( 2, 2 ),
                new SegmentStub( 10, 20 )
        ];

        $segments = [
                new SegmentStub( 1, 10, true ),
                $notLockedSegments[0],
                new SegmentStub( 8, 4, true ),
                $notLockedSegments[1]
        ];

        $filteredSegments = $this->segmentLockedPropertyFilter->filterNotLockedSegments( $segments );
        $this->assertEquals( $notLockedSegments, array_values( $filteredSegments ) );
    }
}
