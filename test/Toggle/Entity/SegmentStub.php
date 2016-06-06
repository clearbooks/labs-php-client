<?php
namespace Clearbooks\Labs\Client\Toggle\Entity;

class SegmentStub implements Segment
{
    /**
     * @var string
     */
    private $segmentId;

    /**
     * @var int
     */
    private $priority;

    /**
     * @var bool
     */
    private $locked;

    /**
     * @param string $segmentId
     * @param int $priority
     * @param bool $locked
     */
    public function __construct( $segmentId, $priority, $locked = false )
    {
        $this->segmentId = $segmentId;
        $this->priority = $priority;
        $this->locked = $locked;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->segmentId;
    }

    /**
     * @return int
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * @return bool
     */
    public function isLocked()
    {
        return $this->locked;
    }

    /** @param int $priority */
    public function setPriority( $priority )
    {
        $this->priority = $priority;
    }

    
}
