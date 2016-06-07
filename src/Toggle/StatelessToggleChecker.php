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
use Clearbooks\Labs\Client\Toggle\UseCase\Response\TogglePolicyResponse;

class StatelessToggleChecker implements UseCase\ToggleChecker
{
    private $visible;
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
    public function __construct(
        ToggleGateway $toggleGateway,
        UserTogglePolicyGateway $userPolicyGateway,
        GroupTogglePolicyGateway $groupPolicyGateway,
        AutoSubscribersGateway $autoSubscribersGateway,
        SegmentTogglePolicyGateway $segmentTogglePolicyGateway,
        SegmentLockedPropertyFilter $segmentLockedPropertyFilter,
        SegmentPolicyEvaluator $segmentPolicyEvaluator
    )
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
     * @param User $user
     * @param Group $group
     * @param Segment[] $segments
     * @return bool is it active
     */
    public function isToggleActive( $toggleName, User $user, Group $group, array $segments )
    {
        $this->visible = $this->toggleGateway->isToggleVisibleForUsers( $toggleName );
        $result = $this->evaluateHierarchicalPolicies( $toggleName, $user, $group, $segments );

        if ( !$this->visible ) {
            assert( !$result, 'Not visible toggles are always inactive' );
        }
        return $result;
    }

    /**
     * @param string $toggleName
     * @param User $user
     * @param Group $group
     * @param Segment[] $segments
     * @return bool is it active
     */
    private function evaluateHierarchicalPolicies( $toggleName, User $user, Group $group, array $segments )
    {
        if ( !$this->visible ) {
            return false;
        }

        if ( $this->isReleased( $toggleName ) ) {
            return true;
        }

        $lockedSegmentStatus = $this->getLockedSegmentPolicy( $toggleName, $segments );
        if ( $this->isLockedSegmentPolicyApplicable( $lockedSegmentStatus ) ) {
            return $lockedSegmentStatus;
        }

        $userToggleStatus = $this->userPolicyGateway->getTogglePolicy( $toggleName, $user );
        if ( $this->isUserPolicyApplicable( $toggleName, $userToggleStatus ) ) {
            return $userToggleStatus->isEnabled();
        }

        $groupToggleStatus = $this->groupPolicyGateway->getTogglePolicy( $toggleName, $group );
        if ( $this->isGroupPolicyApplicable( $groupToggleStatus ) ){
            return $groupToggleStatus->isEnabled();
        }

        $unlockedSegmentStatus = $this->getUnlockedSegmentPolicy( $toggleName, $segments );
        if( $this->isUnlockedSegmentPolicyApplicable( $unlockedSegmentStatus ) ) {
            return $unlockedSegmentStatus;
        }

        return $this->isUserToggle( $toggleName )
               && $this->isUserAutoSubscribed( $user );
    }
    
    /**
     * @param $toggleName
     * @return bool
     */
    private function isReleased( $toggleName )
    {
        return $this->toggleGateway->isReleaseDateOfToggleReleaseTodayOrInThePast( $toggleName );
    }
    
    /**
     * @param $toggleName
     * @param array $segments
     * @return bool|null
     */
    private function getLockedSegmentPolicy( $toggleName, array $segments )
    {
        $lockedSegments = $this->segmentLockedPropertyFilter->filterLockedSegments( $segments );
        $segmentResponse = $this->segmentPolicyEvaluator->evaluateSegmentPoliciesForToggle(
            $toggleName,
            $lockedSegments
        );
        return $segmentResponse;
    }
    
    /**
     * @param $segmentResponse
     * @return bool
     */
    private function isLockedSegmentPolicyApplicable( $segmentResponse )
    {
        return $segmentResponse !== null;
    }
    
    /**
     * @param $toggleName
     * @param $userToggleStatus
     * @return bool
     */
    private function isUserPolicyApplicable( $toggleName, $userToggleStatus )
    {
        return $this->isUserToggle( $toggleName ) && $this->isUserPolicySet( $userToggleStatus );
    }
    
    /**
     * @param $toggleName
     * @return bool
     */
    private function isUserToggle( $toggleName )
    {
        return !$this->toggleGateway->isGroupToggle( $toggleName );
    }
    
    /**
     * @param TogglePolicyResponse $userToggleStatus
     * @return bool
     */
    private function isUserPolicySet( TogglePolicyResponse $userToggleStatus )
    {
        return !$userToggleStatus->isNotSet();
    }

    /**
     * @param $groupToggleStatus
     * @return bool
     */
    private function isGroupPolicyApplicable( $groupToggleStatus )
    {
        return !$groupToggleStatus->isNotSet();
    }

    /**
     * @param $toggleName
     * @param array $segments
     * @return bool|null
     */
    private function getUnlockedSegmentPolicy( $toggleName, array $segments )
    {
        $unlockedSegments = $this->segmentLockedPropertyFilter->filterNotLockedSegments(
            $segments
        );
        $unlockedSegmentStatus = $this->segmentPolicyEvaluator->evaluateSegmentPoliciesForToggle(
            $toggleName,
            $unlockedSegments
        );
        return $unlockedSegmentStatus;
    }

    /**
     * @param $unlockedSegmentStatus
     * @return bool
     */
    private function isUnlockedSegmentPolicyApplicable( $unlockedSegmentStatus )
    {
        return $unlockedSegmentStatus !== null;
    }

    /**
     * @param User $user
     * @return bool
     */
    private function isUserAutoSubscribed( User $user )
    {
        return $this->autoSubscribersGateway->isUserAutoSubscriber( $user );
    }
    
}
