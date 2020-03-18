<?php

//This class awards prizes to users for a timed event using bit flags

class ActivityFest {

	// ****************** //
	// ***** PUBLIC ***** //
	// ****************** //

	// *** TIME FUNCTIONS *** //

	public static function getTime() {
		if (self::$time === null) self::$time = time();
		return self::$time;
	}

	public static function setTime($time = null) {
		if ($time === null) self::$time = null;
		else self::$time = $time;
	}

	public static function getYear() {
		return date("Y", self::getTime());
	}

	// Which challenge day is it now?  Typically, challenge days begin at
	// 14:00:00 and end at 13:59:59 the following calendar day.
	public static function curDay($day = '') {
		foreach (self::$dates as $day_id => $start_ut) {
			if (self::$time >= strtotime($start_ut) && self::$time < strtotime(self::$dates[($day_id+1)])) return $day_id;
		}
		return false;
	}



	// *** USER FUNCTIONS *** //

	public static function getUser($username) {
		$sql = "SELECT * FROM Activityfest WHERE username = '{$username}' AND year = '" . self::getYear() . "'";
		$rsUser = mysql("", $sql);
		if ($User = mysql_fetch_object($rsUser)) {
			return $User;
		}
		return false;
	}

	public static function createUser($username) {
		$sql = "INSERT INTO Activityfest (username, year, day_flags, final) VALUES ('{$username}', '" . self::getYear() . "', 0, 0)";
		$rsInsert = mysql("", $sql);
		return mysql_affected_rows();
	}

	// *** PRIZE FUNCTIONS *** //


	public static function dayFlag($day_id) {
		if (array_key_exists($day_id, self::$day_flags) && self::$day_flags[$day_id]) {
			return self::$day_flags[$day_id];
		}
	}
    //THIS IS WHERE THE FLAGS ARE USED
	public static function givePrize($username, $day_id) {
		$cur_day = self::curDay();
		// If the passed day ID is the current day, AND that day exists in the
		// array, AND it has a prize, grant it.
		if (($cur_day == $day_id && array_key_exists($day_id, self::$prizes) && self::$prizes[$day_id]) || IS_DEV) {
			$User = self::getUser($username);
			if (!$User) {
				self::createUser($username);
				$User = self::getUser($username);
			}
			$day_flag = self::dayFlag($day_id);

			//determine if user already has day flag

            $sql = "SELECT username FROM Activityfest WHERE username = '{$username}' AND year = '" . self::getYear() . "' AND day_flag & {$day_flag}";
            $res =  mysql("", $sql);
            if (mysql_num_rows($res) > 0) {
                return false;
            }

			$ok = true;
			$trans = array();
			$trans[] = "UPDATE Activityfest SET day_flags = day_flags | {$day_flag} WHERE username = '{$username}' AND year = '" . self::getYear() . "' AND (day_flags & {$day_flag} = 0)";
			$day_prize = self::$prizes[$day_id];
			if (is_array($day_prize)) {
				$day_prize = array_rand($day_prize);
			}
			if (NPItems::give_item($username, $day_prize, &$trans)) $ok = false;

			if ($ok) {
				$res = mysql("", $trans);
				return $res;
			} else {
				return false;
			}
		}
	}

	// ******************* //
	// ***** PRIVATE ***** //
	// ******************* //

	private static $time = null;

	// The UT that each prize day begins.  Day 16 has no prize and no ending
	// (it's really just there to indicate when day 15 ends).
	private static $dates = array(
		 1 => "2017-09-17 14:00",
		 2 => "2017-09-18 14:00",
		 3 => "2017-09-19 14:00",
		 4 => "2017-09-20 14:00",
		 5 => "2017-09-21 14:00",
		 6 => "2017-09-22 14:00",
		 7 => "2017-09-23 14:00",
		 8 => "2017-09-24 14:00",
		 9 => "2017-09-25 14:00",
		10 => "2017-09-26 14:00",
		11 => "2017-09-27 14:00",
		12 => "2017-09-28 14:00",
		13 => "2017-09-29 14:00",
		14 => "2017-09-30 14:00",
		15 => "2017-10-01 14:00",
		16 => "2017-10-02 14:00",
	);

	private static $prizes = array(
		 1 => array(123, 124, 125, 126, 127,128),
		 2 => 45960,
		 3 => 23617,
		 4 => 45961,
		 5 => 33213,
		 6 => 45962,
		 7 => 33221,
		 8 => 45963,
		 9 => 27167,
		10 => 45964,
		11 => 22138,
		12 => 45965,
		13 => 18945,
		14 => 45966,
		15 => 19896,
	);

	private static $day_flags = array(
		 1 => 0x0001,
		 2 => 0x0002,
		 3 => 0x0004,
		 4 => 0x0008,
		 5 => 0x0010,
		 6 => 0x0020,
		 7 => 0x0040,
		 8 => 0x0080,
		 9 => 0x0100,
		10 => 0x0200,
		11 => 0x0400,
		12 => 0x0800,
		13 => 0x1000,
		14 => 0x2000,
		15 => 0x4000,
	);

	// The flag indicating that you got the final prize (a reward for getting the prize all 15 days).
	private static $final_flag = 0x8000;




}

