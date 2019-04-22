<?php

namespace AppBundle\Entity;

use AppBundle\Entity\Type\EntityCollection;
use Symfony\Component\Validator\Constraints as Assert;
use AppBundle\Repository\Annotation as Api;

/**
 * @Api\Entity(endPoint="meeting_progresses")
 */
class MeetingProgress extends BaseEntity implements \JsonSerializable {
    const CALL_STATUS_IN_PROGRESS = 'inprogress';
    /**
     * @var int
     * Indicates how often is the heartbit message sent and how much time does one should add to the duration when received
     */
    public static $HEARTBIT_INTERVAL_SECONDS = 60;
    /**
     * @var int
     *
     * @Api\Property(type="integer")
     * @Api\Id()
     */
    private $id;
    /**
     * @var int meeting duration
     *
     * @Api\Property(type="integer")
     * @Assert\Type(type="integer")
     * @Assert\GreaterThanOrEqual(0)
     */
    private $duration = 0;
    /**
     * @var \DateTime last date/time when duration has been updated (both users were connected)
     *
     * @Api\Property(type="datetime")
     * @Assert\DateTime
     */
    private $lastDurationUpdate;
    /**
     * @var \DateTime Last time user entered meeting room (after onboarding)
     *
     * @Api\Property(type="datetime")
     * @Assert\DateTime
     */
    private $lastUserConnection;
    /**
     * @var \DateTime Last time guest entered meeting room (after onboarding)
     *
     * @Api\Property(type="datetime")
     * @Assert\DateTime
     */
    private $lastGuestConnection;
    /**
     * @var \DateTime Last time guest entered meeting code
     *
     * @Api\Property(type="datetime")
     * @Assert\DateTime
     */
    private $lastGuestEntrance;
    /**
     * @var \DateTime Last time user entered meeting room
     *
     * @Api\Property(type="datetime")
     * @Assert\DateTime
     */
    private $lastUserEntrance;
    /**
     * @var boolean flag that indicates if meeting has been recorder
     *
     * @Api\Property(type="boolean")
     * @Assert\Choice({"0", "1"})
     */
    private $recorded = false;
    /**
     * @var int recording duration
     *
     * @Api\Property(type="integer")
     * @Assert\Type(type="integer")
     * @Assert\GreaterThanOrEqual(0)
     */
    private $recordingDuration = 0;
    /**
     * @var int recording file size
     *
     * @Api\Property(type="integer")
     * @Assert\Type(type="integer")
     * @Assert\GreaterThanOrEqual(0)
     */
    private $recordingSize = 0;
    /**
     * @var boolean flag that indicates if meeting is running in compatibility mode
     *
     * @Api\Property(type="boolean")
     * @Assert\Choice({"0", "1"})
     */
    private $compatibilityMode;
    /**
     * @var string Status of phone call (initiated in compatibility mode)
     *
     * @Api\Property()
     * @Assert\Type(type="string")
     */
    private $phoneCallStatus;
    /**
     * @var EntityCollection<PhoneConferenceRecording> list of phone conference recordings
     *
     * @Api\Property(entity="PhoneConferenceRecording")
     * @Api\Collection()
     */
    private $phoneConferenceRecordings;
    /**
     * @var string streaming server geographically closest to the user
     *
     * @Api\Property()
     * @Assert\Type(type="string")
     */
    private $streamingServer;

    public static $countriesForStreaming = [
        ['text' => ' Netherlands', 'id' => 'NL', 'phone' => ' +31 20 262 2302'],
        ['text' => ' Belgium', 'id' => 'BE', 'phone' => ' +32 460 24 07 67'],
        ['text' => ' United Kingdom', 'id' => 'GB', 'phone' => ' +44 808 169 2432'],
        ['text' => ' Chili', 'id' => 'CL', 'phone' => ' +56 2 2666 5557'],
        ['text' => ' United States', 'id' => 'US', 'phone' => ' +1 415 649 5092 '],
        ['text' => ' Australia', 'id' => 'AU', 'phone' => ' +61 488 839 365 '],
        ['text' => ' Austria', 'id' => 'AT', 'phone' => ' +43 720 229796 '],
        ['text' => ' Brazil', 'id' => 'BR', 'phone' => ' +55 11 4118-0727 '],
        ['text' => ' Canada', 'id' => 'CA', 'phone' => ' +1 647-696-8354 '],
        ['text' => ' Czech Republic', 'id' => 'CZ', 'phone' => ' +420 910 902 511 '],
        ['text' => ' France', 'id' => 'FR', 'phone' => ' +33 6 44 60 88 76 '],
        ['text' => ' Ireland', 'id' => 'IE', 'phone' => ' +353 76 620 5816 '],
        ['text' => ' Israel', 'id' => 'IL', 'phone' => ' +972 2-376-0439 '],
        ['text' => ' Spain', 'id' => 'ES', 'phone' => ' +34 518 80 89 56 '],
        ['text' => ' Sweden', 'id' => 'SE', 'phone' => ' +46 10 888 64 62 '],
    ];

    public function __construct() {
        $this->phoneConferenceRecordings = new EntityCollection();
    }

    /**
     * @return int
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @param int $id
     * @return $this|void
     */
    public function setId($id) {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getDuration() {
        return $this->duration;
    }

    /**
     * @param int $duration
     */
    public function setDuration($duration) {
        $this->duration = $duration;
    }

    /**
     * @return \DateTime
     */
    public function getLastDurationUpdate() {
        return $this->lastDurationUpdate;
    }

    /**
     * @param \DateTime $lastDurationUpdate
     */
    public function setLastDurationUpdate($lastDurationUpdate) {
        $this->lastDurationUpdate = $lastDurationUpdate;
    }

    public function incrementDuration($incrementIntervalInSeconds) {
        $this->duration += $incrementIntervalInSeconds;
    }

    function jsonSerialize() {
        return [
            'id' => $this->getId(),
            'duration' => $this->getDuration(),
            'lastDurationUpdate' => $this->getLastDurationUpdate()
        ];
    }

    /**
     * @return \DateTime
     */
    public function getLastUserConnection() {
        return $this->lastUserConnection;
    }

    /**
     * @param \DateTime $lastUserConnection
     */
    public function setLastUserConnection(\DateTime $lastUserConnection) {
        $this->lastUserConnection = $lastUserConnection;
    }

    /**
     * @return \DateTime
     */
    public function getLastGuestConnection() {
        return $this->lastGuestConnection;
    }

    /**
     * @param \DateTime $lastGuestConnection
     */
    public function setLastGuestConnection(\DateTime $lastGuestConnection) {
        $this->lastGuestConnection = $lastGuestConnection;
    }

    /**
     * @return mixed
     */
    public function getLastGuestEntrance() {
        return $this->lastGuestEntrance;
    }

    /**
     * @param mixed $lastGuestEntrance
     */
    public function setLastGuestEntrance($lastGuestEntrance) {
        $this->lastGuestEntrance = $lastGuestEntrance;
    }

    /**
     * @return \DateTime
     */
    public function getLastUserEntrance() {
        return $this->lastUserEntrance;
    }

    /**
     * @param \DateTime $lastUserEntrance
     */
    public function setLastUserEntrance(\DateTime $lastUserEntrance) {
        $this->lastUserEntrance = $lastUserEntrance;
    }

    /**
     * @return bool
     */
    public function isRecorded(): bool {
        return $this->recorded;
    }

    /**
     * @param bool $recorded
     */
    public function setRecorded(bool $recorded) {
        $this->recorded = $recorded;
    }

    /**
     * @return int
     */
    public function getRecordingDuration() {
        return $this->recordingDuration;
    }

    /**
     * @param int $recordingDuration
     */
    public function setRecordingDuration($recordingDuration) {
        $this->recordingDuration = $recordingDuration;
    }

    /**
     * @return int
     */
    public function getRecordingSize() {
        return $this->recordingSize;
    }

    /**
     * @param int $recordingSize
     */
    public function setRecordingSize($recordingSize) {
        $this->recordingSize = $recordingSize;
    }



    /**
     * @return bool
     */
    public function isCompatibilityMode(): bool {
        return is_null($this->compatibilityMode) ? false : $this->compatibilityMode;
    }

    /**
     * @param bool $compatibilityMode
     */
    public function setCompatibilityMode(bool $compatibilityMode) {
        $this->compatibilityMode = $compatibilityMode;
    }

    /**
     * @return string
     */
    public function getPhoneCallStatus() {
        return $this->phoneCallStatus;
    }

    /**
     * @param string $phoneCallStatus
     */
    public function setPhoneCallStatus(string $phoneCallStatus) {
        $this->phoneCallStatus = $phoneCallStatus;
    }

    public function isPhoneCallActive() {
        return $this->getPhoneCallStatus() == self::CALL_STATUS_IN_PROGRESS;
    }

    public function hasPhoneConferenceRecordings() {
        return !$this->phoneConferenceRecordings->isEmpty();
    }

    /**
     * @return EntityCollection
     */
    public function getPhoneConferenceRecordings() {
        return $this->phoneConferenceRecordings;
    }

    /**
     * @param EntityCollection $phoneConferenceRecordings
     */
    public function setPhoneConferenceRecordings($phoneConferenceRecordings) {
        $this->phoneConferenceRecordings = $phoneConferenceRecordings;
    }

    /**
     * @return string
     */
    public function getStreamingServer() {
        return $this->streamingServer;
    }

    /**
     * @param string $streamingServer
     */
    public function setStreamingServer($streamingServer) {
        $this->streamingServer = $streamingServer;
    }

    public function hasStreamingServer() {
        return !empty($this->streamingServer);
    }
}
