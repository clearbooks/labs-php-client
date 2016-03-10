<?php
namespace Clearbooks\Labs\Client\Toggle\Segment;

use Clearbooks\Labs\Client\Toggle\Gateway\SegmentTogglePolicyGateway;

class SegmentPolicyEvaluator
{
    /**
     * @var SegmentPriorityArranger
     */
    private $segmentPriorityArranger;

    /**
     * @var SegmentTogglePolicyGateway
     */
    private $segmentTogglePolicyGateway;

    /**
     * @param SegmentPriorityArranger $segmentPriorityArranger
     * @param SegmentTogglePolicyGateway $segmentTogglePolicyGateway
     */
    public function __construct( SegmentPriorityArranger $segmentPriorityArranger,
                                 SegmentTogglePolicyGateway $segmentTogglePolicyGateway )
    {

        $this->segmentPriorityArranger = $segmentPriorityArranger;
        $this->segmentTogglePolicyGateway = $segmentTogglePolicyGateway;
    }

    /**
     * @param array $segments
     * @param string $toggleName
     * @return bool|null
     */
    public function evaluateSegmentPoliciesForToggle( $toggleName, array $segments )
    {
        $segments = $this->segmentPriorityArranger->orderSegmentsByPriority( $segments );

        foreach ( $segments as $segment ) {
            $segmentPolicyResponse = $this->segmentTogglePolicyGateway->getTogglePolicy( $toggleName, $segment );

            if ( $segmentPolicyResponse->isNotSet() ) {
                continue;
            }

            return $segmentPolicyResponse->isEnabled();
        }

        return null;
    }
}
