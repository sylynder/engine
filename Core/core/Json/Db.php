<?php

/*
| -------------------------------------------------------------------------
| Json Database
| -------------------------------------------------------------------------
| This file implements json as a flat file database
|
*/
namespace Base\Json;

use Base\Json\Helpers\Helper;

class Db
{
    /**
     * File variable
     *
     * @var string
     */
    public $file = '';

    /**
     * Content variable
     *
     * @var array
     */
    public $content = [];

    /**
     * JSON File variable
     *
     * @var
     */
    private $jsonFile;

    /**
     * Load variable
     *
     * @var
     */
    private $load;

    /**
     * Where variable
     *
     * @var
     */
    private $where;

    /**
     * Select variable
     *
     * @var
     */
    private $select;

    /**
     * Merge variable
     *
     * @var
     */
    private $merge;

    /**
     * Update variable
     *
     * @var
     */
    private $update;

    /**
     * Delete variable
     *
     * @var boolean
     */
    private $delete = false;

    /**
     * Last Indexes variable
     *
     * @var array
     */
    private $lastIndexes = [];

    /**
     * Order By variable
     *
     * @var array
     */
    private $orderBy = [];

    /**
     * JSON Directory variable
     *
     * @var
     */
    public $jsonDirectory;

    /**
     * JSON Options variable
     *
     * @var array
     */
    private $jsonOptions = [];

    /**
     * Write Error Constant
     */
    private const WRITE_ERROR = EXIT_UNKNOWN_FILE;

    /**
     * JSON File Extension Constant
     */
    private const JSON_FILE_EXTENSION = '.json';

    /**
     * XML File Extension Constant
     */
    private const XML_FILE_EXTENSION = '.xml';

    /**
     * SQL File Extension Constant
     */
    private const SQL_FILE_EXTENSION = '.sql';

    /**
     * Asc Constant
     */
    public const ASC = 1;

    /**
     * Desc Constant
     */
    public const DESC = 0;

    /**
     * And Constant
     */
    public const AND = 'AND';

    /**
     * Or Constant
     */
    public const OR = 'OR';

    /**
     * Constructor function
     *
     * @param string $jsonDirectory
     * @param mixed $jsonEncodeOption
     */
    public function __construct($jsonDirectory = '', $jsonEncodeOption = JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
    {

        $rootDbPath = config_item('json_db_path');

        if (empty($jsonDirectory)) {
            $this->jsonDirectory = $rootDbPath;
        } else {
            $this->jsonDirectory = $rootDbPath . DIRECTORY_SEPARATOR . $jsonDirectory;
        }

        $this->jsonOptions['encode'] = $jsonEncodeOption;
    }

    /**
     * Create a folder as database
     * 
     * This groups your JSON files
     * 
     * @param string $directory
     * @return bool|string
     */
    public function createDatabase($directory = '')
    {
        if (!is_dir($directory)) {

            $directory = $this->jsonDirectory . DIRECTORY_SEPARATOR . $directory;

            if (is_dir($directory)) {
                return 'exists';
            }

            try {
                mkdir($directory, 0700, true);
                return true;
            } catch (\Exception $ex) {
                return false;
            }
        }

        return false;
    }

    public function getDb()
    {
        return $this->jsonDirectory;
    }

    /**
     * Get JSON file size
     *
     * @return int
     */
    public function getFileSize()
    {
        $size = 0;
        $currentSize = 0;

        if ($this->jsonFile) {
            $currentSize = ftell($this->jsonFile);
            fseek($this->jsonFile, 0, SEEK_END);
            $size = ftell($this->jsonFile);
            fseek($this->jsonFile, $currentSize, SEEK_SET);
        }

        return $size;
    }

    /**
     * Checks and validates if JSON file exists
     *
     * @return bool
     */
    private function fileExists()
    {
        // Checks if JSON file exists, if not create
        if (!file_exists($this->file)) {
            touch($this->file);
        }

        if ($this->load == 'partial') {

            $this->jsonFile = fopen($this->file, 'r+');

            if (!$this->jsonFile) {
                throw new \Exception('Unable to open JSON file');
            }

            $size = $this->getFileSize();

            if ($size) {

                $content = Helper::getJsonChunk($this->jsonFile);

                // We could not get the first chunk of JSON. 
                // Lets try to load everything then
                if (!$content) {
                    $content = fread($this->jsonFile, $size);
                } else {
                    // We got the first chunk, we still need to put it into an array
                    $content = sprintf('[%s]', $content);
                }

                $content = json_decode($content, true);
            } else {
                // Empty file. File was just created
                $content = [];
            }
        } else {
            // Read content of JSON file
            $content = file_get_contents($this->file);
            $content = json_decode(($content == '') ? '[]' : $content, true);
        }

        // Check if its arrays of JSON
        if (!is_array($content) && is_object($content)) {
            throw new \Exception('An array of JSON is required: JSON data enclosed with []');
        }

        // An invalid JSON file
        if (!is_array($content) && !is_object($content)) {
            // throw new \Exception('Invalid JSON or No Content in JSON file');
            $content = [];
        }
        
        $this->content = $content;
        return true;
    }

    /**
     * Count Content of Json
     *
     * Use key to count content
     * 
     * @param $string $key
     * @return int
     */
    public function count($key = null, $hasValue = true)
    {
        $content = file_get_contents($this->file);

        $total = 0;

        $keyExists = strpos(json_encode($content), $key ?? '') > 0 ? true : false;

        if (!$keyExists && !empty($key)) {
            return $total;
        }

        $content = $this->processOrderBy($this->content);
        
        if (empty($key)) {
            return count($content);
        }

        $elements = [];

        if ($keyExists && $hasValue) {
            foreach ($content as $item) {
                if ($item[$key]) {
                    $elements[] = $item[$key];
                }
            }
        }

        if ($keyExists && !$hasValue) {
            foreach ($content as $item) {
                if (isset($item[$key])) {
                    $elements[] = $item[$key];
                }
            }
        }
        
        return $total = count($elements);
    }

    /**
     * Count content of json and increase with one
     *
     * @return int
     */
    public function autoincrement()
    {
        return $this->count() + 1;
    }

    /**
     * Explodes the selected columns into array
     *
     * @param string $args Optional. Default *
     * @return object
     */
    public function select($args = '*')
    {
        // Explode to array
        $this->select = explode(',', $args);
        // Remove whitespaces
        $this->select = array_map('trim', $this->select);
        // Remove empty values
        $this->select = array_filter($this->select);

        return $this;
    }

    /**
     * Loads the JSON file
     *
     * @param string $file Accepts file path to JSON file
     * @param string $load
     * @return object
     */
    public function from($file = '', $load = 'full')
    {

        if (empty($file) && !empty($this->file)) {
            $file = $this->file;
        }

        $file = $file . Db::JSON_FILE_EXTENSION;

        $this->file = sprintf('%s/%s.json', $this->jsonDirectory, str_replace('.json', '', $file)); // Adding .json extension is no longer necessary

        // Reset where
        $this->where([]);
        $this->content = '';
        $this->load = $load;

        // Reset order by
        $this->orderBy = [];

        if ($this->fileExists()) {
            // $this->content = ( array ) json_decode( file_get_contents( $this->file ) );
        }

        return $this;
    }

    /**
     * Gets data using columns specified
     *
     * @param array $columns
     * @param string $merge
     * @return object
     */
    public function where(array $columns, $merge = 'OR')
    {
        $this->where = $columns;
        $this->merge = $merge;
        return $this;
    }

    /**
     * Implements regex search on where statement.
     *
     * @param   string  $pattern            Regex pattern
     * @param   int     $pregMatchFlags Flags for preg_grep(). See - https://www.php.net/manual/en/function.preg-match.php
     */
    public static function regex(string $pattern, int $pregMatchFlags = 0): object
    {
        $content = new \stdClass();
        $content->isRegex = true;
        $content->value = $pattern;
        $content->options = $pregMatchFlags;

        return $content;
    }

    /**
     * Implements regex search on where statement.
     *
     * Alias to above static function
     *
     * @param string $pattern
     * @param integer $pregMatchFlags
     * @return object
     */
    public function search(string $pattern, int $pregMatchFlags = 0): object
    {
        return static::regex($pattern, $pregMatchFlags);
    }

    /**
     * Deletes data from JSON file
     *
     * @return object
     */
    public function delete($file = '')
    {
        if (!empty($file) && is_string($file)) {
            $this->from($file);
        }

        if (empty($file)) {
            $source = str_ext(str_last_word($this->file, '/'), true);
            $this->from($source);
        }

        $this->delete = true;
        return $this;
    }

    /**
     * Updates data in JSON file
     *
     * @param string $file JSON file as table
     * @param array $columns
     * @return object
     */
    public function update($file = '', array $columns = [])
    {
        if (!empty($file) && is_string($file)) {
            $this->from($file);
        }

        if (is_array($file)) {
            $source = str_ext(str_last_word($this->file, '/'), true);
            $this->from($source);
        }

        if (is_array($file)) {
            $this->update = $file;
        } else {
            $this->update = $columns;
        }

        return $this;
    }

    /**
     * Check Last Index
     *
     * @param string $column
     * @return mixed
     */
    private function checkLastIndex($column)
    {
        $this->orderBy($column, self::DESC);

        $content = $this->processOrderBy($this->content);

        $records = count($content);

        if ($records == 0) {
            $lastIndex = 1;
        } else {
            $this->lastIndexes = $content[($records - 1)];
            $lastIndex = $this->lastIndexes[$column] + 1;
        }

        return $lastIndex;
    }

    /**
     * Last Index
     *
     * @param string $column
     * @return mixed
     */
    public function lastIndex($column)
    {
        $this->from($this->file);

        $lastIndex = 0;

        if (!empty($column) && !empty($this->content)) {
            $lastIndex = $this->checkLastIndex($column);
        }

        return $lastIndex;
    }

    /**
     * Inserts data into JSON file
     *
     * @param string|array $file JSON filename without extension
     * @param array $values Array of columns as keys and values
     */
    public function insert($file = '', array $values = [])
    {
        if (!empty($file) && is_string($file)) {
            $this->from($file, 'partial');
        }

        if (is_array($file)) {
            $values = $file;
            $source = str_ext(str_last_word($this->file, '/'), true);
            $this->from($source, 'partial');
        }

        $firstRow = current($this->content);
        
        $this->content = [];

        if (!empty($firstRow)) {
            $unmatchedColumns = 0;

            foreach ($firstRow as $column => $value) {
                if (!isset($values[$column])) {
                    $values[$column] = null;
                }
            }

            foreach ($values as $column => $value) {
                if (!array_key_exists($column, $firstRow)) {
                    $unmatchedColumns = 1;
                    break;
                }
            }

            if ($unmatchedColumns) {
                throw new \Exception('Table columns must match as of the first row', EXIT_DATABASE);
            }
        }

        $this->content[] = $values;

        try {
            $this->commit();
            return true;
        } catch (\Exception $ex) {
            return false;
        }
    }

    /**
     * Commit to JSON file
     *
     * @return void
     */
    public function commit()
    {
        if ($this->jsonFile && is_resource($this->jsonFile)) {
            $file = $this->jsonFile;
        } else {
            $file = fopen($this->file, 'w+');
        }

        if ($this->load === 'full') {
            // Write everything back into the file
            fwrite($file, (!$this->content ? '[]' : json_encode($this->content, $this->jsonOptions['encode'])));
        } elseif ($this->load === 'partial') {
            // Append it
            $this->append();
        } else {
            // Unknown load type
            fclose($file);
            throw new \Exception('Write fail: Unknown load type provided', Db::WRITE_ERROR);
        }

        fclose($file);
    }

    /**
     * Append to JSON file
     *
     * @return void
     */
    private function append()
    {
        $size = $this->getFileSize();
        $perRead = $size > 64 ? 64 : $size;
        $readSize = -$perRead;
        $lastBracket = false;
        $lastinput = false;
        $i = $size;
        $data = json_encode($this->content, $this->jsonOptions['encode']);

        if ($size) {
            fseek($this->jsonFile, $readSize, SEEK_END);

            while (($read = fread($this->jsonFile, $perRead))) {
                $perRead = $i - $perRead < 0 ? $i : $perRead;
                if ($lastBracket === false) {
                    $lastBracket = strrpos($read, ']', 0);
                    if ($lastBracket !== false) {
                        $lastBracket = ($i - $perRead) + $lastBracket;
                    }
                }

                if ($lastBracket !== false) {
                    $lastinput = strrpos($read, '}');
                    if ($lastinput !== false) {
                        $lastinput = ($i - $perRead) + $lastinput;
                        break;
                    }
                }

                $i -= $perRead;
                $readSize += -$perRead;

                if (abs($readSize) >= $size) {
                    break;
                }

                fseek($this->jsonFile, $readSize, SEEK_END);
            }
        }

        if ($lastBracket !== false) {
            // We found existing JSON data, don't write extra [
            $data = substr($data, 1);
            if ($lastinput !== false) {
                $data = sprintf(',%s', $data);
            }
        } else {
            if ($size > 0) {
                throw new \Exception('Append error: JSON file looks malformed');
            }

            $lastBracket = 0;
        }

        fseek($this->jsonFile, $lastBracket, SEEK_SET);
        fwrite($this->jsonFile, $data);
    }

    /**
     * Update JSON file content
     *
     * @return void
     */
    private function updateFile()
    {
        if (!empty($this->lastIndexes) && !empty($this->where)) {
            foreach ($this->content as $i => $v) {
                if (in_array($i, $this->lastIndexes)) {
                    $content = (array) $this->content[$i];
                    if (!array_diff_key($this->update, $content)) {
                        $this->content[$i] = (object) array_merge($content, $this->update);
                    } else {
                        throw new \Exception('Update method has an off key');
                    }
                } else {
                    continue;
                }
            }
        } elseif (!empty($this->where) && empty($this->lastIndexes)) {
            return;
        } else {
            foreach ($this->content as $i => $v) {
                $content = (array) $this->content[$i];
                if (!array_diff_key($this->update, $content)) {
                    $this->content[$i] = (object) array_merge($content, $this->update);
                } else {
                    throw new \Exception('Update method has an off key ');
                }
            }
        }
    }

    /**
     * Prepares data and write to file
     *
     * @return object $this
     */
    public function execute()
    {
        $content = (!empty($this->where) ? $this->whereResult() : $this->content);
        $return = false;

        if ($this->delete) {

            if (!empty($this->lastIndexes) && !empty($this->where)) {
                $this->content = array_filter($this->content, function ($index) {
                    return !in_array($index, $this->lastIndexes);
                }, ARRAY_FILTER_USE_KEY);

                $this->content = array_values($this->content);
            } elseif (empty($this->where) && empty($this->lastIndexes)) {
                $this->content = [];
            }

            $return = true;
            $this->delete = false;
        } elseif (!empty($this->update)) {
            $this->updateFile();
            $this->update = [];
            $return = true;
        } else {
            $return = false;
        }

        $this->commit();

        return $return;
    }

    /**
     * Flushes indexes they won't be reused on next action
     *
     * @param boolean $flushWhere
     * @return void
     */
    private function flushIndexes($flushWhere = false)
    {
        $this->lastIndexes = [];
        if ($flushWhere) {
            $this->where = [];
        }

        if ($this->jsonFile && is_resource($this->jsonFile)) {
            fclose($this->jsonFile);
        }
    }

    /**
     * Check Value Intersections
     *
     * @param mixed $firstInstance
     * @param mixed $secondInstance
     * @return mixed
     */
    private function intersectValueCheck($firstInstance, $secondInstance)
    {
        if ($secondInstance instanceof \stdClass) {
            if ($secondInstance->isRegex) {
                return (int)!preg_match($secondInstance->value, (string) $firstInstance, $_, $secondInstance->options);
            }

            return -1;
        }

        if ($firstInstance instanceof \stdClass) {
            if ($firstInstance->isRegex) {
                return (int)!preg_match($firstInstance->value, (string) $secondInstance, $_, $firstInstance->options);
            }

            return -1;
        }

        return strcasecmp((string) $firstInstance, (string) $secondInstance);
    }

    /**
     * Validates and fetch out the data for manipulation
     *
     * @return array $rows Array of rows matching WHERE
     */
    private function whereResult()
    {
        $this->flushIndexes();

        if ($this->merge == 'AND') {
            return $this->whereAndResult();
        }
        // Filter array
        $rows = array_filter($this->content, function ($row, $index) {
            
            $row = (array) $row; // Convert first stage to array if object

            // Check for rows intersecting with the where values.
            if (array_uintersect_uassoc($row, $this->where, [$this, 'intersectValueCheck'], 'strcasecmp') /*array_intersect_assoc( $row, $this->where )*/) {
                $this->lastIndexes[] = $index;
                return true;
            }

            return false;
        }, ARRAY_FILTER_USE_BOTH);

        // Make sure every  object is turned to array here.
        return array_values(Helper::objectToArray($rows));
    }

    /**
     * Validates and fetch out the data for manipulation for AND
     *
     * @return array $rows Array of fetched WHERE statement
     */
    private function whereAndResult()
    {
        /*
        * Validates the where statement values
        */
        $rows = [];

        // Loop through the db rows. Ge the index and row
        foreach ($this->content as $index => $row) {

            // Make sure its array data type
            $row = (array) $row;

            // check if the row = where['column'=>'value', 'column2'=>'value2']
            if (!array_udiff_uassoc($this->where, $row, [$this, 'intersectValueCheck'], 'strcasecmp')) {
                $rows[] = $row;
                // Append also each row array key
                $this->lastIndexes[] = $index;
            } else {
                continue;
            }
        }
        return $rows;
    }

    /**
     * Order Content By ASC or DESC
     *
     * @param string $column
     * @param string $order
     * @return object
     */
    public function orderBy($column, $order = self::ASC)
    {
        $this->orderBy = [$column, $order];
        return $this;
    }

    /**
     * Process Order By
     *
     * @param array $content
     * @return array
     */
    private function processOrderBy($content)
    {
        if ($this->orderBy && $content && in_array($this->orderBy[0], array_keys((array) $content[0]))) {
            /*
                * Check if order by was specified
                * Check if there's actually a result of the query
                * Makes sure the column  actually exists in the list of columns
            */

            list($sortColumn, $orderBy) = $this->orderBy;
            $sortKeys = [];
            $sorted = [];

            foreach ($content as $index => $value) {
                $value = (array) $value;
                // Save the index and value so we can use them to sort
                $sortKeys[$index] = $value[$sortColumn];
            }

            // Let's sort!
            if ($orderBy == self::ASC) {
                asort($sortKeys);
            } elseif ($orderBy == self::DESC) {
                arsort($sortKeys);
            }

            // We are done with sorting, lets use the sorted array indexes to pull back the original content and return new content
            foreach ($sortKeys as $index => $value) {
                $sorted[$index] = (array) $content[$index];
            }

            $content = $sorted;
        }

        return $content;
    }

    /**
     * Get final results
     *
     * @return array|object
     */
    public function get()
    {

        if ($this->where != null) {
            $content = $this->whereResult();
        } else {
            $content = $this->content;
        }

        if ($this->select && !in_array('*', $this->select)) {
            $rows = [];
            foreach ($content as $id => $row) {
                $row = (array) $row;
                foreach ($row as $key => $val) {
                    if (in_array($key, $this->select)) {
                        $rows[$id][$key] = $val;
                    } else {
                        continue;
                    }
                }
            }
            $content = $rows;
        }

        // Finally, lets do sorting :)
        $content = $this->processOrderBy($content);

        $this->flushIndexes(true);
        return $content;
    }

    /**
     * Get final results
     *
     * @return array|object
     */
    public function all()
    {
        return $this->get();
    }

    /**
     * Export JSON Table to XML
     *
     * @param string $from
     * @param string $to
     *
     * @return  bool Returns true if file was created, else false
     */
    public function toXML($from = '', $to = null)
    {
        $source = '';

        if (is_string($from) && ($to === null)) {

            $source = str_ext(str_last_word($this->file, '/'), true);
            $this->from($source); // Reads the JSON file

            // Assign $to to $from
            $from = str_ext($from);
            $to = $from . Db::XML_FILE_EXTENSION;;
        } else {
            $source = $from;
            $this->from($from); // Reads the JSON file
        }

        if ($this->content) {
            
            $element = pathinfo($source, PATHINFO_FILENAME);
            
            $xml = '
            <?xml version="1.0"?>
                <' . $element . '>
            ';

            foreach ($this->content as $index => $value) {
                $xml .= '
                <DATA>';
                foreach ($value as $column => $value) {
                    $xml .= sprintf('
                    <%s>%s</%s>', $column, $value, $column);
                }
                $xml .= '
                </DATA>
                ';
            }

            $xml .= '</' . $element . '>';

            $xml = trim($xml);

            $to = str_ext($to);
            $to = $to . Db::XML_FILE_EXTENSION;

            file_put_contents($to, $xml);

            return true;
        }

        return false;
    }

    /**
     * Generates SQL from JSON
     *
     * @param   string  $from           JSON file to get data from
     * @param   string  $to             Filename to write SQL into
     * @param   bool    $createTable    If to include create table in this export
     *
     * @return  bool    Returns true if file was created, else false
     */
    public function toSQL(string $from = '', string $to = null, bool $createTable = true): bool
    {

        if (is_string($from) && ($to === null)) {
            
            $source = str_ext(str_last_word($this->file, '/'), true);
            $this->from($source); // Reads the JSON file

            // Assign $to to $from
            $from = str_ext($from);
            $to = $from . Db::SQL_FILE_EXTENSION;

        } else {
            $this->from($from); // Reads the JSON file
        }

        if ($this->content) {

            $to = str_ext($to);
            $to = $to . Db::SQL_FILE_EXTENSION;
            $table = pathinfo($to, PATHINFO_FILENAME); // Get filename to use as table

            $sql = "-- WEBBY JSONDB to MySQL Dump\n--\n\n";

            if ($createTable) {
                // Should create table, generate a CREATE TABLE statement using the column of the first row
                $firstRow = (array) $this->content[0];
                
                $columns = array_map(function ($column) use ($firstRow) {
                    return sprintf("\t`%s` %s", $column, $this->toSQLType(gettype($firstRow[$column])));
                }, array_keys($firstRow));

                $sql = sprintf("%s-- Table Structure for `%s`\n--\n\nCREATE TABLE `%s` \n(\n%s\n);\n", $sql, $table, $table, implode(",\n", $columns));
            }

            foreach ($this->content as $row) {

                $row = (array) $row;

                $values = array_map(function ($content) {
                    $content = (is_array($content) || is_object($content) ? serialize($content) : $content);
                    return sprintf("'%s'", addslashes((string) $content));
                }, array_values($row));

                $columns = array_map(function ($column) {
                    return sprintf('`%s`', $column);
                }, array_keys($row));

                $sql .= sprintf("INSERT INTO `%s` ( %s ) VALUES ( %s );\n", $table, implode(', ', $columns), implode(', ', $values));
            }

            file_put_contents($to, $sql);
            return true;
        }

        return false;
    }

    /**
     * Set SQL Type
     *
     * @param string $type
     * @return string
     */
    private function toSQLType($type)
    {
        if ($type == 'bool') {
            $return = 'BOOLEAN';
        } elseif ($type == 'integer') {
            $return = 'INT';
        } elseif ($type == 'double') {
            $return = strtoupper($type);
        } else {
            $return = 'VARCHAR( 255 )';
        }
        return $return;
    }

}
