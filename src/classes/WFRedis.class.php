<?php 

/**
 * @class WFRedis
 * @brief Helper class for interacting with Redis.
 *
 * Acts as a helper class for interacting with Redis, providing a nicer way of doing things than with raw php-redis,
 * as well as providing a way to map to a specific database by name.
 * @author Benjamin Clarke
*/

class WFRedis {
    private $redis;
    public $type;
    private $connected;

    public function __construct(string $type) {
        try {
           $this->connectToRedis($type);
        } catch(Exception $e) {
           return false;
        }
        if ($this->type) {
            return true;
        } else {
            return false;
        }
    }

    private function connectToRedis(string $type) {
        /**
         * Adding a new database is as easy as adding an entry in the table below. 
         * Simply add a key and a value, where the value is one more than the previous
         * value. Start at 0. 
         * 
         * Caveat: If we need more than 16 databases, this will need to be modified to account
         * for multiple pools. This won't be hard to do. 
         * 
         * By default, connects to localhost. Can be changed to connect to a pool or elsewhere
         * without issues, and also supports authentication.
         */ 
        $typeMap = array(
            'sessions' => 0,
            'notes' => 1,
            'note_counts' => 2,
            'session_map' => 3,
            'csrf_tokens' => 4,
            'poll_results' => 5,
            'queues' => 6,
            'login_failures' => 7
        );
        if (!array_key_exists($type, $typeMap)) {
            throw new Exception("This type is not mapped to a valid database.");
        } else {
            $this->redis = new Redis();
            $this->connected = $this->redis->connect('127.0.0.1');
            if ($this->connected) {
                $this->redis->select($typeMap[$type]);
                $this->type = $type;
                return true;
            } else {
                return false;
            }
        }
    }

    public function increment(string $key) {
        return $this->redis->incr($key);
    }

    public function incrementBy(string $key, int $value) {
        return $this->redis->incrBy($key, $value);
    }

    public function incrementByFloat(string $key, float $value) {
        return $this->redis->incrByFloat($key, $value);
    }

    public function decrement(string $key) {
        return $this->redis->decr($key);
    }

    public function decrementBy(string $key, int $value) {
        return $this->redis->decrBy($key);
    }

    public function expireAt(string $key, int $time) {
        // Expires a key at a given timestamp. $time should be a unix timestamp.
        return $this->redis->expireAt($key, $time);
    }

    public function expireIn(string $key, int $time) {
        // Expires a key in $time seconds. 
        return $this->redis->expire($key, $time);
    }

    public function hSet(string $key, string $hashKey, $value) {
        // We'll be nice and do the typing shit on value in here since they need
        // to be strings, but we'll probably have integers passed a lot by accident.
        $value = strval($value);
        return $this->redis->hSet($key, $hashKey, $value);
    }

    public function set(string $key, $value) {
        $value = strval($value);
        return $this->redis->set($key,  $value);
    }

    public function del(string $key) {
        return $this->redis->del($key);
    }

    public function get(string $key) {
        return $this->redis->get($key);
    }

    public function hGet(string $key) {
        // Diverges from standard commands. This'll return the WHOLE key
        // rather than one value because we're bastards.
        return $this->redis->hGetAll($key);
    }

    public function hDel(string $key) {
        // Deletes a key.
        return $this->redis->hDel($key);
    }

    public function lPush(string $key, $value) {
        // Puts something at the START of a list. Complements rPop.
        return $this->redis->lPush($key, $value);
    }

    public function rPop(string $key) {
        // Returns something from the END of a list. 
        return $this->redis->rPop($key);
    }
}