# Buildin optimization guide

  - configure php opcode cache 
  - use optimized autoload file
  - enabling symfony2 caching class loader
  - configured doctrine cache driver (Redis)
  - implement HttpProxy

# Opcache
OPcache can only be compiled as a shared extension. If you have disabled the building of default extensions with --disable-all , you must compile PHP with the --enable-opcache option for OPcache to be available.

Once compiled, you can use the zend_extension configuration directive to load the OPcache extension into PHP. This can be done with zend_extension=/full/path/to/opcache.so on non-Windows platforms, and zend_extension=C:\path\to\php_opcache.dll on Windows.
### Warning
> If you want to use OPcache with » Xdebug, you must load OPcache before Xdebug.
### ExampleConfig
<pre><code>zend_extension = "C:/Server/web/bin/php/php5.5.12/ext/php_opcache.dll"
;Determines if Zend OPCache is enabled
opcache.enable=1
;Determines if Zend OPCache is enabled for the CLI version of PHP
opcache.enable_cli=1
;The OPcache shared memory storage size.
opcache.memory_consumption=128
; The amount of memory for interned strings in Mbytes.
opcache.interned_strings_buffer=8
; The maximum number of keys (scripts) in the OPcache hash table.
; Only numbers between 200 and 100000 are allowed.
opcache.max_accelerated_files=4000
; How often (in seconds) to check file timestamps for changes to the shared
; memory storage allocation. ("1" means validate once per second, but only
; once per request. "0" means always validate)
opcache.revalidate_freq=60
; If enabled, a fast shutdown sequence is used for the accelerated code
opcache.fast_shutdown=1
</code></pre>
###Notice

You can use following config as performance feature, then you must perform a clean opcache with after changing existing code<pre><code>opcache.enable_file_override=1
</code></pre>
Invalidation<pre><code>boolean opcache_invalidate ( string $script [, boolean $force = FALSE ] )
</code></pre>

### Optimizing autoload
Execute<pre><code>php composer.phar dumpautoload -o
</code></pre>after comoser install. This creates a "classmap" mapping each class to its file in a big array. When you use an optimized autoloader, you don't perform any calls to file_exists so your application is faster.

### Enable ApcClassLoader
Uncomment following lines in /path/web/app.php (or app_dev.php)
<pre><code>$loader = new ApcClassLoader('sf2', $loader);
$loader->unregister();
$loader->register(true);
</code></pre>