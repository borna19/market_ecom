<?php
// Helper functions to map users to vendors and vice versa. Keep simple and robust.

function getVendorIdForUser($conn, $user_id) {
    // Validate input
    $user_id = (int) $user_id;
    if ($user_id <= 0) return 0;

    // Try to find existing vendor entry
    $stmt = mysqli_prepare($conn, "SELECT id FROM vendors WHERE user_id = ? LIMIT 1");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'i', $user_id);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        if ($res && mysqli_num_rows($res) === 1) {
            $row = mysqli_fetch_assoc($res);
            mysqli_stmt_close($stmt);
            return (int) $row['id'];
        }
        mysqli_stmt_close($stmt);
    } else {
        // Prepare failed - possibly vendors table doesn't exist or has different schema
        // We try to create a vendor using user_id anyway later if possible.
    }

    // If no vendor row, try to create one with minimal data
    // First, check if vendors table exists and has a user_id column
    $check = mysqli_query($conn, "SHOW TABLES LIKE 'vendors'");
    if (!$check || mysqli_num_rows($check) === 0) {
        // vendors table not found - cannot map
        return 0;
    }

    // Attempt to create a vendor row
    // We'll check if vendors table has a 'user_id' column
    $col_check = mysqli_query($conn, "SHOW COLUMNS FROM vendors LIKE 'user_id'");
    if ($col_check && mysqli_num_rows($col_check) > 0) {
        // Try to insert a minimal vendor row. If that fails due to additional NOT NULL columns,
        // attempt to include name and email from users table where possible.
        $ins = mysqli_prepare($conn, "INSERT INTO vendors (user_id) VALUES (?)");
        if ($ins) {
            mysqli_stmt_bind_param($ins, 'i', $user_id);
            if (mysqli_stmt_execute($ins)) {
                $new_id = mysqli_insert_id($conn);
                mysqli_stmt_close($ins);
                return (int) $new_id;
            }
            mysqli_stmt_close($ins);
        }

        // If minimal insert failed, fetch vendor columns and try to insert with name/email if available.
        $cols_res = mysqli_query($conn, "SHOW COLUMNS FROM vendors");
        $cols = [];
        while ($c = mysqli_fetch_assoc($cols_res)) {
            $cols[] = $c['Field'];
        }

        // Prepare data from users table
        $user_stmt = mysqli_prepare($conn, "SELECT name, email FROM users WHERE id = ? LIMIT 1");
        if ($user_stmt) {
            mysqli_stmt_bind_param($user_stmt, 'i', $user_id);
            mysqli_stmt_execute($user_stmt);
            $user_res = mysqli_stmt_get_result($user_stmt);
            $user_row = $user_res ? mysqli_fetch_assoc($user_res) : null;
            mysqli_stmt_close($user_stmt);
        } else {
            $user_row = null;
        }

        // Build insert dynamically if possible
        $insert_cols = [];
        $insert_vals = [];
        $placeholders = [];
        if (in_array('user_id', $cols)) {
            $insert_cols[] = 'user_id';
            $insert_vals[] = $user_id;
            $placeholders[] = '?';
        }
        if (in_array('name', $cols) && !empty($user_row['name'])) {
            $insert_cols[] = 'name';
            $insert_vals[] = $user_row['name'];
            $placeholders[] = '?';
        }
        if (in_array('email', $cols) && !empty($user_row['email'])) {
            $insert_cols[] = 'email';
            $insert_vals[] = $user_row['email'];
            $placeholders[] = '?';
        }

        if (!empty($insert_cols)) {
            $sql = 'INSERT INTO vendors (' . implode(',', $insert_cols) . ') VALUES (' . implode(',', $placeholders) . ')';
            $ins2 = mysqli_prepare($conn, $sql);
            if ($ins2) {
                // bind params dynamically using call_user_func_array and references
                $types = '';
                foreach ($insert_vals as $v) {
                    $types .= is_int($v) ? 'i' : 's';
                }
                // Prepare parameters as references
                $refs = array();
                $refs[] = &$types;
                foreach ($insert_vals as $k => $v) {
                    $refs[] = &$insert_vals[$k];
                }
                call_user_func_array(array($ins2, 'bind_param'), $refs);
                if (mysqli_stmt_execute($ins2)) {
                    $new_id = mysqli_insert_id($conn);
                    mysqli_stmt_close($ins2);
                    return (int) $new_id;
                }
                mysqli_stmt_close($ins2);
            }
        }
    }

    // As a last resort, return 0 to indicate no vendor mapping
    return 0;
}

?>