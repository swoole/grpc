<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: rpc.proto

namespace Etcdserverpb;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Generated from protobuf message <code>etcdserverpb.StatusResponse</code>
 */
class StatusResponse extends \Google\Protobuf\Internal\Message
{
    /**
     * Generated from protobuf field <code>.etcdserverpb.ResponseHeader header = 1;</code>
     */
    private $header = null;
    /**
     * version is the cluster protocol version used by the responding member.
     *
     * Generated from protobuf field <code>string version = 2;</code>
     */
    private $version = '';
    /**
     * dbSize is the size of the backend database physically allocated, in bytes, of the responding member.
     *
     * Generated from protobuf field <code>int64 dbSize = 3;</code>
     */
    private $dbSize = 0;
    /**
     * leader is the member ID which the responding member believes is the current leader.
     *
     * Generated from protobuf field <code>uint64 leader = 4;</code>
     */
    private $leader = 0;
    /**
     * raftIndex is the current raft committed index of the responding member.
     *
     * Generated from protobuf field <code>uint64 raftIndex = 5;</code>
     */
    private $raftIndex = 0;
    /**
     * raftTerm is the current raft term of the responding member.
     *
     * Generated from protobuf field <code>uint64 raftTerm = 6;</code>
     */
    private $raftTerm = 0;
    /**
     * raftAppliedIndex is the current raft applied index of the responding member.
     *
     * Generated from protobuf field <code>uint64 raftAppliedIndex = 7;</code>
     */
    private $raftAppliedIndex = 0;
    /**
     * errors contains alarm/health information and status.
     *
     * Generated from protobuf field <code>repeated string errors = 8;</code>
     */
    private $errors;
    /**
     * dbSizeInUse is the size of the backend database logically in use, in bytes, of the responding member.
     *
     * Generated from protobuf field <code>int64 dbSizeInUse = 9;</code>
     */
    private $dbSizeInUse = 0;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type \Etcdserverpb\ResponseHeader $header
     *     @type string $version
     *           version is the cluster protocol version used by the responding member.
     *     @type int|string $dbSize
     *           dbSize is the size of the backend database physically allocated, in bytes, of the responding member.
     *     @type int|string $leader
     *           leader is the member ID which the responding member believes is the current leader.
     *     @type int|string $raftIndex
     *           raftIndex is the current raft committed index of the responding member.
     *     @type int|string $raftTerm
     *           raftTerm is the current raft term of the responding member.
     *     @type int|string $raftAppliedIndex
     *           raftAppliedIndex is the current raft applied index of the responding member.
     *     @type string[]|\Google\Protobuf\Internal\RepeatedField $errors
     *           errors contains alarm/health information and status.
     *     @type int|string $dbSizeInUse
     *           dbSizeInUse is the size of the backend database logically in use, in bytes, of the responding member.
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Rpc::initOnce();
        parent::__construct($data);
    }

    /**
     * Generated from protobuf field <code>.etcdserverpb.ResponseHeader header = 1;</code>
     * @return \Etcdserverpb\ResponseHeader
     */
    public function getHeader()
    {
        return $this->header;
    }

    /**
     * Generated from protobuf field <code>.etcdserverpb.ResponseHeader header = 1;</code>
     * @param \Etcdserverpb\ResponseHeader $var
     * @return $this
     */
    public function setHeader($var)
    {
        GPBUtil::checkMessage($var, \Etcdserverpb\ResponseHeader::class);
        $this->header = $var;

        return $this;
    }

    /**
     * version is the cluster protocol version used by the responding member.
     *
     * Generated from protobuf field <code>string version = 2;</code>
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * version is the cluster protocol version used by the responding member.
     *
     * Generated from protobuf field <code>string version = 2;</code>
     * @param string $var
     * @return $this
     */
    public function setVersion($var)
    {
        GPBUtil::checkString($var, True);
        $this->version = $var;

        return $this;
    }

    /**
     * dbSize is the size of the backend database physically allocated, in bytes, of the responding member.
     *
     * Generated from protobuf field <code>int64 dbSize = 3;</code>
     * @return int|string
     */
    public function getDbSize()
    {
        return $this->dbSize;
    }

    /**
     * dbSize is the size of the backend database physically allocated, in bytes, of the responding member.
     *
     * Generated from protobuf field <code>int64 dbSize = 3;</code>
     * @param int|string $var
     * @return $this
     */
    public function setDbSize($var)
    {
        GPBUtil::checkInt64($var);
        $this->dbSize = $var;

        return $this;
    }

    /**
     * leader is the member ID which the responding member believes is the current leader.
     *
     * Generated from protobuf field <code>uint64 leader = 4;</code>
     * @return int|string
     */
    public function getLeader()
    {
        return $this->leader;
    }

    /**
     * leader is the member ID which the responding member believes is the current leader.
     *
     * Generated from protobuf field <code>uint64 leader = 4;</code>
     * @param int|string $var
     * @return $this
     */
    public function setLeader($var)
    {
        GPBUtil::checkUint64($var);
        $this->leader = $var;

        return $this;
    }

    /**
     * raftIndex is the current raft committed index of the responding member.
     *
     * Generated from protobuf field <code>uint64 raftIndex = 5;</code>
     * @return int|string
     */
    public function getRaftIndex()
    {
        return $this->raftIndex;
    }

    /**
     * raftIndex is the current raft committed index of the responding member.
     *
     * Generated from protobuf field <code>uint64 raftIndex = 5;</code>
     * @param int|string $var
     * @return $this
     */
    public function setRaftIndex($var)
    {
        GPBUtil::checkUint64($var);
        $this->raftIndex = $var;

        return $this;
    }

    /**
     * raftTerm is the current raft term of the responding member.
     *
     * Generated from protobuf field <code>uint64 raftTerm = 6;</code>
     * @return int|string
     */
    public function getRaftTerm()
    {
        return $this->raftTerm;
    }

    /**
     * raftTerm is the current raft term of the responding member.
     *
     * Generated from protobuf field <code>uint64 raftTerm = 6;</code>
     * @param int|string $var
     * @return $this
     */
    public function setRaftTerm($var)
    {
        GPBUtil::checkUint64($var);
        $this->raftTerm = $var;

        return $this;
    }

    /**
     * raftAppliedIndex is the current raft applied index of the responding member.
     *
     * Generated from protobuf field <code>uint64 raftAppliedIndex = 7;</code>
     * @return int|string
     */
    public function getRaftAppliedIndex()
    {
        return $this->raftAppliedIndex;
    }

    /**
     * raftAppliedIndex is the current raft applied index of the responding member.
     *
     * Generated from protobuf field <code>uint64 raftAppliedIndex = 7;</code>
     * @param int|string $var
     * @return $this
     */
    public function setRaftAppliedIndex($var)
    {
        GPBUtil::checkUint64($var);
        $this->raftAppliedIndex = $var;

        return $this;
    }

    /**
     * errors contains alarm/health information and status.
     *
     * Generated from protobuf field <code>repeated string errors = 8;</code>
     * @return \Google\Protobuf\Internal\RepeatedField
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * errors contains alarm/health information and status.
     *
     * Generated from protobuf field <code>repeated string errors = 8;</code>
     * @param string[]|\Google\Protobuf\Internal\RepeatedField $var
     * @return $this
     */
    public function setErrors($var)
    {
        $arr = GPBUtil::checkRepeatedField($var, \Google\Protobuf\Internal\GPBType::STRING);
        $this->errors = $arr;

        return $this;
    }

    /**
     * dbSizeInUse is the size of the backend database logically in use, in bytes, of the responding member.
     *
     * Generated from protobuf field <code>int64 dbSizeInUse = 9;</code>
     * @return int|string
     */
    public function getDbSizeInUse()
    {
        return $this->dbSizeInUse;
    }

    /**
     * dbSizeInUse is the size of the backend database logically in use, in bytes, of the responding member.
     *
     * Generated from protobuf field <code>int64 dbSizeInUse = 9;</code>
     * @param int|string $var
     * @return $this
     */
    public function setDbSizeInUse($var)
    {
        GPBUtil::checkInt64($var);
        $this->dbSizeInUse = $var;

        return $this;
    }

}

