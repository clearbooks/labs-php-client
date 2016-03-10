<?php
namespace Clearbooks\Labs\Client\Toggle\Segment;

use Clearbooks\Labs\Client\Toggle\Entity\Segment;

class SegmentLockedPropertyFilter
{
    /**
     * @param Segment[] $segments
     * @param bool $returnLockedSegments
     * @return Segment[]
     */
    private function filterSegmentsByLockedProperty( array $segments, $returnLockedSegments )
    {
        return array_filter( $segments, function( Segment $segment ) use ( $returnLockedSegments ) {
            return $returnLockedSegments === $segment->isLocked();
        } );
    }

    /**
     * @param Segment[] $segments
     * @return Segment[]
     */
    public function filterLockedSegments( array $segments )
    {
        return $this->filterSegmentsByLockedProperty( $segments, true );
    }

    /**
     * @param Segment[] $segments
     * @return Segment[]
     */
    public function filterNotLockedSegments( array $segments )
    {
        return $this->filterSegmentsByLockedProperty( $segments, false );
    }
}
