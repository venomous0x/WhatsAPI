<?php
require 'decode.php';
require 'exception.php';

class IncompleteMessageException extends CustomException
{
    private $input;

    public function __construct($message = null, $code = 0)
    {
        parent::__construct($message, $code);
    }

    public function setInput($input)
    {
        $this->input = $input;
    }

    public function getInput()
    {
        return $this->input;
    }

}

class ProtocolNode
{
    private $tag;
    private $attributeHash;
    private $children;
    private $data;
    private static $cli = null;

    /**
     * check if call is from command line
     * @return bool
     */
    private static function isCli()
    {
        if(self::$cli === null)
        {
            //initial setter
            if(php_sapi_name() == "cli")
            {
                self::$cli = true;
            }
            else
            {
                self::$cli = false;
            }
        }
        return self::$cli;
    }

    /**
     * @return string
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return string
     */
    public function getTag()
    {
        return $this->tag;
    }

    /**
     * @return string[]
     */
    public function getAttributes()
    {
        return $this->attributeHash;
    }

    /**
     * @return ProtocolNode[]
     */
    public function getChildren()
    {
        return $this->children;
    }

    public function __construct($tag, $attributeHash, $children, $data)
    {
        $this->tag = $tag;
        $this->attributeHash = $attributeHash;
        $this->children = $children;
        $this->data = $data;
    }

    /**
     * @param string $indent
     * @param bool $isChild
     * @return string
     */
    public function nodeString($indent = "", $isChild = false)
    {
        //formatters
        $lt = "<";
        $gt = ">";
        $nl = "\n";
        if(!self::isCli())
        {
            $lt = "&lt;";
            $gt = "&gt;";
            $nl = "<br />";
            $indent = str_replace(" ", "&nbsp;", $indent);
        }

        $ret = $indent . $lt . $this->tag;
        if ($this->attributeHash != null) {
            foreach ($this->attributeHash as $key => $value) {
                $ret .= " " . $key . "=\"" . $value . "\"";
            }
        }
        $ret .= $gt;
        if (strlen($this->data) > 0) {
            if (strlen($this->data) <= 1024) {
                //message
                $ret .= $this->data;
            } else {
                //raw data
                $ret .= " " . strlen($this->data) . " byte data";
            }
        }
        if ($this->children) {
            $ret .= $nl;
            $foo = array();
            foreach ($this->children as $child) {
                $foo[] = $child->nodeString($indent . "  ", true);
            }
            $ret .= implode($nl, $foo);
            $ret .= $nl . $indent;
        }
        $ret .=  $lt . "/" . $this->tag . $gt;

        if(!$isChild)
        {
            $ret .= $nl;
            if(!self::isCli())
            {
                $ret .= $nl;
            }
        }

        return $ret;
    }

    /**
     * @param $attribute
     * @return string
     */
    public function getAttribute($attribute)
    {
        $ret = "";
        if (isset($this->attributeHash[$attribute])) {
            $ret = $this->attributeHash[$attribute];
        }

        return $ret;
    }

    /**
     * @param string $needle
     * @return boolean
     */
    public function nodeIdContains($needle)
    {
        return (strpos($this->getAttribute("id"), $needle) !== false);
    }

    //get children supports string tag or int index
    /**
     * @param $tag
     * @return ProtocolNode
     */
    public function getChild($tag)
    {
        $ret = null;
        if ($this->children) {
            if(is_int($tag))
            {
                if(isset($this->children[$tag]))
                {
                    return $this->children[$tag];
                }
                else
                {
                    return null;
                }
            }
            foreach ($this->children as $child) {
                if (strcmp($child->tag, $tag) == 0) {
                    return $child;
                }
                $ret = $child->getChild($tag);
                if ($ret) {
                    return $ret;
                }
            }
        }

        return null;
    }

    /**
     * @param $tag
     * @return bool
     */
    public function hasChild($tag)
    {
        return $this->getChild($tag) == null ? false : true;
    }

    /**
     * @param int $offset
     */
    public function refreshTimes($offset = 0)
    {
        if (isset($this->attributeHash['id'])) {
            $id = $this->attributeHash['id'];
            $parts = explode('-', $id);
            $parts[0] = time() + $offset;
            $this->attributeHash['id'] = implode('-', $parts);
        }
        if (isset($this->attributeHash['t'])) {
            $this->attributeHash['t'] = time();
        }
    }
    
    
    /**
     * Print human readable ProtocolNode object
     *
     * @return string
     */
    public function __toString()
    {
        $readableNode = array(
            'tag'           => $this->tag,
            'attributeHash' => $this->attributeHash,
            'children'      => $this->children,
            'data'          => $this->data
        );

        return print_r( $readableNode, true );
    }

}

class BinTreeNodeReader
{
    private $input;
    /** @var $key KeyStream */
    private $key;

    public function resetKey()
    {
        $this->key = null;
    }

    public function setKey($key)
    {
        $this->key = $key;
    }

    public function nextTree($input = null)
    {
        if ($input != null) {
            $this->input = $input;
        }
        $stanzaFlag = ($this->peekInt8() & 0xF0) >> 4;
        $stanzaSize = $this->peekInt16(1);
        if ($stanzaSize > strlen($this->input)) {
            throw new Exception("Incomplete message $stanzaSize != " . strlen($this->input));
        }
        $this->readInt24();
        if ($stanzaFlag & 8) {
            if (isset($this->key)) {
                $realSize = $stanzaSize - 4;
                $this->input = $this->key->DecodeMessage($this->input, $realSize, 0, $realSize);// . $remainingData;
            } else {
                throw new Exception("Encountered encrypted message, missing key");
            }
        }
        if ($stanzaSize > 0) {
            return $this->nextTreeInternal();
        }

        return null;
    }

    protected function getToken($token)
    {
        $ret = "";
        $subdict = false;
        TokenMap::GetToken($token, $subdict, $ret);
        if(!$ret)
        {
            $token = $this->readInt8();
            TokenMap::GetToken($token, $subdict, $ret);
            if(!$ret)
            {
                throw new Exception("BinTreeNodeReader->getToken: Invalid token $token");
            }
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

            return new ProtocolNode("start", $attributes, null, "");
        } elseif ($token == 2) {
            return null;
        }
        $tag = $this->readString($token);
        $attributes = $this->readAttributes($size);
        if (($size % 2) == 1) {
            return new ProtocolNode($tag, $attributes, null, "");
        }
        $token = $this->readInt8();
        if ($this->isListTag($token)) {
            return new ProtocolNode($tag, $attributes, $this->readList($token), "");
        }

        return new ProtocolNode($tag, $attributes, null, $this->readString($token));
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
        if (strlen($this->input) >= (3 + $offset)) {
            $ret = ord(substr($this->input, $offset, 1)) << 16;
            $ret |= ord(substr($this->input, $offset + 1, 1)) << 8;
            $ret |= ord(substr($this->input, $offset + 2, 1)) << 0;
        }

        return $ret;
    }

    protected function readInt24()
    {
        $ret = $this->peekInt24();
        if (strlen($this->input) >= 3) {
            $this->input = substr($this->input, 3);
        }

        return $ret;
    }

    protected function peekInt16($offset = 0)
    {
        $ret = 0;
        if (strlen($this->input) >= (2 + $offset)) {
            $ret = ord(substr($this->input, $offset, 1)) << 8;
            $ret |= ord(substr($this->input, $offset + 1, 1)) << 0;
        }

        return $ret;
    }

    protected function readInt16()
    {
        $ret = $this->peekInt16();
        if ($ret > 0) {
            $this->input = substr($this->input, 2);
        }

        return $ret;
    }

    protected function peekInt8($offset = 0)
    {
        $ret = 0;
        if (strlen($this->input) >= (1 + $offset)) {
            $sbstr = substr($this->input, $offset, 1);
            $ret = ord($sbstr);
        }

        return $ret;
    }

    protected function readInt8()
    {
        $ret = $this->peekInt8();
        if (strlen($this->input) >= 1) {
            $this->input = substr($this->input, 1);
        }

        return $ret;
    }

    protected function fillArray($len)
    {
        $ret = "";
        if (strlen($this->input) >= $len) {
            $ret = substr($this->input, 0, $len);
            $this->input = substr($this->input, $len);
        }

        return $ret;
    }

}

class BinTreeNodeWriter
{
    private $output;
    /** @var $key KeyStream */
    private $key;

    public function resetKey()
    {
        $this->key = null;
    }

    public function setKey($key)
    {
        $this->key = $key;
    }

    public function StartStream($domain, $resource)
    {
        $attributes = array();
        $header = "WA";
        $header .= $this->writeInt8(1);
        $header .= $this->writeInt8(4);

        $attributes["to"] = $domain;
        $attributes["resource"] = $resource;
        $this->writeListStart(count($attributes) * 2 + 1);

        $this->output .= "\x01";
        $this->writeAttributes($attributes);
        $ret = $header . $this->flushBuffer();

        return $ret;
    }

    /**
     * @param ProtocolNode $node
     * @return string
     */
    public function write($node, $encrypt = true)
    {
        if ($node == null) {
            $this->output .= "\x00";
        } else {
            $this->writeInternal($node);
        }

        return $this->flushBuffer($encrypt);
    }

    /**
     * @param ProtocolNode $node
     */
    protected function writeInternal($node)
    {
        $len = 1;
        if ($node->getAttributes() != null) {
            $len += count($node->getAttributes()) * 2;
        }
        if (count($node->getChildren()) > 0) {
            $len += 1;
        }
        if (strlen($node->getData()) > 0) {
            $len += 1;
        }
        $this->writeListStart($len);
        $this->writeString($node->getTag());
        $this->writeAttributes($node->getAttributes());
        if (strlen($node->getData()) > 0) {
            $this->writeBytes($node->getData());
        }
        if ($node->getChildren()) {
            $this->writeListStart(count($node->getChildren()));
            foreach ($node->getChildren() as $child) {
                $this->writeInternal($child);
            }
        }
    }

    protected function parseInt24($data)
    {
        $ret = ord(substr($data, 0, 1)) << 16;
        $ret |= ord(substr($data, 1, 1)) << 8;
        $ret |= ord(substr($data, 2, 1)) << 0;
        return $ret;
    }

    protected function flushBuffer($encrypt = true)
    {
        $size = strlen($this->output);
        $data = $this->output;
        if($this->key != null && $encrypt)
        {
            $bsize = $this->getInt24($size);
            //encrypt
            $data = $this->key->EncodeMessage($data, $size, 0, $size);
            $len = strlen($data);
            $bsize[0] = chr((8 << 4) | (($len & 16711680) >> 16));
            $bsize[1] = chr(($len & 65280) >> 8);
            $bsize[2] = chr($len & 255);
            $size = $this->parseInt24($bsize);
        }
        $ret = $this->writeInt24($size) . $data;
        $this->output = '';
        return $ret;
    }

    protected function getInt24($length)
    {
        $ret = '';
        $ret .= chr((($length & 0xf0000) >> 16));
        $ret .= chr((($length & 0xff00) >> 8));
        $ret .= chr(($length & 0xff));
        return $ret;
    }

    protected function writeToken($token)
    {
        if ($token < 0xf5) {
            $this->output .= chr($token);
        } elseif ($token <= 0x1f4) {
            $this->output .= "\xfe" . chr($token - 0xf5);
        }
    }

    protected function writeJid($user, $server)
    {
        $this->output .= "\xfa";
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
            $this->output .= "\xfd";
            $this->output .= $this->writeInt24($len);
        } else {
            $this->output .= "\xfc";
            $this->output .= $this->writeInt8($len);
        }
        $this->output .= $bytes;
    }

    protected function writeString($tag)
    {
        $intVal = -1;
        $subdict = false;
        if(TokenMap::TryGetToken($tag, $subdict, $intVal))
        {
            if($subdict)
            {
                $this->writeToken(236);
            }
            $this->writeToken($intVal);
            return;
        }
        $index = strpos($tag, '@');
        if ($index) {
            $server = substr($tag, $index + 1);
            $user = substr($tag, 0, $index);
            $this->writeJid($user, $server);
        } else {
            $this->writeBytes($tag);
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
            $this->output .= "\x00";
        } elseif ($len < 256) {
            $this->output .= "\xf8" . chr($len);
        } else {
            $this->output .= "\xf9" . $this->writeInt16($len);
        }
    }

}
