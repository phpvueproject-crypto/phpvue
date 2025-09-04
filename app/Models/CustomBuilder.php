<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

class CustomBuilder extends Builder {
    protected $joins = [];
    protected $leftJoins = [];

    public function safeJoin($table, $first, $operator = null, $second = null, $type = 'inner', $where = false) {
        if(in_array($table, $this->joins)) {
            return $this; // 如果已經 join 過，就直接返回
        }

        $this->joins[] = $table; // 記錄已經 join 的表
        return $this->join($table, $first, $operator, $second, $type, $where);
    }

    public function safeLeftJoin($table, $first, $operator = null, $second = null) {
        if(in_array($table, $this->leftJoins)) {
            return $this; // 如果已經 join 過，就直接返回
        }

        $this->leftJoins[] = $table; // 記錄已經 join 的表
        return $this->leftJoin($table, $first, $operator, $second);
    }
}
