<?php
namespace Clearbooks\Labs\Client\Toggle;

use Clearbooks\Labs\Client\Toggle\Entity\Group;
use Clearbooks\Labs\Client\Toggle\Entity\Segment;
use Clearbooks\Labs\Client\Toggle\Entity\User;
use Clearbooks\Labs\Client\Toggle\Gateway\AutoSubscribersGateway;
use Clearbooks\Labs\Client\Toggle\Gateway\GroupTogglePolicyGateway;
use Clearbooks\Labs\Client\Toggle\Gateway\SegmentTogglePolicyGateway;
use Clearbooks\Labs\Client\Toggle\Gateway\ToggleGateway;
use Clearbooks\Labs\Client\Toggle\Gateway\TogglePolicyGateway;
use Clearbooks\Labs\Client\Toggle\Gateway\UserTogglePolicyGateway;
use Clearbooks\Labs\Client\Toggle\Segment\SegmentLockedPropertyFilter;
use Clearbooks\Labs\Client\Toggle\Segment\SegmentPolicyEvaluator;

class StatelessToggleChecker implements UseCase\ToggleChecker
{
    /**
     * @var ToggleGateway
     */
    private $toggleGateway;

    /**
     * @var TogglePolicyGateway
     */
    private $groupPolicyGateway;

    /**
     * @var TogglePolicyGateway
     */
    private $userPolicyGateway;

    /**
     * @var AutoSubscribersGateway
     */
    private $autoSubscribersGateway;

    /**
     * @var SegmentTogglePolicyGateway
     */
    private $segmentTogglePolicyGateway;

    /**
     * @var SegmentLockedPropertyFilter
     */
    private $segmentLockedPropertyFilter;

    /**
     * @var SegmentPolicyEvaluator
     */
    private $segmentPolicyEvaluator;

    /**
     * @param ToggleGateway $toggleGateway
     * @param UserTogglePolicyGateway $userPolicyGateway
     * @param GroupTogglePolicyGateway $groupPolicyGateway
     * @param AutoSubscribersGateway $autoSubscribersGateway
     * @param SegmentTogglePolicyGateway $segmentTogglePolicyGateway
     * @param SegmentLockedPropertyFilter $segmentLockedPropertyFilter
     * @param SegmentPolicyEvaluator $segmentPolicyEvaluator
     */
    public function __construct( ToggleGateway $toggleGateway, UserTogglePolicyGateway $userPolicyGateway,
                                 GroupTogglePolicyGateway $groupPolicyGateway,
                                 AutoSubscribersGateway $autoSubscribersGateway,
                                 SegmentTogglePolicyGateway $segmentTogglePolicyGateway,
                                 SegmentLockedPropertyFilter $segmentLockedPropertyFilter,
                                 SegmentPolicyEvaluator $segmentPolicyEvaluator )
    {
        $this->toggleGateway = $toggleGateway;
        $this->groupPolicyGateway = $groupPolicyGateway;
        $this->userPolicyGateway = $userPolicyGateway;
        $this->autoSubscribersGateway = $autoSubscribersGateway;
        $this->segmentTogglePolicyGateway = $segmentTogglePolicyGateway;
        $this->segmentLockedPropertyFilter = $segmentLockedPropertyFilter;
        $this->segmentPolicyEvaluator = $segmentPolicyEvaluator;
    }

    /**
     * @param string $toggleName
     * @param Group $group
     * @return bool|null
     */
    private function evaluateGroupPolicyForToggle( $toggleName, Group $group )
    {
        $groupPolicy = $this->groupPolicyGateway->getTogglePolicy( $toggleName, $group );
        return $groupPolicy->isNotSet() ? null : $groupPolicy->isEnabled();
    }

    /**
     * @param string $toggleName
     * @param User $user
     * @return bool|null
     */
    private function evaluateUserPolicyForToggle( $toggleName, User $user )
    {
        $userTogglePolicy = $this->userPolicyGateway->getTogglePolicy( $toggleName, $user );
        return $userTogglePolicy->isNotSet() ? null : $userTogglePolicy->isEnabled();
    }

    /**
     * @param string $toggleName
     * @param User $user
     * @param Segment[] $segments
     * @return bool|null
     */
    private function isVisibleToggleActiveForFutureReleasesIfLockedSegmentPolicyAndGroupPolicyHasNoEffect( $toggleName,
                                                                                                           User $user,
                                                                                                           array $segments,
                                                                                                           $isGroupToggle )
    {
        if ( !$isGroupToggle ) {
            $userPolicyResult = $this->evaluateUserPolicyForToggle( $toggleName, $user );
            if ( $userPolicyResult !== null ) {
                return $userPolicyResult;
            }
        }

        $notLockedSegments = $this->segmentLockedPropertyFilter->filterNotLockedSegments( $segments );
        $notLockedSegmentPolicyResult = $this->segmentPolicyEvaluator->evaluateSegmentPoliciesForToggle( $toggleName, $notLockedSegments );
        if ( $notLockedSegmentPolicyResult !== null ) {
            return $notLockedSegmentPolicyResult;
        }

        if ( $isGroupToggle ) {
            return false;
        }

        return $this->autoSubscribersGateway->isUserAutoSubscriber( $user );
    }

    /**
     * @param string $toggleName
     * @param User $user
     * @param Group $group
     * @param Segment[] $segments
     * @return bool|null
     */
    private function isVisibleToggleActiveForFutureRelease( $toggleName, User $user, Group $group, array $segments )
    {
        $isGroupToggle = $this->toggleGateway->isGroupToggle( $toggleName );
        if ( !$isGroupToggle ) {
            $lockedSegments = $this->segmentLockedPropertyFilter->filterLockedSegments( $segments );
            $lockedSegmentPolicyResult = $this->segmentPolicyEvaluator->evaluateSegmentPoliciesForToggle( $toggleName, $lockedSegments );
            if ( $lockedSegmentPolicyResult !== null ) {
                return $lockedSegmentPolicyResult;
            }
        }

        $groupPolicyResult = $this->evaluateGroupPolicyForToggle( $toggleName, $group );
        if ( $groupPolicyResult !== null ) {
            return $groupPolicyResult;
        }

        return $this->isVisibleToggleActiveForFutureReleasesIfLockedSegmentPolicyAndGroupPolicyHasNoEffect( $toggleName, $user, $segments, $isGroupToggle );
    }

    /**
     * @param string $toggleName
     * @param User $user
     * @param Group $group
     * @param Segment[] $segments
     * @return bool is it active
     */
    public function isToggleActive( $toggleName, User $user, Group $group, array $segments )
    {
        if ( !$this->toggleGateway->isToggleVisibleForUsers( $toggleName ) ) {
            return false;
        }

        if ( $this->toggleGateway->isReleaseDateOfToggleReleaseTodayOrInThePast( $toggleName ) ) {
            return true;
        }

        return $this->isVisibleToggleActiveForFutureRelease( $toggleName, $user, $group, $segments );
    }
}
