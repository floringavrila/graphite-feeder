# Graphite IBM Data Feeder

Graphite Data Feeder is a library that collects and aggregates metric values and then sends batches of data directly to Graphite data port.
The library is similar to StatsD and must be used as a singleton. 
Make sure that the library retention settings are exactly the same as the retention settings from graphite server(storage-schemas.conf).
## Installation
````
composer require gavrila/graphite-feeder
````
````
git clone https://github.com/floringavrila/graphite-feeder.git
````

## Usage
````
use  Gavrila\GraphiteFeeder;
...
$retentions = '15s:7d,1m:21d,15m:5y';

$connector = new GraphiteFeeder\Connector\Fsock(
    'graphite.host',
    '2003',
    'tcp'
);

$client = new GraphiteFeeder\Client($connector, $retentions);

$data = [
    new GraphiteFeeder\Entity\Data(
        'test.stats.process_name.elapsed_time',
        10,
        time()    
    ),
    new GraphiteFeeder\Entity\Data(
        'test.stats.process_name.elapsed_time',
        2.5,
        time()    
    ),
];

foreach ($data as $item) {
    $client->dataBuffer->add($item);
}
        
$written = $client->flushAllData();
````