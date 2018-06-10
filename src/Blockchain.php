<?php

namespace flavienbwk\blockchain_php;
use flavienbwk\blockchain_php\Block;

class Blockchain {

    private $_magic = 0xD5E8A97F;
    private $_hashalg = "sha256";
    private $_hashlen = 32;
    private $_blksize = null;

    public function __construct() {
        $this->_blksize = (13 + $this->_hashlen);
    }

    /**
     * Add a block to your blockchain.
     * 
     * @param string The path to the file containing your blockchain.
     * @param string The data you want to put on the next block.
     * @return Block A Block object.
     */
    public function addBlock($file_name, $data) {
        $Block = new Block();

        // The "genesis" block is the first block of your blockchain.
        $genesis = (file_exists($file_name) && filesize($file_name)) ? false : true;
        $indexfn = $file_name . '.idx';
        if (!$genesis) {
            if (!file_exists($file_name))
                return $Block->setErrorMessage('Missing blockchain data file!');
            if (!file_exists($indexfn))
                return $Block->setErrorMessage('Missing blockchain index file!');
            // get disk location of last block from index
            if (!$ix = fopen($indexfn, 'r+b'))
                return $Block->setErrorMessage("Can't open " . $indexfn);

            $maxblock = unpack('V', fread($ix, 4))[1];
            $zpos = (($maxblock * 8) - 4);

            fseek($ix, $zpos, SEEK_SET);
            $ofs = unpack('V', fread($ix, 4))[1];
            $len = unpack('V', fread($ix, 4))[1];
            // read last block and calculate hash
            if (!$bc = fopen($file_name, 'r+b'))
                return $Block->setErrorMessage("Can't open " . $file_name);

            fseek($bc, $ofs, SEEK_SET);
            $block = fread($bc, $len);
            $hash = hash($this->_hashalg, $block);

            // add new block to the end of the chain
            fseek($bc, 0, SEEK_END);
            $pos = ftell($bc);

            $this->write_block($Block, $bc, $data, $hash);
            fclose($bc);

            $this->update_index($Block, $ix, $pos, strlen($data), ($maxblock + 1));
            fclose($ix);
            $Block->setSuccess();
        } else {
            if (file_exists($file_name))
                return $Block->setErrorMessage('Blockchain data file already exists!');
            if (file_exists($indexfn))
                return $Block->setErrorMessage('Blockchain index file already exists!');

            $hash = str_repeat('00', $this->_hashlen);
            $bc = fopen($file_name, 'wb');
            $ix = fopen($indexfn, 'wb');

            $this->write_block($Block, $bc, $data, $hash);
            $this->update_index($Block, $ix, 0, strlen($data), 1);
            fclose($bc);
            fclose($ix);

            $Block->setSuccess();
        }

        return $Block;
    }

    private function write_block(Block &$Block, &$fp, $data, $prevhash) {
        $time = time();
        $version = 1;
        $datalength = pack('V', strlen($data));
        $magic_pack = pack('V', $this->_magic);
        $chr_version = chr($version);
        $time_pack = pack('V', $time);
        $hex2bin_prevhash = hex2bin($prevhash);

        fwrite($fp, $magic_pack, 4);                     // Magic
        fwrite($fp, $chr_version, 1);                    // Version
        fwrite($fp, $time_pack, 4);                      // Timestamp
        fwrite($fp, $hex2bin_prevhash, $this->_hashlen); // Previous Hash
        fwrite($fp, $datalength, 4);                     // Data Length
        fwrite($fp, $data, strlen($data));               // Data


        $header = ($magic_pack . $chr_version . $time_pack . $hex2bin_prevhash . $datalength);
        $Block->setHash(hash($this->_hashalg, $header . $data));
        $Block->setMagic(dechex($this->_magic));
        $Block->setVersion($version);
        $Block->setTimestamp($time);
        $Block->setPrevHash($prevhash);
        $Block->setDataLength(strlen($data));
        $Block->setData($data);

        return $Block;
    }

    private function update_index(Block &$Block, &$fp, $pos, $datalen, $count) {
        $length = ($datalen + $this->_blksize);

        fseek($fp, 0, SEEK_SET);
        fwrite($fp, pack('V', $count), 4);               // Record count
        fseek($fp, 0, SEEK_END);
        fwrite($fp, pack('V', $pos), 4);                 // Offset
        fwrite($fp, pack('V', $length), 4);              // Length

        $Block->setOffset($pos);
        $Block->setOffsetEnd($pos + $length);
        $Block->setPosition($count);

        return $Block;
    }

    /**
     * Walks through your blockchain and returns all the data in a JSON format.
     * Returns false if file was not found.
     * 
     * @param string $file_name The path to the file containing your blockchain.
     * @return string|boolean
     */
    public function getBlockchain($file_name) {
        if (!file_exists($file_name))
            return false;
        $size = filesize($file_name);
        $fp = fopen($file_name, 'rb');

        $height = 0;
        $blockchain = [];
        while (ftell($fp) < $size) {
            $header = fread($fp, $this->_blksize);

            $magic = $this->unpack32($header, 0);
            $version = ord($header[4]);
            $timestamp = $this->unpack32($header, 5);
            $prevhash = bin2hex(substr($header, 9, $this->_hashlen));
            $datalen = $this->unpack32($header, -4);
            $data = fread($fp, $datalen);
            $hash = hash($this->_hashalg, $header . $data);

            array_push($blockchain, [
                "position" => ++$height,
                "header" => ord($header),
                "magic" => dechex($magic),
                "version" => $version,
                "timestamp" => $timestamp,
                "prevhash" => $prevhash,
                "hash" => $hash,
                "datalen" => $datalen,
                "data" => wordwrap($data, 100)
            ]);
        }
        fclose($fp);
        return json_encode($blockchain);
    }

    /**
     * Returns a Block object. Block->hasError() === false if found.
     * 
     * @param type $file_name
     * @param type $hash_search
     * @return type
     */
    public function getBlockByHash($file_name, $hash_search) {
        $Block = new Block();

        if (!file_exists($file_name))
            return $Block->setErrorMessage("Can't open " . $file_name);

        $size = filesize($file_name);
        $fp = fopen($file_name, 'rb');
        $height = 0;
        while (ftell($fp) < $size) {
            $header = fread($fp, $this->_blksize);
            $datalen = $this->unpack32($header, -4);
            $data = fread($fp, $datalen);
            $hash = hash($this->_hashalg, $header . $data);
            $position = ++$height;

            if ($hash == $hash_search) {
                $magic = $this->unpack32($header, 0);
                $version = ord($header[4]);
                $timestamp = $this->unpack32($header, 5);
                $prevhash = bin2hex(substr($header, 9, $this->_hashlen));

                $Block->setSuccess();
                $Block->setPosition($position);
                $Block->setMagic(dechex($magic));
                $Block->setVersion($version);
                $Block->setTimestamp($timestamp);
                $Block->setPrevHash($prevhash);
                $Block->setHash($hash);
                $Block->setDataLength($datalen);
                $Block->setData(wordwrap($data, 100));
                break;
            }
        }
        fclose($fp);

        return $Block;
    }

    /**
     * Returns a Block object. Block->hasError() === false if found.
     * 
     * @param string $file_name The path to the file containing your blockchain.
     * @param string $hash_search SHA256.
     * @return Block A Block object.
     */
    public function getBlockByPrevHash($file_name, $hash_search) {
        $Block = new Block();

        if (!file_exists($file_name))
            return $Block->setErrorMessage("Can't open " . $file_name);

        $size = filesize($file_name);
        $fp = fopen($file_name, 'rb');
        $height = 0;
        while (ftell($fp) < $size) {
            $header = fread($fp, $this->_blksize);
            $datalen = $this->unpack32($header, -4);
            $data = fread($fp, $datalen);
            $position = ++$height;
            $prevhash = bin2hex(substr($header, 9, $this->_hashlen));

            if ($prevhash == $hash_search) {
                $hash = hash($this->_hashalg, $header . $data);
                $magic = $this->unpack32($header, 0);
                $version = ord($header[4]);
                $timestamp = $this->unpack32($header, 5);

                $Block->setSuccess();
                $Block->setPosition($position);
                $Block->setMagic(dechex($magic));
                $Block->setVersion($version);
                $Block->setTimestamp($timestamp);
                $Block->setPrevHash($prevhash);
                $Block->setHash($hash);
                $Block->setDataLength($datalen);
                $Block->setData(wordwrap($data, 100));
                break;
            }
        }
        fclose($fp);

        return $Block;
    }

    private function unpack32($data, $ofs) {
        return unpack('V', substr($data, $ofs, 4))[1];
    }
}
?>
