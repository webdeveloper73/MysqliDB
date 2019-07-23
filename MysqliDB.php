<?php
// Include the interface
require_once ("./MysqliInterface.php");

/**
 * An easy to use mysql database abstraction class with a
 * very basic query builder
 *
 * @author steve
 *        
 */
class MysqliDB extends mysqli implements MysqliInterface
{

    /**
     * Hold the mysql connection
     *
     * @var object
     */
    protected $_connection = null;

    /**
     * Gets incremented each time the
     * where(),whereByArray,whereIN()
     * and whereNotIN methods are called
     *
     * @var integer
     */
    protected $_whereCount = 0;

    /**
     * Gets incremented each time the
     * order_by method is called
     *
     * @var integer
     */
    protected $_orderByCount = 0;

    /**
     * Hold the result set
     *
     * @var
     *
     */
    protected $_result = null;

    /**
     * Holds query string to be executed
     *
     * @var string
     */
    protected $_query = null;

    public function __construct ($params = [])
    {
        list ($HOST, $USER, $PASS, $DB) = $params;
        // Attem to connect with the database
        $this->_connection = new Mysqli($HOST, $USER, $PASS, $DB);
        
        if ($this->_connection->connect_errno) {
            trigger_error(
                    "Mysql connection failed : " .
                             $this->_connection->connect_error, E_USER_ERROR);
        }
    }

    /**
     * Perform a raw mysql query
     *
     * {@inheritdoc}
     *
     * @see MysqliInterface::rawQuery()
     */
    public function rawQuery ($query)
    {
        $q = $this->_connection->query($query);
        
        // Check if mysql query failed or not
        if ($this->_connection->error) {
            trigger_error(
                    "MysqliDB::rawQuery() : Mysql query has failed : " .
                             $this->_connection->error, E_USER_ERROR);
        }
        
        return $q;
    }

    /**
     * Handles inserting data into a database table
     *
     * {@inheritdoc}
     *
     * @see MysqliInterface::insert()
     */
    public function insert ($tbl = null, $data = null, $escape = true)
    {
        if (empty($tbl) || empty($data)) {
            trigger_error(
                    "MysqliDB::insert() : method needs a table name passed 
as the first parameter and an array passed as the second parameter.", 
                    E_USER_ERROR);
        }
        
        // Set query variable
        $query = "";
        
        // Start off an insert query
        $query = "INSERT INTO `{$tbl}`";
        
        // Holds insert values string
        $values = "";
        // Loop through the data
        foreach ($data as $key => $val) :
            $values .= (is_int($val)) ? $val : "'" . $this->escape($val) . "'";
            $values .= ",";
        endforeach
        ;
        // Put together the fields
        $fields = "`" . implode("`,`", array_keys($data)) . "`";
        // Remove extra comma from end of string
        $values = rtrim(trim($values), ",");
        
        // Put together query
        $query .= " (" . $fields . ") VALUES(" . $values . ")";
        
        // Remove backticks and single quotes
        $query = ($escape === false) ? str_replace(
                array(
                        "`",
                        "'"
                ), "", $query) : $query;
        
        // Append the contents of query to the query string
        $this->_query .= $query;
        
        return $this;
    }

    /**
     * Get ID of last inserted record
     *
     * {@inheritdoc}
     *
     * @see MysqliInterface::insertID()
     */
    public function insertID ()
    {
        return $this->_connection->insert_id();
    }

    /**
     * Performs an UPDATE query
     *
     * {@inheritdoc}
     *
     * @see MysqliInterface::update()
     */
    public function update ($tbl = null, $data = [], $escape = true)
    {
        if (empty($tbl) || empty($data)) {
            trigger_error(
                    "MysqliDB::update() : method needs a table name passed 
as the first parameter and an array passed as the second parameter.", 
                    E_USER_ERROR);
        }
        
        // Set query variable
        $query = "";
        
        // Start off the query string
        $query = "UPDATE `{$tbl}` SET";
        
        // Loop through the data
        foreach ($data as $key => $val) :
            $query .= " `{$key}` = ";
            $query .= (is_int($val)) ? $val : "'" . $this->escape($val) . "'";
            $query .= ",";
        endforeach
        ;
        
        // Remove extra comma from end of string
        $query = rtrim(trim($query), ",");
        
        // Remove backticks and single quotes
        $query = ($escape === false) ? str_replace(
                array(
                        "`",
                        "'"
                ), "", $query) : $query;
        
        // Append the contents of query to the query string
        $this->_query .= $query;
        
        return $this;
    }

    /**
     * Start off the DELETE query
     *
     * {@inheritdoc}
     *
     * @see MysqliInterface::delete()
     */
    public function delete ()
    {
        
        // Start off the query string
        $this->_query = "DELETE";
        
        return $this;
    }

    /**
     * Make data safe for passing to a mysql query
     *
     * {@inheritdoc}
     *
     * @see MysqliInterface::escape()
     */
    public function escape ($str)
    {
        $output = "";
        
        if (is_array($str)) {
            $output = array_map(
                    array(
                            $this,
                            "escape"
                    ), $str);
        } else {
            $output = $this->_connection->real_escape_string($str);
        }
        
        return $output;
    }

    /**
     * Get number of rows returned in result set
     *
     * {@inheritdoc}
     *
     * @see MysqliInterface::num_rows()
     */
    public function num_rows ()
    {
        return $this->_result->num_rows;
    }

    /**
     * Adds SELECT `field` portion of query
     *
     * {@inheritdoc}
     *
     * @see MysqliInterface::select()
     */
    public function select ($field = null, $escape = true)
    {
        /**
         * Put together beginning of SELECT query
         */
        if (empty($field)) {
            $this->_query = "SELECT *";
        } else 
            if (is_array($field)) {
                $this->_query = "SELECT `" . implode("`,`", $field) . "`";
            } else {
                $this->_query = "SELECT `{$field}`";
            }
        
        // Remove back ticks from query
        $this->_query = ($escape === false) ? str_replace("`", "", 
                $this->_query) : $this->_query;
        
        return $this;
    }

    /**
     * Create a custom SELECT statement
     *
     * @param unknown $select            
     * @return MysqliDB
     */
    public function selectCustom ($select = null)
    {
        if (empty($select)) {
            return $this;
        }
        
        // Add custom statement to the query string
        $this->_query .= "SELECT {$select}";
        
        return $this;
    }

    /**
     * Adds FROM `table` portion of query
     *
     * {@inheritdoc}
     *
     * @see MysqliInterface::from()
     */
    public function from ($tbl = null)
    {
        if (empty($tbl)) {
            trigger_error("MysqliDB::from() : Must be passed a table name", 
                    E_USER_ERROR);
        }
        
        $this->_query .= " FROM `{$tbl}`";
        
        return $this;
    }

    /**
     * Creates a WHERE condition
     *
     * {@inheritdoc}
     *
     * @see MysqliInterface::where()
     */
    public function where ($field = null, $value = null, $operator = null, $separator = null, 
            $escape = true)
    {
        if (empty($field)) {
            return $this;
        }
        
        // Set where variable
        $where = "";
        
        // Set default operator
        if (empty($operator)) {
            $operator = "=";
        }
        
        // Set default separator
        if (empty($separator)) {
            $separator = "AND";
        }
        
        /**
         * If the where count is less than 1
         * we do not want a separator
         */
        if ($this->_whereCount < 1) {
            $separator = "";
            $this->_query .= " WHERE ";
        }
        
        // Protect data if needed
        if (! is_int($value)) {
            // Escape the value
            $val = $this->escape($value);
            
            $where .= " {$separator} `{$field}` {$operator} '{$val}'";
        } else {
            $where .= " {$separator} `{$field}` {$operator} " . $value . "";
        }
        
        // Remove back ticks and single quotes from query
        $where = ($escape === false) ? str_replace(
                array(
                        "`",
                        "'"
                ), "", $where) : $where;
        
        // Append the contents of where to the query string
        $this->_query .= $where;
        
        // Increment where count
        $this->_whereCount ++;
        
        return $this;
    }

    /**
     * Create a custom where condition
     *
     * {@inheritdoc}
     *
     * @see MysqliInterface::whereCustom()
     */
    public function whereCustom ($where = null, $separator = null)
    {
        if (empty($where)) {
            return $this;
        }
        
        // Set default separator
        if (empty($separator)) {
            $separator = "AND";
        }
        
        /**
         * If the where count is less than 1
         * we do not want a separator
         */
        if ($this->_whereCount < 1) {
            $separator = "";
            $this->_query .= " WHERE ";
        }
        
        // Add custom condition to the query string
        $this->_query .= " {$separator} {$where}";
        
        // Increment where count
        $this->_whereCount ++;
        
        return $this;
    }

    /**
     * Lets you perform a WHERE condition by passing an array of key => value
     * pairs
     *
     * {@inheritdoc}
     *
     * @see MysqliInterface::whereByArray()
     */
    public function whereByArray ($data = null, $separator = null, $escape = true)
    {
        if (empty($data)) {
            return $this;
        }
        
        // Set default separator
        if (empty($separator)) {
            $separator = "AND";
        }
        //Set operator to empty which will default to =
        $operator = "";
        
        // Perform a WHERE condition using key => $val pairs
        foreach ($data as $key => $val) :
        /**
         * If the developer has passed a key that looks like this...
         * id != then let us handle that appropriately so no errors are thrown
         */
        if(preg_match('/[^><<=>>=<=!=<>]/',$val))
        {
            $operator = preg_replace("/[^><<=>>=<=!=<>]/","",$key);
            $key = preg_replace("/[^a-zA-Z\.]/","",$key);
        }
            $this->where($key, $val, $operator, $separator, $escape);
        endforeach
        ;
        
        return $this;
    }

    /**
     * Create a WHERE `field` IN(array) portion of a condition
     *
     * {@inheritdoc}
     *
     * @see MysqliInterface::whereIN()
     */
    public function whereIN ($field = null, $data = null, $separator = null)
    {
        if (empty($field) || empty($data)) {
            return $this;
        }
        
        // Set default separator
        if (empty($separator)) {
            $separator = "AND";
        }
        
        /**
         * If the where count is less than 1
         * we do not want a separator
         */
        if ($this->_whereCount < 1) {
            $separator = "";
            $this->_query .= " WHERE ";
        }
        
        //Make sure data is safe
        $data = $this->escape($data);
        
        // WHERE `field` IN(array) portion of query
        $this->_query .= " {$separator} `{$field}` IN('" . implode("','", $data) .
                 "')";
        
        // Increment where count
        $this->_whereCount ++;
        
        return $this;
    }

    /**
     * Create a WHERE `field` NOT IN(array) portion of a condition
     *
     * {@inheritdoc}
     *
     * @see MysqliInterface::whereNotIN()
     */
    public function whereNotIN ($field = null, $data = null, $separator = null)
    {
        if (empty($field) || empty($data)) {
            return $this;
        }
        
        // Set default separator
        if (empty($separator)) {
            $separator = "AND";
        }
        
        /**
         * If the where count is less than 1
         * we do not want a separator
         */
        if ($this->_whereCount < 1) {
            $separator = "";
            $this->_query .= " WHERE ";
        }
        
        //Make sure data is safe
        $data = $this->escape($data);
        
        // WHERE `field` NOT IN(array) portion of query
        $this->_query .= " {$separator} `{$field}` NOT IN('" .
                 implode("','", $data) . "')";
        
        // Increment where count
        $this->_whereCount ++;
        
        return $this;
    }

    /**
     * Create LIMIT Portion of query
     *
     * {@inheritdoc}
     *
     * @see MysqliInterface::limit()
     */
    public function limit ($limit = null, $offset = null)
    {
        if (empty($limit)) {
            return $this;
        }
        
        if (! empty($offset)) {
            $this->_query .= " LIMIT {$limit},{$offset}";
        } else {
            $this->_query .= " LIMIT {$limit}";
        }
        
        return $this;
    }

    /**
     * Create ORDER BY Portion of query
     *
     * {@inheritdoc}
     *
     * @see MysqliInterface::order_by()
     */
    public function order_by ($field = null, $sort = null)
    {
        if (empty($field) || empty($sort)) {
            return $this;
        }
        
        // Comma
        $comma = ",";
        
        if ($this->_orderByCount < 1) {
            $comma = "";
            $this->_query .= " ORDER BY ";
        }
        
        $this->_query .= "{$comma} `{$field}` {$sort}";
        
        // Increment order by count
        $this->_orderByCount ++;
        
        return $this;
    }

    /**
     * Run the query and reset everything
     *
     * {@inheritdoc}
     *
     * @see MysqliInterface::run()
     */
    public function run ()
    {
        // Reset where count back to 0
        $this->_whereCount = 0;
        // Reset order by count to 0
        $this->_orderByCount = 0;
        
        // Set result
        $this->_result = $this->rawQuery($this->_query);
        
        return $this->_result;
    }

    /**
     * Loop through a result set and return an array of data
     *
     * {@inheritdoc}
     *
     * @see MysqliInterface::result()
     */
    public function result ()
    {
        $result = [];
        
        // Loop through object data
        while ($row = $this->_result->fetch_object()) :
            $result[] = $row;
        endwhile
        ;
        
        return $result;
    }

    /**
     * Fetch single row
     *
     * {@inheritdoc}
     *
     * @see MysqliInterface::row()
     */
    public function row ()
    {
        return $this->_result->fetch_object();
    }

    /**
     * Can be used for debugging purposes shows the last executed query string
     *
     * {@inheritdoc}
     *
     * @see MysqliInterface::getLastQuery()
     */
    public function getLastQuery ()
    {
        return $this->_query;
    }
}
