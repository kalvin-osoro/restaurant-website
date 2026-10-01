<?php
return ['default'=>env('CACHE_STORE','database'),'stores'=>['database'=>['driver'=>'database','connection'=>null,'table'=>'cache','lock_connection'=>null,'lock_table'=>null]],'prefix'=>env('CACHE_PREFIX','restaurant_cache')];
