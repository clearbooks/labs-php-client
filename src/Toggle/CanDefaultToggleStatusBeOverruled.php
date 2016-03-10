<?php
namespace Clearbooks\Labs\Client\Toggle;

use Clearbooks\Labs\Client\Toggle\Entity\Segment;
use Clearbooks\Labs\Client\Toggle\Gateway\ToggleGateway;
use Clearbooks\Labs\Client\Toggle\Segment\SegmentLockedPropertyFilter;
use Clearbooks\Labs\Client\Toggle\Segment\SegmentPolicyEvaluator;

class CanDefaultToggleStatusBeOverruled implements UseCase\CanDefaultToggleStatusBeOverruled
{
    /**
     * @var ToggleGateway
     */
    private $toggleGateway;

    /**
     * @var SegmentLockedPropertyFilter
     */
    private $segmentLockedPropertyFilter;

    /**
     * @var SegmentPolicyEvaluator
     */
    private $segmentPolicyEvaluator;

    public function __construct( ToggleGateway $toggleGateway, SegmentLockedPropertyFilter $segmentLockedPropertyFilter,
                                 SegmentPolicyEvaluator $segmentPolicyEvaluator )
    {

        $this->toggleGateway = $toggleGateway;
        $this->segmentLockedPropertyFilter = $segmentLockedPropertyFilter;
        $this->segmentPolicyEvaluator = $segmentPolicyEvaluator;
    }

    /**
     * @param string $toggleName
     * @param Segment[] $segments
     * @return bool
     */
    public function canBeOverruled( $toggleName, array $segments )
    {
        if ( $this->toggleGateway->isReleaseDateOfToggleReleaseTodayOrInThePast( $toggleName ) ) {
            return false;
        }

        $isGroupToggle = $this->toggleGateway->isGroupToggle( $toggleName );
        if ( $isGroupToggle ) {
            return true;
        }

        $lockedSegments = $this->segmentLockedPropertyFilter->filterLockedSegments( $segments );
        $lockedSegmentPolicyResult = $this->segmentPolicyEvaluator->evaluateSegmentPoliciesForToggle( $toggleName, $lockedSegments );
        return $lockedSegmentPolicyResult === null;
    }
}
