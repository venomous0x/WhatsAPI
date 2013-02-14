<?php
require 'decode.php';
require 'exception.php';

class IncompleteMessageException extends CustomException
{
    private $_input;
    public function __construct($message = NULL, $code = 0)
    {
        parent::__construct($message, $code);
    }
    public function setInput($input)
    {
        $this->_input = $input;
    }
    public function getInput()
    {
        return $this->_input;
    }
}

class ProtocolNode
{
    public $_tag;
    public $_attributeHash;
    public $_children;
    public $_data;

    public function __construct($tag, $attributeHash, $children, $data)
    {
        $this->_tag = $tag;
        $this->_attributeHash = $attributeHash;
        $this->_children = $children;
        $this->_data = $data;
    }

    public function NodeString($indent = "")
    {
        $ret = "\n" . $indent . "<" . $this->_tag;
        if ($this->_attributeHash != NULL) {
            foreach ($this->_attributeHash as $key => $value) {
                $ret .= " " . $key . "=\"" . $value . "\"";
            }
        }
        $ret .= ">";
        if (strlen($this->_data) > 0) {
            $ret .= $this->_data;
        }
        if ($this->_children) {
            foreach ($this->_children as $child) {
                $ret .= $child->NodeString($indent . "  ");
            }
            $ret .= "\n" . $indent;
        }
        $ret .= "</" . $this->_tag . ">";

        return $ret;
    }

    public function getAttribute($attribute)
    {
        $ret = "";
        if (isset($this->_attributeHash[$attribute])) {
            $ret = $this->_attributeHash[$attribute];
        }

        return $ret;
    }

    public function getChild($tag)
    {
        $ret = NULL;
        if ($this->_children) {
            foreach ($this->_children as $child) {
                if (strcmp($child->_tag, $tag) == 0) {
                    return $child;
                }
                $ret = $child->getChild($tag);
                if ($ret) {
                    return $ret;
                }
            }
        }

        return NULL;
    }

    public function hasChild($tag)
    {
        return $this->getChild($tag) == NULL ? FALSE : TRUE;
    }

    public function refreshTimes($offset=0)
    {
        if (isset($this->_attributeHash['id'])) {
            $id = $this->_attributeHash['id'];
            $parts = explode('-', $id);
            $parts[0] = time() + $offset;
            $this->_attributeHash['id'] = implode('-',$parts);
        }
        if (isset($this->_attributeHash['t'])) {
            $this->_attributeHash['t'] = time();
        }
    }
}

class BinTreeNodeReader
{
    private $_dictionary;
    private $_input;
    private $_key;

    public function __construct($dictionary)
    {
        $this->_dictionary = $dictionary;
    }

    public function setKey($key)
    {
        $this->_key = $key;
    }

    public function nextTree($input = NULL)
    {
        if ($input != NULL) {
            $this->_input = $input;
        }
        $stanzaFlag = ($this->peekInt8() & 0xF0) >> 4;
        $stanzaSize = $this->peekInt16(1);
        if ($stanzaSize > strlen($this->_input)) {
            $exception = new IncompleteMessageException("Incomplete message");
            $exception->setInput($this->_input);
            throw $exception;
        }
        $this->readInt24();
        if (($stanzaFlag & 8) && isset($this->_key)) {
            $remainingData = substr($this->_input, $stanzaSize);
            $this->_input = $this->_key->decode($this->_input, 0, $stanzaSize) . $remainingData;
        }
        if ($stanzaSize > 0) {
            return $this->nextTreeInternal();
        }

        return NULL;
    }

    protected function getToken($token)
    {
        $ret = "";
        if (($token >= 0) && ($token < count($this->_dictionary))) {
            $ret = $this->_dictionary[$token];
        } else {
            throw new Exception("BinTreeNodeReader->getToken: Invalid token $token");
        }

        return $ret;
    }

    protected function readString($token)
    {
        $ret = "";
        if ($token == -1) {
            throw new Exception("BinTreeNodeReader->readString: Invalid token $token");
        }
        if (($token > 4) && ($token < 0xf5)) {
            $ret = $this->getToken($token);
        } elseif ($token == 0) {
            $ret = "";
        } elseif ($token == 0xfc) {
            $size = $this->readInt8();
            $ret = $this->fillArray($size);
        } elseif ($token == 0xfd) {
            $size = $this->readInt24();
            $ret = $this->fillArray($size);
        } elseif ($token == 0xfe) {
            $token = $this->readInt8();
            $ret = $this->getToken($token + 0xf5);
        } elseif ($token == 0xfa) {
            $user = $this->readString($this->readInt8());
            $server = $this->readString($this->readInt8());
            if ((strlen($user) > 0) && (strlen($server) > 0)) {
                $ret = $user . "@" . $server;
            } elseif (strlen($server) > 0) {
                $ret = $server;
            }
        }

        return $ret;
    }

    protected function readAttributes($size)
    {
        $attributes = array();
        $attribCount = ($size - 2 + $size % 2) / 2;
        for ($i = 0; $i < $attribCount; $i++) {
            $key = $this->readString($this->readInt8());
            $value = $this->readString($this->readInt8());
            $attributes[$key] = $value;
        }

        return $attributes;
    }

    protected function nextTreeInternal()
    {
        $token = $this->readInt8();
        $size = $this->readListSize($token);
        $token = $this->readInt8();
        if ($token == 1) {
            $attributes = $this->readAttributes($size);

            return new ProtocolNode("start", $attributes, NULL, "");
        } elseif ($token == 2) {
            return NULL;
        }
        $tag = $this->readString($token);
        $attributes = $this->readAttributes($size);
        if (($size % 2) == 1) {
            return new ProtocolNode($tag, $attributes, NULL, "");
        }
        $token = $this->readInt8();
        if ($this->isListTag($token)) {
            return new ProtocolNode($tag, $attributes, $this->readList($token), "");
        }

        return new ProtocolNode($tag, $attributes, NULL, $this->readString($token));
    }

    protected function isListTag($token)
    {
        return (($token == 248) || ($token == 0) || ($token == 249));
    }

    protected function readList($token)
    {
        $size = $this->readListSize($token);
        $ret = array();
        for ($i = 0; $i < $size; $i++) {
            array_push($ret, $this->nextTreeInternal());
        }

        return $ret;
    }

    protected function readListSize($token)
    {
        $size = 0;
        if ($token == 0xf8) {
            $size = $this->readInt8();
        } elseif ($token == 0xf9) {
            $size = $this->readInt16();
        } else {
            throw new Exception("BinTreeNodeReader->readListSize: Invalid token $token");
        }

        return $size;
    }

    protected function peekInt24($offset = 0)
    {
        $ret = 0;
        if (strlen($this->_input) >= (3 + $offset)) {
            $ret  = ord(substr($this->_input, $offset, 1)) << 16;
            $ret |= ord(substr($this->_input, $offset + 1, 1)) << 8;
            $ret |= ord(substr($this->_input, $offset + 2, 1)) << 0;
        }

        return $ret;
    }

    protected function readInt24()
    {
        $ret = $this->peekInt24();
        if (strlen($this->_input) >= 3) {
            $this->_input = substr($this->_input, 3);
        }

        return $ret;
    }

    protected function peekInt16($offset = 0)
    {
        $ret = 0;
        if (strlen($this->_input) >= (2 + $offset)) {
            $ret  = ord(substr($this->_input, $offset, 1)) << 8;
            $ret |= ord(substr($this->_input, $offset + 1, 1)) << 0;
        }

        return $ret;
    }

    protected function readInt16()
    {
        $ret = $this->peekInt16();
        if ($ret > 0) {
            $this->_input = substr($this->_input, 2);
        }

        return $ret;
    }

    protected function peekInt8($offset = 0)
    {
        $ret = 0;
        if (strlen($this->_input) >= (1 + $offset)) {
            $sbstr = substr($this->_input, $offset, 1);
            $ret = ord($sbstr);
        }

        return $ret;
    }

    protected function readInt8()
    {
        $ret = $this->peekInt8();
        if (strlen($this->_input) >= 1) {
            $this->_input = substr($this->_input, 1);
        }

        return $ret;
    }

    protected function fillArray($len)
    {
        $ret = "";
        if (strlen($this->_input) >= $len) {
            $ret = substr($this->_input, 0, $len);
            $this->_input = substr($this->_input, $len);
        }

        return $ret;
    }
}

class BinTreeNodeWriter
{
    private $_output;
    private $_tokenMap = array();
    private $_key;

    public function __construct($dictionary)
    {
        for ($i = 0; $i < count($dictionary); $i++) {
            if (strlen($dictionary[$i]) > 0) {
                $this->_tokenMap[$dictionary[$i]] = $i;
            }
        }
    }

    public function setKey($key)
    {
        $this->_key = $key;
    }

    public function StartStream($domain, $resource)
    {
        $attributes = array();
        $header = "WA";
        $header .= $this->writeInt8(1);
        $header .= $this->writeInt8(2);

        $attributes["to"] = $domain;
        $attributes["resource"] = $resource;
        $this->writeListStart(count($attributes) * 2 + 1);

        $this->_output .= "\x01";
        $this->writeAttributes($attributes);
        $ret = $header.$this->flushBuffer();

        return $ret;
    }

    public function write($node)
    {
        if ($node == NULL) {
            $this->_output .= "\x00";
        } else {
            $this->writeInternal($node);
        }

        return $this->flushBuffer();
    }

    protected function writeInternal($node)
    {
        $len = 1;
        if ($node->_attributeHash != NULL) {
            $len += count($node->_attributeHash) * 2;
        }
        if (count($node->_children) > 0) {
            $len += 1;
        }
        if (strlen($node->_data) > 0) {
            $len += 1;
        }
        $this->writeListStart($len);
        $this->writeString($node->_tag);
        $this->writeAttributes($node->_attributeHash);
        if (strlen($node->_data) > 0) {
            $this->writeBytes($node->_data);
        }
        if ($node->_children) {
            $this->writeListStart(count($node->_children));
            foreach ($node->_children as $child) {
                $this->writeInternal($child);
            }
        }
    }

    protected function flushBuffer()
    {
        $data = (isset($this->_key)) ? $this->_key->encode($this->_output, 0, strlen($this->_output)) : $this->_output;
        $size = strlen($data);
        $ret  = $this->writeInt8(isset($this->_key) ? (1 << 4) : 0);
        $ret .= $this->writeInt16($size);
        $ret .= $data;
        $this->_output = "";

        return $ret;
    }

    protected function writeToken($token)
    {
        if ($token < 0xf5) {
            $this->_output .= chr($token);
        } elseif ($token <= 0x1f4) {
            $this->_output .= "\xfe" . chr($token - 0xf5);
        }
    }

    protected function writeJid($user, $server)
    {
        $this->_output .= "\xfa";
        if (strlen($user) > 0) {
            $this->writeString($user);
        } else {
            $this->writeToken(0);
        }
        $this->writeString($server);
    }

    protected function writeInt8($v)
    {
        $ret = chr($v & 0xff);

        return $ret;
    }

    protected function writeInt16($v)
    {
        $ret = chr(($v & 0xff00) >> 8);
        $ret .= chr(($v & 0x00ff) >> 0);

        return $ret;
    }

    protected function writeInt24($v)
    {
        $ret = chr(($v & 0xff0000) >> 16);
        $ret .= chr(($v & 0x00ff00) >> 8);
        $ret .= chr(($v & 0x0000ff) >> 0);

        return $ret;
    }

    protected function writeBytes($bytes)
    {
        $len = strlen($bytes);
        if ($len >= 0x100) {
            $this->_output .= "\xfd";
            $this->_output .= $this->writeInt24($len);
        } else {
            $this->_output .= "\xfc";
            $this->_output .= $this->writeInt8($len);
        }
        $this->_output .= $bytes;
    }

    protected function writeString($tag)
    {
        if (isset($this->_tokenMap[$tag])) {
            $key = $this->_tokenMap[$tag];
            $this->writeToken($key);
        } else {
            $index = strpos($tag, '@');
            if ($index) {
                $server = substr($tag, $index + 1);
                $user = substr($tag, 0, $index);
                $this->writeJid($user, $server);
            } else {
                $this->writeBytes($tag);
            }
        }
    }

    protected function writeAttributes($attributes)
    {
        if ($attributes) {
            foreach ($attributes as $key => $value) {
                $this->writeString($key);
                $this->writeString($value);
            }
        }
    }

    protected function writeListStart($len)
    {
        if ($len == 0) {
            $this->_output .= "\x00";
        } elseif ($len < 256) {
            $this->_output .= "\xf8" . chr($len);
        } else {
            $this->_output .= "\xf9" . chr($len);
        }
    }
}
