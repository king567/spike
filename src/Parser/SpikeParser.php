<?php
/**
 * Spike library
 * @author Tao <taosikai@yeah.net>
 */
namespace Spike\Parser;

use Spike\Exception\InvalidArgumentException;

class SpikeParser extends AbstractParser
{
    /**
     * Parse the incoming buffer
     * @return array
     */
    public function parse()
    {
        $messages = [];
        while ($this->incomingData) {
            $message = $this->parseFirst();
            if (is_null($message)) {
                break;
            }
            $messages[] = $message;
        }
        return $messages;
    }

    /**
     * {@inheritdoc}
     */
    public function parseFirst()
    {
        $pos = strpos($this->incomingData, "\r\n\r\n");
        if ($pos === false) {
            return null;
        }
        $header = substr($this->incomingData, 0, $pos);
        if (preg_match("/Content-Length: ?(\d+)/i", $header, $match)) {
            $bodyLength = $match[1];
            //incoming buffer length - header length  - two\r\n
            if (strlen($this->incomingData) - $pos - 4 >= $bodyLength) {
                $body = substr($this->incomingData, $pos + 4, $bodyLength);
            }  else {
                return null;
            }
            $message = $header . "\r\n\r\n" . $body;
        } else {
            throw new InvalidArgumentException('Bad spike message');
        }
        $this->incomingData  = substr($this->incomingData, strlen($message));
        return $message;
    }
}