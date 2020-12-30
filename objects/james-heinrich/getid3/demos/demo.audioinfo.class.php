<?php
/////////////////////////////////////////////////////////////////
/// getID3() by James Heinrich <info@getid3.org>               //
//  available at https://github.com/JamesHeinrich/getID3       //
//            or https://www.getid3.org                        //
//            or http://getid3.sourceforge.net                 //
//                                                             //
// /demo/demo.audioinfo.class.php - part of getID3()           //
//                                                            ///
/////////////////////////////////////////////////////////////////
// +----------------------------------------------------------------------+
// | PHP version 4.1.0                                                    |
// +----------------------------------------------------------------------+
// | Placed in public domain by Allan Hansen, 2002. Share and enjoy!      |
// +----------------------------------------------------------------------+
// | /demo/demo.audioinfo.class.php                                       |
// |                                                                      |
// | Example wrapper class to extract information from audio files        |
// | through getID3().                                                    |
// |                                                                      |
// | getID3() returns a lot of information. Much of this information is   |
// | not needed for the end-application. It is also possible that some    |
// | users want to extract specific info. Modifying getID3() files is a   |
// | bad idea, as modifications needs to be done to future versions of    |
// | getID3().                                                            |
// |                                                                      |
// | Modify this wrapper class instead. This example extracts certain     |
// | fields only and adds a new root value - encoder_options if possible. |
// | It also checks for mp3 files with wave headers.                      |
// +----------------------------------------------------------------------+
// | Example code:                                                        |
// |   $au = new AudioInfo();                                             |
// |   print_r($au->Info('file.flac');                                    |
// +----------------------------------------------------------------------+
// | Authors: Allan Hansen <ahØartemis*dk>                                |
// +----------------------------------------------------------------------+



/**
* getID3() settings
*/

require_once('../getid3/getid3.php');




/**
* Class for extracting information from audio files with getID3().
*/

class AudioInfo {

	/**
	* Private variables
	*/
	private $result;
	private $info;
	private $getID3;


	/**
	* Constructor
	*/

	function __construct() {

		// Initialize getID3 engine
		$this->getID3 = new getID3;
		$this->getID3->option_md5_data        = true;
		$this->getID3->option_md5_data_source = true;
		$this->getID3->encoding               = 'UTF-8';
	}




	/**
	* Extract information - only public function
	*
	* @param    string  $file    Audio file to extract info from.
	*
	* @return array
	*/

	public function Info($file) {

		// Analyze file
		$this->info = $this->getID3->analyze($file);

		// Exit here on error
		if (isset($this->info['error'])) {
			return array ('error' => $this->info['error']);
		}

		// Init wrapper object
		$this->result = array();
		$this->result['format_name']     = (isset($this->info['fileformat']) ? $this->info['fileformat'] : '').'/'.(isset($this->info['audio']['dataformat']) ? $this->info['audio']['dataformat'] : '').(isset($this->info['video']['dataformat']) ? '/'.$this->info['video']['dataformat'] : '');
		$this->result['encoder_version'] = (isset($this->info['audio']['encoder'])         ? $this->info['audio']['encoder']         : '');
		$this->result['encoder_options'] = (isset($this->info['audio']['encoder_options']) ? $this->info['audio']['encoder_options'] : '');
		$this->result['bitrate_mode']    = (isset($this->info['audio']['bitrate_mode'])    ? $this->info['audio']['bitrate_mode']    : '');
		$this->result['channels']        = (isset($this->info['audio']['channels'])        ? $this->info['audio']['channels']        : '');
		$this->result['sample_rate']     = (isset($this->info['audio']['sample_rate'])     ? $this->info['audio']['sample_rate']     : '');
		$this->result['bits_per_sample'] = (isset($this->info['audio']['bits_per_sample']) ? $this->info['audio']['bits_per_sample'] : '');
		$this->result['playing_time']    = (isset($this->info['playtime_seconds'])         ? $this->info['playtime_seconds']         : '');
		$this->result['avg_bit_rate']    = (isset($this->info['audio']['bitrate'])         ? $this->info['audio']['bitrate']         : '');
		$this->result['tags']            = (isset($this->info['tags'])                     ? $this->info['tags']                     : '');
		$this->result['comments']        = (isset($this->info['comments'])                 ? $this->info['comments']                 : '');
		$this->result['warning']         = (isset($this->info['warning'])                  ? $this->info['warning']                  : '');
		$this->result['md5']             = (isset($this->info['md5_data'])                 ? $this->info['md5_data']                 : '');

		// Post getID3() data handling based on file format
		$method = (isset($this->info['fileformat']) ? $this->info['fileformat'] : '').'Info';
		if ($method && method_exists($this, $method)) {
			$this->$method();
		}

		return $this->result;
	}




	/**
	* post-getID3() data handling for AAC files.
	*
	*/

	private function aacInfo() {
		$this->result['format_name']     = 'AAC';
	}




	/**
	* post-getID3() data handling for Wave files.
	*
	*/

	private function riffInfo() {
		if ($this->info['audio']['dataformat'] == 'wav') {

			$this->result['format_name'] = 'Wave';

		} elseif (preg_match('#^mp[1-3]$#', $this->info['audio']['dataformat'])) {

			$this->result['format_name'] = strtoupper($this->info['audio']['dataformat']);

		} else {

			$this->result['format_name'] = 'riff/'.$this->info['audio']['dataformat'];

		}
	}




	/**
	* * post-getID3() data handling for FLAC files.
	*
	*/

	private function flacInfo() {
		$this->result['format_name']     = 'FLAC';
	}





	/**
	* post-getID3() data handling for Monkey's Audio files.
	*
	*/

	private function macInfo() {
		$this->result['format_name']     = 'Monkey\'s Audio';
	}





	/**
	 * post-getID3() data handling for Lossless Audio files.
	 */
	private function laInfo() {
		$this->result['format_name']     = 'La';
	}





	/**
	* post-getID3() data handling for Ogg Vorbis files.
	*
	* @access   private
	*/

	function oggInfo() {
		if ($this->info['audio']['dataformat'] == 'vorbis') {

			$this->result['format_name']     = 'Ogg Vorbis';

		} else if ($this->info['audio']['dataformat'] == 'flac') {

			$this->result['format_name'] = 'Ogg FLAC';

		} else if ($this->info['audio']['dataformat'] == 'speex') {

			$this->result['format_name'] = 'Ogg Speex';

		} else {

			$this->result['format_name'] = 'Ogg '.$this->info['audio']['dataformat'];

		}
	}




	/**
	* post-getID3() data handling for Musepack files.
	*
	* @access   private
	*/

	function mpcInfo() {
		$this->result['format_name']     = 'Musepack';
	}




	/**
	* post-getID3() data handling for MPEG files.
	*
	* @access   private
	*/

	function mp3Info() {
		$this->result['format_name']     = 'MP3';
	}




	/**
	* post-getID3() data handling for MPEG files.
	*
	* @access   private
	*/

	function mp2Info() {
		$this->result['format_name']     = 'MP2';
	}





	/**
	* post-getID3() data handling for MPEG files.
	*
	* @access   private
	*/

	function mp1Info() {
		$this->result['format_name']     = 'MP1';
	}




	/**
	* post-getID3() data handling for WMA files.
	*
	* @access   private
	*/

	function asfInfo() {
		$this->result['format_name']     = strtoupper($this->info['audio']['dataformat']);
	}



	/**
	* post-getID3() data handling for Real files.
	*
	* @access   private
	*/

	function realInfo() {
		$this->result['format_name']     = 'Real';
	}





	/**
	* post-getID3() data handling for VQF files.
	*
	* @access   private
	*/

	function vqfInfo() {
		$this->result['format_name']     = 'VQF';
	}

}
