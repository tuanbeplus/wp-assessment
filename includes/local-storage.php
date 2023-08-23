<?php

/**
 * A simple helper class to
 * persistently store primitive data.
 * 
 * Note:
 * This class is implemented as a singleton
 * you can not instanciate it directly.
 * use LocalStorage::getInstance() to get an instance
 * of LocalStorage.
 * 
 * Usage:
 * LocalStorage::getInstance()->setValue('foo', 'bar');		// Set "foo" to the value "bar".
 * LocalStorage::getInstance()->commit();					// Save all settings to the filesystem.
 * LocalStorage::getInstance()->getValue('foo');			// returns bar.
 * echo (string)LocalStorage::getInstance();				// Casting localstorage to string will return a JSON-representation of all values.
 * 
 * @author Andre Uschmann
 *
 */
class LocalStorage {
	
	/** Default filepath to the file where the data will be stored. */
	const DEFAULT_FILENAME = "localStorage.json";	
	/** Static reference to hold the singleton instance. */
	protected static $instance = null;
	/** An associative array to store the key value pairs **/
	private $data = array();
	private $filename;
	
	/**
	 * Forbid external instantiation
	 */
	protected function __construct($filename) {
		$this->filename = $filename;
		if(file_exists($this->filename))
			$this->data = json_decode(file_get_contents($this->filename));
		else 
			$this->data = json_decode("{}");
	}
	
	/**
	 * Return the one and only instance
	 * of this Class.
	 * @return LocalStorage
	 */
	public static function getInstance($filename = self::DEFAULT_FILENAME){
		if(self::$instance == null){
			self::$instance = new self($filename);
		}
		return self::$instance;
	}
	
	/**
	 * Sets the name of the file
	 * where all data will be stored when
	 * calling commit().
	 * @param string $filename
	 */
	public function setFilename($filename){
		$this->filename = $filename;
	}
	
	/**
	 * Sets a new value or updates
	 * a value.
	 * @param string $key
	 * @param mixed $value
	 */
	public function setValue($key, $value){
		$this->data->$key = $value;
	}
	
	/**
	 * Unsets the value with the
	 * given key.
	 * @param string $key
	 */
	public function unsetValue($key){
		if(isset($this->data->$key))
			unset($this->data->$key);
	}
	
	/**
	 * Gets the value by its key.
	 * @param unknown $key
	 * @return unknown
	 */
	public function getValue($key){
		if(isset($this->data->$key))
			return $this->data->$key;
		return $key;
	}
	
	/**
	 * Deletes all values.
	 */
	public function clear(){
		$this->data = array();
	}
	
	/**
	 * Saves the data to the filesystem.
	 */
	public function commit(){
		file_put_contents($this->filename, json_encode($this->data));
	}
	
	/**
	 * Returns all data as
	 * a JSON-string
	 */
	public function toJson(){
		return json_encode($this->data);
	}
	
	/**
	 * Override the default
	 * __toString() method to return a JSON-string
	 * of all values.
	 * @return string
	 */
	public function __toString() {
		return $this->toJson();
	}
}