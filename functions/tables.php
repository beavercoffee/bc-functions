<?php

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('_bc_table_column')){
    function _bc_table_column($table = '', $column = ''){
        $columns = _bc_table_columns($table);
        if(!array_key_exists($column, $columns)){
            return null;
        }
        return $columns[$column];
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('_bc_table_column_exists')){
    function _bc_table_column_exists($table = '', $column = ''){
        $columns = _bc_table_column($table);
        return array_key_exists($column, $columns);
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('_bc_table_columns')){
    function _bc_table_columns($table = ''){
        global $wpdb;
        $found = false;
        $columns = wp_cache_get($table, 'bc_custom_table_columns');
        if(true === $found){
            return $columns;
        }
        if(_bc_table_exists($table)){
            $query = "DESC `{$table}`";
            $columns = $wpdb->get_results($query, OBJECT_K);
        } else {
            $columns = [];
        }
        wp_cache_set($table, $columns, 'bc_custom_table_columns');
        return $columns;
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('_bc_table_default_values')){
    function _bc_table_default_values($table = ''){
        $columns = _bc_table_columns($table);
        return wp_list_pluck($columns, 'Default');
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('_bc_table_exists')){
    function _bc_table_exists($table = ''){
        global $wpdb;
        $found = false;
        $exists = wp_cache_get($table, 'bc_custom_table');
        if(true === $found){
            return $exists;
        }
        $query = $wpdb->prepare('SHOW TABLES LIKE %s', $wpdb->esc_like($table));
        $exists = ($table === $wpdb->get_var($query));
        wp_cache_set($table, $exists, 'bc_custom_table');
        return $exists;
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('_bc_table_invalid_columns')){
    function _bc_table_invalid_columns($table = '', $data = []){
        $columns = _bc_table_columns($table);
        $invalid = [];
        foreach($data as $key => $value){
            if(array_key_exists($key, $columns)){
                if(null === $value and 'NO' === $columns[$key]->Null){
                    $invalid[] = $key;
                }
            } else {
                $invalid[] = $key;
            }
        }
        return $invalid;
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('_bc_table_is_auto_increment')){
    function _bc_table_is_auto_increment($table = ''){
        $id = _bc_table_column($table, 'ID');
        if(null === $id){
            return false;
        }
        return (false !== strpos($id->Extra, 'auto_increment'));
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('_bc_table_is_valid')){
    function _bc_table_is_valid($table = ''){
    	if(!is_string($table)){
    		return bc_error('Invalid table name.');
    	}
    	if(!_bc_table_exists($table)){
    		return bc_error(sprintf('Table &#8220;%s&#8221; does not exist.', $table));
    	}
    	$id = _bc_table_column($table, 'ID');
        if(null === $id){
            return bc_error('ID column does not exist.');
        }
    	$invalid = [];
    	if('bigint(20) unsigned' !== $id->Type){
    		$invalid[] = 'type';
    	}
    	if('NO' !== $id->Null){
    		$invalid[] = 'null';
    	}
    	if('PRI' !== $id->Key){
    		$invalid[] = 'key';
    	}
    	if(null !== $id->Default){
    		$invalid[] = 'default';
    	}
    	if($invalid){
    		return bc_error(sprintf('Invalid column information: %s', implode(', ', $invalid)) . '.');
    	}
    	return $table;
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('_bc_table_missing_columns')){
    function _bc_table_missing_columns($table = '', $data = []){
        $missing = [];
        $not_null_columns = _bc_table_not_null_columns($table);
        if(array_key_exists('ID', $not_null_columns) and _bc_table_is_auto_increment($table)){
            unset($not_null_columns['ID']);
        }
        foreach($not_null_columns as $key => $default_value){
            if(!array_key_exists($key, $data) and null === $default_value){
                $missing[] = $key;
            }
        }
        return $missing;
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('_bc_table_not_null_columns')){
    function _bc_table_not_null_columns($table = ''){
        $columns = _bc_table_columns($table);
        return wp_filter_object_list($columns, [
            'Null' => 'NO',
        ], 'and', 'Default');
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('_bc_table_null_columns')){
    function _bc_table_null_columns($table = ''){
        $columns = _bc_table_columns($table);
        return wp_filter_object_list($columns, [
            'Null' => 'YES',
        ], 'and', 'Default');
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('_bc_table_row_exists')){
    function _bc_table_row_exists($table = '', $id = 0){
    	$row = bc_table_row($table, $id);
    	if(is_wp_error($row)){
    		return false;
    	}
        return (null !== $row);
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('bc_table_delete')){
    function bc_table_delete($table = '', $id = 0){
        global $wpdb;
    	$table = _bc_table_is_valid($table);
    	if(is_wp_error($table)){
    		return $table;
    	}
        $id = absint($id);
        if(0 === $id){
            return bc_error('Invalid row ID.');
        }
        if(!_bc_table_row_exists($table, $id)){
            return bc_error(sprintf('Row %d does not exist.', $id));
        }
    	$result = $wpdb->delete($table, [
            'ID' => $id,
        ]);
        if(false === $result){
            return bc_error($wpdb->last_error);
        }
        wp_cache_delete($id, "bc_custom_table_{$table}_rows");
        return true;
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('bc_table_insert')){
    function bc_table_insert($table = '', $data = [], $id = 0){
        global $wpdb;
        $table = _bc_table_is_valid($table);
    	if(is_wp_error($table)){
    		return $table;
    	}
        if(!is_array($data)){
    		return bc_error('Invalid data type.');
    	}
        $auto_increment = _bc_table_is_auto_increment($table);
        $id = absint($id);
        if(0 === $id){
            if(!$auto_increment){
                return bc_error('Invalid row ID.');
            }
            if(isset($data['ID'])){
                return bc_error('Data is inconsistent.');
            }
            if(!$data){
                return bc_error('Data is empty.');
            }
        } else {
            if($auto_increment){
    			return bc_error('Invalid row ID.');
    		}
            if(isset($data['ID'])){
                if($id !== absint($data['ID'])){
                    return bc_error('Data is inconsistent.');
                }
                unset($data['ID']);
            }
            if(!$data){
                return bc_error('Data is empty.');
            }
    		if(_bc_table_row_exists($table, $id)){
    			return bc_error(sprintf('Row %d already exists.', $id));
    		}
            $data['ID'] = $id;
        }
        $data = array_map('maybe_serialize', $data);
        $missing = _bc_table_missing_columns($table, $data);
        if($missing){
            return bc_error(sprintf('Missing column(s): %s', implode(', ', $missing)) . '.');
        }
        $invalid = _bc_table_invalid_columns($table, $data);
        if($invalid){
            return bc_error(sprintf('Invalid column(s): %s', implode(', ', $invalid)) . '.');
        }
    	$result = $wpdb->insert($table, $data);
        if(false === $result){
    		return bc_error($wpdb->last_error);
        }
        if(isset($data['ID'])){
    		return $data['ID'];
    	} else {
    		return $wpdb->insert_id;
    	}
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('bc_table_update')){
    function bc_table_update($table = '', $data = [], $id = 0){
        global $wpdb;
    	$table = _bc_table_is_valid($table);
    	if(is_wp_error($table)){
    		return $table;
    	}
        if(!is_array($data)){
    		return bc_error('Invalid data type.');
    	}
        $id = absint($id);
        if(0 === $id){
            return bc_error('Invalid row ID.');
        }
        if(isset($data['ID'])){
            if($id !== absint($data['ID'])){
                return bc_error('Data is inconsistent.');
            }
            unset($data['ID']);
        }
        if(!$data){
            return bc_error('Data is empty.');
        }
        if(!_bc_table_row_exists($table, $id)){
            return bc_table_insert($table, $data, $id);
        }
        $data = array_map('maybe_serialize', $data);
        $invalid = _bc_table_invalid_columns($table, $data);
        if($invalid){
    		return bc_error(sprintf('Invalid column(s): %s', implode(', ', $invalid)) . '.');
        }
    	$result = $wpdb->update($table, $data, [
            'ID' => $id,
        ]);
        if(false === $result){
    		return bc_error($wpdb->last_error);
        }
        wp_cache_delete($id, "bc_custom_table_{$table}_rows");
        return true;
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('bc_table_row')){
    function bc_table_row($table = '', $id = 0){
        global $wpdb;
    	$table = _bc_table_is_valid($table);
    	if(is_wp_error($table)){
    		return $table;
    	}
        $id = absint($id);
        if(0 === $id){
            return bc_error('Invalid row ID.');
        }
        $found = false;
        $row = wp_cache_get($id, "bc_custom_table_{$table}_rows");
        if(true === $found){
            return $row;
        }
        $query = $wpdb->prepare("SELECT * FROM `{$table}` WHERE ID = %d LIMIT 1", $id);
    	$row = $wpdb->get_row($query);
    	if(null !== $row){
    		$vars = get_object_vars($row);
    		foreach($vars as $var => $value){
    			$row->$var = maybe_unserialize($value);
    		}
    	}
        wp_cache_set($id, $row, "bc_custom_table_{$table}_rows");
        return $row;
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
