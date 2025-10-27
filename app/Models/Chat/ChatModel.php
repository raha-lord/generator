<?php

namespace App\Models\Chat;

use Illuminate\Database\Eloquent\Model;

abstract class ChatModel extends Model
{
    /**
     * Get the database connection name.
     * Override to prevent Laravel from parsing schema.table as connection.table
     */
    public function getConnectionName()
    {
        // Always return the default connection, don't parse table name
        return $this->connection;
    }

    /**
     * Get the table name with schema prefix.
     */
    public function getTable()
    {
        // Return table name as-is, including schema prefix
        return $this->table ?? 'chat.' . str_replace('\\', '', class_basename($this));
    }
}
