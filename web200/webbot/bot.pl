use Selenium::Remote::Driver;
 
my $driver = new Selenium::Remote::Driver( 'javascript' => 1 );
$driver->get('http://90.157.117.82:8088/web200/');
$driver->quit();
sleep(20);