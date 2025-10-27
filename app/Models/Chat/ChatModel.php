<?php

namespace App\Models\Chat;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Base model for all Chat schema models.
 *
 * This class handles PostgreSQL schema.table notation correctly
 * by preventing Laravel from parsing 'chat.table' as 'connection.table'.
 */
abstract class ChatModel extends Model
{
    /**
     * Get the database connection for the model.
     *
     * Override to prevent Laravel from parsing schema.table as connection.table
     */
    public function getConnectionName()
    {
        return $this->connection ?? config('database.default');
    }

    /**
     * Get the table associated with the model.
     *
     * Returns the table name as-is to preserve schema.table notation.
     */
    public function getTable()
    {
        return $this->table ?? 'chat.' . Str::snake(class_basename($this)) . 's';
    }
}
