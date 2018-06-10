<?php

namespace flavienbwk\blockchain_php;

class Block {

    private $_error = true; // Has correctly initialized ?
    private $_message = false; // Message (generally, an error message).
    private $_offset = null;
    private $_offset_end = null;
    private $_position = null;
    private $_magic = null;
    private $_version = null;
    private $_timestamp = null;
    private $_prevhash = null;
    private $_hash = null;
    private $_datalen = null;
    private $_data = null;

    /**
     * Returns a JSON array of the block.
     * Block must be initialized with setters before.
     * 
     * @return string
     */
    public function getJson() {
        return json_encode([
            "position" => $this->getPosition(),
            "offset" => $this->getOffset(),
            "offset_end" => $this->getOffsetEnd(),
            "magic" => $this->getMagic(),
            "version" => $this->getVersion(),
            "timestamp" => $this->getTimestamp(),
            "prevhash" => $this->getPrevHash(),
            "hash" => $this->getHash(),
            "datalen" => $this->getDataLength(),
            "data" => $this->getData()
        ]);
    }

    /*
     * Setters.
     */

    public function setError($error = true) {
        $this->_error = (bool) $error;
        return $this;
    }

    public function setSuccess($error = false) {
        $this->_error = (bool) $error;
        return $this;
    }

    public function setErrorMessage($message) {
        $this->_error = true;
        $this->_message = $message;
        return $this;
    }

    public function setMessage($message) {
        $this->_message = $message;
        return $this;
    }

    public function setOffset($offset) {
        if (is_numeric($offset) && intval($offset) >= 0)
            $this->_offset = $offset;
        else {
            $this->setErrorMessage("[setter] Invalid offset set. Must be numeric and >= 0.");
            return false;
        }
        return $this;
    }

    public function setOffsetEnd($offset) {
        if (is_numeric($offset) && intval($offset) >= 0)
            $this->_offset_end = $offset;
        else {
            $this->setErrorMessage("[setter] Invalid offset set. Must be numeric and >= 0.");
            return false;
        }
        return $this;
    }

    public function setPosition($position) {
        if (is_numeric($position) && intval($position) > 0)
            $this->_position = $position;
        else {
            $this->setErrorMessage("[setter] Invalid position set. Must be numeric and > 0.");
            return false;
        }
        return $this;
    }

    public function setMagic($magic) {
        $this->_magic = $magic;
        return $this;
    }

    public function setVersion($version) {
        $this->_version = $version;
        return $this;
    }

    public function setTimestamp($timestamp) {
        $this->_timestamp = $timestamp;
        return $this;
    }

    public function setPrevHash($prevhash) {
        $this->_prevhash = $prevhash;
        return $this;
    }

    public function setHash($hash) {
        $this->_hash = $hash;
        return $this;
    }

    public function setDataLength($datalen) {
        $this->_datalen = $datalen;
        return $this;
    }

    public function setData($data) {
        $this->_data = $data;
        return $this;
    }

    /*
     * Getters.
     */

    public function hasError() {
        return $this->_error;
    }

    public function getMessage() {
        return $this->_message;
    }

    public function getOffset() {
        return $this->_offset;
    }

    public function getOffsetEnd() {
        return $this->_offset_end;
    }

    public function getPosition() {
        return $this->_position;
    }

    public function getMagic() {
        return $this->_magic;
    }

    public function getVersion() {
        return $this->_version;
    }

    public function getTimestamp() {
        return $this->_timestamp;
    }

    public function getPrevHash() {
        return $this->_prevhash;
    }

    public function getHash() {
        return $this->_hash;
    }

    public function getDataLength() {
        return $this->_datalen;
    }

    public function getData() {
        return $this->_data;
    }

}

?>
