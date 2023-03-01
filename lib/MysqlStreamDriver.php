<?php
/*
Copyright 2019 Ivo Smits <Ivo@UCIS.nl>. All rights reserved.
This software may be used for personal use. Distribution of the complete
unmodified source code is permitted.

namespace Modules\MySQLStream;
use Exception;
use Modules\MySQL\StreamDriver\Packet;
use Modules\MySQL\StreamDriver\ResultSet;
*/
class MysqlStreamDriver {
  private $stream;

  const CLIENT_PROTOCOL_41 = 0x00000200;
  const CLIENT_PLUGIN_AUTH = 0x00080000;
  const CLIENT_CONNECT_WITH_DB = 0x00000008;

  public $connect_error = NULL, $connect_errno = 0;
  public $affected_rows = 0, $insert_id = 0, $field_count = 0;
  private $sequence = 0;

  public function __construct($stream, $username, $password, $database) {
    stream_set_blocking($stream, TRUE);
    $this->stream = $stream;
    $packet = $this->readPacket();
    if ($packet->getInt1() < 0x0A) throw new Exception('Unsupported protocol version');
    $server_version = $packet->getStringNul();
    $connection_id = $packet->getInt4();
    $packet->getBytes(8);
    $packet->getBytes(1);
    $capabilities = $packet->getInt2();
    if (!$packet->eof()) {
      $packet->getInt1();
      $packet->getInt2();
      $capabilities |= $packet->getInt2() << 16;
    }

    $packet = new Packet();
    $packet->putInt4(self::CLIENT_PROTOCOL_41 | self::CLIENT_PLUGIN_AUTH | self::CLIENT_CONNECT_WITH_DB);
    $packet->putInt4(0xFFFFFF);
    $packet->putInt1(255); //utf8mb4
    $packet->putBytes(str_repeat("\x00", 23));
    $packet->putStringNul($username);
    $packet->putStringNul(''); //$password
    $packet->putStringNul($database);
    $packet->putStringNul(''); //'mysql_clear_password'
    $this->writePacket($packet);

    $packet = $this->readPacket();
    $status = $packet->getInt1();
    if ($status === 0xFF) {
      $this->handleErrorPacket($packet);
    } elseif ($status === 0xFE) {
      $auth_method = $packet->getStringNul();
      $auth_data = $packet->getStringEof();
      if ($auth_method === 'mysql_native_password') {
        $auth_data = substr($auth_data, 0, 20);
        $packet = new Packet();
        $packet->putBytes(sha1($password, TRUE) ^ sha1($auth_data.sha1(sha1($password, TRUE), TRUE), TRUE));
        $this->writePacket($packet);

        $packet = $this->readPacket();
        $status = $packet->getInt1();
        if ($status === 0xFF) {
          $this->handleErrorPacket($packet);
        } elseif ($status === 0x00) {
        } else {
          throw new Exception('Received unexpected packet type '.$status);
        }
      }
    } elseif ($status === 0x00) {
    } else {
      throw new Exception('Received unexpected packet type '.$status);
    }
  }

  public function handleErrorPacket($packet) {
    $code = $packet->getInt2();
    $packet->getBytes(1);
    $packet->getBytes(5);
    $message = $packet->getStringEof();
    throw new Exception('Error code '.$code.': '.$message);
  }

  public function set_charset($charset) {
    if ($charset !== 'utf8mb4') throw new Exception('Unsupported charset, only utf8mb4 is supported');
  }

  public function real_escape_string($string) {
    return strtr($string, ["\0" => '\\0', '\'' => '\\\'', '"' => '\\"', "\b" => '\\b', "\n" => '\\n', "\r" => '\\r', "\t" => '\\t', "\x1A" => '\\Z', '\\' => '\\\\']);
  }

  public function query($query, $resultmode = MYSQLI_STORE_RESULT) {
    $this->sequence = 0;
    $packet = new Packet();
    $packet->putInt1(0x03);
    $packet->putBytes($query);
    $this->writePacket($packet);

    $packet = $this->readPacket();
    $status = $packet->getInt1();
    if ($status === 0xFF) {
      $this->affected_rows = -1;
      $this->handleErrorPacket($packet);
      return FALSE;
    } elseif ($status === 0x00) {
      $this->affected_rows = $packet->getIntVar();
      $this->insert_id = $packet->getIntVar();
      $packet->getInt2();
      $packet->getInt2();
      return TRUE;
    }
    $packet->offset--;
    $this->field_count = $packet->getIntVar();
    $buffered = $resultmode !== MYSQLI_USE_RESULT;
    return new ResultSet($this, $this->field_count, $resultmode !== MYSQLI_USE_RESULT);
  }

  private function readBytes($length) {
    $data = fread($this->stream, $length);
    if ($data === FALSE || strlen($data) === 0) throw new Exception('End of stream');
    while (strlen($data) < $length) {
      $newdata = fread($this->stream, $length - strlen($data));
      if ($newdata === FALSE || strlen($newdata) === 0) throw new Exception('End of stream');
      $data .= $newdata;
    }
    return $data;
  }

  public function readPacket() {
    $data = $this->readBytes(4);
    $length = ord($data[0]) | (ord($data[1]) << 8) | (ord($data[2]) << 16);
    if ($length >= 0xFFFFFF) throw new Exception('Packet too big');
    $sequence = ord($data[3]);
    if ($sequence !== ($this->sequence & 0xFF)) throw new Exception('Out of sequence acket received');
    $this->sequence++;
    $data = $length ? $this->ReadBytes($length) : '';
    return new Packet($sequence, $data);
  }

  private function writePacket($packet) {
    $packet->sequence = $this->sequence++;
    $length = strlen($packet->data);
    if ($length > 0xFFFFFF) throw new Exception('Packet too big');
    fwrite($this->stream, chr($length & 0xFF).chr(($length >> 8) & 0xFF).chr(($length >> 16) & 0xFF).chr($packet->sequence));
    fwrite($this->stream, $packet->data);
  }
}

//namespace Framework\Modules\MySQL\StreamDriver;

class Packet {
  public $sequence;
  public $data;
  public $offset = 0;

  public function __construct($sequence = 0, $data = '') {
    $this->sequence = $sequence;
    $this->data = $data;
  }

  public function getInt1() {
    return ord($this->data[$this->offset++]);
  }
  public function getInt2() {
    return ord($this->data[$this->offset++]) | (ord($this->data[$this->offset++]) << 8);
  }
  public function getInt3() {
    return ord($this->data[$this->offset++]) | (ord($this->data[$this->offset++]) << 8) | (ord($this->data[$this->offset++]) << 16);
  }
  public function getInt4() {
    return ord($this->data[$this->offset++]) | (ord($this->data[$this->offset++]) << 8) | (ord($this->data[$this->offset++]) << 16) | (ord($this->data[$this->offset++]) << 24);
  }
  public function getIntVar() {
    $value = ord($this->data[$this->offset++]);
    if ($value < 0xFB) return $value;
    if ($value === 0xFC) return $this->getInt2();
    if ($value === 0xFD) return $this->getInt3();
    if ($value === 0xFE) return $this->getInt4() + ($this->getInt4() << 32);
    throw new Exception('Invalid variable length integer encoding');
  }
  public function getBytes($length) {
    $this->offset += $length;
    return substr($this->data, $this->offset - $length, $length);
  }
  public function eof() {
    return $this->offset < strlen($this->data);
  }
  public function getStringNul() {
    $end = strpos($this->data, "\0", $this->offset);
    if ($end === FALSE) throw new Exception('End of string marker not found');
    $ret = substr($this->data, $this->offset, $end - $this->offset);
    $this->offset = $end + 1;
    return $ret;
  }
  public function getStringVar() {
    return $this->getBytes($this->getIntVar());
  }
  public function getStringEof() {
    $ret = substr($this->data, $this->offset);
    $this->offset = strlen($this->data);
    return $ret;
  }

  public function putInt1($value) {
    $this->data .= chr($value);
  }
  public function putInt2($value) {
    $this->data .= chr($value & 0xFF).chr(($value >> 8) & 0xFF);
  }
  public function putInt3($value) {
    $this->data .= chr($value & 0xFF).chr(($value >> 8) & 0xFF).chr(($value >> 16) & 0xFF);
  }
  public function putInt4($value) {
    $this->data .= chr($value & 0xFF).chr(($value >> 8) & 0xFF).chr(($value >> 16) & 0xFF).chr(($value >> 24) & 0xFF);
  }
  public function putBytes($data) {
    $this->data .= $data;
  }
  public function putStringNul($data) {
    $this->data .= $data."\0";
  }
}

class ResultSet {
  private $driver;
  public $field_count;
  private $field_names;
  private $buffered_rows = NULL;
  private $eof = FALSE;
  public $num_rows = 0;
  public function __construct($driver, $field_count, $buffered) {
    $this->driver = $driver;
    $this->field_count = $field_count;
    $this->field_names = array();
    for ($i = 0; $i < $field_count; $i++) {
      $packet = $driver->readPacket();
      $packet->getStringVar();
      $packet->getStringVar();
      $packet->getStringVar();
      $packet->getStringVar();
      $this->field_names[] = $packet->getStringVar();
    }
    $packet = $driver->readPacket();
    $status = $packet->getInt1();
    if ($status !== 0xFE) throw new Exception('Unexpected packet');
    if ($buffered) {
      $this->buffered_rows = array();
      while (($row = $this->fetchRowInternal()) !== NULL) $this->buffered_rows[] = $row;
      $this->num_rows = count($this->buffered_rows);
      reset($this->buffered_rows);
      $this->driver = NULL;
    }
  }
  private function fetchRowInternal() {
    if ($this->eof) return NULL;
    $packet = $this->driver->readPacket();
    $status = $packet->getInt1();
    if ($status === 0xFE && strlen($packet->data) < 9) {
      $this->eof = TRUE;
      return NULL;
    } elseif ($status === 0xFF) {
      $this->driver->handleErrorPacket($packet);
      return NULL;
    }
    $packet->offset--;
    $row = array();
    foreach ($this->field_names as $key) {
      $status = $packet->getInt1();
      if ($status === 0xFB) {
        $row[$key] = NULL;
      } else {
        $packet->offset--;
        $row[$key] = $packet->getStringVar();
      }
    }
    return $row;
  }
  public function fetch_assoc() {
    if ($this->buffered_rows) {
      $row = current($this->buffered_rows);
      next($this->buffered_rows);
      return $row === FALSE ? NULL : $row;
    }
    return $this->fetchRowInternal();
  }
  public function fetch_object($class_name = 'stdClass', $params = array()) {
    $row = $this->Fetch_assoc();
    if ($row === NULL) return NULL;
    if ($class_name === 'stdClass') return (object)$row;
    $class = new \ReflectionClass($class_name);
    $object = $class->newInstanceWithoutConstructor();
    foreach ($row as $key => $value) {
      if ($class->hasProperty($key)) {
        $prop = $class->getProperty($key);
        $prop->setAccessible(true);
        $prop->setValue($object, $value);
      } else {
        $object->$key = $value;
      }
    }
    $class->getConstructor()->invokeArgs($object, $params);
    return $object;
  }
  public function close() {
    while (!$this->eof) $this->fetch_assoc();
  }
}
