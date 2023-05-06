
# JasperReports for PHP

Package to generate reports with [JasperReports 6](http://community.jaspersoft.com/project/jasperreports-library) library through [JasperStarter v3.6.2](http://jasperstarter.sourceforge.net/) command-line tool.

## Install

```
composer require lopezsoft/JasperPHP
```

## Introduction

This package aims to be a solution to compile and process JasperReports (.jrxml & .jasper files).

### Why?

Did you ever had to create a good looking Invoice with a lot of fields for your great web app?

I had to, and the solutions out there were not perfect. Generating *HTML* + *CSS* to make a *PDF*? WTF? That doesn't make any sense! :)

Then I found **JasperReports** the best open source solution for reporting.

### What can I do with this?

Well, everything. JasperReports is a powerful tool for **reporting** and **BI**.

**From their website:**

> The JasperReports Library is the world's most popular open source reporting engine. It is entirely written in Java and it is able to use data coming from any kind of data source and produce pixel-perfect documents that can be viewed, printed or exported in a variety of document formats including HTML, PDF, Excel, OpenOffice and Word.

I recommend using [Jaspersoft Studio](http://community.jaspersoft.com/project/jaspersoft-studio) to build your reports, connect it to your datasource (ex: MySQL), loop thru the results and output it to PDF, XLS, DOC, RTF, ODF, etc.

*Some examples of what you can do:*

* Invoices
* Reports
* Listings

## Examples

### The *Hello World* example.

Go to the examples directory in the root of the repository (`vendor/lopezsoft/jasperphp/examples`).
Open the `hello_world.jrxml` file with iReport or with your favorite text editor and take a look at the source code.

#### Compiling

First we need to compile our `JRXML` file into a `JASPER` binary file. We just have to do this one time.

**Note:** You don't need to do this step if you are using *Jaspersoft Studio*. You can compile directly within the program.

```php
JasperStartet::compile(base_path('/vendor/lopezsoft/JasperPHP/examples/hello_world.jrxml'))->execute();
```

This command will compile the `hello_world.jrxml` source file to a `hello_world.jasper` file.

**Note:** If you are using Laravel 4 run `php artisan tinker` and copy & paste the command above.

#### Processing

Now lets process the report that we compile before:

```php
JasperStartet::process(
	base_path('/vendor/lopezsoft/JasperPHP/examples/hello_world.jasper'),
	false,
	array('pdf', 'rtf'),
	array('php_version' => phpversion())
)->execute();
```

Now check the examples folder! :) Great right? You now have 2 files, `hello_world.pdf` and `hello_world.rtf`.

Check the *API* of the  `compile` and `process` functions in the file `src/JasperPHP/JasperStartet.php` file.

#### Listing Parameters

Querying the jasper file to examine parameters available in the given jasper report file:

```php
$output = JasperStartet::list_parameters(
		base_path('/vendor/lopezsoft/JasperPHP/examples/hello_world.jasper')
	)->execute();

foreach($output as $parameter_description)
	echo $parameter_description;
```

### Advanced example

We can also specify parameters for connecting to database:

```php
JasperStartet::process(
    base_path('/vendor/lopezsoft/JasperPHP/examples/hello_world.jasper'),
    false,
    array('pdf', 'rtf'),
    array('php_version' => phpversion()),
    array(
      'driver' => 'postgres',
      'username' => 'vagrant',
      'host' => 'localhost',
      'database' => 'samples',
      'port' => '5433',
    )
  )->execute();
```

## Requirements

* Java JDK 1.8
* PHP 7.4
* PHP [exec()](http://php.net/manual/function.exec.php) function
* [optional] [Mysql Connector](http://dev.mysql.com/downloads/connector/j/) (if you want to use database)
* [optional] [Jaspersoft Studio](http://community.jaspersoft.com/project/jaspersoft-studio) (to draw and compile your reports)


## Installation

### Java

Check if you already have Java installed:

```
$ java -version
openjdk version "1.8.0_242"
OpenJDK Runtime Environment (AdoptOpenJDK)(build 1.8.0_242-b08)
OpenJDK 64-Bit Server VM (AdoptOpenJDK)(build 25.242-b08, mixed mode)
```

If you get:

	command not found: java

Then install it with: (Ubuntu/Debian)

	$ sudo apt-get install default-jdk

Now run the `java -version` again and check if the output is ok.

### Composer

Install [Composer](http://getcomposer.org) if you don't have it.

```
composer require lopezsoft/JasperPHP
```

Or in your `composer.json` file add:

```javascript
{
    "require": {
		"lopezsoft/JasperPHP": "^2.9.0",
    }
}
```

And the just run:

	composer update

and thats it.


Now you will have the `JasperStartet` alias available.

### MySQL

We ship the [MySQL connector](http://dev.mysql.com/downloads/connector/j/) (v5.1.45) in the `/src/JasperStarter/jdbc/` directory.

### PostgreSQL

We ship the [PostgreSQL](https://jdbc.postgresql.org/) (v9.4-1212.jre6) in the `/src/JasperStarter/jdbc/` directory.

Note: Laravel uses `pgsql` driver name instead of `postgres`.

### SQLite

We ship the [SQLite](https://www.sqlite.org/) (version v056, based on SQLite 3.6.14.2) in the `/src/JasperStarter/jdbc/` directory.

```
array(
    'driver' => 'generic',
    'jdbc_driver' => 'org.sqlite.JDBC',
    'jdbc_url' => 'jdbc:sqlite:/database.sqlite'
)
```


### JSON

Source file example:

```json
{
    "result":{
        "id":26,
        "reference":"0051711080021460005",
        "account_id":1,
        "user_id":2,
        "date":"2017-11-08 00:21:46",
        "type":"",
        "gross":138,
        "discount":0,
        "tax":4.08,
        "nett":142.08,
        "details":[
            {"id":26, "line": 1, "product_id": 26 },
        ]
    },
    "options":{
        "category":[
            {"id":3,"name":"Hair care","service":0,"user_id":1, },
        ],
        "default":{
            "id":1,"name":"I Like Hairdressing",
            "description":null,
            "address":null,
            "website":"https:\/\/www.ilikehairdressing.com",
            "contact_number":"+606 601 5889",
            "country":"MY",
            "timezone":"Asia\/Kuala_Lumpur",
            "currency":"MYR",
            "time_format":"24-hours",
            "user_id":1
        }
    }
}
```

Using Laravel:

```php
	public function generateReceipt($id) {

        $datafile = base_path('/storage/jasper/data.json');
        $output = base_path('/storage/jasper/data'); //indicate the name of the output PDF
        JasperStartet::process(
                    base_path('/resources/reports/taxinvoice80.jrxml'),
                    $output,
                    array("pdf"),
                    array("msg"=>"Tax Invoice"),
                    array("driver"=>"json", "json_query" => "data", "data_file" =>  $datafile)  
                )->execute();
     }
```

Some hack to JasperReport datasource is required. You need to indicate datasource expression for each table, list, and subreport.

```xml
	<datasetRun subDataset="invoice_details" uuid="a91cc22b-9a3f-45eb-9b35-244890d35fc7">
            <dataSourceExpression>
	       <![CDATA[((net.sf.jasperreports.engine.data.JsonDataSource)$P{REPORT_DATA_SOURCE}).subDataSource("result.details")]]>
	    </dataSourceExpression>
	</datasetRun>
```

## Performance

Depends on the complexity, amount of data and the resources of your machine (let me know your use case).

I have a report that generates a *Invoice* with a DB connection, images and multiple pages and it takes about **3/4 seconds** to process. I suggest that you use a worker to generate the reports in the background.

## Thanks

Thanks to [Cenote GmbH](http://www.cenote.de/) for the [JasperStarter](http://jasperstarter.sourceforge.net/) tool.

## Questions?

Drop me a line on Github [lopezsoft](https://github.com/lopezsoft/JasperPHP/issues).

## License

MIT
