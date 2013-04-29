# TheBigDB PHP Wrapper

A simple PHP wrapper for making requests to the API of [TheBigDB.com](http://thebigdb.com). [Full API documentation](http://developers.thebigdb.com/api).

## Simple usage

First, you need to initialize the class with:
    
    require("thebigdb.php");
    $thebigdb = new TheBigDB;

The following actions return a dict object from the parsed JSON the server answered.


### Search \([api doc](http://developers.thebigdb.com/api#statements-search)\)
  
    # Usage: $thebigdb->search($nodes, $other_params = array());
    $thebigdb->search("iPhone", array("per_page" => 1));
    $thebigdb->search(array(array("match" => "James"), "job", "President of the United States"));
    $thebigdb->search(array(array("match" => "Facebook"), "job", array("match" => "Executive")));

### Create \([api doc](http://developers.thebigdb.com/api#statements-create)\)
    
    # Usage: $thebigdb->create($nodes, $other_params = array());
    $thebigdb->create(array("iPhone 5", "weight", "112 grams"));
    $thebigdb->create(array("Bill Clinton", "job", "President of the United States"), array("period" => array("from" => "1993-01-20 12:00:00", "to" => "2001-01-20 12:00:00")))

### Show \([api doc](http://developers.thebigdb.com/api#statements-show)\), Upvote \([api doc](http://developers.thebigdb.com/api#statements-upvote)\) and Downvote \([api doc](http://developers.thebigdb.com/api#statements-downvote)\)

    $thebigdb->show("id-of-the-sentence");

    $thebigdb->upvote("id-of-the-sentence"); # don't forget to set your API key
    $thebigdb->downvote("id-of-the-sentence"); # don't forget to set your API key

That's it!

## Response

Each action returns an object translating the JSON answered by the server.  
Example:
    
    # Will print the id of the first statement matching that search
    $response = $thebigdb->search(array(array("match" => "James"), "job", "President of the United States"));
    var_dump($response->statements[0]->id);

## Other Features

You can access other parts of the API in the same way as statements:
    
    # $thebigdb->user($action, $params);
    # Examples
    $response = $thebigdb->user("show", array("login" => "christophe"));
    $response->user->karma;


## Requirements

* PHP 5.2+
* curl extension

## Contributing

Don't hesitate to send a pull request !

## License

This software is distributed under the MIT License. Copyright (c) 2013, Christophe Maximin <christophe@thebigdb.com>