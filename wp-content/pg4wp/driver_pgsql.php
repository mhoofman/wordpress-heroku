<?php
/**
 * @package PostgreSQL_For_Wordpress
 * @version $Id$
 * @author	Hawk__, www.hawkix.net
 */

/**
* Provides a driver for PostgreSQL
*
* This file maps original mysql_* functions with PostgreSQL equivalents
*
* This was originally based on usleepless's original 'mysql2pgsql.php' file, many thanks to him
*/
	// Check pgsql extension is loaded
	if ( !extension_loaded('pgsql') )
		wp_die( 'Your PHP installation appears to be missing the PostgreSQL extension which is required by WordPress with PG4WP.' );

	// Initializing some variables
	$GLOBALS['pg4wp_version'] = '7.0';
	$GLOBALS['pg4wp_result'] = 0;
	$GLOBALS['pg4wp_numrows_query'] = '';
	$GLOBALS['pg4wp_ins_table'] = '';
	$GLOBALS['pg4wp_ins_field'] = '';
	$GLOBALS['pg4wp_connstr'] = '';
	$GLOBALS['pg4wp_conn'] = false;
	
	function wpsql_num_rows($result)
		{ return pg_num_rows($result); }
	function wpsql_numrows($result)
		{ return pg_num_rows($result); }
	function wpsql_num_fields($result)
		{ return pg_num_fields($result); }
	function wpsql_fetch_field($result)
		{ return 'tablename'; }
	function wpsql_fetch_object($result)
		{ return pg_fetch_object($result); }
	function wpsql_free_result($result)
		{ return pg_free_result($result); }
	function wpsql_affected_rows()
	{
		if( $GLOBALS['pg4wp_result'] === false)
			return 0;
		else
			return pg_affected_rows($GLOBALS['pg4wp_result']);
	}
	function wpsql_fetch_row($result)
		{ return pg_fetch_row($result); }
	function wpsql_data_seek($result, $offset)
		{ return pg_result_seek ( $result, $offset ); }
	function wpsql_error()
		{ if( $GLOBALS['pg4wp_conn']) return pg_last_error(); else return ''; }
	function wpsql_fetch_assoc($result) { return pg_fetch_assoc($result); }
	function wpsql_escape_string($s) { return pg_escape_string($s); }
	function wpsql_get_server_info() { return '5.0.30'; } // Just want to fool wordpress ...
	function wpsql_result($result, $i, $fieldname)
		{ return pg_fetch_result($result, $i, $fieldname); }

	// This is a fake connection except during installation
	function wpsql_connect($dbserver, $dbuser, $dbpass)
	{
		$GLOBALS['pg4wp_connstr'] = '';
		if( !empty( $dbserver))
			$GLOBALS['pg4wp_connstr'] .= ' host='.$dbserver;
		if( !empty( $dbuser))
			$GLOBALS['pg4wp_connstr'] .= ' user='.$dbuser;
		if( !empty( $dbpass))
			$GLOBALS['pg4wp_connstr'] .= ' password='.$dbpass;
		elseif( !PG4WP_INSECURE)
			wp_die( 'Connecting to your PostgreSQL database without a password is considered insecure.
					<br />If you want to do it anyway, please set "PG4WP_INSECURE" to true in your "db.php" file.' );
		
		// While installing, we test the connection to 'template1' (as we don't know the effective dbname yet)
		if( defined('WP_INSTALLING') && WP_INSTALLING)
			return wpsql_select_db( 'template1');
		
		return 1;
	}
	
	// The effective connection happens here
	function wpsql_select_db($dbname, $connection_id = 0)
	{
		$pg_connstr = $GLOBALS['pg4wp_connstr'].' dbname='.$dbname;

		$GLOBALS['pg4wp_conn'] = pg_connect($pg_connstr);
		
		if( $GLOBALS['pg4wp_conn'])
		{
			$ver = pg_version($GLOBALS['pg4wp_conn']);
			if( isset($ver['server']))
				$GLOBALS['pg4wp_version'] = $ver['server'];
		}
		
		// Now we should be connected, we "forget" about the connection parameters (if this is not a "test" connection)
		if( !defined('WP_INSTALLING') || !WP_INSTALLING)
			$GLOBALS['pg4wp_connstr'] = '';
		
		// Execute early transmitted commands if needed
		if( isset($GLOBALS['pg4wp_pre_sql']) && !empty($GLOBALS['pg4wp_pre_sql']))
			foreach( $GLOBALS['pg4wp_pre_sql'] as $sql2run)
				wpsql_query( $sql2run);
		
		return $GLOBALS['pg4wp_conn'];
	}

	function wpsql_fetch_array($result)
	{
		$res = pg_fetch_array($result);
		
		if( is_array($res) )
		foreach($res as $v => $k )
			$res[$v] = trim($k);
		return $res;
	}
	
	function wpsql_query($sql)
	{
		if( !$GLOBALS['pg4wp_conn'])
		{
			// Catch SQL to be executed as soon as connected
			$GLOBALS['pg4wp_pre_sql'][] = $sql;
			return true;
		}
		
		$sql = pg4wp_rewrite( $sql);
		
		$GLOBALS['pg4wp_result'] = pg_query($sql);
		if( (PG4WP_DEBUG || PG4WP_LOG_ERRORS) && $GLOBALS['pg4wp_result'] === false && $err = pg_last_error())
			if( false === strpos($err, 'relation "'.$wpdb->options.'"'))
				error_log("Error running :\n$initial\n---- converted to ----\n$sql\n----\n$err\n---------------------\n", 3, PG4WP_LOG.'pg4wp_errors.log');
		
		return $GLOBALS['pg4wp_result'];
	}
	
	function wpsql_insert_id($table)
	{
		global $wpdb;
		$ins_field = $GLOBALS['pg4wp_ins_field'];
		
		$tbls = split("\n", $GLOBALS['pg4wp_ins_table']); // Workaround for bad tablename
		$t = $tbls[0] . '_seq';
		
		if( in_array( $t, array( '_seq', $wpdb->prefix.'term_relationships_seq')))
			return 0;
		
		if( $ins_field == '"cat_ID"' || $ins_field == 'rel_id' || $ins_field == 'term_id')
			$sql = "SELECT MAX($ins_field) FROM ".$tbls[0];
		else
			$sql = "SELECT CURRVAL('$t')";
		
		$res = pg_query($sql);
		$data = pg_fetch_result($res, 0, 0);
		if( PG4WP_DEBUG && $sql)
			error_log("Getting inserted ID for '$t' : $sql => $data\n", 3, PG4WP_LOG.'pg4wp_insertid.log');
		return $data;
	}
	
	function pg4wp_rewrite( $sql)
	{
		global $wpdb;
		
		$logto = 'queries';
		// The end of the query may be protected against changes
		$end = '';
		
		// Remove unusefull spaces
		$initial = $sql = trim($sql);
		
		if( 0 === strpos($sql, 'SELECT'))
		{
			$logto = 'SELECT';
			// SQL_CALC_FOUND_ROWS doesn't exist in PostgreSQL but it's needed for correct paging
			if( false !== strpos($sql, 'SQL_CALC_FOUND_ROWS'))
			{
				$sql = str_replace('SQL_CALC_FOUND_ROWS', '', $sql);
				$GLOBALS['pg4wp_numrows_query'] = $sql;
				if( PG4WP_DEBUG)
					error_log( "Number of rows required for :\n$sql\n---------------------\n", 3, PG4WP_LOG.'pg4wp_NUMROWS.log');
			}
			elseif( false !== strpos($sql, 'FOUND_ROWS()'))
			{
				// Here we convert the latest query into a COUNT query
				$sql = $GLOBALS['pg4wp_numrows_query'];
				// Remove any LIMIT ... clause (this is the blocking part)
				$pattern = '/\s+LIMIT.+/';
				$sql = preg_replace( $pattern, '', $sql);
				// Now add the COUNT() statement
				$pattern = '/SELECT\s+([^\s]+)\s+(FROM.+)/';
				$sql = preg_replace( $pattern, 'SELECT COUNT($1) $2', $sql);
			}
			
			// Handle COUNT(*)...ORDER BY...
			$sql = preg_replace( '/COUNT(.+)ORDER BY.+/', 'COUNT$1', $sql);
			
			// In order for users counting to work...
			$matches = array();
			if( preg_match_all( '/COUNT[^C]+\),/',$sql, $matches))
			{
				foreach( $matches[0] as $num => $one)
				{
					$sub = substr( $one, 0, -1);
					$sql = str_replace( $sub, $sub.' AS count'.$num, $sql);
				}
			}
			
			$pattern = '/LIMIT[ ]+(\d+),[ ]*(\d+)/';
			$sql = preg_replace($pattern, 'LIMIT $2 OFFSET $1', $sql);
			
			$pattern = '/DATE_ADD[ ]*\(([^,]+),([^\)]+)\)/';
			$sql = preg_replace( $pattern, '($1 + $2)', $sql);
			
			// UNIX_TIMESTAMP in MYSQL returns an integer
			$pattern = '/UNIX_TIMESTAMP\(([^\)]+)\)/';
			$sql = preg_replace( $pattern, 'ROUND(DATE_PART(\'epoch\',$1))', $sql);
			
			$date_funcs = array(
				'DAYOFMONTH('	=> 'EXTRACT(DAY FROM ',
				'YEAR('			=> 'EXTRACT(YEAR FROM ',
				'MONTH('		=> 'EXTRACT(MONTH FROM ',
				'DAY('			=> 'EXTRACT(DAY FROM ',
			);
			
			$sql = str_replace( 'ORDER BY post_date DESC', 'ORDER BY YEAR(post_date) DESC, MONTH(post_date) DESC', $sql);
			$sql = str_replace( 'ORDER BY post_date ASC', 'ORDER BY YEAR(post_date) ASC, MONTH(post_date) ASC', $sql);
			$sql = str_replace( array_keys($date_funcs), array_values($date_funcs), $sql);
			$curryear = date( 'Y');
			$sql = str_replace( 'FROM \''.$curryear, 'FROM TIMESTAMP \''.$curryear, $sql);
			
			// MySQL 'IF' conversion - Note : NULLIF doesn't need to be corrected
			$pattern = '/ (?<!NULL)IF[ ]*\(([^,]+),([^,]+),([^\)]+)\)/';
			$sql = preg_replace( $pattern, ' CASE WHEN $1 THEN $2 ELSE $3 END', $sql);
			
			$sql = str_replace('GROUP BY '.$wpdb->prefix.'posts.ID', '' , $sql);
			$sql = str_replace("!= ''", '<> 0', $sql);
			
			// MySQL 'LIKE' is case insensitive by default, whereas PostgreSQL 'LIKE' is
			$sql = str_replace( ' LIKE ', ' ILIKE ', $sql);
			
			// INDEXES are not yet supported
			if( false !== strpos( $sql, 'USE INDEX (comment_date_gmt)'))
				$sql = str_replace( 'USE INDEX (comment_date_gmt)', '', $sql);
			
			// HB : timestamp fix for permalinks
			$sql = str_replace( 'post_date_gmt > 1970', 'post_date_gmt > to_timestamp (\'1970\')', $sql);
			
			// Akismet sometimes doesn't write 'comment_ID' with 'ID' in capitals where needed ...
			if( false !== strpos( $sql, $wpdb->comments))
				$sql = str_replace(' comment_id ', ' comment_ID ', $sql);
			
		} // SELECT
		elseif( 0 === strpos($sql, 'UPDATE'))
		{
			$logto = 'UPDATE';
			$pattern = '/LIMIT[ ]+\d+/';
			$sql = preg_replace($pattern, '', $sql);
			
			// For correct ID quoting
			$pattern = '/[ ]*`([^`]*ID[^`]*)`[ ]*=/';
			$sql = preg_replace( $pattern, ' "$1" =', $sql);
			
			// For correct bactick removal
			$pattern = '/[ ]*`([^` ]+)`[ ]*=/';
			$sql = preg_replace( $pattern, ' $1 =', $sql);

			// WP 2.6.1 => 2.8 upgrade, removes a PostgreSQL error but there are some remaining
			$sql = str_replace( "post_date = '0000-00-00 00:00:00'", "post_date IS NULL", $sql);
			
			// This will avoid modifications to anything following ' SET '
			list($sql,$end) = explode( ' SET ', $sql, 2);
			$end = ' SET '.$end;
		} // UPDATE
		elseif( 0 === strpos($sql, 'INSERT'))
		{
			$logto = 'INSERT';
			$sql = str_replace('(0,',"('0',", $sql);
			$sql = str_replace('(1,',"('1',", $sql);
			
			// Fix inserts into wp_categories
			if( false !== strpos($sql, 'INSERT INTO '.$wpdb->categories))
			{
				$sql = str_replace('"cat_ID",', '', $sql);
				$sql = str_replace("VALUES ('0',", "VALUES(", $sql);
			}
			
			// Those are used when we need to set the date to now() in gmt time
			$sql = str_replace( "'0000-00-00 00:00:00'", 'now() AT TIME ZONE \'gmt\'', $sql);
			
			// Multiple values group when calling INSERT INTO don't always work
			if( false !== strpos( $sql, $wpdb->options) && false !== strpos( $sql, '), ('))
			{
				$pattern = '/INSERT INTO.+VALUES/';
				preg_match($pattern, $sql, $matches);
				$insert = $matches[0];
				$sql = str_replace( '), (', ');'.$insert.'(', $sql);
			}
			
			// Support for "INSERT ... ON DUPLICATE KEY UPDATE ..." is a dirty hack
			// consisting in deleting the row before inserting it
			if( false !== $pos = strpos( $sql, 'ON DUPLICATE KEY'))
			{
				// Remove 'ON DUPLICATE KEY UPDATE...' and following
				$sql = substr( $sql, 0, $pos);
				// Get the elements we need (table name, first field, corresponding value)
				$pattern = '/INSERT INTO\s+([^\(]+)\(([^,]+)[^\(]+VALUES\s*\(([^,]+)/';
				preg_match($pattern, $sql, $matches);
				$sql = 'DELETE FROM '.$matches[1].' WHERE '.$matches[2].' = '.$matches[3].';'.$sql;
			}
			
			// To avoid Encoding errors when inserting data coming from outside
			if( preg_match('/^.{1}/us',$sql,$ar) != 1)
				$sql = utf8_encode($sql);
			
			// This will avoid modifications to anything following ' VALUES'
			list($sql,$end) = explode( ' VALUES', $sql, 2);
			$end = ' VALUES'.$end;
		} // INSERT
		elseif( 0 === strpos( $sql, 'DELETE' ))
		{
			$logto = 'DELETE';
			// LIMIT is not allowed in DELETE queries
			$sql = str_replace( 'LIMIT 1', '', $sql);
			$sql = str_replace( ' REGEXP ', ' ~ ', $sql);
			
			// This handles removal of duplicate entries in table options
			if( false !== strpos( $sql, 'DELETE o1 FROM '))
				$sql = "DELETE FROM $wpdb->options WHERE option_id IN " .
					"(SELECT o1.option_id FROM $wpdb->options AS o1, $wpdb->options AS o2 " .
					"WHERE o1.option_name = o2.option_name " .
					"AND o1.option_id < o2.option_id)";
			
			// Akismet sometimes doesn't write 'comment_ID' with 'ID' in capitals where needed ...
			if( false !== strpos( $sql, $wpdb->comments))
				$sql = str_replace(' comment_id ', ' comment_ID ', $sql);
		}
		// Fix tables listing
		elseif( 0 === strpos($sql, 'SHOW TABLES'))
		{
			$logto = 'SHOWTABLES';
			$sql = 'SELECT tablename FROM pg_tables WHERE schemaname = \'public\';';
		}
		// Rewriting optimize table
		elseif( 0 === strpos($sql, 'OPTIMIZE TABLE'))
		{
			$logto = 'OPTIMIZE';
			$sql = str_replace( 'OPTIMIZE TABLE', 'VACUUM', $sql);
		}
		// Handle 'SET NAMES ... COLLATE ...'
		elseif( false !== strpos($sql, 'COLLATE'))
		{
			$logto = 'SETNAMES';
			$sql = "SET NAMES 'utf8'";
		}
		// Load up upgrade and install functions as required
		$begin = substr( $sql, 0, 3);
		$search = array( 'SHO', 'ALT', 'DES', 'CRE');
		if( in_array($begin, $search))
		{
			require_once( PG4WP_ROOT.'/driver_pgsql_install.php');
			$sql = pg4wp_installing( $sql, $logto);
		}
		
		// WP 2.9.1 uses a comparison where text data is not quoted
		$pattern = '/AND meta_value = (-?\d+)/';
		$sql = preg_replace( $pattern, 'AND meta_value = \'$1\'', $sql);
		
		// Generic "INTERVAL xx YEAR|MONTH|DAY|HOUR|MINUTE|SECOND" handler
		$pattern = '/INTERVAL[ ]+(\d+)[ ]+(YEAR|MONTH|DAY|HOUR|MINUTE|SECOND)/';
		$sql = preg_replace( $pattern, "'\$1 \$2'::interval", $sql);
		$pattern = '/DATE_SUB[ ]*\(([^,]+),([^\)]+)\)/';
		$sql = preg_replace( $pattern, '($1::timestamp - $2)', $sql);
		
		// Remove illegal characters
		$sql = str_replace('`', '', $sql);
		
		// Field names with CAPITALS need special handling
		if( false !== strpos($sql, 'ID'))
		{
			$pattern = '/ID([^ ])/';
				$sql = preg_replace($pattern, 'ID $1', $sql);
			$pattern = '/ID$/';
				$sql = preg_replace($pattern, 'ID ', $sql);
			$pattern = '/\(ID/';
				$sql = preg_replace($pattern, '( ID', $sql);
			$pattern = '/,ID/';
				$sql = preg_replace($pattern, ', ID', $sql);
			$pattern = '/[a-zA-Z_]+ID/';
				$sql = preg_replace($pattern, '"$0"', $sql);
			$pattern = '/\.ID/';
				$sql = preg_replace($pattern, '."ID"', $sql);
			$pattern = '/[\s]ID /';
				$sql = preg_replace($pattern, ' "ID" ', $sql);
			$pattern = '/"ID "/';
				$sql = preg_replace($pattern, ' "ID" ', $sql);
		} // CAPITALS
		
		// Empty "IN" statements are erroneous
		$sql = str_replace( 'IN (\'\')', 'IN (NULL)', $sql);
		$sql = str_replace( 'IN ( \'\' )', 'IN (NULL)', $sql);
		$sql = str_replace( 'IN ()', 'IN (NULL)', $sql);
		
		// For insert ID catching
		if( $logto == 'INSERT')
		{
			$pattern = '/INSERT INTO (\w+)\s+\([ a-zA-Z_"]+/';
			preg_match($pattern, $sql, $matches);
			$GLOBALS['pg4wp_ins_table'] = $matches[1];
			$match_list = split(' ', $matches[0]);
			if( $GLOBALS['pg4wp_ins_table'])
			{
				$GLOBALS['pg4wp_ins_field'] = trim($match_list[3],' ()	');
				if(! $GLOBALS['pg4wp_ins_field'])
					$GLOBALS['pg4wp_ins_field'] = trim($match_list[4],' ()	');
			}
		}
		
		// Put back the end of the query if it was separated
		$sql .= $end;
		
		if( PG4WP_DEBUG)
		{
			if( $initial != $sql)
				error_log("Converting :\n$initial\n---- to ----\n$sql\n---------------------\n", 3, PG4WP_LOG.'pg4wp_'.$logto.'.log');
			else
				error_log("$sql\n---------------------\n", 3, PG4WP_LOG.'pg4wp_unmodified.log');
		}
		return $sql;
	}
