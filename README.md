# PHP Blockchain
<p align="center">
<a href="#backers" alt="MIT License"><img src="https://img.shields.io/github/license/mashape/apistatus.svg"/></a>
</p>

An object-oriented PHP library for creating a simple blockchain easily.

Original code (august 2015) by Marty Anstey (https://marty.anstey.ca/), on [Github](https://github.com/rhondle/BlockChain).  
The code has been improved and adapted to an object-oriented library.

## Characteristics

- SHA256 hash algorithm.
- Record any data.
- File-based (the blockchain lives in two files only).
- PHP >= 7.1 (verified).

## Installation

````bash
composer require flavienbwk/blockchain-php
````

## Usage

### Adding a block.

Our blockchain will be saved in the `blockchain.dat` file for the example.

````php
require 'vendor/autoload.php';

$Blockchain = new \flavienbwk\BlockchainPHP\Blockchain();
$block = $Blockchain->addBlock("blockchain.dat", "What you want to put in the blockchain");
````

You can now get the data of your block :

````php
$block->hasError();     // Returns true or false if there was an error while adding the block.
$block->getMessage();   // Returns the error message.
$block->getHash();      // Returns the hash (SHA256) of the block.
$block->getPrevHash();  // Returns the hash (SHA256) of the block added before this one.
$block->getData();      // Returns the data stored in the block.
$block->getPosition();  // Returns the height/position of the block in the blockchain.
$block->getJson();      // Returns a JSON associative array with all the data of the block.
// ...
// Go to /src/Block.php to see all the getters.
````

You can get the data of one block by its hash or previous hash block :

````php
$Blockchain->getBlockByHash("blockchain.dat", "INSERT_THE_BLOCK_HASH_HERE");
$Blockchain->getBlockByPrevHash("blockchain.dat", "INSERT_THE_BLOCK_HASH_HERE");
````

### Printing all your blockchain.

````php
$Blockchain = new \flavienbwk\BlockchainPHP\Blockchain();

$all = $Blockchain->getBlockchain("blockchain.dat");
var_dump($all);
````

As 3 blocks has been added, it will display :

````php
[  
   {  
      "position":1,
      "header":127,
      "magic":"d5e8a97f",
      "version":1,
      "timestamp":1528658690,
      "prevhash":"0000000000000000000000000000000000000000000000000000000000000000",
      "hash":"ef6ecc71fc1570e7fbebf3d5d24f3d396f71e5588a5a5d930ef6d6118443095d",
      "datalen":15,
      "data":"{\"id\":\"BLABLA\"}"
   },
   {  
      "position":2,
      "header":127,
      "magic":"d5e8a97f",
      "version":1,
      "timestamp":1528658691,
      "prevhash":"ef6ecc71fc1570e7fbebf3d5d24f3d396f71e5588a5a5d930ef6d6118443095d",
      "hash":"cec0f91c9bdca41d3356508f3eaefdeb302b92e111c889d1743350d9e0912710",
      "datalen":15,
      "data":"{\"id\":\"BLABLA\"}"
   },
   {  
      "position":3,
      "header":127,
      "magic":"d5e8a97f",
      "version":1,
      "timestamp":1528658692,
      "prevhash":"cec0f91c9bdca41d3356508f3eaefdeb302b92e111c889d1743350d9e0912710",
      "hash":"555905b331a019bed206b772dd191e6ad2e7cb263e6c9e610222c1afd4c8b0c9",
      "datalen":15,
      "data":"{\"id\":\"BLABLA\"}"
   }
]
````
