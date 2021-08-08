<?php 
/**
 * @class WFUtils
 * @brief A class for providing miscellaneous functions.
 *
 * Basic set of static functions that do useful things. 
 * @author Benjamin Clarke
*/

class WFUtils {
	/** @var string[] RESTRICTED_BLOG_NAMES */
	const RESTRICTED_BLOG_NAMES = [
		"staff",
		"mail",
		"api",
		"analytics",
		"commissions",
		"waterfall",
		"glacier",
		"security",
		"www",
		"testing",
		"support",
		"assets",
		"media",
		"cdn",
		"labs",
		"developers",
		"dev",
		"status",
		"internal",
		"internals",
		"waterfall",
		"theoverseerproject",
		"departmentofapprovals",
		"unidentified-blog",
	];

    // All functions should be public static.

    public static function detectMobile() {
        /** Detects whether a device is a tablet or mobile device for forcing themes.
        *
        * @return true The device is a tablet or mobile.
        * @return false The device is a desktop or laptop.
        */
        $detect = new Mobile_Detect;
        if ( $detect->isMobile() ) {
          return true;
        } else {
          return false;
        }
      }

    public static function getTimezoneOffset($remote_tz, $origin_tz = null) {
        if($origin_tz === null) {
            if(!is_string($origin_tz = date_default_timezone_get())) {
                return false; // A UTC timestamp was returned -- bail out!
            }
        }
        $origin_dtz = new DateTimeZone($origin_tz);
        $remote_dtz = new DateTimeZone($remote_tz);
        $origin_dt = new DateTime("now", $origin_dtz);
        $remote_dt = new DateTime("now", $remote_dtz);
        $offset = $origin_dtz->getOffset($origin_dt) - $remote_dtz->getOffset($remote_dt);
        return $offset;
    }

    public static function getReportCount($type = 'pending') {
        $database = Postgres::getInstance();
        if ($type == 'pending') {
            $values = array(0);
        } else {
            $values = array(1);
        }
        $results = $database->db_count("SELECT * FROM reports WHERE finished = $1", $values);
        return $results;
    }

    public static function generateFingerprint() {
        /** Generates a UUID. Remains for legacy reasons, old code in comments. */
        $uuid = Ramsey\Uuid\Uuid::uuid4();
        return $uuid->toString();
        /** $data = random_bytes(16);
        * $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        * $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
        * return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4)); 
        */
    }

    public static function generateRandomString($length = 32) {
        /** Generates a random string for verification keys and such.
        *
        * @param length Length of string to generate, defaults to 32.
        */
          $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
          $charactersLength = strlen($characters);
          $randomString = '';
          for ($i = 0; $i < $length; $i++) {
              $randomString .= $characters[rand(0, $charactersLength - 1)];
          }
          return $randomString;
      }

    public static function generateSessionID() {
        /** Generates session ID. 
         * 
         */
        return bin2hex(random_bytes(32));
    }

    public static function makeTagsSafe($tags) {
        /** Makes tags safe for the database.
        *
        * @param tags The tags to make safe.
        * @return result The safe tags.
        */
        $result = $tags;
        $result = str_replace(', ', ',', $result);
        $result = preg_replace('/\bon\w+=\S+(?=.*>)/', '', $result);
        $result = str_replace(' ,', ',', $result);
        $result = str_replace(',,', ',', $result);
        $result = str_replace('<', '&lt;', $result);
        $result = str_replace('>', '&gt;', $result);
        //$result = str_replace(';', '\;', $result);
        $result = rtrim($result,',');
        $result = ltrim($result);
        $result = ltrim($result, ',');
        return $result;
      }
    

    public static function generateBase32Key($secretLength = 16) {
        $validChars = array(
                 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', //  7
                 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', // 15
                 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', // 23
                 'Y', 'Z', '2', '3', '4', '5', '6', '7', // 31
                 '=',  // padding char
             );
             // Valid secret lengths are 80 to 640 bits
        if ($secretLength < 16 || $secretLength > 128) {
            throw new Exception('Bad secret length');
        }
        $secret = '';
        $rnd = false;
        if (function_exists('random_bytes')) {
            $rnd = random_bytes($secretLength);
        } elseif (function_exists('mcrypt_create_iv')) {
            $rnd = mcrypt_create_iv($secretLength, MCRYPT_DEV_URANDOM);
        } elseif (function_exists('openssl_random_pseudo_bytes')) {
            $rnd = openssl_random_pseudo_bytes($secretLength, $cryptoStrong);
            if (!$cryptoStrong) {
                $rnd = false;
            }
        }
        if ($rnd !== false) {
            for ($i = 0; $i < $secretLength; ++$i) {
                $secret .= $validChars[ord($rnd[$i]) & 31];
            }
        } else {
            throw new Exception('No source of secure random');
        }
        return $secret;
    } 

    public static function checkPermission($permission, int $bitmask) {
        if ($bitmask & $permission) {
            return true;
          } else {
            return false;
          }
    }

	public static function blogNameCheck(string $blogName) {
		/**
		 * Checks whether a blog name is taken or not. Runs through urlFixer().
		 *
		 * @param blogName The blog name
		 * @return bool Whether the name can be used
		 */

		// Sanitize the blog name
		$blogName = WFUtils::urlFixer($blogName);
		$blogName = strtolower($blogName);

		// Bail early if under three characters
		if (strlen($blogName) < 3) {
			return false;
		}

		// Bail early if it's a restricted name
		if (in_array($blogName, self::RESTRICTED_BLOG_NAMES)) {
			return false;
		}

		// Check the database for a blog with this name
		$database = Postgres::getInstance();
		$query = "SELECT * FROM blogs WHERE blog_name = $1;";
		$blogRow = $database->db_select($query, [$blogName]);
		return !$blogRow;
	}

    public static function withinPercent($percentage, $desiredValue, $valueToCheck) {
        /** Checks whether a number is within a percentage range.
         * @param percentage An integer or float value that describes the maximum permitted deviation in percent.
         * @param desiredValue The desired value to be close to.
         * @param valueToCheck The number we're checking.
         */
        if (!is_int($percentage) || !is_float($percentage)) {
            return null;
        } else {
            $dividedValue = $percentage / 100;
            $negativeMultiplier = 1 - $dividedValue;
            $positiveMultiplier = 1 + $dividedValue;
            $positiveValue = $desiredValue * $positiveMultiplier;
            $negativeValue = $desiredValue * $negativeMultiplier;
            if ($valueToCheck < $positiveValue && $valueToCheck > $negativeValue) {
                return true;
            } else { 
                return false;
            }
        }
    }

    public static function selectFeaturedPost() {
        $database = Postgres::getInstance();
        $res = $database->db_select("SELECT * FROM featured_posts ORDER BY RANDOM() LIMIT 1", array());
        if ($res) {
            return $res[0]['post_id'];
        }
    }

    public static function emailCheck($email) {
        /** Checks whether an email is taken or not.
        *
        * @param email The email address.
        * @return true if the name can be useD
        * @return false if it's taken.
        */
        $values = array($email);
        $query = "SELECT * FROM users WHERE lower(email) = lower($1);";
        $database = Postgres::getInstance();
        $emailRow = $database->db_select($query, $values);
        if ($emailRow) {
            return false;
        } else {
            return true;
        }  
    }

    public static function urlFixer($postBlogName) {
        /** Fixes a blog URL to a usable format.
        *
        * @param postBlogName A proposed blogname in the form of a string.
        * @return postBlogName The fixed blogname.
        */
        $postBlogName = strtolower($postBlogName);
          $postBlogName = preg_replace( "/[^0-9a-z-]/", '', $postBlogName);
        $postBlogName = preg_replace("/(^-+)/", '', $postBlogName);
        $postBlogName = preg_replace("/(-+$)/", '', $postBlogName);
        $postBlogName = substr($postBlogName,0,30);
          return $postBlogName;
    }
    



    public static function textContentCheck($content) {
        /** Whether or not we add to the reblog chain table. Checks whether
        * anything was added to the post. If false, nothing added. If true, stuff was added.7
        *
        * @param content The content of a post to check.
        * @return true Yes.
        * @return false No.
        */
        $content = strip_tags($content, '<img>');
        if (empty($content) || $content == '' || $content == NULL) {
            return false;
        } else {
            return true;
        }
    }

    public static function pickServer() {
        $database = Postgres::getInstance();
        $values = array('content');
        $results = $database->db_select("SELECT * FROM raven_servers WHERE server_role = $1", $values);
        $result = $results[array_rand($results)];
        $serverURL = 'http://'.$result['server_ip'].':'.$result['server_port'];
        return $serverURL;
    }
    
    public static function get_http_response_code($url) {
        $headers = get_headers($url);
        return substr($headers[0], 9, 3);
    }

    public static function doArtTheftCheck($array) {
        // Array is a collection of MD5s
        $database = Postgres::getInstance();
        $newArray = array();
        foreach ($array as $item) {
            $newArray = "\'$item\'";
        }
        $md5list = implode(',', $array);
        $values = array($database->php_to_postgres($array));
        $result = $database->db_select("SELECT * FROM art_data WHERE image_md5 && $1 ORDER BY post_id LIMIT 1", $values);
        if (!$result) {
            return false;
        } else {
            $post = $result[0]['post_id'];
            return $post;
        }
    }
}