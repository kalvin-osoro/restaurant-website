<?php
return ['default'=>env('CACHE_STORE','database'),'stores'=>['database'=>['driver'=>'database','connection'=>null,'table'=>'tbl_cache','lock_connection'=>null,'lock_table'=>'tbl_cache_locks']],'prefix'=>env('CACHE_PREFIX','lexora_legal_cache')];
