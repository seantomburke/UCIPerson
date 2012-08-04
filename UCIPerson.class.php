<?php

ini_set('allow_url_fopen', 'on');

/**
 * Page UCIPerson Class
 *
 * this class can only be used for UCI Students, it will access the directory 
 * and construct an object based on the users UCInetID.
 * 
 *
 * @author Sean Burke, http://www.seantburke.com
 *
 *
 **/

class UCIPerson
{
	public $department;					//department
	public $email;						//user's email UCI address
	public $error;						//Error message created by this class if an error occurs
	public $home_page;					//the users home page if given
	public $is_valid;					//Determines whether the given query is valid
	public $isEngineer;					//Determines if the person is an Engineer
	public $level;						//Year in school example: freshman, sophomore...
	public $major;						//Person's major discipline (Format varies)
	public $name;						//name of user
	public $nickname;					//nickname if provided
	public $picture_url;				//URL of the persons picture if given
	public $search_url;					//URL used to get the users RAW information in plain text format
	public $title;						//User's title
	public $ucinetid;					//The person's UCInetID
	public $user_array;					//All of the users information in an Array format
	
	//Variables defined by the UCI Directory for the given Engineering Majors

	public static $AEROSPACE_ENGINEERING = 'Engr AE';
	public static $BIOMEDICAL_ENGINEERING = 'Engr BM';
	public static $BIOMEDICAL_PREMED_ENGINEERING = 'Engr BMP';
	public static $CHEMICAL_ENGINEERING = 'EngrChm';
	public static $CIVIL_ENGINEERING = 'Engr CE';
	public static $COMPUTER_ENGINEERING = 'EngrCpE';
	public static $COMPUTER_SCIENCE_ENGINEERING = 'CSE';
	public static $ELECTRICAL_ENGINEERING = 'Engr EE';
	public static $ENVIRONMENTAL_ENGINEERING = 'EngrEnv';
	public static $MATERIAL_SCIENCE_ENGINEERING = 'Enr MSE';
	public static $MECHANICAL_ENGINEERING = 'Engr ME';

	/**
	 * function Constructs the User Class creating the user array and all fields
	 *
	 * @author Sean Thomas Burke <http://www.seantburke.com>
	 * @param $id = User's UCINetID
	 * @return boolean isValid, if the user is valid it returns true, else false.
	 */
	 
	function __construct($id)
	{
		$id = $this->clean($id);

		if($this->validate($id))
		{
			$this->ucinetid = $id;
			$this->search_url = 'http://directory.uci.edu/index.php?uid='.$this->ucinetid.'&form_type=plaintext';
				
			//$data = file_get_contents($this->search_url);
			
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $this->search_url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			$data = curl_exec($ch);
			curl_close($ch);

			$data = trim($data);
			$error_string = strpos($data, 'rror:');

			$pos = ($error_string) ? $error_string-1: strpos($data, '<body>')+8;
			$data = substr($data, $pos);
			$data_array = explode('<br/>', $data);
			$this->user_array = array();
				
			foreach($data_array as $line)
			{
				$temp = explode(':', $line);
				$extra = ($temp[2])? ':'.$temp[2]:'';
				$this->user_array[strtolower(trim($temp[0]))] = trim($temp[1]).$extra;
			}
				
			$this->error = $this->user_array['error'];
			$this->name = ucwords(strtolower($this->user_array['name']));
			$this->email = $this->user_array['e-mail'];
			$this->nickname = $this->user_array['nickname'];
			$this->title = $this->user_array['title'];
			$this->department = $this->user_array['department'];
			$this->home_page = $this->user_array['home page'];
			$this->picture_url = $this->user_array['picture url'];
			$this->level = $this->user_array['student\'s level'];
			$this->isEngineer = $this->isEngineer();
			$this->major = $this->user_array['major'];
			$this->convertMajor();
			$this->convertEmail();
			$this->convertLevel();
			$this->is_valid = !(isset($this->user_array['error']));
			if(!$this->is_valid)
			{
				$this->error = $this->ucinetid.' is not a valid UCInetID';
			}
		}
		return $this->is_valid;
	}

	/**
	 * function Cleans the UCINetID from dangerous, or unnecessary tags
	 *
	 * @author Sean Thomas Burke <http://www.seantburke.com>
	 * @param $id = User's UCINetID
	 * @return $id, cleaned UCInetID.
	 */
	 
	private function clean($id)
	{
		//if user enters email address, strip the '@uci.edu' portion
		if($posi = strpos($id, '@uci.edu'))
		$id = substr($id, 0, $posi);

		//strip any other dangerous tags
		$id = strtolower($id);
		$id = strip_tags($id);
		$id = trim($id);
			
		return $id;
	}
	
	
	/**
	 * function Validates the UCInetID
	 *
	 * @author Sean Thomas Burke <http://www.seantburke.com>
	 * @param $id = User's UCINetID
	 * @return boolean true if valid, false if not
	 */
	
	private function validate($id)
	{
		if($id > 8)
		{
			$this->is_valid = false;
			$this->error = $id.' is not a valid UCInetID';
			return false;
		}
		
		if(!(preg_match('/^[a-zA-Z0-9]+$/', $id , $array, PREG_OFFSET_CAPTURE)))
		{
			$this->is_valid = false;
			$this->error = $id.' is not a valid UCInetID';
			return false;
		}
		return true;
	}
	
	/**
	 * function description
	 *
	 * @author Sean Thomas Burke <http://www.seantburke.com>
	 * @param none
	 * @return boolean, if engineer then return true, else false.
	 */
	 
	function isEngineer()
	{
		//if their title includes engineer at all
		if(stripos($this->title, 'engineer'))
		{
			$this->isEngineer = true;
		}
		//if their position includes engineer at all
		
		if(stripos($this->department, 'engineer'))
		{
			$this->isEngineer = true;
		}
		return $this->isEngineer;
	}
	
	/**
	 * function converts the email of the user
	 *
	 * @author Sean Thomas Burke <http://www.seantburke.com>
	 * @param none
	 * @return none
	 */
	 
	private function convertEmail()
	{
		if(!(strstr($this->email, '@')))
		$this->email = $this->ucinetid.'@uci.edu';
	}

	/**
	 * function converts the level of the user
	 *
	 * @author Sean Thomas Burke <http://www.seantburke.com>
	 * @param none
	 * @return none
	 */
	
	private function convertLevel()
	{
		switch ($this->level)
		{

			case 'FR':
				$this->level = 'Freshman';
				break;
			case 'SO':
				$this->level = 'Sophomore';
				break;
			case 'JR':
				$this->level = 'Junior';
				break;
			case 'SR':
				$this->level = 'Senior';
				break;
			case 'GR':
				$this->level = 'Graduate';
				break;
			default:
				//if the person is not the above levels, then they must be faculty/staff, but check to see if they are engineering faculty
				if($this->isEngineer())
					$this->level = 'Faculty/Staff';
				break;
		}
	}
	
	/**
	 * function converts the major of the user
	 *
	 * @author Sean Thomas Burke <http://www.seantburke.com>
	 * @param none
	 * @return none
	 */

	private function convertMajor()
	{
		switch ($this->major)
		{

			case 'Engr AE':
				$this->major = 'Aerospace Engineering';
				$this->isEngineer = true;
				break;
			case 'Engr BM':
				$this->major = 'Biomedical Engineering';
				$this->isEngineer = true;
				break;
			case 'EngrBMP':
				$this->major = 'Biomedical Engineering: Premedical';
				$this->isEngineer = true;
				break;
			case 'EngrChm':
				$this->major = 'Chemical Engineering';
				$this->isEngineer = true;
				break;
			case 'Engr CE':
				$this->major = 'Civil Engineering';
				$this->isEngineer = true;
				break;
			case 'EngrCpE':
				$this->major = 'Computer Engineering';
				$this->isEngineer = true;
				break;
			case 'CSE':
				$this->major = 'Computer Science Engineering';
				$this->isEngineer = true;
				break;
			case 'Engr EE':
				$this->major = 'Electrical Engineering';
				$this->isEngineer = true;
				break;
			case 'EngrEnv':
				$this->major = 'Environmental Engineering';
				$this->isEngineer = true;
				break;
			case 'Enr MSE':
				$this->major = 'Material Science Engineering';
				$this->isEngineer = true;
				break;
			case 'Engr ME':
				$this->major = 'Mechanical Engineering';
				$this->isEngineer = true;
				break;
			case 'EngrMAE':
				$this->major = 'Mechanical Aerospace Engineering';
				$this->isEngineer = true;
				break;
		
		}
	}

	function isValid()
	{
		return $this->is_valid;
	}
	
	function getName()
	{
		return $this->name;
	}

	function getNickname()
	{
		return $this->nickname;
	}

	function getUCInetID()
	{
		return $this->ucinetid;
	}

	function getMajor()
	{
		return $this->major;
	}

	function getDepartment()
	{
		return $this->level;
	}

	function getHomePage()
	{
		return $this->home_page;
	}

	function getPictureURL()
	{
		return $this->picture_url;
	}

	function getUserArray()
	{
		return $this->user_array;
	}

	function getLevel()
	{
		return $this->level;
	}

	function getSearchURL()
	{
		return $this->search_url;
	}

	function getEmail()
	{
		return $this->email;
	}
	
}

?>