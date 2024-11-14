<?php

namespace iamcal;

class SQLParserSyntaxException extends \Exception { }

class SQLParser{

	#
	# the main public interface is very simple
	#

	public $tokens = array();
	public $tables = array();
	public $source_map = array();

	public $find_single_table = false;
	public $throw_on_bad_syntax = false;

	public $sql;

	public function parse($sql){

		// stashes tokens and source_map in $this
		$this->lex($sql);
		$ret = $this->walk($this->tokens, $sql, $this->source_map);

		$this->tables = $ret['tables'];
		return $this->tables;
	}

	#
	# lex and collapse tokens
	#
	public function lex($sql) {
		$this->sql = $sql;
		$this->source_map = $this->_lex($this->sql);
		$this->tokens = $this->_extract_tokens($this->sql, $this->source_map);
		return $this->tokens;
	}

	#
	# simple lexer based on http://www.contrib.andrew.cmu.edu/~shadow/sql/sql1992.txt
	#
	# returns an array of [position, len] tuples for each token

	private function _lex($sql){

		$pos = 0;
		$len = strlen($sql);

		$source_map = array();

		while ($pos < $len){

			# <space>
			# <newline>
			if (preg_match('!\s+!A', $sql, $m, 0, $pos)){
				$pos += strlen($m[0]);
				continue;
			}

			# <comment>
			if (preg_match('!--!A', $sql, $m, 0, $pos)){
				$p2 = strpos($sql, "\n", $pos);
				if ($p2 === false){
					if ($this->throw_on_bad_syntax) throw new SQLParserSyntaxException("Unterminated comment at position $pos");
					$pos = $len;
				}else{
					$pos = $p2+1;
				}
				continue;
			}
			if (preg_match('!/\\*!A', $sql, $m, 0, $pos)){
				$p2 = strpos($sql, "*/", $pos);
				if ($p2 === false){
					if ($this->throw_on_bad_syntax) throw new SQLParserSyntaxException("Unterminated comment at position $pos");
					$pos = $len;
				}else{
					$pos = $p2+2;
				}
				continue;
			}


			# <regular identifier>
			# <key word>
			if (preg_match('![[:alpha:]][[:alnum:]_]*!A', $sql, $m, 0, $pos)){
				$source_map[] = array($pos, strlen($m[0]));
				$pos += strlen($m[0]);
				continue;
			}

			# backtick quoted field
			if (substr($sql, $pos, 1) == '`'){
				$p2 = strpos($sql, "`", $pos+1);
				if ($p2 === false){
					if ($this->throw_on_bad_syntax) throw new SQLParserSyntaxException("Unterminated backtick at position $pos");
					$pos = $len;
				}else{
					$source_map[] = array($pos, 1+$p2-$pos);
					$pos = $p2+1;
				}
				continue;
			}

			# <unsigned numeric literal>
			#	<unsigned integer> [ <period> [ <unsigned integer> ] ]
			#	<period> <unsigned integer>
			#	<unsigned integer> ::= <digit>...
			if (preg_match('!(\d+\.?\d*|\.\d+)!A', $sql, $m, 0, $pos)){
				$source_map[] = array($pos, strlen($m[0]));
				$pos += strlen($m[0]);
				continue;
			}

			# <approximate numeric literal> :: <mantissa> E <exponent>
			# <national character string literal>
			# <bit string literal>
			# <hex string literal>

			# <character string literal>
			if ($sql[$pos] == "'" || $sql[$pos] == '"'){
				$str_start_pos = $pos;
				$c = $pos+1;
				$q = $sql[$pos];
				while ($c < strlen($sql)){
					if ($sql[$c] == '\\'){
						$c += 2;
						continue;
					}
					if ($sql[$c] == $q){
						$slen = $c + 1 - $pos;
						$source_map[] = array($pos, $slen);
						$pos += $slen;
						break;
					}
					$c++;
				}
				if ($c >= strlen($sql)){
					if ($this->throw_on_bad_syntax) throw new SQLParserSyntaxException("Unterminated string at position $str_start_pos");
					$pos = $len;
				}
				continue;
			}

			# <date string>
			# <time string>
			# <timestamp string>
			# <interval string>
			# <delimited identifier>
			# <SQL special character>
			# <not equals operator>
			# <greater than or equals operator>
			# <less than or equals operator>
			# <concatenation operator>
			# <double period>
			# <left bracket>
			# <right bracket>
			$source_map[] = array($pos, 1);
			$pos++;
		}

		return $source_map;
	}


	function walk($tokens, $sql, $source_map){


		#
		# split into statements
		#

		$statements = array();
		$temp = array();
		$start = 0;
		for ($i = 0; $i < count($tokens); $i++) {
			$t = $tokens[$i];
			if ($t == ';'){
				if (count($temp)) {
					$statements[] = array(
						"tuples" => $temp,
						"sql" => substr($sql, $source_map[$start][0], $source_map[$i][0] - $source_map[$start][0] + $source_map[$i][1]),
					);
				}
				$temp = array();
				$start = $i + 1;
			}else{
				$temp[] = $t;
			}
		}
		if (count($temp)) {
			$source_map_start_0 = (isset($source_map[$start]) && isset($source_map[$start][0])) ? $source_map[$start][0] : null;
			$source_map_start_i_0 = (isset($source_map[$i]) && isset($source_map[$i][0])) ? $source_map[$i][0] : null;
			$source_map_start_i_1 = (isset($source_map[$i]) && isset($source_map[$i][1])) ? $source_map[$i][1] : null;
			$statements[] = array(
				"tuples" => $temp,
				"sql" => substr($sql, $source_map_start_0, $source_map_start_i_0 - $source_map_start_0 + $source_map_start_i_1),
			);
		}

		#
		# find CREATE TABLE statements
		#

		$tables = array();

		foreach ($statements as $stmt){
			$s = $stmt['tuples'];

			if (StrToUpper($s[0]) == 'CREATE TABLE'){

				$table = $this->parse_create_table($s, 1, count($s));
				$table['sql'] = $stmt['sql'];
				$tables[$this->generateTableKey($table)] = $table;
			}

			if (StrToUpper($s[0]) == 'CREATE TEMPORARY TABLE'){

				$table = $this->parse_create_table($s, 1, count($s));
				$table['props']['temporary'] = true;
				$tables[$this->generateTableKey($table)] = $table;
				$table['sql'] = $stmt['sql'];
			}

			if ($this->find_single_table && count($tables)) return array(
				'tables' => $tables,
			);
		}

		return array(
			'tables' => $tables,
		);
	}

	private function generateTableKey(array $table){
		if (!is_null($table['database'])){
			return $table['database'] . '.' . $table['name'];
		}else{
			return $table['name'];
		}
	}

	function parse_create_table($tokens, $i, $num){

		if ($tokens[$i] == 'IF NOT EXISTS'){
			$i++;
		}


		#
		# name
		#

		$database = null;
		$name = $this->decode_identifier($tokens[$i++]);

		if (isset($tokens[$i]) && $tokens[$i] === '.'){
			$i++;
			$database = $name;
			$name = $this->decode_identifier($tokens[$i++]);
		}


		#
		# CREATE TABLE x LIKE y
		#

		if ($this->next_tokens($tokens, $i, 'LIKE')){
			$i++;
			$old_name = $this->decode_identifier($tokens[$i++]);

			$like_database = null;
			if (isset($tokens[$i]) && $tokens[$i] === '.'){
				$i++;
				$like_database = $old_name;
				$old_name = $this->decode_identifier($tokens[$i++]);
			}

			return array(
				'name'	=> $name,
				'database' => $database,
				'like'	=> $old_name,
				'like_database' => $like_database,
			);
		}


		#
		# create_definition
		#

		$fields = array();
		$indexes = array();

		if ($this->next_tokens($tokens, $i, '(')){
			$i++;
			$ret = $this->parse_create_definition($tokens, $i);
			$fields = $ret['fields'];
			$indexes = $ret['indexes'];
		}

		$props = $this->parse_table_props($tokens, $i);

		$table = array(
			'name'		=> $name,
			'database'	=> $database,
			'fields'	=> $fields,
			'indexes'	=> $indexes,
			'props'		=> $props,
		);

		if ($i <= count($tokens)) $table['more'] = array_slice($tokens, $i);

		return $table;
	}


	function next_tokens($tokens, $i){

		$args = func_get_args();
		array_shift($args);
		array_shift($args);

		foreach ($args as $v){
			if ($i >= count($tokens) ) return false;
			if (StrToUpper($tokens[$i]) != $v) return false;
			$i++;
		}
		return true;
	}

	function parse_create_definition($tokens, &$i){

		$fields = array();
		$indexes = array();

		while ($i < count($tokens) && $tokens[$i] != ')'){

			$these_tokens = $this->slice_until_next_field($tokens, $i);

			$this->parse_field_or_key($these_tokens, $fields, $indexes);
		}

		$i++;

		return array(
			'fields'	=> $fields,
			'indexes'	=> $indexes,
		);
	}

	function parse_field_or_key(&$tokens, &$fields, &$indexes){

		#
		# parse a single create_definition
		#

		$has_constraint = false;
		$constraint = null;


		#
		# constraints can come before a few different things
		#

		if ($tokens[0] == 'CONSTRAINT'){

			$has_constraint = true;

			if ($tokens[1] == 'PRIMARY KEY'
				|| $tokens[1] == 'UNIQUE'
				|| $tokens[1] == 'UNIQUE KEY'
				|| $tokens[1] == 'UNIQUE INDEX'
				|| $tokens[1] == 'FOREIGN KEY'){
				array_shift($tokens);
			}else{
				array_shift($tokens);
				$constraint = $this->decode_identifier(array_shift($tokens));
			}
		}


		switch ($tokens[0]){

			#
			# named indexes
			#
			# INDEX		[index_name]	[index_type] (index_col_name,...) [index_option] ...
			# KEY		[index_name]	[index_type] (index_col_name,...) [index_option] ...
			# UNIQUE	[index_name]	[index_type] (index_col_name,...) [index_option] ...
			# UNIQUE INDEX	[index_name]	[index_type] (index_col_name,...) [index_option] ...
			# UNIQUE KEY	[index_name]	[index_type] (index_col_name,...) [index_option] ...
			#

			case 'INDEX':
			case 'KEY':
			case 'UNIQUE':
			case 'UNIQUE INDEX':
			case 'UNIQUE KEY':

				$index = array(
					'type' => 'INDEX',
				);

				if ($has_constraint){
					$index['constraint'] = true;
					if (!is_null($constraint)) $index['constraint_name'] = $constraint;
				}

				if ($tokens[0] == 'UNIQUE'	) $index['type'] = 'UNIQUE';
				if ($tokens[0] == 'UNIQUE INDEX') $index['type'] = 'UNIQUE';
				if ($tokens[0] == 'UNIQUE KEY'	) $index['type'] = 'UNIQUE';

				array_shift($tokens);

				if ($tokens[0] != '(' && $tokens[0] != 'USING BTREE' && $tokens[0] != 'USING HASH'){
					$index['name'] = $this->decode_identifier(array_shift($tokens));
				}

				$this->parse_index_mode($tokens, $index);
				$this->parse_index_columns($tokens, $index);
				$this->parse_index_options($tokens, $index);


				if (count($tokens)) $index['more'] = $tokens;
				$indexes[] = $index;
				return;


			#
			# PRIMARY KEY [index_type] (index_col_name,...) [index_option] ...
			#

			case 'PRIMARY KEY':

				$index = array(
					'type'	=> 'PRIMARY',
				);

				if ($has_constraint){
					$index['constraint'] = true;
					if (!is_null($constraint)) $index['constraint_name'] = $constraint;
				}

				array_shift($tokens);

				$this->parse_index_mode($tokens, $index);
				$this->parse_index_columns($tokens, $index);
				$this->parse_index_options($tokens, $index);

				if (count($tokens)) $index['more'] = $tokens;
				$indexes[] = $index;
				return;


			# FULLTEXT		[index_name] (index_col_name,...) [index_option] ...
			# FULLTEXT INDEX	[index_name] (index_col_name,...) [index_option] ...
			# FULLTEXT KEY		[index_name] (index_col_name,...) [index_option] ...
			# SPATIAL		[index_name] (index_col_name,...) [index_option] ...
			# SPATIAL INDEX		[index_name] (index_col_name,...) [index_option] ...
			# SPATIAL KEY		[index_name] (index_col_name,...) [index_option] ...

			case 'FULLTEXT':
			case 'FULLTEXT INDEX':
			case 'FULLTEXT KEY':
			case 'SPATIAL':
			case 'SPATIAL INDEX':
			case 'SPATIAL KEY':

				$index = array(
					'type' => 'FULLTEXT',
				);

				if ($tokens[0] == 'SPATIAL'	) $index['type'] = 'SPATIAL';
				if ($tokens[0] == 'SPATIAL INDEX') $index['type'] = 'SPATIAL';
				if ($tokens[0] == 'SPATIAL KEY'	) $index['type'] = 'SPATIAL';

				array_shift($tokens);

				if ($tokens[0] != '('){
					$index['name'] = $this->decode_identifier(array_shift($tokens));
				}

				$this->parse_index_mode($tokens, $index);
				$this->parse_index_columns($tokens, $index);
				$this->parse_index_options($tokens, $index);

				if (count($tokens)) $index['more'] = $tokens;
				$indexes[] = $index;
				return;


			# FOREIGN KEY [index_name] (index_col_name,...) reference_definition
			#  reference_definition:
			#    REFERENCES tbl_name (index_col_name,...)
			#      [MATCH FULL | MATCH PARTIAL | MATCH SIMPLE]
			#      [ON DELETE reference_option]
			#      [ON UPDATE reference_option]

			case 'FOREIGN KEY':

				$index = array(
					'type' => 'FOREIGN',
				);

				array_shift($tokens);

				if ($tokens[0] != '('){
					$index['name'] = $this->decode_identifier(array_shift($tokens));
				}

				$this->parse_index_columns($tokens, $index);

				if ($tokens[0] == 'REFERENCES'){
					array_shift($tokens);
					$index['ref_table'] = $this->decode_identifier(array_shift($tokens));

					$old_cols = $index['cols'];
					$index['cols'] = array();
					$this->parse_index_columns($tokens, $index);
					$index['ref_cols'] = $index['cols'];
					$index['cols'] = $old_cols;

					if (count($tokens) >= 1 && $tokens[0] == 'MATCH FULL'   ){ $index['ref_match'] = 'FULL'   ; array_shift($tokens); }
					if (count($tokens) >= 1 && $tokens[0] == 'MATCH PARTIAL'){ $index['ref_match'] = 'PARTIAL'; array_shift($tokens); }
					if (count($tokens) >= 1 && $tokens[0] == 'MATCH SIMPLE' ){ $index['ref_match'] = 'SIMPLE' ; array_shift($tokens); }

					if (count($tokens) > 1 && $tokens[0] == 'ON DELETE'){ array_shift($tokens); $index['ref_on_delete'] = array_shift($tokens); }
					if (count($tokens) > 1 && $tokens[0] == 'ON UPDATE'){ array_shift($tokens); $index['ref_on_delete'] = array_shift($tokens); }
				}

				if (count($tokens)) $index['more'] = $tokens;
				$indexes[] = $index;
				return;

			case 'CHECK':

				$indexes[] = array(
					'type'		=> 'CHECK',
					'tokens'	=> array_slice($tokens, 1),
				);
				return;
		}

		$fields[] = $this->parse_field($tokens);
	}

	function slice_until_next_field($tokens, &$i){

		$out = array();
		$stack = 0;

		while ($i < count($tokens)){
			$next = $tokens[$i];
			if ($next == '('){
				$stack++;
				$out[] = $tokens[$i++];
			}elseif ($next == ')'){
				if ($stack){
					$stack--;
					$out[] = $tokens[$i++];
				}else{
					return $out;
				}
			}elseif ($next == ','){
				if ($stack){
					$out[] = $tokens[$i++];
				}else{
					$i++;
					return $out;
				}
			}else{
				$out[] = $tokens[$i++];
			}
		}

		return $out;
	}

	function parse_field($tokens){

		$f = array(
			'name'	=> $this->decode_identifier(array_shift($tokens)),
			'type'	=> StrToUpper(array_shift($tokens)),
		);

		switch ($f['type']){

			# DATE
			case 'DATE':
			case 'YEAR':
			case 'TINYBLOB':
			case 'BLOB':
			case 'MEDIUMBLOB':
			case 'LONGBLOB':
			case 'JSON':
			case 'GEOMETRY':
			case 'POINT':
			case 'LINESTRING':
			case 'POLYGON':
			case 'MULTIPOINT':
			case 'MULTILINESTRING':
			case 'MULTIPOLYGON':
			case 'GEOMETRYCOLLECTION':
			case 'BOOLEAN':
			case 'BOOL':

				# nothing more to read
				break;


			# TIME[(fsp)]
			case 'TIME':
			case 'TIMESTAMP':
			case 'DATETIME':

				# optional fractional seconds precision
				if (count($tokens) >= 3){
					if ($tokens[0] == '(' && $tokens[2] == ')'){
						$f['fsp'] = $tokens[1];
						array_shift($tokens);
						array_shift($tokens);
						array_shift($tokens);
					}
				}
				break;


			# TINYINT[(length)] [UNSIGNED] [ZEROFILL]
			case 'TINYINT':
			case 'SMALLINT':
			case 'MEDIUMINT':
			case 'INT':
			case 'INTEGER':
			case 'BIGINT':

				$this->parse_field_length($tokens, $f);
				$this->parse_field_unsigned($tokens, $f);
				$this->parse_field_zerofill($tokens, $f);
				break;


			# REAL[(length,decimals)] [UNSIGNED] [ZEROFILL]
			case 'REAL':
			case 'DOUBLE':
			case 'DOUBLE PRECISION':
			case 'FLOAT':

				$this->parse_field_length_decimals($tokens, $f);
				$this->parse_field_unsigned($tokens, $f);
				$this->parse_field_zerofill($tokens, $f);
				break;


			# DECIMAL[(length[,decimals])] [UNSIGNED] [ZEROFILL]
			case 'DECIMAL':
			case 'NUMERIC':
			case 'DEC':
			case 'FIXED':

				$this->parse_field_length_decimals($tokens, $f);
				$this->parse_field_length($tokens, $f);
				$this->parse_field_unsigned($tokens, $f);
				$this->parse_field_zerofill($tokens, $f);
				break;


			# BIT[(length)]
			# BINARY[(length)]
			case 'BIT':
			case 'BINARY':

				$this->parse_field_length($tokens, $f);
				break;


			# VARBINARY(length)
			case 'VARBINARY':

				$this->parse_field_length($tokens, $f);
				break;

			# CHAR[(length)] [BINARY] [CHARACTER SET charset_name] [COLLATE collation_name]
			case 'CHAR':

				$this->parse_field_binary($tokens, $f);
				$this->parse_field_length($tokens, $f);
				$this->parse_field_charset($tokens, $f);
				$this->parse_field_collate($tokens, $f);
				break;

			# VARCHAR(length) [BINARY] [CHARACTER SET charset_name] [COLLATE collation_name]
			case 'VARCHAR':
			case 'CHARACTER VARYING':

				$this->parse_field_binary($tokens, $f);
				$this->parse_field_length($tokens, $f);
				$this->parse_field_charset($tokens, $f);
				$this->parse_field_collate($tokens, $f);
				break;

			# TINYTEXT   [BINARY] [CHARACTER SET charset_name] [COLLATE collation_name]
			# TEXT       [BINARY] [CHARACTER SET charset_name] [COLLATE collation_name]
			# MEDIUMTEXT [BINARY] [CHARACTER SET charset_name] [COLLATE collation_name]
			# LONGTEXT   [BINARY] [CHARACTER SET charset_name] [COLLATE collation_name]
			case 'TINYTEXT':
			case 'TEXT':
			case 'MEDIUMTEXT':
			case 'LONGTEXT':
			case 'JSON':

				$this->parse_field_binary($tokens, $f);
				$this->parse_field_charset($tokens, $f);
				$this->parse_field_collate($tokens, $f);
				break;

			# ENUM(value1,value2,value3,...) [CHARACTER SET charset_name] [COLLATE collation_name]
			# SET (value1,value2,value3,...) [CHARACTER SET charset_name] [COLLATE collation_name]
			case 'ENUM':
			case 'SET':

				$f['values'] = $this->parse_value_list($tokens);
				$this->parse_field_charset($tokens, $f);
				$this->parse_field_collate($tokens, $f);
				break;

			default:
				die("Unsupported field type: {$f['type']}");
		}


		# [NOT NULL | NULL]
		if (count($tokens) >= 1 && StrToUpper($tokens[0]) == 'NOT NULL'){
			$f['null'] = false;
			array_shift($tokens);
		}
		if (count($tokens) >= 1 && StrToUpper($tokens[0]) == 'NULL'){
			$f['null'] = true;
			array_shift($tokens);
		}

		# [DEFAULT default_value]
		if (count($tokens) >= 1 && StrToUpper($tokens[0]) == 'DEFAULT'){
			$f['default'] = $this->decode_value($tokens[1]);
			if ($f['default'] === 'NULL'){
				$f['null'] = true;
			}

			array_shift($tokens);
			array_shift($tokens);
		}

		# [AUTO_INCREMENT]
		if (count($tokens) >= 1 && StrToUpper($tokens[0]) == 'AUTO_INCREMENT'){
			$f['auto_increment'] = true;
			array_shift($tokens);
		}

		# [UNIQUE [KEY] | [PRIMARY] KEY]
		# [COMMENT 'string']
		# [COLUMN_FORMAT {FIXED|DYNAMIC|DEFAULT}]
		# [STORAGE {DISK|MEMORY|DEFAULT}]
		# [reference_definition]

		if (count($tokens)) $f['more'] = $tokens;

		return $f;
	}

	function parse_table_props($tokens, &$i){

		$alt_names = array(
			'CHARACTER SET'		=> 'CHARSET',
			'DEFAULT CHARACTER SET'	=> 'CHARSET',
			'DEFAULT CHARSET'	=> 'CHARSET',
			'DEFAULT COLLATE'	=> 'COLLATE',
		);

		$props = array();

		while ($i < count($tokens)){

		switch (StrToUpper($tokens[$i])){
			case 'ENGINE':
			case 'AUTO_INCREMENT':
			case 'AVG_ROW_LENGTH':
			case 'CHECKSUM':
			case 'COMMENT':
			case 'CONNECTION':
			case 'DELAY_KEY_WRITE':
			case 'INSERT_METHOD':
			case 'KEY_BLOCK_SIZE':
			case 'MAX_ROWS':
			case 'MIN_ROWS':
			case 'PACK_KEYS':
			case 'PASSWORD':
			case 'ROW_FORMAT':
			case 'COLLATE':
			case 'CHARSET':
			case 'DATA DIRECTORY':
			case 'INDEX DIRECTORY':
				$prop = StrToUpper($tokens[$i++]);
				if (isset($tokens[$i]) && $tokens[$i] == '=') $i++;
				$props[$prop] = $tokens[$i++];
				if (isset($tokens[$i]) && $tokens[$i] == ',') $i++;
				break;

			case 'CHARACTER SET':
			case 'DEFAULT COLLATE':
			case 'DEFAULT CHARACTER SET':
			case 'DEFAULT CHARSET':
				$prop = $alt_names[StrToUpper($tokens[$i++])];
				if (isset($tokens[$i]) && $tokens[$i] == '=') $i++;
				$props[$prop] = $tokens[$i++];
				if (isset($tokens[$i]) && $tokens[$i] == ',') $i++;
				break;

			default:
				break 2;
		}
		}

		return $props;
	}


	# Given the source map, extract the tokens from the original sql,
	# Along the way, simplify parsing by merging certain tokens when
	# they occur next to each other. MySQL treats these productions
	# equally: 'UNIQUE|UNIQUE INDEX|UNIQUE KEY' and if they are
	# all always a single token it makes parsing easier.

	function _extract_tokens($sql, &$source_map){
		$lists = array(
			'FULLTEXT INDEX',
			'FULLTEXT KEY',
			'SPATIAL INDEX',
			'SPATIAL KEY',
			'FOREIGN KEY',
			'USING BTREE',
			'USING HASH',
			'PRIMARY KEY',
			'UNIQUE INDEX',
			'UNIQUE KEY',
			'CREATE TABLE',
			'CREATE TEMPORARY TABLE',
			'DATA DIRECTORY',
			'INDEX DIRECTORY',
			'DEFAULT CHARACTER SET',
			'CHARACTER SET',
			'DEFAULT CHARSET',
			'DEFAULT COLLATE',
			'IF NOT EXISTS',
			'NOT NULL',
			'WITH PARSER',
			'MATCH FULL',
			'MATCH PARTIAL',
			'MATCH SIMPLE',
			'ON DELETE',
			'ON UPDATE',
			'SET NULL',
			'NO ACTION',
			'SET DEFAULT',
			'DOUBLE PRECISION',
			'CHARACTER VARYING',
		);

		$singles = array(
			'NULL',
			'CONSTRAINT',
			'INDEX',
			'KEY',
			'UNIQUE',
		);


		$maps = array();
		foreach ($lists as $l){
			$a = explode(' ', $l);
			$maps[$a[0]][] = $a;
		}
		$smap = array();
		foreach ($singles as $s) $smap[$s] = 1;

		$out = array();
		$out_map = array();

		$i = 0;
		$len = count($source_map);
		while ($i < $len){
			$token = substr($sql, $source_map[$i][0], $source_map[$i][1]);
			$tokenUpper = StrToUpper($token);
			if (isset($maps[$tokenUpper]) && is_array($maps[$tokenUpper])){
				$found = false;
				foreach ($maps[$tokenUpper] as $list){
					$fail = false;
					foreach ($list as $k => $v){
						$next = StrToUpper(substr($sql, $source_map[$k+$i][0], $source_map[$k+$i][1]));
						if ($v != $next){
							$fail = true;
							break;
						}
					}
					if (!$fail){
						$out[] = implode(' ', $list);

						# Extend the length of the first token to include everything
						# up through the last in the sequence.
						$j = $i + count($list) - 1;
						$out_map[] = array($source_map[$i][0], ($source_map[$j][0] - $source_map[$i][0]) + $source_map[$j][1]);

						$i = $j + 1;
						$found = true;
						break;
					}
				}
				if ($found) continue;
			}
			if (isset($smap[$tokenUpper])){
				$out[] = $tokenUpper;
				$out_map[]= $source_map[$i];
				$i++;
				continue;
			}
			$out[] = $token;
			$out_map[]= $source_map[$i];
			$i++;
		}

		$source_map = $out_map;
		return $out;
	}

	function parse_index_mode(&$tokens, &$index){
		if (count($tokens) >= 1){
			if ($tokens[0] == 'USING BTREE'){ $index['mode'] = 'BTREE'; array_shift($tokens); return; }
			if ($tokens[0] == 'USING HASH' ){ $index['mode'] = 'HASH'; array_shift($tokens); return; }
		}
	}

	function parse_index_columns(&$tokens, &$index){

		# col_name [(length)] [ASC | DESC]

		if ($tokens[0] != '(') return;
		array_shift($tokens);

		while (true){

			$col = array(
				'name' => $this->decode_identifier(array_shift($tokens)),
			);

			if ($tokens[0] == '(' && $tokens[2] == ')'){
				$col['length'] = $tokens[1];
				array_shift($tokens);
				array_shift($tokens);
				array_shift($tokens);
			}

			if (StrToUpper($tokens[0]) == 'ASC'){
				$col['direction'] = 'ASC';
				array_shift($tokens);
			}elseif (StrToUpper($tokens[0]) == 'DESC'){
				$col['direction'] = 'DESC';
				array_shift($tokens);
			}

			$index['cols'][] = $col;

			if ($tokens[0] == ')'){
				array_shift($tokens);
				return;
			}

			if ($tokens[0] == ','){
				array_shift($tokens);
				continue;
			}

			# hmm, an unexpected token
			return;
		}
	}

	function parse_index_options(&$tokens, &$index){

		# index_option:
		#    KEY_BLOCK_SIZE [=] value
		#  | index_type
		#  | WITH PARSER parser_name
		#  | COMMENT 'string'

		while (count($tokens) >= 1){

			if ($tokens[0] == 'KEY_BLOCK_SIZE'){
				array_shift($tokens);
				if ($tokens[0] == '=') array_shift($tokens);
				$index['key_block_size'] = $tokens[0];
				array_shift($tokens);
				continue;
			}

			if ($tokens[0] == 'WITH PARSER'){
				$index['parser'] = $tokens[1];
				array_shift($tokens);
				array_shift($tokens);
				continue;
			}

			if ($tokens[0] == 'COMMENT'){
				$index['comment'] = $this->decode_value($tokens[1]);
				array_shift($tokens);
				array_shift($tokens);
				continue;
			}

			if (!isset($index['mode'])){
				$this->parse_index_mode($tokens, $index);
				if (isset($index['mode'])) continue;
			}

			break;
		}
	}


	#
	# helper functions for parsing bits of field definitions
	#

	function parse_field_length(&$tokens, &$f){
		if (count($tokens) >= 3){
			if ($tokens[0] == '(' && $tokens[2] == ')'){
				$f['length'] = $tokens[1];
				array_shift($tokens);
				array_shift($tokens);
				array_shift($tokens);
			}
		}
	}

	function parse_field_length_decimals(&$tokens, &$f){
		if (count($tokens) >= 5){
			if ($tokens[0] == '(' && $tokens[2] == ',' && $tokens[4] == ')'){
				$f['length'] = $tokens[1];
				$f['decimals'] = $tokens[3];
				array_shift($tokens);
				array_shift($tokens);
				array_shift($tokens);
				array_shift($tokens);
				array_shift($tokens);
			}
		}
	}

	function parse_field_binary(&$tokens, &$f){
		if (count($tokens) >= 1){
			if (StrToUpper($tokens[0]) == 'BINARY'){
				$f['binary'] = true;
				array_shift($tokens);
			}
		}
	}

	function parse_field_unsigned(&$tokens, &$f){
		if (count($tokens) >= 1){
			if (StrToUpper($tokens[0]) == 'UNSIGNED'){
				$f['unsigned'] = true;
				array_shift($tokens);
			}
		}
	}

	function parse_field_zerofill(&$tokens, &$f){
		if (count($tokens) >= 1){
			if (StrToUpper($tokens[0]) == 'ZEROFILL'){
				$f['zerofill'] = true;
				array_shift($tokens);
			}
		}
	}

	function parse_field_charset(&$tokens, &$f){
		if (count($tokens) >= 1){
			if (StrToUpper($tokens[0]) == 'CHARACTER SET'){
				$f['character_set'] = $this->decode_identifier($tokens[1]);
				array_shift($tokens);
				array_shift($tokens);
			}
		}
	}

	function parse_field_collate(&$tokens, &$f){
		if (count($tokens) >= 1){
			if (StrToUpper($tokens[0]) == 'COLLATE'){
				$f['collation'] = $this->decode_identifier($tokens[1]);
				array_shift($tokens);
				array_shift($tokens);
			}
		}
	}

	function parse_value_list(&$tokens){
		if ($tokens[0] != '(') return null;
		array_shift($tokens);

		$values = array();
		while (count($tokens)){

			if ($tokens[0] == ')'){
				array_shift($tokens);
				return $values;
			}

			$values[] = $this->decode_value(array_shift($tokens));

			if ($tokens[0] == ')'){
				array_shift($tokens);
				return $values;
			}

			if ($tokens[0] == ','){
				array_shift($tokens);
			}else{
				# error
				return $values;
			}
		}
	}

	function decode_identifier($token){
		if ($token[0] == '`'){
			return substr($token, 1, -1);
		}
		return $token;
	}

	function decode_value($token){

		#
		# decode strings
		#

		if ($token[0] == "'" || $token[0] == '"'){
			$map = array(
				'n'	=> "\n",
				'r'	=> "\r",
				't'	=> "\t",
			);
			$out = '';
			for ($i=1; $i<strlen($token)-1; $i++){
				if ($token[$i] == '\\'){
					if ($map[$token[$i+1]]){
						$out .= $map[$token[$i+1]];
					}else{
						$out .= $token[$i+1];
					}
					$i++;
				}else{
					$out .= $token[$i];
				}
			}
			return $out;
		}

		return $token;
	}
}


