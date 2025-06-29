var/www/html # php artisan migrate:fresh

In Connection.php line 822:
                                                                                                                                                        
  SQLSTATE[08006] [7] could not translate host name "pgsql" to address: Name does not resolve (Connection: pgsql, SQL: select exists (select 1 from pg  
  _class c, pg_namespace n where n.nspname = current_schema() and c.relname = 'migrations' and c.relkind in ('r', 'p') and n.oid = c.relnamespace))     
                                                                                                                                                        

In Connector.php line 67:
                                                                                               
  SQLSTATE[08006] [7] could not translate host name "pgsql" to address: Name does not resolve  
                                                                                               