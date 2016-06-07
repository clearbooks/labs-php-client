<?php
namespace Clearbooks\Labs\Client\Toggle\Segment;

use Clearbooks\Labs\Client\Toggle\Entity\Segment;

class SegmentPriorityArranger
{
    /**
     * @param Segment[] $segments
     * @return Segment[]
     */
    public function orderSegmentsByPriority( array $segments )
    {
        usort( $segments, function ( Segment $segment1, Segment $segment2 ) {
            if ( $segment1->getPriority() === $segment2->getPriority() ) {
                return $segment2->getId() - $segment1->getId();
            }

            return $segment1->getPriority() > $segment2->getPriority() ? -1 : 1;
        } );

        return $segments;
    }
}
